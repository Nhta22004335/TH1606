<?php
// Kết nối CSDL
$host = 'localhost';
$db   = 'nhadep24h';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Xử lý POST khi đăng ký
$message = '';
$message_type = 'red'; // red = lỗi, green = thành công

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = $_POST['ten'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dia_chi = $_POST['dia_chi'];
    $matkhau = password_hash($_POST['matkhau'], PASSWORD_DEFAULT);

    // Kiểm tra email đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT * FROM moigioi WHERE email=?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $message = "Email này đã được đăng ký!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO moigioi (ten, email, matkhau, phone, dia_chi) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ten, $email, $matkhau, $phone, $dia_chi]);
        $message = "Đăng ký môi giới thành công! Bạn có thể đăng nhập.";
        $message_type = 'green';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký môi giới</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-100 to-blue-50 min-h-screen flex items-center justify-center">

<div class="bg-white shadow-lg rounded-2xl p-8 max-w-md w-full">
    <h2 class="text-3xl font-bold mb-6 text-center text-blue-600">Đăng ký môi giới</h2>

    <?php if($message): ?>
        <div class="mb-4 px-4 py-2 rounded <?= $message_type === 'red' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="" method="post" class="space-y-4">
        <div>
            <label class="block font-semibold mb-1" for="ten">Họ và tên</label>
            <input type="text" name="ten" id="ten" required class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
            <label class="block font-semibold mb-1" for="email">Email</label>
            <input type="email" name="email" id="email" required class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
            <label class="block font-semibold mb-1" for="matkhau">Mật khẩu</label>
            <input type="password" name="matkhau" id="matkhau" required class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
            <label class="block font-semibold mb-1" for="phone">Số điện thoại</label>
            <input type="text" name="phone" id="phone" class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
            <label class="block font-semibold mb-1" for="dia_chi">Địa chỉ</label>
            <textarea name="dia_chi" id="dia_chi" rows="3" class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">Đăng ký</button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        Bạn đã có tài khoản? 
        <a href="login_mg.php" class="text-blue-600 hover:underline">Đăng nhập</a>
    </p>
</div>

</body>
</html>
