<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    function get_status_badge($status) {
        $styles = [
            'dangnhap'    => 'bg-green-100 text-green-800',
            'dangxuat'    => 'bg-yellow-100 text-yellow-800',
            'doimatkhau'  => 'bg-blue-100 text-blue-800',
            'quenmatkhau' => 'bg-purple-100 text-purple-800',
            'default'     => 'bg-gray-100 text-gray-800'
        ];
        return $styles[$status] ?? $styles['default'];
    }

    $limit  = 10;
    $page   = isset($_GET['p']) && is_numeric($_GET['p']) ? intval($_GET['p']) : 1;
    $offset = ($page - 1) * $limit;

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=lich_su_xac_thuc.csv');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Người dùng', 'Loại sự kiện', 'Thời gian bắt đầu', 'Thời gian kết thúc', 'Địa chỉ IP', 'User Agent', 'Ghi chú']);
        $stmt = $pdo->query("SELECT nd.ten_dang_nhap, lsxt.loai_su_kien, lsxt.thoi_gian_bat_dau, lsxt.thoi_gian_ket_thuc, lsxt.dia_chi_ip, lsxt.user_agent, lsxt.ghi_chu FROM lich_su_xac_thuc lsxt JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung ORDER BY lsxt.thoi_gian_bat_dau DESC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    $search = $_GET['search'] ?? '';
    $filters = isset($_GET['boloc']) ? json_decode($_GET['boloc'], true) : [];

    $baseSql = "FROM lich_su_xac_thuc lsxt JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung";
    $whereClauses = [];
    $params = [];

    if ($search !== '') {
        $whereClauses[] = "nd.ten_dang_nhap ILIKE :search";
        $params[':search'] = "%$search%";
    }
    if (!empty($filters['loaisukien'])) {
        $whereClauses[] = "lsxt.loai_su_kien = :loaisukien";
        $params[':loaisukien'] = $filters['loaisukien'];
    }

    $sql = "SELECT lsxt.*, nd.ten_dang_nhap " . $baseSql;
    $countSql = "SELECT COUNT(*) " . $baseSql;

    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(' AND ', $whereClauses);
        $countSql .= " WHERE " . implode(' AND ', $whereClauses);
    }

    $totalStmt = $pdo->prepare($countSql);
    $totalStmt->execute($params);
    $totalRows = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    $sql .= " ORDER BY lsxt.thoi_gian_bat_dau DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete_from'], $_POST['delete_to'])) {
            $from = $_POST['delete_from'];
            $to   = $_POST['delete_to'];
            if ($from && $to) {
                $stmt = $pdo->prepare("DELETE FROM lich_su_xac_thuc WHERE thoi_gian_bat_dau BETWEEN :from AND :to");
                $stmt->execute([':from' => $from, ':to' => $to]);
                echo "<script>alert('Xóa thành công!')</script>";
            } else {
                echo "<script>alert('Vui lòng chọn đầy đủ khoảng thời gian.')</script>";
            }
        } elseif (isset($_POST['delete_id'])) {
            $id = $_POST['delete_id'];
            $stmt = $pdo->prepare("DELETE FROM lich_su_xac_thuc WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() > 0) {
                echo "<script>alert('Đã xóa bản ghi thành công!')</script>";
            } else
                echo "<script>alert('Không tìm thấy bản ghi!')</script>";
        }
    }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Xác Thực</title>
