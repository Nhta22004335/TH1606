<?php
// FILE: xuly_dangky_gui_otp.php

ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json');

// --- KẾT NỐI CSDL VÀ EMAIL ---
try {
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();
    require_once '../../../config/email.php';
    $mailer = createmailer();
} catch (Exception $e) {
    error_log("Lỗi khởi tạo: " . $e->getMessage());
    json_response(false, "Lỗi hệ thống, vui lòng thử lại sau.", 500);
}

// --- CÁC HÀM TIỆN ÍCH VÀ BẢO MẬT ---

/**
 * Trả về phản hồi JSON và kết thúc kịch bản.
 */
function json_response($success, $message, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

/**
 * Tạo mã OTP an toàn, chỉ gồm số.
 */
function generateNumericOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}


// --- LUỒNG XỬ LÝ CHÍNH ---

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(false, "Phương thức không hợp lệ.", 405);
}

// BƯỚC 1: Lấy và xác thực dữ liệu đầu vào
$ho_ten        = trim($_POST['hoten'] ?? '');
$ten_dang_nhap = trim($_POST['tendangnhap'] ?? '');
$email         = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$so_dt         = trim($_POST['sodienthoai'] ?? '');
$mat_khau      = $_POST['matkhau'] ?? '';
$mat_khau_nhap_lai = $_POST['matkhaunhaplai'] ?? '';

if (empty($ho_ten) || empty($ten_dang_nhap) || empty($email) || empty($mat_khau)) {
    json_response(false, "Vui lòng điền đầy đủ các trường bắt buộc.");
}
if (!$email) {
    json_response(false, "Định dạng email không hợp lệ.");
}
if ($mat_khau !== $mat_khau_nhap_lai) {
    json_response(false, "Mật khẩu nhập lại không khớp.");
}
if (strlen($mat_khau) < 8) {
    json_response(false, "Mật khẩu phải có ít nhất 8 ký tự.");
}

// BƯỚC 2: Kiểm tra sự tồn tại của tài khoản
$stmt = $pdo->prepare("SELECT 1 FROM nguoi_dung WHERE email = :email OR ten_dang_nhap = :ten_dang_nhap LIMIT 1");
$stmt->execute([':email' => $email, ':ten_dang_nhap' => $ten_dang_nhap]);
if ($stmt->fetchColumn()) {
    json_response(false, "Email hoặc Tên đăng nhập đã được sử dụng.");
}

$stmt = $pdo->prepare("SELECT 1 FROM yeu_cau_otp WHERE email = :email AND het_han > NOW()");
$stmt->execute([':email' => $email]);
if ($stmt->fetchColumn()) {
    json_response(false, "Một mã OTP đã được gửi tới email này. Vui lòng kiểm tra hộp thư hoặc đợi 5 phút để thử lại.");
}

// BƯỚC 3: Tạo và lưu dữ liệu đăng ký tạm thời
$otp_code = generateNumericOTP();
$het_han  = new DateTime('+5 minutes');

// Băm mật khẩu và OTP
$mat_khau_hashed = password_hash($mat_khau, PASSWORD_ARGON2ID);
$otp_hashed      = password_hash($otp_code, PASSWORD_DEFAULT);

// Đóng gói dữ liệu người dùng vào JSON
$user_data_json = json_encode([
    'ho_ten' => $ho_ten,
    'ten_dang_nhap' => $ten_dang_nhap,
    'so_dt' => $so_dt,
    'mat_khau_hashed' => $mat_khau_hashed
]);

try {
    // Xóa các yêu cầu OTP cũ, hết hạn của email này
    $pdo->prepare("DELETE FROM yeu_cau_otp WHERE email = :email")->execute([':email' => $email]);
    
    // Thêm yêu cầu mới
    $sql = "INSERT INTO yeu_cau_otp (email, otp_hash, het_han, user_data_json) 
            VALUES (:email, :otp_hash, :het_han, :user_data_json)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email'          => $email,
        ':otp_hash'       => $otp_hashed,
        ':het_han'        => $het_han->format('Y-m-d H:i:s'),
        ':user_data_json' => $user_data_json
    ]);
} catch (PDOException $e) {
   // Tạm thời hiển thị lỗi chi tiết để gỡ rối
    json_response(false, "Lỗi CSDL: " . $e->getMessage(), 500);
}

// BƯỚC 4: Gửi email OTP
try {
    $subject = "Mã xác thực đăng ký tài khoản";
    $body = "Xin chào " . htmlspecialchars($ho_ten) . ",<br><br>"
          . "Cảm ơn bạn đã đăng ký. Mã OTP của bạn là: <h1>$otp_code</h1>"
          . "Mã này sẽ hết hạn trong vòng 5 phút.<br><br>"
          . "Vui lòng sử dụng mã này để hoàn tất quá trình đăng ký.<br><br>"
          . "Trân trọng.";

    $mailer->clearAddresses();
    $mailer->addAddress($email, $ho_ten);
    $mailer->Subject = $subject;
    $mailer->Body    = $body;
    $mailer->isHTML(true);

    if ($mailer->send()) {
        json_response(true, "Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra và nhập mã.");
    } else {
        json_response(false, "Không thể gửi email OTP. Vui lòng thử lại sau.", 500);
    }
} catch (Exception $e) {
    error_log("Lỗi PHPMailer: " . $mailer->ErrorInfo);
    json_response(false, "Lỗi hệ thống khi gửi email. Vui lòng thử lại sau.", 500);
}
?>