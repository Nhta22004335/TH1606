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

function ckYeuCauOTP($pdo, $tokenotp, $email, $sodienthoai) {
    $sql = "SELECT * FROM yeu_cau_otp WHERE token_code = :token_code AND (email = :email OR so_dt = :so_dt) LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token_code' => $tokenotp, ':email' => $email, ':so_dt' => $sodienthoai]);
    $otpData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($otpData) {
        return true;
    } else {
        return false;
    }
}

function capNhatYeuCauOTP($pdo, $email, $sodienthoai, $otp, $tokenotp, $expire_time) {
    $sql = "UPDATE otp_requests SET otp_code = :otp, token_code = :tokenotp, het_han = :expire_time 
            WHERE so_dt = :sodienthoai OR email = :email";
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

    $verify_link = "http://localhost:8080/app/models/auth/xacnhan_otp.php?tokenotp=" . urlencode($tokenotp);

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
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zolux 4335 - Gửi lại OTP</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-indigo-100 via-white to-indigo-50">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
    <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Gửi lại OTP</h1>
    <form action="" method="POST" id="formGuiLaiOTP" class="space-y-5">
      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-600 mb-2">Email</label>
        <input type="email" id="email" name="email" placeholder="Nhập email của bạn"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent" />
      </div>

      <!-- Số điện thoại -->
      <div>
        <label for="sodienthoai" class="block text-sm font-medium text-gray-600 mb-2">Số điện thoại</label>
        <input type="text" id="sodienthoai" name="sodienthoai" placeholder="Nhập số điện thoại của bạn"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent" />
      </div>

      <!-- Button -->
      <button type="submit" id="btnGuiLaiOTP" name="btnGuiLaiOTP"
        class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md">
        Gửi lại OTP
      </button>
    </form>

    <p class="text-sm text-gray-500 text-center mt-6">
      Đã nhớ OTP? <a href="xacnhan_otp.php" class="text-indigo-600 hover:underline">Xác nhận ngay</a>
    </p>
  </div>
</body>
</html>
