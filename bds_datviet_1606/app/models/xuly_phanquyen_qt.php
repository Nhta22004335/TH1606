<?php
header('Content-Type: application/json');
require_once "../../config/database.php";

$response = ['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.'];

// Lấy dữ liệu JSON từ request body
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? null;
$role_ids = $data['role_ids'] ?? [];

if ($user_id && is_array($role_ids)) {
    $pdo = ketnoicsdl();
    try {
        // Bắt đầu một transaction để đảm bảo toàn vẹn dữ liệu
        $pdo->beginTransaction();

        // 1. Xóa tất cả các quyền cũ của người dùng này
        $stmt_delete = $pdo->prepare("DELETE FROM phan_quyen WHERE id_nguoi_dung = ?");
        $stmt_delete->execute([$user_id]);

        // 2. Thêm lại các quyền mới đã chọn
        // Chỉ thực hiện nếu người dùng chọn ít nhất 1 quyền
        if (!empty($role_ids)) {
            $stmt_insert = $pdo->prepare("INSERT INTO phan_quyen (id_nguoi_dung, id_quyen) VALUES (?, ?)");
            foreach ($role_ids as $role_id) {
                // Validate UUID format (đơn giản)
                if (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $role_id)) {
                    $stmt_insert->execute([$user_id, $role_id]);
                }
            }
        }

        // 3. Hoàn tất transaction
        $pdo->commit();

        $response['status'] = 'success';
        $response['message'] = 'Cập nhật quyền thành công!';

    } catch (Exception $e) {
        // Nếu có lỗi, rollback lại tất cả các thay đổi
        $pdo->rollBack();
        $response['message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>