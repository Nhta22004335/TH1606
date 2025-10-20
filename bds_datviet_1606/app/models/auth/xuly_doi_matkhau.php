<?php
session_start();
header('Content-Type: application/json');

function json_response($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// 1. Kiểm tra phiên đăng nhập
if (!isset($_SESSION['id_nguoi_dung'])) {
    json_response(false, 'Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.');
}

// 2. Lấy và xác thực dữ liệu đầu vào
$input = json_decode(file_get_contents('php://input'), true);
$id_nguoi_dung = $_SESSION['id_nguoi_dung'];
$matkhaucu = $input['matkhaucu'] ?? '';
$matkhaumoi = $input['matkhaumoi'] ?? '';
$tendangnhap = $input['tendangnhap'] ?? '';

if (empty($matkhaucu) || empty($matkhaumoi) || empty($tendangnhap)) {
    json_response(false, 'Vui lòng điền đầy đủ tất cả các trường thông tin.');
}

// 3. Kết nối CSDL và xử lý logic
require_once "../../../config/database.php";
try {
    $pdo = ketnoicsdl();

    // 3a. Lấy mật khẩu hiện tại từ CSDL, xác thực cả ID và Tên đăng nhập
    $stmt = $pdo->prepare("SELECT mat_khau FROM nguoi_dung WHERE id = :id AND ten_dang_nhap = :tendangnhap");
    $stmt->execute([':id' => $id_nguoi_dung, ':tendangnhap' => $tendangnhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        json_response(false, 'Tài khoản không tồn tại hoặc tên đăng nhập không đúng.');
    }

    // 3b. Xác thực mật khẩu cũ
    if (!password_verify($matkhaucu, $user['mat_khau'])) {
        json_response(false, 'Mật khẩu cũ không chính xác.');
    }

    // 3c. Hash và cập nhật mật khẩu mới
    $matkhaumoi_hashed = password_hash($matkhaumoi, PASSWORD_DEFAULT);
    $updateStmt = $pdo->prepare("UPDATE nguoi_dung SET mat_khau = :matkhaumoi WHERE id = :id");
    $updateStmt->execute([':matkhaumoi' => $matkhaumoi_hashed, ':id' => $id_nguoi_dung]);

    // 3d. Trả về thành công
    json_response(true, 'Đổi mật khẩu thành công!');

} catch (PDOException $e) {
    error_log("Lỗi đổi mật khẩu: " . $e->getMessage());
    json_response(false, 'Lỗi hệ thống, không thể đổi mật khẩu vào lúc này.');
}
?>

