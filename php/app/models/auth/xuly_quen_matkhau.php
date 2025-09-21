<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once '../../config.php'; 

$tendangnhap = trim($_GET['tendangnhap'] ?? '');
$email = trim($_GET['email'] ?? '');

try {
    $db = new Database();
    $conn = $db->connect();

    $em = new Email();
    $mailer = $em->createMailer();

    // Kiểm tra tài khoản tồn tại
    $stmt = $conn->prepare("SELECT * FROM taikhoan WHERE tendangnhap = :tendangnhap");
    $stmt->execute(['tendangnhap' => $tendangnhap]);
    $tk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tk) {
        echo "<script>alert('Tài khoản không tồn tại!');</script>";
        exit;
    }

    if ($email != $tk['email']) {
        echo "<script>alert('Email không khớp với email đăng ký!');</script>";
        exit;
    }

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

    // Cập nhật mật khẩu tạm thời vào database
    $expire_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $otp = generateOTP();
    $tokenotp = generateToken();
    $sql = "INSERT INTO otp_requests (email, otp, tokenotp, expire_time) 
            VALUES (:email, :otp, :tokenotp, :expire_time)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':email'       => $email,
        ':otp'         => $otp,
        ':tokenotp'    => $tokenotp,
        ':expire_time' => $expire_time
    ]);

    /**
     * Gửi OTP qua email kèm link xác nhận
     */
    function sendOTPEmail($mailer, $email, $tendangnhap, $otp, $tokenotp, $expire_time) {
        $subject = "Mã xác thực OTP của bạn";

        $verify_link = "http://localhost/4335/auth/php/xacnhan_otp.php?tokenotp=" . urlencode($tokenotp);

        $body = "Xin chào $tendangnhap,\n\n"
            . "Mã OTP của bạn là: $otp\n"
            . "Mã có hiệu lực đến: $expire_time\n\n"
            . "Vui lòng bấm vào liên kết dưới đây để xác nhận OTP:\n$verify_link\n\n"
            . "Trân trọng.";

        try {
            $mailer->clearAddresses();
            $mailer->addAddress($email, $tendangnhap);
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
    
    $sendResult = sendOTPEmail($mailer, $email, $tendangnhap, $otp, $tokenotp, $expire_time);

    if ($sendResult) {
        echo "<script>alert('Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra email để xác nhận.');</script>";
    } else {
        echo "<script>alert('Lỗi gửi email!');</script>";
    }
} catch (PDOException $e) {
    
}
?>
