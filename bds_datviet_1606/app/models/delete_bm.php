<?php
require_once "../../../config/database.php";

header('Content-Type: application/json');

$pdo = ketnoicsdl();
$id = $_POST['id'] ?? '';

if (!$id) {
    echo json_encode(['status'=>'error','message'=>'ID không hợp lệ']);
    exit;
}

// Xóa tệp đính kèm trước nếu có
$stmt = $pdo->prepare("SELECT tep_dk FROM bieu_mau WHERE id = :id");
$stmt->execute([':id'=>$id]);
$file = $stmt->fetchColumn();
if ($file && file_exists("../../../storage/documents/$file")) {
    unlink("../../../storage/documents/$file");
}

// Xóa hồ sơ trong CSDL
$stmt = $pdo->prepare("DELETE FROM bieu_mau WHERE id = :id");
if($stmt->execute([':id'=>$id])){
    echo json_encode(['status'=>'success','message'=>'Xóa hồ sơ thành công']);
} else {
    echo json_encode(['status'=>'error','message'=>'Xóa hồ sơ thất bại']);
}
