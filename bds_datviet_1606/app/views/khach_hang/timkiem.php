<?php
// Kết nối database và session
session_start();
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Xử lý tìm kiếm - Ví dụ: Tìm theo từ khóa
$keyword = $_GET['keyword'] ?? '';
$sql = "SELECT * FROM san_pham_bds WHERE ten_san_pham LIKE :keyword"; // Thêm filter khác nếu cần
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':keyword', "%$keyword%");
$stmt->execute();
$ketqua = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết Quả Tìm Kiếm</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-white p-4 shadow">
        <h1 class="text-2xl font-bold">Kết Quả Tìm Kiếm: <?= htmlspecialchars($keyword) ?></h1>
    </header>
    <main class="max-w-7xl mx-auto p-4">
        <?php if (!empty($ketqua)): ?>
            <ul>
                <?php foreach ($ketqua as $kq): ?>
                    <li class="bg-white p-4 mb-4 shadow rounded">
                        <h2><?= $kq['ten_san_pham'] ?? 'Không tên' ?></h2>
                        <p>Giá: <?= $kq['gia'] ?? 'Chưa có' ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Không tìm thấy kết quả.</p>
        <?php endif; ?>
    </main>
</body>
</html>