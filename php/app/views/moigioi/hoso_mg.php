<?php
// Kết nối CSDL với XAMPP
$host = 'localhost';
$db   = 'nhadep24h'; // đổi thành tên database của bạn
$user = 'root';       // XAMPP mặc định root
$pass = '';           // XAMPP mặc định trống
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

// Lấy thông tin user từ CSDL
$userId = 1; // giả sử user đang đăng nhập có id = 1
$stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Xử lý POST khi cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dia_chi = $_POST['address'];

    $update = $pdo->prepare("UPDATE nguoi_dung SET ten=?, email=?, phone=?, dia_chi=? WHERE id=?");
    $update->execute([$ten, $email, $phone, $dia_chi, $userId]);

    // Lấy lại dữ liệu mới
    $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $message = "Cập nhật hồ sơ thành công!";
}
?>

<div>
    <h2 class="text-3xl font-bold mb-6 text-blue-600">Hồ sơ cá nhân</h2>

    <?php if(isset($message)) echo "<p class='text-green-600 mb-4'>$message</p>"; ?>

    <form action="" method="post" class="max-w-lg bg-white p-6 rounded shadow space-y-4">
        <div>
            <label class="block font-semibold mb-1" for="name">Họ và tên</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['ten']) ?>" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" readonly />
        </div>
        <div>
            <label class="block font-semibold mb-1" for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" readonly />
        </div>
        <div>
            <label class="block font-semibold mb-1" for="phone">Số điện thoại</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" readonly />
        </div>
        <div>
            <label class="block font-semibold mb-1" for="address">Địa chỉ</label>
            <textarea id="address" name="address" rows="3" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" readonly><?= htmlspecialchars($user['dia_chi']) ?></textarea>
        </div>

        <div class="flex space-x-2">
            <button type="button" id="editBtn" class="bg-yellow-500 text-white px-5 py-2 rounded-lg hover:bg-yellow-600 transition">Chỉnh sửa</button>
            <button type="submit" id="saveBtn" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition hidden">Lưu</button>
        </div>
    </form>
</div>

<script>
// Khi click nút Chỉnh sửa
document.getElementById('editBtn').addEventListener('click', function() {
    document.querySelectorAll('input, textarea').forEach(el => el.removeAttribute('readonly'));
    document.getElementById('saveBtn').classList.remove('hidden');
    this.classList.add('hidden');
});
</script>
