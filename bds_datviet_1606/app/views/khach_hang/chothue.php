<?php
// Kết nối database và session
session_start();
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Xử lý logic cho "Cho thuê"
$sql = "SELECT * FROM san_pham_bds WHERE loai_giao_dich = 'Cho thuê'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$sanpham = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cho Thuê Bất Động Sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-white p-4 shadow">
        <h1 class="text-2xl font-bold">Danh sách sản phẩm Cho Thuê</h1>
    </header>
    <main class="max-w-7xl mx-auto p-4">
        <?php if (!empty($sanpham)): ?>
            <ul>
                <?php foreach ($sanpham as $sp): ?>
                    <li class="bg-white p-4 mb-4 shadow rounded">
                        <h2><?= $sp['ten_san_pham'] ?? 'Không tên' ?></h2>
                        <p>Giá thuê: <?= $sp['gia_thue'] ?? 'Chưa có' ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Không có sản phẩm cho thuê.</p>
        <?php endif; ?>
    </main>
</body>
</html>