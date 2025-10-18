<?php
// File: ../../models/xoa_danhmuc.php
require_once "../../config/database.php"; 
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        try {
            $pdo = ketnoicsdl();
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM bat_dong_san WHERE id_danh_muc = ?");
            $stmt_check->execute([$id]);
            $count = $stmt_check->fetchColumn();
            if ($count > 0) {
                $response['message'] = 'Không thể xóa danh mục đang được sử dụng (' . $count . ' BĐS).';
            } else {
                $stmt_delete = $pdo->prepare("DELETE FROM danh_muc WHERE id = ?");
                if ($stmt_delete->execute([$id])) {
                    if ($stmt_delete->rowCount() > 0) { $response = ['success' => true, 'message' => 'Đã xóa danh mục thành công.']; } 
                    else { $response['message'] = 'Không tìm thấy danh mục để xóa.'; }
                } else { $response['message'] = 'Lỗi khi thực thi lệnh xóa.'; }
            }
        } catch (PDOException $e) {
            error_log("PDOException in xoa_danhmuc.php: " . $e->getMessage());
            $response['message'] = 'Lỗi cơ sở dữ liệu. Không thể xóa.';
        }
    } else { $response['message'] = 'Thiếu ID của danh mục cần xóa.'; }
}
echo json_encode($response);
?>