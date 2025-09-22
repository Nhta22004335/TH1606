<?php
// date_default_timezone_set('Asia/Ho_Chi_Minh');
// require_once "../../../config/database.php";
// $pdo = ketnoicsdl();

// if (!isset($_GET['tokenotp'])) {
//     die("Thiếu token!");
// }

// $token_code = trim($_GET['tokenotp']);

// // Kiểm tra token trong DB
// $sql = "SELECT * FROM yeu_cau_otp WHERE token_code = :token_code LIMIT 1";
// $stmt = $pdo->prepare($sql);
// $stmt->execute([':token_code' => $token_code]);
// $otpData = $stmt->fetch(PDO::FETCH_ASSOC);

// if (!$otpData) {
//     die("Token không hợp lệ!");
// }

// // Kiểm tra thời gian hết hạn
// if (strtotime($otpData['het_han']) < time()) {
//     echo "Mã OTP đã hết hạn!";
//     echo "<br> <a href='yeucau_otp.php?tokenotp=". $token_code ."'>Gửi lại OTP</a>";
//     exit;
// }

// function xoaYeuCauOTP($pdo, $token_code, $otp_code) {
//     $sql = "DELETE FROM yeu_cau_otp WHERE token_code = :token_code AND otp_code = :otp_code";
//     $stmt = $pdo->prepare($sql);
//     $stmt->execute([':token_code' => $token_code, ':otp_code' => $otp_code]);
// }

// // Kiểm tra mã OTP
// if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['otp'])) {
//     $otp_code = trim($_POST['otp'] ?? '');
//     if ($otp_code !== $otpData['otp_code']) {
//         echo "<script>alert('Mã OTP không đúng!');</script>";
//     } else {
//         xoaYeuCauOTP($pdo, $token_code, $otp_code);
//         header("Location: ../../views/auth/dangnhap.html");
//         exit();
//     }
// }
?>
<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zolux 4335 - Xác nhận OTP</title>
</head>
<body>
    <form action="" method="POST" id="formXacNhan">
        <input type="text" id="otp" name="otp" placeholder="Nhập mã OTP">
        <button type="submit" id="btnXacNhan">Xác nhận</button>
    </form>
</body>
</html> -->

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zolux 4335 - Xác nhận OTP</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-100 via-white to-blue-50">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
    <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Xác nhận OTP</h1>
    <form action="" method="POST" id="formXacNhan" class="space-y-5">
      <div>
        <label for="otp" class="block text-sm font-medium text-gray-600 mb-2">Nhập mã OTP</label>
        <input type="text" id="otp" name="otp" placeholder="••••••"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-center text-lg tracking-widest" />
      </div>
      <button type="submit" id="btnXacNhan"
        class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition duration-300 shadow-md">
        Xác nhận
      </button>
    </form>
    <p class="text-sm text-gray-500 text-center mt-6">Chưa nhận được OTP? 
      <a href="yeucau_otp.php" class="text-blue-600 hover:underline">Gửi lại</a>
    </p>
  </div>
</body>
</html>
