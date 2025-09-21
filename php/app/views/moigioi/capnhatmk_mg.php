<?php
session_start();
require_once __DIR__ . '/database_mg.php'; // kết nối PDO

// Kiểm tra nếu chưa login → chuyển về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login_mg.php');
    exit;
}

$error = '';
$success = '';

$userId = $_SESSION['user_id'];

// Xử lý POST khi cập nhật mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $error = "Vui lòng điền đầy đủ tất cả các trường.";
    } elseif ($new !== $confirm) {
        $error = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
    } else {
        // Lấy mật khẩu hiện tại từ DB
        $stmt = $pdo->prepare("SELECT matkhau FROM moigioi WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current, $user['matkhau'])) {
            $error = "Mật khẩu hiện tại không đúng.";
        } else {
            // Hash mật khẩu mới
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
<body class="h-screen w-screen bg-gray-100 flex items-center justify-center">

<div class="bg-white p-8 rounded-2xl shadow w-96">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Cập nhật mật khẩu</h2>

    <?php if($error) : ?>
        <p class="text-red-500 mb-4 text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if($success) : ?>
        <p class="text-green-600 mb-4 text-center"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-4">
        <div>
            <label class="block mb-1 font-semibold" for="current_password">Mật khẩu hiện tại</label>
            <input type="password" name="current_password" id="current_password" required 
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:ring-blue-300">
        </div>
        <div>
            <label class="block mb-1 font-semibold" for="new_password">Mật khẩu mới</label>
            <input type="password" name="new_password" id="new_password" required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:ring-blue-300">
        </div>
        <div>
            <label class="block mb-1 font-semibold" for="confirm_password">Xác nhận mật khẩu mới</label>
            <input type="password" name="confirm_password" id="confirm_password" required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:ring-blue-300">
        </div>
        <button type="submit" 
            class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">
            Cập nhật
        </button>
    </form>
</div>

</body>
</html>
