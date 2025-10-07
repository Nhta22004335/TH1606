<?php
require_once __DIR__ . "/../../../config/database.php";

$pdo = ketnoicsdl();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_mg = $_SESSION["id_nguoi_dung"] ?? null;

if (!$id_mg) {
    echo "<div class='p-4 bg-red-200 text-red-800 rounded'>Vui lòng đăng nhập!</div>";
    exit;
}

$sql = "SELECT * FROM thong_bao WHERE id_nguoi_dung = :id_mg";
$stmt = $pdo->prepare($sql);
$stmt->execute(["id_mg" => $id_mg]);
$thongbaos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý thông báo - Môi giới</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto py-8 px-4">
    <h3 class="mb-6 text-2xl font-semibold text-blue-600">📢 Quản lý thông báo</h3>

    <?php if (!empty($thongbaos)): ?>
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($thongbaos as $i => $tb): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?= $i+1 ?></td>
                        <td class="px-6 py-4 whitespace-normal"><?= htmlspecialchars($tb["noi_dung"]) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= $tb["thoi_gian_gui"] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($tb["trang_thai"] === "chua_doc"): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Chưa đọc</span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đã đọc</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="p-4 bg-blue-100 text-blue-800 rounded">Không có thông báo nào!</div>
    <?php endif; ?>
</div>
</body>
</html>
