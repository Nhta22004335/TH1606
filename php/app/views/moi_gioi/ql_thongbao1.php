<?php
require_once __DIR__ . "/../../../config/database.php";
$pdo = ketnoicsdl();

// Bắt đầu phiên
if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy ID người dùng từ session
$id_mg = $_SESSION["id_nguoi_dung"] ?? null;
if (!$id_mg) {
    echo "<div class='p-4 bg-red-100 text-red-800 rounded-lg text-center font-medium'>
            ⚠️ Vui lòng đăng nhập để xem thông báo!
          </div>";
    exit;
}

// Xử lý tìm kiếm
$search = trim($_GET["search"] ?? "");

// Truy vấn lấy thông báo từ CSDL
$sql = "SELECT * FROM thong_bao WHERE id_nguoi_dung = :id_mg";
$params = ["id_mg" => $id_mg];

if ($search) {
    $sql .= " AND noi_dung ILIKE :search";
    $params["search"] = "%$search%";
}
$sql .= " ORDER BY thoi_gian_gui DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$thongbaos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý thông báo - Môi giới</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a2e0ad1b6d.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto py-8 px-6">
    <!-- Tiêu đề -->
    <h1 class="flex items-center text-3xl font-bold text-indigo-700 mb-6 border-b pb-3">
        <i class="fas fa-bell mr-3 text-yellow-500"></i> Quản Lý Thông Báo
    </h1>

    <!-- Thanh tìm kiếm -->
    <div class="bg-white p-4 rounded-xl shadow-md mb-6 flex flex-col md:flex-row md:items-center justify-between space-y-3 md:space-y-0 md:space-x-3">
        <form method="GET" class="w-full md:w-1/2 relative">
            <input type="text" name="search" placeholder="🔍 Tìm kiếm thông báo..."
                   value="<?= htmlspecialchars($search) ?>"
                   class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-full 
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
        </form>

        <a href="?page=../moi_gioi/ql_thongbao" 
           class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-full text-sm transition-all">
            <i class="fas fa-rotate-right mr-1"></i> Làm mới
        </a>
    </div>

    <!-- Bảng thông báo -->
    <?php if (!empty($thongbaos)): ?>
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Nội dung</th>
                        <th class="px-6 py-3 text-center">Ngày gửi</th>
                        <th class="px-6 py-3 text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($thongbaos as $i => $tb): ?>
                        <tr class="<?= $tb["trang_thai"] === "chua_doc" ? 'bg-indigo-50' : 'bg-white' ?> hover:bg-gray-50 transition">
                            <td class="px-6 py-3"><?= $i + 1 ?></td>
                            <td class="px-6 py-3 text-gray-800"><?= htmlspecialchars($tb["noi_dung"]) ?></td>
                            <td class="px-6 py-3 text-center text-gray-600">
                                <?= date('H:i d/m/Y', strtotime($tb["thoi_gian_gui"])) ?>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <?php if ($tb["trang_thai"] === "chua_doc"): ?>
                                    <span class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-600 rounded-full">Chưa đọc</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-600 rounded-full">Đã đọc</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-blue-50 text-blue-700 p-4 rounded-lg text-center">
            <i class="fas fa-info-circle mr-2"></i> Không có thông báo nào để hiển thị.
        </div>
    <?php endif; ?>
</div>
</body>
</html>
