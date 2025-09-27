<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $limit  = 10; 
    $page   = isset($_GET['p']) && is_numeric($_GET['p']) ? intval($_GET['p']) : 1;
    $offset = ($page - 1) * $limit;

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=lich_su_xac_thuc.csv');
        
        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['Người dùng', 'Loại sự kiện', 'Thời gian bắt đầu', 'Thời gian kết thúc', 'Địa chỉ IP', 'User Agent', 'Ghi chú']);

        $stmt = $pdo->query("
            SELECT nd.ten_dang_nhap, lsxt.loai_su_kien, lsxt.thoi_gian_bat_dau, lsxt.thoi_gian_ket_thuc, lsxt.dia_chi_ip, lsxt.user_agent, lsxt.ghi_chu
            FROM lich_su_xac_thuc lsxt
            JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung
            ORDER BY lsxt.thoi_gian_bat_dau DESC
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    $totalStmt = $pdo->query("SELECT COUNT(*) FROM lich_su_xac_thuc");
    $totalRows = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    $search = $_GET['search'] ?? '';
    if ($search !== '') {
        $sql = "
            SELECT lsxt.*, nd.ten_dang_nhap
            FROM lich_su_xac_thuc lsxt
            JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung
            WHERE nd.ten_dang_nhap ILIKE :searchPattern
            ORDER BY lsxt.thoi_gian_bat_dau DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':searchPattern', "%$search%", PDO::PARAM_STR);
    } else {
        $sql = "
            SELECT lsxt.*, nd.ten_dang_nhap
            FROM lich_su_xac_thuc lsxt
            JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung
            ORDER BY lsxt.thoi_gian_bat_dau DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_from'], $_POST['delete_to'])) {
        $from = $_POST['delete_from'];
        $to   = $_POST['delete_to'];

        if ($from && $to) {
            $stmt = $pdo->prepare("DELETE FROM lich_su_xac_thuc WHERE thoi_gian_bat_dau BETWEEN :from AND :to");
            $stmt->execute([
                ':from' => $from,
                ':to'   => $to
            ]);

            $deletedRows = $stmt->rowCount();
            $_SESSION['message'] = "✅ Đã xóa $deletedRows bản ghi từ $from đến $to.";
        } else {
            $_SESSION['message'] = "⚠️ Vui lòng chọn đầy đủ khoảng thời gian.";
        }
    }

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
    <?php if (!empty($_SESSION['message'])): ?>
        <div id="alertBox" class="mb-4 p-3 rounded-lg bg-green-100 text-green-700 shadow">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <!-- Search & Export CSV -->
    <div class="mb-4 flex justify-between items-center">
        <a href="lichsuauth.php?export=csv" class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">Xuất CSV</a>
        <form method="POST" class="flex space-x-2 items-center">
            <input type="date" name="delete_from" class="border rounded-lg p-2" required>
            <span>đến</span>
            <input type="date" name="delete_to" class="border rounded-lg p-2" required>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700">Xóa</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm text-left border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 border-b">Người dùng</th>
                    <th class="px-4 py-2 border-b">Loại sự kiện</th>
                    <th class="px-4 py-2 border-b">Thời gian bắt đầu</th>
                    <th class="px-4 py-2 border-b">Thời gian kết thúc</th>
                    <th class="px-4 py-2 border-b">Địa chỉ IP</th>
                    <th class="px-4 py-2 border-b">User Agent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border-b font-medium text-gray-700">
                            <?= htmlspecialchars($log['ten_dang_nhap']) ?>
                        </td>
                        <td class="px-4 py-2 border-b text-blue-600 font-semibold">
                            <?= htmlspecialchars($log['loai_su_kien']) ?>
                        </td>
                        <td class="px-4 py-2 border-b">
                            <?= htmlspecialchars($log['thoi_gian_bat_dau']) ?>
                        </td>
                        <td class="px-4 py-2 border-b text-gray-600">
                            <?= $log['thoi_gian_ket_thuc'] 
                                ? htmlspecialchars($log['thoi_gian_ket_thuc']) 
                                : '<span class="text-red-500">Chưa kết thúc</span>' ?>
                        </td>
                        <td class="px-4 py-2 border-b">
                            <?= htmlspecialchars($log['dia_chi_ip']) ?>
                        </td>
                        <td class="px-4 py-2 border-b truncate max-w-xs" 
                            title="<?= htmlspecialchars($log['user_agent']) ?>">
                            <?= htmlspecialchars(substr($log['user_agent'], 0, 50)) ?>...
                        </td>
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
            <a href="?page=lichsuxacthuc&p=<?= $i ?>" class="px-3 py-1 border rounded-lg <?= $i == $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-200' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>" class="px-3 py-1 border rounded-lg hover:bg-gray-200">»</a>
        <?php endif; ?>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let alertBox = document.getElementById("alertBox");
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.display = "none";
                window.location.href = "trangchu.php?page=lichsuxacthuc";
            }, 2000); 
        }
    });
</script>

</body>
</html>
