<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

require_once '../../../config/email.php';
$mailer = createmailer();

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
function ckTaiKhoan($pdo, $email, $so_dt, $ten_dang_nhap) {
    $sql = "SELECT COUNT(*) FROM nguoi_dung WHERE email = :email OR so_dt = :so_dt OR ten_dang_nhap = :ten_dang_nhap";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email, ':so_dt' => $so_dt, ':ten_dang_nhap' => $ten_dang_nhap]);
    $taikhoan = $stmt->fetchColumn();
    if ($taikhoan == 0) {
        $sql = "SELECT COUNT(*) FROM yeu_cau_otp WHERE email = :email OR so_dt = :so_dt";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email, ':so_dt' => $so_dt]);
        $yeu_cau_otp = $stmt->fetchColumn();
        if ($yeu_cau_otp > 0) {
            return ['success' => false, 'error' => 'Tài khoản này đã gửi yêu cầu!'];
        } else {
            return ['success' => true, 'tb' => 'OK!'];
        }
    } else {
        return ['success' => false, 'error' => 'Tài khoản đã tồn tại!'];
    }
}

/**
 * Lưu OTP vào PostgreSQL
 */
function saveOTPToDatabase($pdo, $email, $so_dt, $otp_code, $token_code) {
    try {
        $het_han = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $sql = "INSERT INTO yeu_cau_otp (email, so_dt, otp_code, token_code, het_han) 
                VALUES (:email, :so_dt, :otp_code, :token_code, :het_han)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':email'       => $email,
            ':so_dt' => $so_dt,
            ':otp_code'         => $otp_code,
            ':token_code'       => $token_code,
            ':het_han' => $het_han
        ]);

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Lỗi cơ sở dữ liệu!'];
    }
}

/**
 * Lưu thông tin cơ bản vào PostgreSQL
 */
function saveUserInfo($pdo, $ho_ten, $ten_dang_nhap, $email, $so_dt) {
    try {
        $pdo->beginTransaction();

        $command = "/opt/venv/bin/python ../../helpers/xuly_matkhau.py " . escapeshellarg("Demo@123");
        $result = shell_exec($command);

        // 1. Thêm vào bảng taikhoan và lấy id vừa thêm
        $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, email, so_dt)
                VALUES (:ten_dang_nhap, :mat_khau, :email, :so_dt)
                RETURNING id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ten_dang_nhap' => $ten_dang_nhap,
            ':mat_khau'     => trim($result), 
            ':email'       => $email,
            ':so_dt' => $so_dt
        ]);
        
        $id = $stmt->fetchColumn();

        //2. Thêm vào bảng nguoidung
        $sql_update = "UPDATE khach_hang 
               SET ho_ten = :ho_ten 
               WHERE id_nguoi_dung = :id";
        $stmt = $pdo->prepare($sql_update);
        $stmt->execute([
            ':ho_ten' => $ho_ten,
            ':id' => $id
        ]);

        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => 'Lỗi lưu thông tin người dùng!'];
    }
}

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ho_ten       = trim($_POST['hoten'] ?? '');
    $ten_dang_nhap = trim($_POST['tendangnhap'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $so_dt = trim($_POST['sodienthoai'] ?? '');

    $otp_code = generateOTP();
    $token_code = generateToken();
    $het_han = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    
    $result = ckTaiKhoan($pdo, $email, $so_dt, $ten_dang_nhap);
    if (!$result['success']) {
        echo $result['error'];
        exit;
    }

    $result1 = saveUserInfo($pdo, $ho_ten, $ten_dang_nhap, $email, $so_dt);
    if (!$result1['success']) {
        echo $result1['error'];
        exit;
    }

    $result2 = saveOTPToDatabase($pdo, $email, $so_dt, $otp_code, $token_code);
    if (!$result2['success']) {
        echo $result2['error'];
        exit;
    }

    $result3 = sendOTPEmail($mailer, $email, $ten_dang_nhap, $otp_code, $token_code, $het_han);
    if (!$result3['success']) {
        echo $result3['error'];
        exit;
    }
}
?>
