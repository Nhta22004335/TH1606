<?php

if(!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.html");
    exit;
}
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

function getUserInfo($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT nd.ten_dang_nhap, nd.email, nd.so_dt, nd.avt,
               info.ho_ten, info.gioi_tinh, info.dia_chi, info.ngay_sinh, info.mo_ta
        FROM nguoi_dung nd
        JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
        WHERE nd.id=:id
    ");
    $stmt->execute(['id'=>$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$mg = getUserInfo($pdo, $_SESSION['id_nguoi_dung']);

function isValidVietnamPhone($phone) {
    $validPrefixes = ['032','033','034','035','036','037','038','039','070','076','077','078','079',
                      '081','082','083','084','085','086','087','088','089','090','091','092','093','094','095','096','097','098','099'];
    if(str_starts_with($phone, '+84')) $phone = '0'.substr($phone,3);
    if(!preg_match('/^0\d{9}$/',$phone)) return false;
    return in_array(substr($phone,0,3),$validPrefixes);
}

$success = $error = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $ho_ten = trim($_POST['ho_ten']);
    $gioi_tinh = $_POST['gioi_tinh'];
    $dia_chi = trim($_POST['dia_chi']);
    $ngay_sinh = $_POST['ngay_sinh'];
    $mo_ta = trim($_POST['mo_ta']);
    $so_dt = trim($_POST['so_dt']);

    if(!isValidVietnamPhone($so_dt)) $error="Số điện thoại không hợp lệ!";
    else {
        $changed = ($mg['ho_ten']!=$ho_ten || $mg['gioi_tinh']!=$gioi_tinh ||
                    $mg['dia_chi']!=$dia_chi || $mg['ngay_sinh']!=$ngay_sinh ||
                    $mg['mo_ta']!=$mo_ta || $mg['so_dt']!=$so_dt);
        if($changed){
            $stmt=$pdo->prepare("UPDATE info_nguoi_dung SET ho_ten=:ho_ten, gioi_tinh=:gioi_tinh, dia_chi=:dia_chi, ngay_sinh=:ngay_sinh, mo_ta=:mo_ta WHERE id_nguoi_dung=:id");
            $stmt->execute(['ho_ten'=>$ho_ten,'gioi_tinh'=>$gioi_tinh,'dia_chi'=>$dia_chi,'ngay_sinh'=>$ngay_sinh,'mo_ta'=>$mo_ta,'id'=>$_SESSION['id_nguoi_dung']]);
            $stmt=$pdo->prepare("UPDATE nguoi_dung SET so_dt=:so_dt WHERE id=:id");
            $stmt->execute(['so_dt'=>$so_dt,'id'=>$_SESSION['id_nguoi_dung']]);
            $success="Cập nhật thông tin thành công!";
            $mg = getUserInfo($pdo, $_SESSION['id_nguoi_dung']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Trang Cá Nhân</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-4xl mx-auto mt-10 p-10 bg-white rounded-2xl shadow-2xl">
    <!-- Tiêu đề -->
    <h2 class="text-4xl font-extrabold text-center mb-8 
               bg-gradient-to-r from-blue-500 to-cyan-500 
               bg-clip-text text-transparent drop-shadow-lg">
        Trang Cá Nhân
    </h2>

    <!-- Avatar + Info -->
    <div class="flex items-center mb-8 p-5 bg-blue-50 rounded-xl shadow-inner">
        <img src="../../../storage/pictures/avt/<?= htmlspecialchars($mg['avt']) ?>" 
             alt="Avatar" class="w-28 h-28 rounded-full border-2 border-blue-200 mr-6 shadow-md">
        <div>
            <p class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($mg['ho_ten']) ?></p>
            <p class="text-gray-500"><?= htmlspecialchars($mg['email']) ?></p>
            <p class="text-gray-500">📞 <?= htmlspecialchars($mg['so_dt']) ?></p>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Họ tên -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Họ tên</label>
                <input type="text" name="ho_ten" value="<?= htmlspecialchars($mg['ho_ten']) ?>"
                       class="w-full px-4 py-2 border border-blue-200 rounded-lg bg-blue-50 
                              focus:bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none shadow-sm">
            </div>

            <!-- Giới tính -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Giới tính</label>
                <select name="gioi_tinh" 
                        class="w-full px-4 py-2 border border-blue-200 rounded-lg bg-blue-50 
                               focus:bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none shadow-sm">
                    <option value="nam" <?= $mg['gioi_tinh']=='nam'?'selected':'' ?>>Nam</option>
                    <option value="nu" <?= $mg['gioi_tinh']=='nu'?'selected':'' ?>>Nữ</option>
                    <option value="khac" <?= $mg['gioi_tinh']=='khac'?'selected':'' ?>>Khác</option>
                </select>
            </div>

            <!-- Ngày sinh -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Ngày sinh</label>
                <input type="date" name="ngay_sinh" value="<?= $mg['ngay_sinh'] ?>"
                       class="w-full px-4 py-2 border border-blue-200 rounded-lg bg-blue-50 
                              focus:bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none shadow-sm">
            </div>

            <!-- Số điện thoại -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Số điện thoại</label>
                <input type="text" name="so_dt" value="<?= htmlspecialchars($mg['so_dt']) ?>"
                       class="w-full px-4 py-2 border border-blue-200 rounded-lg bg-blue-50 
                              focus:bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none shadow-sm">
            </div>
        </div>

        <!-- Địa chỉ -->
        <div>
            <label class="block text-gray-700 font-medium mb-1">Địa chỉ</label>
            <input type="text" name="dia_chi" value="<?= htmlspecialchars($mg['dia_chi']) ?>"
                   class="w-full px-4 py-2 border border-blue-200 rounded-lg bg-blue-50 
                          focus:bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none shadow-sm">
        </div>

        <!-- Mô tả -->
        <div>
            <label class="block text-gray-700 font-medium mb-1">Mô tả</label>
            <textarea name="mo_ta" rows="4"
                      class="w-full px-4 py-2 border border-blue-200 rounded-lg bg-blue-50 
                             focus:bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none shadow-sm"><?= htmlspecialchars($mg['mo_ta']) ?></textarea>
        </div>

        <!-- Button -->
        <button type="submit"
                class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 
                       hover:from-cyan-500 hover:to-blue-500
                       text-white font-semibold py-3 rounded-lg 
                       transition duration-300 transform hover:scale-105 shadow-xl">
            💾 Cập nhật thông tin
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="index.php" class="text-blue-600 hover:underline font-medium">← Quay lại danh sách BĐS</a>
    </div>
</div>

</body>
</html>

