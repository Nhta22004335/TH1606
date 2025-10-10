<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

require_once '../../../config/email.php';
$mailer = createmailer();

$id = trim($_SESSION['id_nguoi_dung'] ?? '');
$email = trim($_GET['email'] ?? '');

try {
    // Kiểm tra tài khoản tồn tại
    $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id = :id");
    $stmt->execute(['id' => $id]);
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
    $het_han = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $otp_code = generateOTP();
    $token_code = generateToken();
    $sql = "INSERT INTO yeu_cau_otp (email, otp_code, token_code, het_han) 
            VALUES (:email, :otp_code, :token_code, :het_han)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email'       => $email,
        ':otp_code'         => $otp_code,
        ':token_code'    => $token_code,
        ':het_han' => $het_han
    ]);

    /**
     * Gửi OTP qua email kèm link xác nhận
     */
    function sendOTPEmail($mailer, $email, $ten_dang_nhap, $otp_code, $token_code, $het_han) {
        $subject = "Mã xác thực OTP của bạn";

        $verify_link = "http://localhost:8080/app/models/auth/xacnhan_otp.php?tokenotp=" . urlencode($token_code);

        $body = "Xin chào $ten_dang_nhap,\n\n"
            . "Mã OTP của bạn là: $otp_code\n"
            . "Mã có hiệu lực đến: $het_han\n\n"
            . "Vui lòng bấm vào liên kết dưới đây để xác nhận OTP:\n$verify_link\n\n"
            . "Trân trọng.";

        try {
            $mailer->clearAddresses();
            $mailer->addAddress($email, $ten_dang_nhap);
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
    
    $sendResult = sendOTPEmail($mailer, $email, $ten_dang_nhap, $otp_code, $token_code, $het_han);

    if ($sendResult) {
        echo "<script>alert('Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra email để xác nhận.');</script>";
    } else {
        echo "<script>alert('Lỗi gửi email!');</script>";
    }
} catch (PDOException $e) {
    echo "<script>alert('Lỗi gửi email!');</script>";
}
?>
