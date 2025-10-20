<?php
// FILE: resend_otp.php

// --- CÀI ĐẶT & KHỞI TẠO ---
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json');

// --- HÀM TIỆN ÍCH ---
function json_response($success, $message, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// --- KẾT NỐI CSDL VÀ EMAIL ---
try {
    require_once "../../../config/database.php";
    require_once '../../../config/email.php'; // Nạp file cấu hình email
    $pdo = ketnoicsdl();
    $mailer = createmailer(); // Tạo đối tượng mailer
} catch (Exception $e) {
    error_log("Lỗi khởi tạo hoặc kết nối CSDL/Email: " . $e->getMessage());
    json_response(false, "Lỗi hệ thống, vui lòng thử lại sau.", 500);
}

// --- LUỒNG XỬ LÝ CHÍNH ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(false, "Phương thức không hợp lệ.", 405);
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    json_response(false, "Thiếu địa chỉ email hợp lệ.");
}

try {
    // 1. Kiểm tra xem có yêu cầu OTP nào tồn tại cho email này không
    $sql_find = "SELECT id FROM yeu_cau_otp WHERE email = :email LIMIT 1";
    $stmt_find = $pdo->prepare($sql_find);
    $stmt_find->execute([':email' => $email]);

    if (!$stmt_find->fetch()) {
        json_response(false, "Không tìm thấy yêu cầu đăng ký ban đầu. Vui lòng quay lại và thử đăng ký từ đầu.");
    }

    // 2. Tạo OTP mới
    $otp_code = random_int(100000, 999999);
    $otp_hash = password_hash($otp_code, PASSWORD_DEFAULT);
    $expiry_time = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP có hiệu lực 10 phút

    // 3. Cập nhật bản ghi OTP hiện có
    $sql_update = "UPDATE yeu_cau_otp 
                   SET otp_hash = :otp_hash, het_han = :het_han, so_lan_thu_sai = 0
                   WHERE email = :email";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([
        ':otp_hash' => $otp_hash,
        ':het_han' => $expiry_time,
        ':email' => $email
    ]);

    // 4. Gửi email chứa OTP mới bằng PHPMailer
    $subject = "Mã xác thực OTP mới của bạn";
    $body = "Mã OTP mới của bạn là: <strong>$otp_code</strong>. Mã này sẽ hết hạn trong 10 phút.";
    
    try {
        $mailer->addAddress($email);
        $mailer->Subject = $subject;
        $mailer->Body = $body;

        $mailer->send();
        json_response(true, "Đã gửi lại mã OTP thành công. Vui lòng kiểm tra email.");

    } catch (Exception $e) {
        error_log("Lỗi PHPMailer khi gửi lại OTP: " . $mailer->ErrorInfo);
        json_response(false, "Không thể gửi email OTP lúc này. Vui lòng thử lại sau.", 500);
    }

} catch (PDOException $e) {
    error_log("Lỗi CSDL khi gửi lại OTP: " . $e->getMessage());
    json_response(false, "Lỗi hệ thống khi xử lý yêu cầu của bạn.", 500);
}
?>
