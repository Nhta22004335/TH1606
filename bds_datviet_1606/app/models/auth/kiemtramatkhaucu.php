<?php
session_start();
header('Content-Type: application/json');

// Hàm trả về JSON
function json_response($data) {
    echo json_encode($data);
    exit;
}

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION['id_nguoi_dung'])) {
    json_response(['success' => false, 'message' => 'Phiên đăng nhập không hợp lệ.']);
}

// Lấy dữ liệu từ request
$input = json_decode(file_get_contents('php://input'), true);
$id_nguoi_dung = $_SESSION['id_nguoi_dung'];
$matkhaucu = $input['matkhaucu'] ?? '';
$tendangnhap = $input['tendangnhap'] ?? ''; // Lấy thêm tên đăng nhập

// Kiểm tra dữ liệu đầu vào
if (empty($matkhaucu) || empty($tendangnhap)) {
    json_response(['success' => false, 'message' => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu cũ.']);
}

// Kết nối CSDL và kiểm tra
require_once "../../../config/database.php";
try {
    $pdo = ketnoicsdl();
    // Cập nhật: Kiểm tra cả ID (từ session) VÀ tên đăng nhập (từ input)
    $stmt = $pdo->prepare("SELECT mat_khau FROM nguoi_dung WHERE id = :id AND ten_dang_nhap = :tendangnhap");
    $stmt->execute([':id' => $id_nguoi_dung, ':tendangnhap' => $tendangnhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nếu tìm thấy user VÀ mật khẩu khớp
    if ($user && password_verify($matkhaucu, $user['mat_khau'])) {
        json_response(['success' => true]);
    } else {
        // Sai tên đăng nhập hoặc sai mật khẩu cũ
        json_response(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu cũ không chính xác.']);
    }
} catch (PDOException $e) {
    error_log("Lỗi kiểm tra mật khẩu cũ: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Lỗi hệ thống, không thể kiểm tra.']);
}
?>

