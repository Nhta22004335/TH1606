<?php
require 'database_mg.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($email === '' || $new_password === '' || $confirm_password === '') {
        $message = "Vui lòng nhập đầy đủ thông tin!";
    } elseif ($new_password !== $confirm_password) {
        $message = "Mật khẩu mới và xác nhận không trùng khớp!";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM moigioi WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE moigioi SET matkhau = ? WHERE email = ?");
            $stmt->execute([$new_hash, $email]);
            $message = "Đổi mật khẩu thành công! Bạn có thể đăng nhập lại.";
            $success = true;
        } else {
            $message = "Email không tồn tại trong hệ thống!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu môi giới</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="relative flex items-center justify-center min-h-screen">

    <!-- Ảnh nền + overlay mờ -->
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=1470&q=80"
             alt="Nhà hiện đại sang trọng" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
    </div>

    <!-- Form đổi mật khẩu -->
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md relative z-10">
        <h2 class="text-2xl font-bold text-blue-600 mb-6 text-center">Quên mật khẩu</h2>

        <?php if ($message): ?>
            <div class="mb-4 px-4 py-3 rounded <?php echo $success ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <label for="email" class="block mb-2 text-gray-700 font-medium">Email đăng ký</label>
            <input type="email" id="email" name="email" required class="w-full px-3 py-2 mb-4 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400">

            <label for="new_password" class="block mb-2 text-gray-700 font-medium">Mật khẩu mới</label>
            <input type="password" id="new_password" name="new_password" required class="w-full px-3 py-2 mb-4 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400">

            <label for="confirm_password" class="block mb-2 text-gray-700 font-medium">Nhập lại mật khẩu mới</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-3 py-2 mb-6 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400">

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-semibold hover:bg-blue-700 transition">Đổi mật khẩu</button>
        </form>

        <div class="mt-4 text-center">
            <a href="login.php" class="text-blue-600 hover:underline">Quay lại đăng nhập</a>
        </div>
    </div>
</body>
</html>
