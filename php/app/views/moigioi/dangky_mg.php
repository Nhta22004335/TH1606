<?php
session_start();

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

// Session hiển thị modal
$show_modal = $_SESSION['show_modal'] ?? false;
unset($_SESSION['show_modal']);

// Xử lý POST đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dia_chi = trim($_POST['dia_chi']);
    $matkhau = password_hash($_POST['matkhau'], PASSWORD_DEFAULT);

    // Kiểm tra email tồn tại
    $stmt = $pdo->prepare("SELECT * FROM moigioi WHERE email=?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo "<script>alert('Email này đã được đăng ký!'); window.location.href='".$_SERVER['PHP_SELF']."';</script>";
        exit;
    } else {
        $stmt = $pdo->prepare("INSERT INTO moigioi (ten, email, matkhau, phone, dia_chi, vai_tro) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ten, $email, $matkhau, $phone, $dia_chi, 'moigioi']);

        $_SESSION['show_modal'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
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
<body class="h-screen w-screen flex items-center justify-center relative overflow-hidden">

    <!-- Ảnh nền -->
   <!-- Ảnh nền -->
<!-- Ảnh nền -->
<div class="absolute inset-0">
    <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=1470&q=80
"
         alt="Nhà hiện đại sang trọng" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-black bg-opacity-20"></div>
</div>


    <!-- Form -->
    <div class="relative z-10 bg-white bg-opacity-90 backdrop-blur-md p-6 w-full max-w-sm rounded-3xl shadow-2xl">
        <h2 class="text-3xl font-bold text-center text-blue-600 mb-6">Đăng ký môi giới</h2>

        <form action="" method="post" class="space-y-3" autocomplete="off">
            <div>
                <label class="block font-semibold mb-1" for="ten">Họ và tên</label>
                <input type="text" name="ten" id="ten" required
                       class="w-full border border-gray-300 px-3 py-2 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       autocomplete="off" value="">
            </div>
            <div>
                <label class="block font-semibold mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" required
                       class="w-full border border-gray-300 px-3 py-2 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       autocomplete="off" value="">
            </div>
            <div>
                <label class="block font-semibold mb-1" for="matkhau">Mật khẩu</label>
                <input type="password" name="matkhau" id="matkhau" required
                       class="w-full border border-gray-300 px-3 py-2 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       autocomplete="new-password" value="">
            </div>
            <div>
                <label class="block font-semibold mb-1" for="phone">Số điện thoại</label>
                <input type="text" name="phone" id="phone"
                       class="w-full border border-gray-300 px-3 py-2 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       autocomplete="off" value="">
            </div>
            <div>
                <label class="block font-semibold mb-1" for="dia_chi">Địa chỉ</label>
                <textarea name="dia_chi" id="dia_chi" rows="2"
                          class="w-full border border-gray-300 px-3 py-2 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                          autocomplete="off"></textarea>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-2xl hover:bg-blue-700 transition font-semibold">
                Đăng ký
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Bạn đã có tài khoản? 
            <a href="dangnhap_mg.php" class="text-blue-600 hover:underline font-medium">Đăng nhập</a>
        </p>
    </div>

    <!-- Modal đăng ký thành công -->
    <?php if($show_modal): ?>
    <div id="modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-xl text-center">
            <h3 class="text-xl font-bold mb-4 text-green-600">Đăng ký thành công!</h3>
            <p class="mb-6">Bạn có muốn đăng nhập ngay bây giờ?</p>
            <div class="flex justify-center gap-4">
                <button onclick="window.location.href='dangnhap_mg.php'" class="bg-blue-600 text-white px-4 py-2 rounded-2xl hover:bg-blue-700 transition">Đăng nhập</button>
                <button onclick="document.getElementById('modal').style.display='none'" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-2xl hover:bg-gray-400 transition">Hủy</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>
