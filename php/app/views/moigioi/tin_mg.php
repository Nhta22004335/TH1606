<?php
session_start();
require_once __DIR__ . '/database_mg.php'; // file kết nối PDO

if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap_mg.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Xử lý thêm tin mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($title === '') {
        $error = "Tiêu đề không được để trống.";
    } else {
        // Thêm tin mới vào bảng posts
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, description, price, address) VALUES (?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$user_id, $title, $description, $price ?: null, $address ?: null]);
        if ($ok) {
            $success = "Đăng tin thành công!";
        } else {
            $error = "Lỗi khi đăng tin, vui lòng thử lại.";
        }
    }
}

// Lấy danh sách tin đăng của user
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-2xl font-bold mb-4">Tin đăng của tôi</h2>

<?php if ($error): ?>
    <p class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<!-- Form đăng tin mới -->
<form method="POST" class="mb-8 bg-white p-4 rounded shadow">
    <h3 class="text-xl font-semibold mb-2">Đăng tin mới</h3>
    <div class="mb-3">
        <label class="block mb-1 font-medium">Tiêu đề</label>
        <input type="text" name="title" class="w-full border px-3 py-2 rounded" required />
    </div>
    <div class="mb-3">
        <label class="block mb-1 font-medium">Mô tả</label>
        <textarea name="description" rows="4" class="w-full border px-3 py-2 rounded"></textarea>
    </div>
    <div class="mb-3">
        <label class="block mb-1 font-medium">Giá (VNĐ)</label>
        <input type="number" name="price" min="0" class="w-full border px-3 py-2 rounded" />
    </div>
    <div class="mb-3">
        <label class="block mb-1 font-medium">Địa chỉ</label>
        <input type="text" name="address" class="w-full border px-3 py-2 rounded" />
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
        Đăng tin
    </button>
</form>

<!-- Danh sách tin đăng -->
<?php if (count($posts) === 0): ?>
    <p>Bạn chưa có tin đăng nào.</p>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($posts as $post): ?>
            <div class="bg-white p-4 rounded shadow">
                <h3 class="text-lg font-semibold mb-1"><?= htmlspecialchars($post['title']) ?></h3>
                <p class="text-gray-700 mb-1"><?= nl2br(htmlspecialchars($post['description'])) ?></p>
                <p class="text-sm text-gray-500 mb-1">
                    Giá: <?= $post['price'] !== null ? number_format($post['price'], 0, ',', '.') . " VNĐ" : "Liên hệ" ?> | 
                    Địa chỉ: <?= htmlspecialchars($post['address'] ?: 'Chưa cập nhật') ?>
                </p>
                <p class="text-xs text-gray-400">Ngày đăng: <?= $post['created_at'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
