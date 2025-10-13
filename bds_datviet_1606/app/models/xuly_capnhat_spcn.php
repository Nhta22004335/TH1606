<?php
// Fix: Removed non-breaking space characters ( ) that caused the parse error.

require_once "../../../config/database.php";
$pdo = ketnoicsdl();

session_start();
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
$id_bds = $_POST['id'] ?? null;

if (!$id_nguoi_dung || !$id_bds) {
    header("Location: ../../views/moi_gioi/sp_canhan.php?status=error&message=Thiếu thông tin.");
    exit;
}

// Cập nhật thông tin cơ bản
$sql = "UPDATE bat_dong_san SET 
            tieu_de = :tieu_de,
            hinh_thuc = :hinh_thuc,
            loai = :loai,
            gia = :gia,
            dien_tich = :dien_tich,
            khu_vuc = :khu_vuc,
            dia_chi = :dia_chi,
            mo_ta = :mo_ta,
            ngay_cap_nhat = NOW()
        WHERE id = :id AND id_nguoi_dung = :id_nguoi_dung";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':tieu_de' => $_POST['tieu_de'] ?? '',
    ':hinh_thuc' => $_POST['hinh_thuc'] ?? '',
    ':loai' => $_POST['loai'] ?? '',
    ':gia' => $_POST['gia'] ?? 0,
    ':dien_tich' => $_POST['dien_tich'] ?? 0,
    ':khu_vuc' => $_POST['khu_vuc'] ?? '',
    ':dia_chi' => $_POST['dia_chi'] ?? '',
    ':mo_ta' => $_POST['mo_ta'] ?? '',
    ':id' => $id_bds,
    ':id_nguoi_dung' => $id_nguoi_dung
]);

// === XỬ LÝ ẢNH UPLOAD ===
// Note: This block currently handles the main form submission AND image upload.
// If you are uploading via AJAX in sua_san_pham.php, ensure that the AJAX
// request is hitting this script and expects a JSON response, not a redirect.
if (!empty($_FILES['anh_bds']['name'][0])) {
    $uploadDir = '../../../storage/pictures/bds/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    foreach ($_FILES['anh_bds']['tmp_name'] as $key => $tmp_name) {
        $fileName = basename($_FILES['anh_bds']['name'][$key]);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Chỉ cho phép ảnh
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) continue;

        // Tạo tên file mới tránh trùng
        $newName = uniqid('bds_') . '.' . $ext;
        $filePath = $uploadDir . $newName;

        if (move_uploaded_file($tmp_name, $filePath)) {
            // Lưu vào bảng ảnh
            $sqlImg = "INSERT INTO hinh_anh_bds (id_bds, url, ngay_tao) VALUES (:id_bds, :url, NOW())";
            $stmtImg = $pdo->prepare($sqlImg);
            $stmtImg->execute([':id_bds' => $id_bds, ':url' => $newName]);
        }
    }
}



// Redirect về lại form với thông báo thành công
header("Location: ../../views/moi_gioi/sua_san_pham.php?id=$id_bds&status=success");
exit;
?>
