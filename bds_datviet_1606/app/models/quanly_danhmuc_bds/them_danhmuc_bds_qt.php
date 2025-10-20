<?php
// File: ../../models/them_danhmuc.php
require_once "../../../config/database.php"; 
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_danh_muc = trim($_POST['ten_danh_muc'] ?? '');
    $ma_danh_muc = trim($_POST['ma_danh_muc'] ?? '');
    if (empty($ten_danh_muc) || empty($ma_danh_muc)) {
        $response['message'] = 'Vui lòng nhập đầy đủ Tên và Mã danh mục.';
    } elseif (!preg_match('/^[a-z0-9]+$/', $ma_danh_muc)) {
        $response['message'] = 'Mã danh mục chỉ được chứa chữ thường không dấu và số.';
    } else {
        try {
            $pdo = ketnoicsdl();
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM danh_muc WHERE ma_danh_muc = ?");
            $stmt_check->execute([$ma_danh_muc]);
            if ($stmt_check->fetchColumn() > 0) {
                 $response['message'] = 'Mã danh mục này đã tồn tại.';
            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO danh_muc (ten_danh_muc, ma_danh_muc) VALUES (?, ?)");
                if ($stmt_insert->execute([$ten_danh_muc, $ma_danh_muc])) {
                    $response = ['success' => true, 'message' => 'Đã thêm danh mục thành công!'];
                } else { $response['message'] = 'Không thể thêm danh mục vào CSDL.'; }
            }
        } catch (PDOException $e) {
            error_log("PDOException in them_danhmuc.php: " . $e->getMessage());
            $response['message'] = 'Lỗi cơ sở dữ liệu.';
        }
    }
}
echo json_encode($response);
?>