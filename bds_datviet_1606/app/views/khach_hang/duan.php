<?php
// Kết nối database và session
session_start();
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Xử lý logic cho "Dự án"
$sql = "SELECT * FROM du_an_bds"; // Giả sử có bảng du_an_bds
$stmt = $pdo->prepare($sql);
$stmt->execute();
$duan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dự Án Bất Động Sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-white p-4 shadow">
        <h1 class="text-2xl font-bold">Danh sách Dự Án</h1>
    </header>
    <main class="max-w-7xl mx-auto p-4">
        <?php if (!empty($duan)): ?>
            <ul>
                <?php foreach ($duan as $da): ?>
                    <li class="bg-white p-4 mb-4 shadow rounded">
                        <h2><?= $da['ten_du_an'] ?? 'Không tên' ?></h2>
                        <p>Địa điểm: <?= $da['dia_diem'] ?? 'Chưa có' ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Không có dự án.</p>
        <?php endif; ?>
    </main>
</body>
</html>