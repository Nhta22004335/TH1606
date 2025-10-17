<?php
require_once "config/database.php"; // kết nối PDO
session_start();

$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
$id_bds = $_POST['id_bds'] ?? null;
$diem = $_POST['diem'] ?? null;
$binh_luan = $_POST['binh_luan'] ?? '';

if (!$id_nguoi_dung || !$id_bds || !$diem) {
    die("Thiếu thông tin đánh giá!");
}

// 1. Thêm vào bảng danh_gia_bds
$stmt = $pdo->prepare("INSERT INTO danh_gia_bds(id_nguoi_dung, id_bds, diem, binh_luan) 
                       VALUES(:id_nguoi_dung, :id_bds, :diem, :binh_luan) RETURNING id");
$stmt->execute([
    ':id_nguoi_dung' => $id_nguoi_dung,
    ':id_bds' => $id_bds,
    ':diem' => $diem,
    ':binh_luan' => $binh_luan
]);

$id_dg_bds = $stmt->fetchColumn();

// 2. Xử lý upload ảnh nếu có
if (!empty($_FILES['hinh_anh']['name'][0])) {
    foreach ($_FILES['hinh_anh']['tmp_name'] as $index => $tmp_name) {
        $filename = 'uploads/' . basename($_FILES['hinh_anh']['name'][$index]);
        move_uploaded_file($tmp_name, $filename);

        $stmt_img = $pdo->prepare("INSERT INTO hinh_anh_danh_gia_bds(id_dg_bds, url) 
                                   VALUES(:id_dg_bds, :url)");
        $stmt_img->execute([
            ':id_dg_bds' => $id_dg_bds,
            ':url' => $filename
        ]);
    }
}

header("Location: chi_tiet_bds.php?id=$id_bds"); // quay lại chi tiết BĐS
exit;
