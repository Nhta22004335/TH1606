<?php
session_start();
require_once __DIR__ . '/database_mg.php'; // file kết nối PDO

$error = '';
$username = '';
$role_selected = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role_selected = $_POST['vai_tro'] ?? '';

    if ($username === '' || $password === '' || $role_selected === '') {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập, mật khẩu và vai trò.';
    } else {
        try {
            // Chọn người dùng theo email hoặc tên đăng nhập và vai trò
            $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE (email = ? OR ten = ?) AND vai_tro = ? LIMIT 1");
            $stmt->execute([$username, $username, $role_selected]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['matkhau'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['ten'];
                $_SESSION['role'] = $user['vai_tro']; // admin / moigioi / khachhang

                // Chuyển hướng dựa trên vai trò
                switch ($user['vai_tro']) {
                    case 'admin':
                        header('Location: trangchu_admin.php');
                        break;
                    case 'moigioi':
                        header('Location: trangchu_mg.php');
                        break;
                    case 'khachhang':
                        header('Location: trangchu_kh.php');
                        break;
                }
                exit;
            } else {
                $error = 'Tên đăng nhập, mật khẩu hoặc vai trò không đúng.';
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
    <title>Đăng nhập Nhà Đẹp 24h</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-screen w-screen bg-cover bg-center flex items-center justify-center"
      style="background-image: url('https://images.unsplash.com/photo-1505691723518-36a5ac3be353?auto=format&fit=crop&w=1350&q=80');">

    <div class="bg-white/90 p-8 rounded-2xl shadow-2xl w-96 backdrop-blur">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Đăng nhập Nhà Đẹp 24h</h2>

        <form method="POST" action="" autocomplete="off" class="space-y-4">
            <div>
                <input type="text" name="username" placeholder="Tên đăng nhập hoặc Email"
                       class="w-full px-4 py-2 border rounded-2xl focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-sm hover:shadow-md transition-all duration-300"
                       required value="<?= htmlspecialchars($username) ?>"
                       autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false">
            </div>
            <div>
                <input type="password" name="password" placeholder="Mật khẩu"
                       class="w-full px-4 py-2 border rounded-2xl focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-sm hover:shadow-md transition-all duration-300"
                       required autocomplete="new-password">
            </div>
            <div>
                <label class="block font-semibold mb-1" for="vai_tro">Chọn vai trò</label>
                <div class="relative">
                    <select name="vai_tro" id="vai_tro"
                            class="appearance-none w-full border border-gray-300 px-4 py-2 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-md hover:shadow-lg transition-all duration-300 cursor-pointer">
                        <option value="" disabled <?= $role_selected === '' ? 'selected' : '' ?>>-- Chọn vai trò --</option>
                        <option value="khachhang" <?= $role_selected === 'khachhang' ? 'selected' : '' ?>>Khách hàng</option>
                        <option value="moigioi" <?= $role_selected === 'moigioi' ? 'selected' : '' ?>>Môi giới</option>
                        <option value="admin" <?= $role_selected === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <!-- Icon mũi tên xuống -->
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded-2xl hover:bg-blue-700 transition duration-200 font-semibold">
                Đăng nhập
            </button>
        </form>

        <?php if ($error !== '') : ?>
            <p class="text-red-500 text-center mt-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="flex justify-between items-center mt-6 text-sm">
            <a href="capnhatmk.php" class="text-blue-600 hover:underline">Quên mật khẩu?</a>
            <a href="dangky_mg.php" class="text-blue-600 hover:underline">Đăng ký</a>
        </div>
    </div>

</body>
</html>
