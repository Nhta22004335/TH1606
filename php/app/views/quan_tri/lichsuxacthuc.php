<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $limit  = 10; 
    $page   = isset($_GET['p']) && is_numeric($_GET['p']) ? intval($_GET['p']) : 1;
    $offset = ($page - 1) * $limit;

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=lich_su_dn_dx.csv');
        
        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Người dùng', 'Thời gian đăng nhập', 'Thời gian đăng xuất', 'Địa chỉ IP', 'User Agent']);

        $stmt = $pdo->query("
            SELECT nd.ten_dang_nhap, lsdx.thoi_gian_dang_nhap, lsdx.thoi_gian_dang_xuat, lsdx.dia_chi_ip, lsdx.user_agent
            FROM lich_su_dn_dx lsdx
            JOIN nguoi_dung nd ON nd.id = lsdx.id_nguoi_dung
            ORDER BY lsdx.thoi_gian_dang_nhap DESC
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    $totalStmt = $pdo->query("SELECT COUNT(*) FROM lich_su_dn_dx");
    $totalRows = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    $search = $_GET['search'] ?? '';
    if ($search !== '') {
        $sql = "
            SELECT lsdx.*, nd.ten_dang_nhap
            FROM lich_su_dn_dx lsdx
            JOIN nguoi_dung nd ON nd.id = lsdx.id_nguoi_dung
            WHERE nd.ten_dang_nhap ILIKE :searchPattern
            ORDER BY lsdx.thoi_gian_dang_nhap DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':searchPattern', "%$search%", PDO::PARAM_STR);
    } else {
        $sql = "
            SELECT lsdx.*, nd.ten_dang_nhap
            FROM lich_su_dn_dx lsdx
            JOIN nguoi_dung nd ON nd.id = lsdx.id_nguoi_dung
            ORDER BY lsdx.thoi_gian_dang_nhap DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đăng nhập/đăng xuất</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<!-- Header -->
<header class="flex bg-white shadow p-4">
    <img src="../../../public/assets/anhht/0/lichsu.gif" alt="Quản lý sản phẩm" style="width: 40px; height: 40px; margin-right: 10px;">
    <h1 class="text-2xl font-bold text-gray-600">Quản lý lịch sử đăng nhập / đăng xuất</h1>
</header>

<!-- Main -->
<main class="flex-1 p-6">
    <!-- Search & Export CSV -->
    <div class="mb-4 flex justify-between items-center">
        <a href="lichsuauth.php?export=csv" class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">Xuất CSV</a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm text-left border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 border-b">Người dùng</th>
                    <th class="px-4 py-2 border-b">Thời gian đăng nhập</th>
                    <th class="px-4 py-2 border-b">Thời gian đăng xuất</th>
                    <th class="px-4 py-2 border-b">Địa chỉ IP</th>
                    <th class="px-4 py-2 border-b">User Agent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border-b font-medium text-gray-700"><?= htmlspecialchars($log['ten_dang_nhap']) ?></td>
                        <td class="px-4 py-2 border-b"><?= htmlspecialchars($log['thoi_gian_dang_nhap']) ?></td>
                        <td class="px-4 py-2 border-b text-gray-600"><?= $log['thoi_gian_dang_xuat'] ? htmlspecialchars($log['thoi_gian_dang_xuat']) : '<span class="text-red-500">Đang hoạt động</span>' ?></td>
                        <td class="px-4 py-2 border-b"><?= htmlspecialchars($log['dia_chi_ip']) ?></td>
                        <td class="px-4 py-2 border-b truncate max-w-xs" title="<?= htmlspecialchars($log['user_agent']) ?>"><?= htmlspecialchars(substr($log['user_agent'], 0, 50)) ?>...</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-center space-x-2">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-200">«</a>
        <?php endif; ?>

        <?php for ($i=1; $i <= $totalPages; $i++): ?>
            <a href="?page=lichsuauth&p=<?= $i ?>" class="px-3 py-1 border rounded-lg <?= $i == $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-200' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-200">»</a>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
