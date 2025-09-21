<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once '../../config.php';
$db = new Database();
$conn = $db->connect();

if (!isset($_GET['tokenotp'])) {
    die("Thiếu token!");
}

$tokenotp = trim($_GET['tokenotp']);

// Kiểm tra token trong DB
$sql = "SELECT * FROM otp_requests WHERE tokenotp = :tokenotp LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':tokenotp' => $tokenotp]);
$otpData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$otpData) {
    die("Token không hợp lệ!");
}

// Kiểm tra thời gian hết hạn
if (strtotime($otpData['expire_time']) < time()) {
    echo "Mã OTP đã hết hạn!";
    echo "<br> <a href='yeucau_otp.php?tokenotp=". $tokenotp ."'>Gửi lại OTP</a>";
    exit;
}

function xoaYeuCauOTP($conn, $tokenotp, $otp) {
    $sql = "DELETE FROM otp_requests WHERE tokenotp = :tokenotp AND otp = :otp";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':tokenotp' => $tokenotp, ':otp' => $otp]);
}

// Kiểm tra mã OTP
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['otp'])) {
    $otp = trim($_POST['otp'] ?? '');
    if ($otp !== $otpData['otp']) {
        echo "<script>alert('Mã OTP không đúng!');</script>";
    } else {
        xoaYeuCauOTP($conn, $tokenotp, $otp);
        header("Location: ../../index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
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
</html>
