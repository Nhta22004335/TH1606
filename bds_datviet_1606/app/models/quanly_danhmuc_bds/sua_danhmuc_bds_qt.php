<?php
// File: ../../models/sua_danhmuc.php
require_once "../../../config/database.php"; 
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $ten_danh_muc = trim($_POST['ten_danh_muc'] ?? '');
    $ma_danh_muc = trim($_POST['ma_danh_muc'] ?? '');
    if (empty($id) || empty($ten_danh_muc) || empty($ma_danh_muc)) {
        $response['message'] = 'Thiếu thông tin cần thiết.';
    } elseif (!preg_match('/^[a-z0-9]+$/', $ma_danh_muc)) {
        $response['message'] = 'Mã danh mục chỉ được chứa chữ thường không dấu và số.';
    } else {
        try {
            $pdo = ketnoicsdl();
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM danh_muc WHERE ma_danh_muc = ? AND id != ?");
            $stmt_check->execute([$ma_danh_muc, $id]);
            if ($stmt_check->fetchColumn() > 0) {
                 $response['message'] = 'Mã danh mục này đã được sử dụng bởi một danh mục khác.';
            } else {
                $stmt_update = $pdo->prepare("UPDATE danh_muc SET ten_danh_muc = ?, ma_danh_muc = ? WHERE id = ?");
                if ($stmt_update->execute([$ten_danh_muc, $ma_danh_muc, $id])) {
                     if ($stmt_update->rowCount() > 0) { $response = ['success' => true, 'message' => 'Đã cập nhật danh mục!']; } 
                     else { $response['message'] = 'Không có gì thay đổi.'; $response['success'] = true; }
                } else { $response['message'] = 'Không thể cập nhật danh mục.'; }
            }
        } catch (PDOException $e) {
            error_log("PDOException in sua_danhmuc.php: " . $e->getMessage());
            $response['message'] = 'Lỗi cơ sở dữ liệu.';
        }
    }
}
echo json_encode($response);
?>