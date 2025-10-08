<?php
// BẬT HIỂN THỊ LỖI ĐỂ DEBUG (NÊN XÓA KHI ĐƯA LÊN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$upload_dir = '../../../storage/pictures/avt/'; // Đảm bảo thư mục này tồn tại

if($_SERVER['REQUEST_METHOD']==='POST'){
    $ho_ten = trim($_POST['ho_ten']);
    $gioi_tinh = $_POST['gioi_tinh'];
    $dia_chi = trim($_POST['dia_chi']);
    $ngay_sinh = $_POST['ngay_sinh'];
    $mo_ta = trim($_POST['mo_ta']);
    $so_dt = trim($_POST['so_dt']);
    $id_nguoi_dung = $_SESSION['id_nguoi_dung'];
    $avt_file_name = $mg['avt']; // Giữ tên file cũ mặc định

    // 1. Xử lý Upload Avatar
    if (isset($_FILES['avt']) && $_FILES['avt']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['avt']['tmp_name'];
        $file_name = $_FILES['avt']['name'];
        $file_size = $_FILES['avt']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_ext, $allowed_exts)) {
            $error = "Chỉ chấp nhận file ảnh (JPG, PNG, GIF)!";
        } elseif ($file_size > $max_size) {
            $error = "Kích thước file không được quá 5MB!";
        } else {
            // Tạo tên file mới duy nhất (ví dụ: ID_timestamp.ext)
            $new_file_name = $id_nguoi_dung . '_' . time() . '.' . $file_ext;
            $dest_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                // Xóa file cũ nếu nó không phải là file mặc định (VD: default.jpg)
                if ($mg['avt'] && $mg['avt'] !== 'default.jpg' && file_exists($upload_dir . $mg['avt'])) {
                    unlink($upload_dir . $mg['avt']);
                }
                $avt_file_name = $new_file_name;
                
                // Cập nhật tên file AVT trong bảng nguoi_dung
                $stmt = $pdo->prepare("UPDATE nguoi_dung SET avt=:avt WHERE id=:id");
                $stmt->execute(['avt' => $avt_file_name, 'id' => $id_nguoi_dung]);
            } else {
                $error = "Lỗi khi di chuyển file ảnh. Kiểm tra quyền ghi!";
            }
        }
    }

    // 2. Xử lý Cập nhật Thông tin form
    if (!$error) { // Chỉ xử lý nếu không có lỗi upload file
        if(!isValidVietnamPhone($so_dt)) {
            $error="Số điện thoại không hợp lệ!";
        } else {
            // Kiểm tra xem có bất kỳ trường nào khác file ảnh bị thay đổi không
            $changed = ($mg['ho_ten']!=$ho_ten || $mg['gioi_tinh']!=$gioi_tinh ||
                        $mg['dia_chi']!=$dia_chi || $mg['ngay_sinh']!=$ngay_sinh ||
                        $mg['mo_ta']!=$mo_ta || $mg['so_dt']!=$so_dt || $mg['avt']!=$avt_file_name);
            
            if($changed){
                // Cập nhật bảng info_nguoi_dung
                $stmt=$pdo->prepare("UPDATE info_nguoi_dung SET ho_ten=:ho_ten, gioi_tinh=:gioi_tinh, dia_chi=:dia_chi, ngay_sinh=:ngay_sinh, mo_ta=:mo_ta WHERE id_nguoi_dung=:id");
                $stmt->execute(['ho_ten'=>$ho_ten,'gioi_tinh'=>$gioi_tinh,'dia_chi'=>$dia_chi,'ngay_sinh'=>$ngay_sinh,'mo_ta'=>$mo_ta,'id'=>$id_nguoi_dung]);
                
                // Cập nhật bảng nguoi_dung (Số ĐT)
                $stmt=$pdo->prepare("UPDATE nguoi_dung SET so_dt=:so_dt WHERE id=:id");
                $stmt->execute(['so_dt'=>$so_dt,'id'=>$id_nguoi_dung]);
                
                $success="Cập nhật thông tin thành công!";
                // Lấy lại dữ liệu mới nhất sau khi cập nhật
                $mg = getUserInfo($pdo, $id_nguoi_dung); 
            } else {
                 $error="Không có thay đổi nào được ghi nhận.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hồ sơ môi giới</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-5xl mx-auto mt-4 p-8 md:p-10 bg-white rounded-2xl shadow-2xl border border-gray-100">
    <h2 class="text-4xl md:text-4xl font-bold text-center mb-10 text-gray-700 tracking-tight">
        <i class="fa-solid fa-user-circle text-blue-600 mr-3"></i> Hồ sơ cá nhân
    </h2>

    <?php if ($success): ?>
        <div class="p-4 mb-6 text-sm text-green-700 bg-green-100 rounded-lg border border-green-200 flex items-center gap-3" role="alert">
            <i class="fa-solid fa-check-circle text-lg"></i>
            <span class="font-medium"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="p-4 mb-6 text-sm text-red-700 bg-red-100 rounded-lg border border-red-200 flex items-center gap-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <span class="font-medium"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="../../models/cn_trangcn.php" method="POST" enctype="multipart/form-data" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1 bg-gray-50 p-6 rounded-xl shadow-inner border border-gray-200 text-center space-y-4 h-full">
                <p class="text-xs uppercase font-bold text-gray-500 mb-4 border-b pb-2">Thông tin Cơ bản</p>

                <div class="relative inline-block">
                    <img src="../../../storage/pictures/avt/<?= htmlspecialchars($mg['avt']) ?>" 
                        alt="Avatar" class="w-32 h-32 object-cover rounded-full border-4 border-blue-400 mx-auto shadow-xl transition transform hover:scale-105">
                    
                    <label for="avt-upload" class="absolute bottom-0 right-0 p-2 text-gray-500 cursor-pointer hover:text-gray-700 transition">
                        <i class="fa-solid fa-camera"></i>
                    </label>

                    <input type="file" name="avt" class="hidden" id="avt-upload" accept="image/*">
                </div>

                <p class="text-2xl font-extrabold text-gray-800 mt-4"><?= htmlspecialchars($mg['ho_ten']) ?></p>
                <p class="text-sm text-gray-600 break-all"><i class="fa-solid fa-envelope text-blue-500 mr-2"></i> <?= htmlspecialchars($mg['email']) ?></p>
                <div class="pb-2">
                    <label class="block text-sm font-medium text-gray-700 text-left mb-1">Số điện thoại</label>
                    <input type="text" name="so_dt" value="<?= htmlspecialchars($mg['so_dt']) ?>"
                        class="w-full text-center px-4 py-2 border border-blue-300 rounded-lg bg-white 
                                focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-md transition">
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                
                <p class="text-xs uppercase font-bold text-gray-500 mb-4 border-b pb-2">Chi tiết Hồ sơ</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên</label>
                        <input type="text" name="ho_ten" value="<?= htmlspecialchars($mg['ho_ten']) ?>"
                                class="w-full px-4 py-2 border border-blue-300 rounded-lg bg-white outline-none
                                        focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giới tính</label>
                        <select name="gioi_tinh" 
                                class="w-full px-4 py-2 border border-blue-300 rounded-lg bg-white outline-none
                                        focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm transition">
                            <option value="nam" <?= $mg['gioi_tinh']=='nam'?'selected':'' ?>>Nam</option>
                            <option value="nu" <?= $mg['gioi_tinh']=='nu'?'selected':'' ?>>Nữ</option>
                            <option value="khac" <?= $mg['gioi_tinh']=='khac'?'selected':'' ?>>Khác</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày sinh</label>
                        <input type="date" name="ngay_sinh" value="<?= htmlspecialchars($mg['ngay_sinh']) ?>"
                                class="w-full px-4 py-2 border border-blue-300 rounded-lg bg-white outline-none
                                        focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                        <input type="text" name="dia_chi" value="<?= htmlspecialchars($mg['dia_chi']) ?>"
                                class="w-full px-4 py-2 border border-blue-300 rounded-lg bg-white outline-none
                                        focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả/Giới thiệu bản thân</label>
                    <textarea name="mo_ta" rows="4"
                                class="w-full px-4 py-3 border border-blue-300 rounded-lg bg-white outline-none
                                        focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm transition"><?= htmlspecialchars($mg['mo_ta']) ?></textarea>
                </div>
                
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 
                                text-white font-extrabold py-3 rounded-xl 
                                transition duration-300 transform hover:scale-[1.01] shadow-lg shadow-blue-200/50">
                    LƯU THAY ĐỔI VÀ CẬP NHẬT
                </button>
            </div>
        </div>
    </form>

</div>

</body>
</html>