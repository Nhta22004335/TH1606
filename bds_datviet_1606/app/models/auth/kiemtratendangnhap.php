<?php
session_start();
header('Content-Type: application/json');

// Hàm trả về JSON
function json_response($data) {
    echo json_encode($data);
    exit;
}

// Kiểm tra quyền và dữ liệu
if (!isset($_SESSION['id_nguoi_dung'])) {
    json_response(['success' => false, 'message' => 'Phiên đăng nhập không hợp lệ.']);
}

$input = json_decode(file_get_contents('php://input'), true);
$id_nguoi_dung_session = $_SESSION['id_nguoi_dung'];
$tendangnhap_input = $input['tendangnhap'] ?? '';

if (empty($tendangnhap_input)) {
    json_response(['success' => false, 'message' => 'Tên đăng nhập không được để trống.']);
}

// Kết nối CSDL
require_once "../../../config/database.php";
try {
    $pdo = ketnoicsdl();
    // Kiểm tra xem tên đăng nhập có thuộc về người dùng đang đăng nhập không
    $stmt = $pdo->prepare("SELECT id FROM nguoi_dung WHERE id = :id AND ten_dang_nhap = :tendangnhap");
    $stmt->execute([':id' => $id_nguoi_dung_session, ':tendangnhap' => $tendangnhap_input]);
    
    if ($stmt->fetch()) {
        json_response(['success' => true]);
    } else {
        json_response(['success' => false, 'message' => 'Tên đăng nhập không đúng với tài khoản của bạn.']);
    }
} catch (PDOException $e) {
    error_log("Lỗi kiểm tra tên đăng nhập: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Lỗi hệ thống.']);
}
?>
