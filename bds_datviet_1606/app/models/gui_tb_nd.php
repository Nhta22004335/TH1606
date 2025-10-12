<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

require_once "../../config/database.php";
$pdo = ketnoicsdl();

$id = $data['id'] ?? '';
$loai          = $data['loai'] ?? '';
$tieu_de       = $data['tieude'] ?? '';
$noi_dung      = $data['noidung'] ?? '';

try {
    $stmt = $pdo->prepare("INSERT INTO thong_bao (id_nguoi_dung, loai, tieu_de, noi_dung, thoi_gian_gui)
                           VALUES (:id, :loai, :tieu_de, :noi_dung, NOW())");
    $stmt->execute([
        ':id' => $id,
        ':loai' => $loai,
        ':tieu_de' => $tieu_de,
        ':noi_dung' => $noi_dung
    ]);

    echo json_encode(['success' => true, 'message' => 'Thông báo đã được gửi!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>