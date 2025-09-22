<?php
session_start();
require_once __DIR__ . '/database_mg.php'; // file kết nối PDO

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM moigioi WHERE email = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['matkhau'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['ten'];
                header('Location: trangchu_mg.php');
                exit;
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập môi giới</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-screen w-screen bg-cover bg-center flex items-center justify-center"
      style="background-image: url('https://images.unsplash.com/photo-1505691723518-36a5ac3be353?auto=format&fit=crop&w=1350&q=80');">

    <div class="bg-white/90 p-8 rounded-2xl shadow-2xl w-96 backdrop-blur">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Đăng nhập môi giới</h2>

        <form method="POST" action="" autocomplete="off">
            <div class="mb-4">
                <input type="text" name="username" placeholder="Tên đăng nhập hoặc Email"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                       required
                       value="<?= htmlspecialchars($username) ?>"
                       autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false">
            </div>
            <div class="mb-4">
                <input type="password" name="password" placeholder="Mật khẩu"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                       required
                       autocomplete="new-password">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                Đăng nhập
            </button>
        </form>

        <?php if ($error !== '') : ?>
            <p class="text-red-500 text-center mt-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="flex justify-between items-center mt-6 text-sm">
            <a href="capnhatmk_mg.php" class="text-blue-600 hover:underline">Quên mật khẩu?</a>
            <a href="dangky_mg.php" class="text-blue-600 hover:underline">Đăng ký</a>
        </div>
    </div>

</body>
</html>
