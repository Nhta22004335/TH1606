<?php
require_once "../../../config/database.php";

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $newStatus = $_POST['trang_thai'] ?? null;
    $validStatuses = ['chuaduyet', 'daduyet', 'huy'];

    if ($id && $newStatus && in_array($newStatus, $validStatuses)) {
        try {
            $pdo = ketnoicsdl();
            // Cập nhật trạng thái trong bảng bat_dong_san
            $stmt = $pdo->prepare("UPDATE bat_dong_san SET trang_thai = ? WHERE id = ?");
            
            if ($stmt->execute([$newStatus, $id])) {
                $response = [
                    'success' => true,
                    'message' => 'Cập nhật trạng thái thành công!',
                    'new_status' => $newStatus
                ];
            } else {
                $response['message'] = 'Cập nhật thất bại trong CSDL.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Dữ liệu ID hoặc trạng thái không hợp lệ.';
    }
}

echo json_encode($response);
?>