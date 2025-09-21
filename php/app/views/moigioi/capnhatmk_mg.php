<?php
session_start();
require_once __DIR__ . '/database_mg.php'; // kết nối PDO

if (!isset($_SESSION['user_id'])) {
    header('Location: login_mg.php');
    exit;
}

$error = '';
$success = '';
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $error = "Vui lòng điền đầy đủ tất cả các trường.";
    } elseif ($new !== $confirm) {
        $error = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
    } else {
        $stmt = $pdo->prepare("SELECT matkhau FROM moigioi WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current, $user['matkhau'])) {
            $error = "Mật khẩu hiện tại không đúng.";
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE moigioi SET matkhau = ? WHERE id = ?");
            $update->execute([$newHash, $userId]);
            $success = "Cập nhật mật khẩu thành công!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật mật khẩu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-screen w-screen flex items-center justify-center relative">

    <!-- Ảnh nền -->
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1470&q=80"
             alt="Nhà cửa tinh tế" class="w-full h-full object-cover">
    </div>

    <!-- Form -->
    <div class="relative bg-white bg-opacity-90 backdrop-blur-md p-8 rounded-3xl shadow-xl w-96 z-10">
        <h2 class="text-3xl font-bold text-center text-blue-600 mb-6">Cập nhật mật khẩu</h2>

        <?php if($error) : ?>
            <p class="text-red-600 mb-4 text-center font-medium"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if($success) : ?>
            <p class="text-green-600 mb-4 text-center font-medium"><?= htmlspecialchars($success) ?></p>
            <div class="text-center">
                <a href="trangchu_mg.php" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition font-semibold">
                   Về trang chính
                </a>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block mb-1 font-semibold" for="current_password">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" id="current_password" required 
                       class="w-full border border-gray-300 px-4 py-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block mb-1 font-semibold" for="new_password">Mật khẩu mới</label>
                <input type="password" name="new_password" id="new_password" required
                       class="w-full border border-gray-300 px-4 py-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block mb-1 font-semibold" for="confirm_password">Xác nhận mật khẩu mới</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                       class="w-full border border-gray-300 px-4 py-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 rounded-xl hover:bg-blue-700 transition font-semibold">
                Cập nhật
            </button>
        </form>
    </div>

</body>
</html>
