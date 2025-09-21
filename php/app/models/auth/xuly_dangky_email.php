<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once '../../config.php';

$em = new Email();
$mailer = $em->createMailer();

$db = new Database();
$conn = $db->connect();

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

/**
 * Kiểm tra xem email có hợp lệ không
 */
function ckTaiKhoan($conn, $email, $sodienthoai, $tendangnhap) {
    $sql = "SELECT COUNT(*) FROM taikhoan WHERE email = :email OR sodienthoai = :sodienthoai OR tendangnhap = :tendangnhap";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email, ':sodienthoai' => $sodienthoai, ':tendangnhap' => $tendangnhap]);
    $taikhoan = $stmt->fetchColumn();
    if ($taikhoan == 0) {
        $sql = "SELECT COUNT(*) FROM otp_requests WHERE email = :email OR sodienthoai = :sodienthoai";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email, ':sodienthoai' => $sodienthoai]);
        $otp_requests = $stmt->fetchColumn();
        if ($otp_requests > 0) {
            return ['success' => false, 'error' => 'Tài khoản này đã gửi yêu cầu!'];
        } else {
            return ['success' => true];
        }
    } else {
        return ['success' => false, 'error' => 'Tài khoản đã tồn tại!'];
    }
}

/**
 * Lưu OTP vào PostgreSQL
 */
function saveOTPToDatabase($conn, $email, $sodienthoai, $otp, $tokenotp) {
    try {
        $expire_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $sql = "INSERT INTO otp_requests (email, sodienthoai, otp, tokenotp, expire_time) 
                VALUES (:email, :sodienthoai, :otp, :tokenotp, :expire_time)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':email'       => $email,
            ':sodienthoai' => $sodienthoai,
            ':otp'         => $otp,
            ':tokenotp'       => $tokenotp,
            ':expire_time' => $expire_time
        ]);
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Lỗi cơ sở dữ liệu!'];
    }
}

/**
 * Lưu thông tin cơ bản vào PostgreSQL
 */
function saveUserInfo($conn, $hoten, $tendangnhap, $email, $sodienthoai) {
    try {
        $conn->beginTransaction();

        $command = "python ../../xuly_matkhau.py " . escapeshellarg("Demo@123");
        $result = shell_exec($command);

        // 1. Thêm vào bảng taikhoan và lấy id vừa thêm
        $sql1 = "INSERT INTO taikhoan (tendangnhap, matkhau, email, sodienthoai, trangthai)
                 VALUES (:tendangnhap, :matkhau, :email, :sodienthoai, 'chuakichhoat')
                 RETURNING idtaikhoan";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute([
            ':tendangnhap' => $tendangnhap,
            ':matkhau'     => trim($result), 
            ':email'       => $email,
            ':sodienthoai' => $sodienthoai
        ]);
        
        $idtaikhoan = $stmt1->fetchColumn();

        // 2. Thêm vào bảng nguoidung
        $sql2 = "INSERT INTO nguoidung (idtaikhoan, hoten, anhdaidien)
                 VALUES (:idtaikhoan, :hoten, 'user.png')";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([
            ':idtaikhoan' => $idtaikhoan,
            ':hoten'      => $hoten
        ]);

        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'error' => 'Lỗi lưu thông tin người dùng!'];
    }
}

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $hoten       = trim($_POST['hoten'] ?? '');
    $tendangnhap = trim($_POST['tendangnhap'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $sodienthoai = trim($_POST['sodienthoai'] ?? '');

    $otp = generateOTP();
    $tokenotp = generateToken();
    $expire_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $result = ckTaiKhoan($conn, $email, $sodienthoai, $tendangnhap);
    if (!$result['success']) {
        echo $result['error'];
        exit;
    }

    $result2 = saveUserInfo($conn, $hoten, $tendangnhap, $email, $sodienthoai);
    if (!$result2['success']) {
        echo $result1['error'];
        exit;
    }

    $result1 = saveOTPToDatabase($conn, $email, $sodienthoai, $otp, $tokenotp);
    if (!$result1['success']) {
        echo $result1['error'];
        exit;
    }

    $result3 = sendOTPEmail($mailer, $email, $tendangnhap, $otp, $tokenotp, $expire_time);
    if (!$result3['success']) {
        echo $result3['error'];
        exit;
    }

    $db->closeConnection();
}
?>
