<?php
// filepath: d:\TMDT\TH1606\php\app\views\khachhang\ChitietSanpham.php
session_start();
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM san_pham_bds WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
    
</head>
<body class="bg-gray-100">
    <main class="max-w-3xl mx-auto p-6 bg-white mt-8 rounded shadow">
        <?php if ($sp): ?>
            <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($sp['ten_san_pham']) ?></h1>
            <p><strong>Giá:</strong> <?= htmlspecialchars($sp['gia']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($sp['dia_chi']) ?></p>
            <p><strong>Mô tả:</strong> <?= nl2br(htmlspecialchars($sp['mo_ta'])) ?></p>
            <!-- Thêm các trường khác nếu cần -->
            <a href="Muaban.php" class="inline-block mt-4 text-blue-600 hover:underline">← Quay lại danh sách</a>
        <?php else: ?>
            <p>Không tìm thấy sản phẩm.</p>
        <?php endif; ?>
    </main>
</body>
</html>