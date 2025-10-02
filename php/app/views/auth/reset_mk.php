<?php
// Bắt đầu session trước mọi output
session_start();

// Đường dẫn tuyệt đối trong container Docker
require_once '/var/www/html/config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tendangnhap = $_POST['tendangnhap'] ?? '';
    $matkhau = $_POST['matkhau'] ?? '';

    if ($tendangnhap && $matkhau) {
        $pdo = ketnoicsdl();

        // Hash mật khẩu mới bằng Argon2id
        $hash = password_hash($matkhau, PASSWORD_ARGON2ID);

        // Cập nhật mật khẩu
        $stmt = $pdo->prepare("UPDATE nguoi_dung SET mat_khau = :mk WHERE ten_dang_nhap = :user");
        $stmt->execute([
            ':mk' => $hash,
            ':user' => $tendangnhap
        ]);

        if ($stmt->rowCount() > 0) {
            $message = "Đặt lại mật khẩu thành công cho user '$tendangnhap'.";
        } else {
            $message = "Không tìm thấy user '$tendangnhap'.";
        }
    } else {
        $message = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Reset mật khẩu</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="bg-white shadow-lg rounded-xl p-6 w-full max-w-md">
    <h1 class="text-xl font-bold text-center mb-4">Đặt lại mật khẩu</h1>

    <?php if($message): ?>
        <p class="text-center text-sm text-red-600 mb-4"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label class="block mb-2 text-sm">Tên đăng nhập</label>
        <input type="text" name="tendangnhap" class="w-full p-2 border rounded mb-4" required>

        <label class="block mb-2 text-sm">Mật khẩu mới</label>
        <input type="password" name="matkhau" class="w-full p-2 border rounded mb-4" required>

        <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition">
            Đặt lại mật khẩu
        </button>
    </form>
</div>
</body>
</html>
