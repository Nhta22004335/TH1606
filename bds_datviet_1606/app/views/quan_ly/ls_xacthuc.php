<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    // Hàm tiện ích để lấy class màu cho badge
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

    // --- Xử lý Phân trang và Lấy dữ liệu ---
    $limit  = 10;
    $page   = isset($_GET['p']) && is_numeric($_GET['p']) ? intval($_GET['p']) : 1;
    $offset = ($page - 1) * $limit;

    // --- Xử lý Xuất file CSV ---
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=lich_su_xac_thuc.csv');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for UTF-8 Excel compatibility
        fputcsv($output, ['Người dùng', 'Loại sự kiện', 'Thời gian bắt đầu', 'Thời gian kết thúc', 'Địa chỉ IP', 'User Agent', 'Ghi chú']);
        
        $stmt = $pdo->query("SELECT nd.ten_dang_nhap, lsxt.loai_su_kien, lsxt.thoi_gian_bat_dau, lsxt.thoi_gian_ket_thuc, lsxt.dia_chi_ip, lsxt.user_agent, lsxt.ghi_chu FROM lich_su_xac_thuc lsxt JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung ORDER BY lsxt.thoi_gian_bat_dau DESC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    // --- Xử lý Tìm kiếm ---
    $search = $_GET['search'] ?? '';
    $baseSql = "FROM lich_su_xac_thuc lsxt JOIN nguoi_dung nd ON nd.id = lsxt.id_nguoi_dung";
    $whereClauses = [];
    $params = [];

    if ($search !== '') {
        $whereClauses[] = "nd.ten_dang_nhap ILIKE :search";
        $params[':search'] = "%$search%";
    }

    $sql = "SELECT lsxt.*, nd.ten_dang_nhap " . $baseSql;
    $countSql = "SELECT COUNT(*) " . $baseSql;

    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(' AND ', $whereClauses);
        $countSql .= " WHERE " . implode(' AND ', $whereClauses);
    }

    // --- Lấy tổng số bản ghi để phân trang ---
    $totalStmt = $pdo->prepare($countSql);
    $totalStmt->execute($params);
    $totalRows = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    // --- Lấy dữ liệu hiển thị cho trang hiện tại ---
    $sql .= " ORDER BY lsxt.thoi_gian_bat_dau DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
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
    <title>Lịch Sử Xác Thực</title>
</head>
<body>

    <div class="space-y-6">
        <header>
            <h1 class="text-2xl font-bold text-gray-900">Lịch Sử Xác Thực</h1>
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
                            value="<?= htmlspecialchars($search) ?>"
                            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-md focus:border-blue-500 focus:outline-none"
                        >
                    </div>
                </form>

                <a href="?page=ls_xacthuc&export=csv"
                   class="flex items-center justify-center px-4 py-2 bg-green-500 border border-gray-300 font-medium text-sm rounded-md text-white hover:bg-green-600 transition flex-shrink-0">
                    <i class="fas fa-file-csv mr-2"></i>
                    Xuất Báo Cáo
                </a>

                <form id="delete-range-form" class="flex flex-col sm:flex-row items-center gap-2 flex-shrink-0">
                    <input type="date" name="delete_from" id="delete-from-input"
                           class="border border-gray-300 text-sm rounded-md p-2 focus:border-blue-500 focus:outline-none" required>
                    <span class="hidden sm:inline text-gray-500">→</span>
                    <input type="date" name="delete_to" id="delete-to-input"
                           class="border border-gray-300 text-sm rounded-md p-2 focus:border-blue-500 focus:outline-none" required>
                    
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
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                <p class="font-medium">Không có dữ liệu</p>
                                <p class="text-sm mt-1">Không tìm thấy bản ghi nào phù hợp.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr data-log-id="<?= $log['id'] ?>">
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
                                <button type="button" class="text-gray-500 hover:text-red-600 delete-log-btn" 
                                        data-id="<?= $log['id'] ?>" title="Xóa">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="flex items-center justify-between pt-2">
            <span class="text-sm text-gray-700">
                Trang <span class="font-semibold"><?= $page ?></span> / <span class="font-semibold"><?= $totalPages ?></span>
            </span>
            <div class="flex items-center space-x-2">
                <a href="?page=ls_xacthuc&p=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md <?= ($page <= 1) ? 'pointer-events-none opacity-50' : '' ?>">
                    Trước
                </a>
                <a href="?page=ls_xacthuc&p=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md <?= ($page >= $totalPages) ? 'pointer-events-none opacity-50' : '' ?>">
                    Sau
                </a>
            </div>
        </nav>
        <?php endif; ?>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- XỬ LÝ TÌM KIẾM ---
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('blur', function() {
                const searchValue = this.value.trim();
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('search', searchValue);
                currentUrl.searchParams.set('p', '1'); // Quay về trang 1 khi tìm kiếm mới
                window.location.href = currentUrl.toString();
            });
        }

        // --- XỬ LÝ XÓA BẰNG FETCH ---
        const apiUrl = '../../models/xoa_ls_xacthuc.php'; // Đảm bảo đường dẫn này đúng!

        // Xử lý xóa từng bản ghi
        document.querySelectorAll('.delete-log-btn').forEach(button => {
            button.addEventListener('click', async function() {
                if (!confirm('Bạn có chắc chắn muốn xóa bản ghi này?')) {
                    return;
                }

                const logId = this.dataset.id;
                const row = this.closest('tr');
                const formData = new FormData();
                formData.append('delete_id', logId);

                try {
                    const response = await fetch(apiUrl, { method: 'POST', body: formData });
                    const result = await response.json();

                    if (result.status === 'success') {
                        row.style.transition = 'opacity 0.3s ease-out';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                        // Cân nhắc hiển thị một thông báo tinh tế hơn alert
                        // Ví dụ: toastr.success(result.message);
                    } else {
                        alert('Lỗi: ' + result.message);
                    }
                } catch (error) {
                    console.error('Lỗi fetch:', error);
                    alert('Không thể kết nối đến máy chủ.');
                }
            });
        });

        // Xử lý xóa theo khoảng thời gian
        const deleteRangeForm = document.getElementById('delete-range-form');
        if (deleteRangeForm) {
            deleteRangeForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const fromDate = document.getElementById('delete-from-input').value;
                const toDate = document.getElementById('delete-to-input').value;

                if (!fromDate || !toDate) {
                    alert('Vui lòng chọn đầy đủ khoảng thời gian.');
                    return;
                }

                if (!confirm(`Bạn có chắc muốn xóa tất cả log từ ${fromDate} đến ${toDate}?`)) {
                    return;
                }

                const formData = new FormData();
                formData.append('delete_from', fromDate);
                formData.append('delete_to', toDate);

                try {
                    const response = await fetch(apiUrl, { method: 'POST', body: formData });
                    const result = await response.json();
                    
                    alert(result.message);

                    if (result.status === 'success') {
                        location.reload(); 
                    }
                } catch (error) {
                    console.error('Lỗi fetch:', error);
                    alert('Không thể kết nối đến máy chủ.');
                }
            });
        }
    });
    </script>

</body>
</html>