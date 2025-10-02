<?php

if(!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.html");
    exit;
}

require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Lấy thông tin môi giới
$stmt = $pdo->prepare("
    SELECT nd.ten_dang_nhap, nd.email, nd.so_dt, nd.avt, nd.anh_bia, nd.trang_thai, nd.hoat_dong,
           info.ho_ten, info.gioi_tinh, info.dia_chi, info.ngay_sinh, info.mo_ta
    FROM nguoi_dung nd
    JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
    WHERE nd.id=:id
");
$stmt->execute(['id'=>$_SESSION['id_nguoi_dung']]);
$mg = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý update thông tin
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = trim($_POST['ho_ten']);
    $gioi_tinh = $_POST['gioi_tinh'];
    $dia_chi = trim($_POST['dia_chi']);
    $ngay_sinh = $_POST['ngay_sinh'];
    $mo_ta = trim($_POST['mo_ta']);
    $so_dt = trim($_POST['so_dt']);

    // Update bảng info_nguoi_dung
    $stmt = $pdo->prepare("
        UPDATE info_nguoi_dung
        SET ho_ten=:ho_ten, gioi_tinh=:gioi_tinh, dia_chi=:dia_chi, ngay_sinh=:ngay_sinh, mo_ta=:mo_ta
        WHERE id_nguoi_dung=:id
    ");
    $stmt->execute([
        'ho_ten'=>$ho_ten,
        'gioi_tinh'=>$gioi_tinh,
        'dia_chi'=>$dia_chi,
        'ngay_sinh'=>$ngay_sinh,
        'mo_ta'=>$mo_ta,
        'id'=>$_SESSION['id_nguoi_dung']
    ]);

    // Update số điện thoại bảng nguoi_dung
    $stmt = $pdo->prepare("UPDATE nguoi_dung SET so_dt=:so_dt WHERE id=:id");
    $stmt->execute(['so_dt'=>$so_dt, 'id'=>$_SESSION['id_nguoi_dung']]);

}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ môi giới</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="max-w-2xl mx-auto p-6 bg-white shadow rounded mt-10">
    <h2 class="text-2xl font-semibold text-blue-600 mb-6">Hồ sơ môi giới</h2>
    
    <div class="flex items-center mb-6">
        <img src="../../../storage/pictures/avt/<?= htmlspecialchars($mg['avt']) ?>" alt="Avatar" class="w-24 h-24 rounded-full mr-4 border border-gray-300">
        <div>
            <p class="text-lg font-medium"><?= htmlspecialchars($mg['ho_ten']) ?></p>
            <p class="text-sm text-gray-500"><?= htmlspecialchars($mg['email']) ?></p>
        </div>
    </div>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block font-medium">Họ tên:</label>
            <input type="text" name="ho_ten" value="<?= htmlspecialchars($mg['ho_ten']) ?>" class="w-full px-3 py-2 border rounded">
        </div>

        <div>
            <label class="block font-medium">Giới tính:</label>
            <select name="gioi_tinh" class="w-full px-3 py-2 border rounded">
                <option value="nam" <?= $mg['gioi_tinh']=='nam'?'selected':'' ?>>Nam</option>
                <option value="nu" <?= $mg['gioi_tinh']=='nu'?'selected':'' ?>>Nữ</option>
                <option value="khac" <?= $mg['gioi_tinh']=='khac'?'selected':'' ?>>Khác</option>
            </select>
        </div>

        <div>
            <label class="block font-medium">Ngày sinh:</label>
            <input type="date" name="ngay_sinh" value="<?= $mg['ngay_sinh'] ?>" class="w-full px-3 py-2 border rounded">
        </div>

        <div>
            <label class="block font-medium">Địa chỉ:</label>
            <input type="text" name="dia_chi" value="<?= htmlspecialchars($mg['dia_chi']) ?>" class="w-full px-3 py-2 border rounded">
        </div>

        <div>
            <label class="block font-medium">Số điện thoại:</label>
            <input type="text" name="so_dt" value="<?= htmlspecialchars($mg['so_dt']) ?>" class="w-full px-3 py-2 border rounded">
        </div>

        <div>
            <label class="block font-medium">Mô tả ngắn:</label>
            <textarea name="mo_ta" class="w-full px-3 py-2 border rounded"><?= htmlspecialchars($mg['mo_ta']) ?></textarea>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Cập nhật thông tin</button>
    </form>

    <a href="index.php" class="inline-block mt-4 text-blue-600 hover:underline">Quay lại danh sách BĐS</a>
</div>
</body>
</html>
