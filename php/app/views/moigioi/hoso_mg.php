<?php
//session_start();
require_once __DIR__ . '/database_mg.php'; // file kết nối PDO

if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap_mg.php');
    exit;
}

$userId = $_SESSION['user_id']; 
$error = '';
$message = '';

// Lấy thông tin user
$stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Xử lý POST khi cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $dia_chi = trim($_POST['address']);

    if ($ten === '' || $phone === '' || $dia_chi === '') {
        $error = "Các trường không được để trống!";
    } else {
        $update = $pdo->prepare("UPDATE nguoi_dung SET ten=?, phone=?, dia_chi=? WHERE id=?");
        $update->execute([$ten, $phone, $dia_chi, $userId]);

        // Cập nhật lại dữ liệu
        $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $message = "Cập nhật hồ sơ thành công!";
    }
}
?>

<div class="max-w-lg mx-auto mt-10 bg-white p-6 rounded-2xl shadow-lg">
    <h2 class="text-3xl font-bold mb-6 text-blue-600 text-center">Hồ sơ cá nhân</h2>

    <?php if($error): ?>
        <p class="text-red-500 mb-4 text-center font-medium"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if($message): ?>
        <p class="text-green-600 mb-4 text-center font-medium"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block font-semibold mb-1" for="name">Họ và tên</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['ten']) ?>" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" readonly />
        </div>
        <div>
            <label class="block font-semibold mb-1" for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border px-3 py-2 rounded-lg bg-gray-100" readonly />
        </div>
        <div>
            <label class="block font-semibold mb-1" for="phone">Số điện thoại</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" readonly />
        </div>
        <div>
            <label class="block font-semibold mb-1" for="address">Địa chỉ</label>
            <textarea id="address" name="address" rows="3" class="w-full border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-blue-300" readonly><?= htmlspecialchars($user['dia_chi']) ?></textarea>
        </div>

        <div class="flex justify-between">
            <button type="button" id="editBtn" class="bg-yellow-500 text-white px-5 py-2 rounded-lg hover:bg-yellow-600 transition">Chỉnh sửa</button>
            <button type="submit" id="saveBtn" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition hidden">Lưu</button>
        </div>
    </form>
</div>

<script>
document.getElementById('editBtn').addEventListener('click', function() {
    document.querySelectorAll('input, textarea').forEach(el => el.removeAttribute('readonly'));
    document.getElementById('saveBtn').classList.remove('hidden');
    this.classList.add('hidden');
});
</script>
