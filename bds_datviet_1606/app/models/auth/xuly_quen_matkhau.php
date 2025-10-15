<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once "../../../config/database.php";
require_once '../../../config/email.php'; // Giả định file này chứa hàm createMailer()

// =================================================================
// 1. CÁC HÀM HELPER
// =================================================================

/**
 * Tạo mã OTP gồm chữ và số. Đây là mã sẽ gửi cho người dùng.
 * @param int $length Độ dài của mã OTP
 * @return string Mã OTP dạng văn bản thuần
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
 * Tạo một token ngẫu nhiên, an toàn để định danh yêu cầu reset.
 * @param int $length Độ dài của token
 * @return string Token dạng hex
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Gửi OTP qua email kèm link xác nhận.
 */
function sendOTPEmail($mailer, $email, $ten_dang_nhap, $otp_plaintext, $reset_token, $het_han) {
    $subject = "Yêu cầu đặt lại mật khẩu của bạn";
    
    // Link xác nhận bây giờ sẽ chứa token reset, không phải token OTP
    $verify_link = "http://localhost:8080/app/models/auth/xacnhan_otp.php?token=" . urlencode($reset_token);

    $body = "Xin chào " . htmlspecialchars($ten_dang_nhap) . ",\n\n"
          . "Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.\n"
          . "Mã OTP của bạn là: $otp_plaintext\n"
          . "Mã này sẽ hết hạn vào lúc: " . date('H:i d/m/Y', strtotime($het_han)) . "\n\n"
          . "Vui lòng nhập mã OTP vào trang xác nhận hoặc nhấp vào liên kết sau:\n$verify_link\n\n"
          . "Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.\n"
          . "Trân trọng.";

    try {
        $mailer->clearAddresses();
        $mailer->addAddress($email, $ten_dang_nhap);
        $mailer->Subject = $subject;
        $mailer->Body    = nl2br($body); // Dùng nl2br để hiển thị xuống dòng trong HTML
        $mailer->AltBody = strip_tags($body);

        return $mailer->send();
    } catch (Exception $e) {
        // Ghi lại lỗi thực tế thay vì hiển thị cho người dùng
        // error_log("Mailer Error: " . $mailer->ErrorInfo);
        return false;
    }
}


// =================================================================
// 2. XỬ LÝ CHÍNH
// =================================================================

try {
    $pdo = ketnoicsdl();
    $mailer = createMailer();
} catch (Exception $e) {
    echo "<script>alert('Lỗi hệ thống. Không thể khởi tạo dịch vụ.'); window.history.back();</script>";
    exit;
}

$ten_dang_nhap = trim($_GET['tendangnhap'] ?? '');
$email = trim($_GET['email'] ?? '');

if (empty($ten_dang_nhap) || empty($email)) {
    echo "<script>alert('Vui lòng nhập đầy đủ tên đăng nhập và email.'); window.history.back();</script>";
    exit;
}

try {
    // Bước 1: Kiểm tra tài khoản và email có khớp không
    $stmt = $pdo->prepare("SELECT id, email FROM nguoi_dung WHERE ten_dang_nhap = :ten_dang_nhap");
    $stmt->execute([':ten_dang_nhap' => $ten_dang_nhap]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        
        echo "<script>alert('Tài khoản không tồn tại!'); window.history.back();</script>";
        exit;
       
    }

    if ($email !== $user['email']) {
        echo "<script>alert('Phải là email bạn sử dụng để đăng ký!'); window.history.back();</script>";
        exit;
    }
    
    // Bước 2: Bắt đầu một transaction
    $pdo->beginTransaction();

    // Bước 3: Hủy tất cả các yêu cầu OTP "choxacnhan" trước đó của email này
    $stmt_invalidate = $pdo->prepare("
        UPDATE yeu_cau_otp 
        SET trang_thai = 'dahuy', cap_nhat = CURRENT_TIMESTAMP 
        WHERE email = :email AND trang_thai = 'choxacnhan'
    ");
    $stmt_invalidate->execute([':email' => $email]);

    // Bước 4: Tạo OTP và token mới
    $otp_plaintext = generateOTP();
    $otp_hash = password_hash($otp_plaintext, PASSWORD_BCRYPT); // Băm OTP để lưu trữ
    $reset_token = generateToken(64); // Token dài hơn để an toàn hơn
    $het_han = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Dữ liệu sẽ được lưu vào cột JSONB
    $user_data = json_encode([
        'reset_token' => $reset_token,
        'user_id' => $user['id'], // Lưu ID người dùng để tiện tra cứu sau này
        'type' => 'password_reset'
    ]);
    
    // Bước 5: Chèn yêu cầu OTP mới vào CSDL
    $sql = "
        INSERT INTO yeu_cau_otp (email, user_data_json, otp_hash, het_han) 
        VALUES (:email, :user_data_json, :otp_hash, :het_han)
    ";
    $stmt_insert = $pdo->prepare($sql);
    $stmt_insert->execute([
        ':email'          => $email,
        ':user_data_json' => $user_data,
        ':otp_hash'       => $otp_hash,
        ':het_han'        => $het_han
    ]);

    // Bước 6: Gửi email chứa OTP (dạng plaintext) và token reset
    $is_sent = sendOTPEmail($mailer, $email, $ten_dang_nhap, $otp_plaintext, $reset_token, $het_han);

    if ($is_sent) {
        // Nếu mọi thứ thành công, commit transaction
        $pdo->commit();
        $_SESSION['reset_email'] = $email; // Lưu email vào session để trang xác nhận OTP biết
        echo "<script>
                alert('Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra và xác nhận trong vòng 5 phút.');
                window.location.href = '../../views/auth/dangnhap.html';
              </script>";
    } else {
        // Nếu gửi email thất bại, rollback transaction
        $pdo->rollBack();
        echo "<script>alert('Không thể gửi email OTP. Vui lòng thử lại sau.'); window.history.back();</script>";
    }

} catch (PDOException $e) {
    // Nếu có lỗi CSDL, rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Ghi lại lỗi chi tiết
    // error_log("Database Error: " . $e->getMessage());
    echo "<script>alert('Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'); window.history.back();</script>";
}
?>