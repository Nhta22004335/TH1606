<?php
session_start();
require_once __DIR__ . '/database_mg.php'; // kết nối PDO

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
        $stmt = $pdo->prepare("INSERT INTO tin_dang (ma_nguoi_dung, tieu_de, mo_ta, gia, dia_chi) VALUES (?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$user_id, $title, $description, $price ?: null, $address ?: null]);
        if ($ok) {
            $success = "Đăng tin thành công!";
        } else {
            $error = "Lỗi khi đăng tin, vui lòng thử lại.";
        }
    }
}

// Lấy danh sách tin đăng của user
$stmt = $pdo->prepare("SELECT * FROM tin_dang WHERE ma_nguoi_dung = ? ORDER BY ngay_dang DESC");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tin đăng của tôi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen relative flex flex-col items-center justify-start pt-10">

    <!-- Ảnh nền -->
    <div class="absolute inset-0 -z-10">
        <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=1470&q=80" 
             alt="Nhà cửa" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
    </div>

    <div class="w-full max-w-3xl px-4">

        <h2 class="text-3xl font-bold text-center text-white mb-6">Tin đăng của tôi</h2>

        <?php if ($error): ?>
            <p class="text-red-500 mb-4 text-center font-medium bg-white/80 p-2 rounded"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="text-green-600 mb-4 text-center font-medium bg-white/80 p-2 rounded"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <!-- Form đăng tin mới -->
        <form method="POST" class="mb-8 bg-white bg-opacity-90 p-6 rounded-2xl shadow-md backdrop-blur">
            <h3 class="text-xl font-semibold mb-4">Đăng tin mới</h3>
            <div class="mb-3">
                <label class="block mb-1 font-medium">Tiêu đề</label>
                <input type="text" name="title" class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none" required />
            </div>
            <div class="mb-3">
                <label class="block mb-1 font-medium">Mô tả</label>
                <textarea name="description" rows="4" class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none"></textarea>
            </div>
            <div class="mb-3">
                <label class="block mb-1 font-medium">Giá (VNĐ)</label>
                <input type="number" name="price" min="0" class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none" />
            </div>
            <div class="mb-3">
                <label class="block mb-1 font-medium">Địa chỉ</label>
                <input type="text" name="address" class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none" />
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-xl hover:bg-blue-700 transition font-semibold">Đăng tin</button>
        </form>

        <!-- Danh sách tin đăng -->
        <?php if (count($posts) === 0): ?>
            <p class="text-white text-center bg-black/30 p-2 rounded">Bạn chưa có tin đăng nào.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($posts as $post): ?>
                    <div class="bg-white bg-opacity-90 p-4 rounded-2xl shadow-md backdrop-blur">
                        <h3 class="text-lg font-semibold mb-1"><?= htmlspecialchars($post['tieu_de']) ?></h3>
                        <p class="text-gray-700 mb-1"><?= nl2br(htmlspecialchars($post['mo_ta'])) ?></p>
                        <p class="text-sm text-gray-500 mb-1">
                            Giá: <?= $post['gia'] !== null ? number_format($post['gia'], 0, ',', '.') . " VNĐ" : "Liên hệ" ?> |
                            Địa chỉ: <?= htmlspecialchars($post['dia_chi'] ?: 'Chưa cập nhật') ?>
                        </p>
                        <p class="text-xs text-gray-400">Ngày đăng: <?= $post['ngay_dang'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
