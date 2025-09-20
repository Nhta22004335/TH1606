<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once '../../config.php';

$db = new Database();
$conn = $db->connect();

$em = new Email();
$mailer = $em->createMailer();

/**
 * Tạo mã OTP gồm chữ và số
 */
function generateOTP($length = 6) {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $otp;
}

/**
 * Tạo token ngẫu nhiên
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function ckYeuCauOTP($conn, $tokenotp, $email, $sodienthoai) {
    $sql = "SELECT * FROM otp_requests WHERE tokenotp = :tokenotp AND (email = :email OR sodienthoai = :sodienthoai) LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':tokenotp' => $tokenotp, ':email' => $email, ':sodienthoai' => $sodienthoai]);
    $otpData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($otpData) {
        return true;
    } else {
        return false;
    }
}

function capNhatYeuCauOTP($conn, $email, $sodienthoai, $otp, $tokenotp, $expire_time) {
    $sql = "UPDATE otp_requests SET otp = :otp, tokenotp = :tokenotp, expire_time = :expire_time 
            WHERE sodienthoai = :sodienthoai OR email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':otp' => $otp,
        ':tokenotp' => $tokenotp,
        ':expire_time' => $expire_time,
        ':sodienthoai' => $sodienthoai,
        ':email' => $email
    ]);
}

/**
 * Gửi OTP qua email kèm link xác nhận
 */
function sendOTPEmail($mailer, $email, $otp, $tokenotp, $expire_time) {
    $subject = "Cấp lại mã xác thực OTP của bạn";

    $verify_link = "http://localhost/4335/auth/php/xacnhan_otp.php?tokenotp=" . urlencode($tokenotp);

    $body = "Xin chào bạn!,\n\n"
          . "Mã OTP của bạn là: $otp\n"
          . "Mã có hiệu lực đến: $expire_time\n\n"
          . "Vui lòng bấm vào liên kết dưới đây để xác nhận OTP:\n$verify_link\n\n"
          . "Trân trọng.";

    try {
        $mailer->clearAddresses();
        $mailer->addAddress($email);
        $mailer->Subject = $subject;
        $mailer->Body    = nl2br(htmlspecialchars($body));
        $mailer->AltBody = $body;

        if ($mailer->send()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Lỗi gửi email!'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Lỗi gửi email!'];
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btnGuiLaiOTP'])) {

    $email = trim($_POST['email'] ?? '');
    $sodienthoai = trim($_POST['sodienthoai'] ?? '');
    $expire_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $tokenotp = trim($_GET['tokenotp']);
    $otp = generateOTP();
    $tokenotpnew = generateToken();

    if (ckYeuCauOTP($conn, $tokenotp, $email, $sodienthoai)) {
        echo "Ok!";
        capNhatYeuCauOTP($conn, $email, $sodienthoai, $otp, $tokenotpnew, $expire_time);
        sendOTPEmail($mailer, $email, $otp, $tokenotpnew, $expire_time);
    } else {
        echo "Dữ liệu mà bạn cung cấp không hợp lệ!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zolux 4335 - Gửi lại OTP</title>
</head>
<body>
    <form action="" method="POST" id="formGuiLaiOTP">
        <input type="email" name="email" id="email" placeholder="Nhập email của bạn">
        <input type="text" name="sodienthoai" id="sodienthoai" placeholder="Nhập số điện thoại của bạn">
        <button type="submit" name="btnGuiLaiOTP" id="btnGuiLaiOTP">Gửi lại OTP</button>
    </form>
</body>
</html>