</head>
<body>

    <div class="space-y-6">
        <header>
            <h1 class="text-2xl font-bold text-gray-900">
                Lịch Sử Xác Thực
            </h1>
            <p class="mt-1 text-sm text-gray-600">Ghi nhận các hoạt động truy cập và bảo mật của người dùng.</p>
        </header>

        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex flex-col md:flex-row items-center gap-3 flex-wrap">
                <form id="search-form" method="GET" class="flex-grow w-full md:w-auto">
                    <input type="hidden" name="page" value="ls_xacthuc">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            name="search"
                            id="search-input"
                            placeholder="Tìm theo tên đăng nhập..."
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-md focus:border-blue-500 focus:outline-none"
                        >
                    </div>
                </form>

                <!-- Xuất báo cáo -->
                <a href="?page=ls_xacthuc&export=csv"
                class="flex items-center justify-center px-4 py-2 bg-green-500 border border-gray-300 font-medium text-sm rounded-md text-white hover:bg-gray-100 transition flex-shrink-0">
                    <i class="fas fa-file-csv mr-2"></i>
                    Xuất Báo Cáo
                </a>

                <!-- Chọn khoảng thời gian -->
                <form method="POST" class="flex flex-col sm:flex-row items-center gap-2 flex-shrink-0">
                    <input type="date" name="delete_from"
                        class="border border-gray-300 text-sm rounded-md p-2 focus:border-blue-500 focus:outline-none"
                        required>
                    <span class="hidden sm:inline text-gray-500">→</span>
                    <input type="date" name="delete_to"
                        class="border border-gray-300 text-sm rounded-md p-2 focus:border-blue-500 focus:outline-none"
                        required>

                    <!-- Nút Xóa -->
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition">
                        <i class="fas fa-trash-alt mr-1"></i> Xóa
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Người dùng</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Sự kiện</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Thời gian</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Địa chỉ IP</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase hidden xl:table-cell">User Agent</th>
                        <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($log['ten_dang_nhap']) ?></td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full <?= get_status_badge($log['loai_su_kien']) ?>">
                                <?= htmlspecialchars(ucfirst($log['loai_su_kien'])) ?>
                            </span>
                        </td>
                        <td class="p-3 text-gray-600 whitespace-nowrap"><?= htmlspecialchars($log['thoi_gian_bat_dau']) ?></td>
                        <td class="p-3 text-gray-600"><?= htmlspecialchars($log['dia_chi_ip']) ?></td>
                        <td class="p-3 text-gray-600 max-w-sm truncate hidden xl:table-cell" title="<?= htmlspecialchars($log['user_agent']) ?>">
                            <?= htmlspecialchars($log['user_agent']) ?>
                        </td>
                        <td class="p-3 text-center">
                            <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi này?');">
                                <input type="hidden" name="delete_id" value="<?= $log['id'] ?>">
                                <button type="submit" class="text-gray-500" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            <p class="font-medium">Không có dữ liệu</p>
                            <p class="text-sm mt-1">Không tìm thấy bản ghi nào phù hợp.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
        <nav class="flex items-center justify-between pt-2">
            <span class="text-sm text-gray-700">
                Trang <span class="font-semibold"><?= $page ?></span> / <span class="font-semibold"><?= $totalPages ?></span>
            </span>
            <div class="flex items-center space-x-2">
                <a href="?page=ls_xacthuc&p=<?= $page - 1 ?>" class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md <?= ($page <= 1) ? 'pointer-events-none opacity-50' : '' ?>">
                    Trước
                </a>
                <a href="?page=ls_xacthuc&p=<?= $page + 1 ?>" class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md <?= ($page >= $totalPages) ? 'pointer-events-none opacity-50' : '' ?>">
                    Sau
                </a>
            </div>
        </nav>
        <?php endif; ?>

    </div>

    <script>
        // 1. Lấy các phần tử HTML cần thiết qua ID
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');

        // 2. Hàm để thực hiện submit
        function submitSearch() {
            const searchValue = searchInput.value;

            const encodedSearchValue = encodeURIComponent(searchValue.trim());

            const newUrl = `trangchu.php?page=ls_xacthuc&search=${encodedSearchValue}`;
            const trove = `trangchu.php?page=ls_xacthuc`;
            if (searchValue) {
                window.location.href = newUrl;          
            } else {
                window.location.href = trove;
            }
        }

        // 3. Gán sự kiện bỏ focus cho ô tìm kiếm
        searchInput.addEventListener('blur', function() {
            submitSearch(); // thực hiện tìm kiếm khi rời khỏi ô input
        });

        function applyFilters() {
            const filterValue = document.getElementById("loaisukien").value;
            const currentUrl = new URL(window.location.href);
            const params = new URLSearchParams(currentUrl.search);
            
            if (filterValue) {
                params.set('boloc', JSON.stringify({ loaisukien: filterValue }));
            } else {
                params.delete('boloc');
            }
            params.set('p', '1'); // Reset về trang 1 khi lọc
            window.location.href = `${currentUrl.pathname}?${params.toString()}`;
        }

        document.getElementById("btnloc").addEventListener("click", applyFilters);

        function clearFilters() {
            const currentUrl = new URL(window.location.href);
            const params = new URLSearchParams(currentUrl.search);
            params.delete('boloc');
            params.set('p', '1');
            window.location.href = `${currentUrl.pathname}?${params.toString()}`;
        }

        document.getElementById("btnhuy").addEventListener("click", clearFilters);
    </script>

</body>
</html>