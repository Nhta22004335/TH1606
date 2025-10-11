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
        <div class="relative mb-4">
            <input type="password" name="matkhau" id="matkhau" class="w-full p-2 border rounded pr-10" required>
            <button type="button" onclick="togglePassword()" class="absolute right-2 top-2 text-gray-600 hover:text-gray-800">
                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition">
            Đặt lại mật khẩu
        </button>
    </form>
</div>

<script>
function togglePassword() {
    const pwInput = document.getElementById('matkhau');
    const eyeIcon = document.getElementById('eyeIcon');
    if (pwInput.type === 'password') {
        pwInput.type = 'text';
        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.083-3.333M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18" />`; // mắt tắt
    } else {
        pwInput.type = 'password';
        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`; // mắt mở
    }
}
</script>
</body>
</html>
