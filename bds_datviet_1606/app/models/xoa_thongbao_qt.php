<?php
header('Content-Type: application/json');

require_once "../../config/database.php"; 

$pdo = ketnoicsdl();
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($data) || !isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu xóa không hợp lệ.']);
    exit;
}

$tb_id = $data['id'];

try {
    // Câu truy vấn xóa thông báo
    $sql_delete = "DELETE FROM thong_bao WHERE id = :id";
    $stmt = $pdo->prepare($sql_delete);
    $stmt->bindValue(':id', $tb_id, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Thông báo đã được xóa thành công.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy thông báo để xóa.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi thực thi câu lệnh xóa.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>