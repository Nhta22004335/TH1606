<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    // --- CÁC HÀM TIỆN ÍCH MỚI CHO GIAO DIỆN TIMELINE ---
    function get_event_style($status) {
        $styles = [
            'dangnhap'    => ['color' => 'bg-green-100 text-green-800', 'icon' => 'fa-solid fa-right-to-bracket'],
            'dangxuat'    => ['color' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fa-solid fa-right-from-bracket'],
            'doimatkhau'  => ['color' => 'bg-blue-100 text-blue-800',    'icon' => 'fa-solid fa-key'],
            'quenmatkhau' => ['color' => 'bg-purple-100 text-purple-800', 'icon' => 'fa-solid fa-user-lock'],
            'default'     => ['color' => 'bg-gray-100 text-gray-800',     'icon' => 'fa-solid fa-circle-question']
        ];
        return $styles[$status] ?? $styles['default'];
    }

    function format_event_text($status) {
        $map = [
            'dangnhap'    => 'Đăng nhập thành công',
            'dangxuat'    => 'Đăng xuất khỏi hệ thống',
            'doimatkhau'  => 'Thay đổi mật khẩu',
            'quenmatkhau' => 'Yêu cầu đặt lại mật khẩu'
        ];
        return $map[$status] ?? ucfirst($status);
    }
    
    // ==========================================================
    // == TOÀN BỘ LOGIC PHP CỦA BẠN ĐƯỢC GIỮ NGUYÊN HOÀN TOÀN ==
    // ==========================================================
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
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { fputcsv($output, $row); }
        fclose($output);
        exit;
    }

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
    $totalStmt = $pdo->prepare($countSql);
    $totalStmt->execute($params);
    $totalRows = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);
    $sql .= " ORDER BY lsxt.thoi_gian_bat_dau DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) { $stmt->bindParam($key, $val); }
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
    <title>Lịch Sử Hoạt Động</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        summary::-webkit-details-marker { display: none; }
        summary { list-style: none; cursor: pointer; }
        details summary .chevron { transition: transform 0.2s; }
        details[open] summary .chevron { transform: rotate(90deg); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>
<div class="max-w-5xl mx-auto" x-data="{ isModalOpen: false }">
    <header class="mb-4 border-b pb-2">
        <h1 class="text-2xl font-bold text-gray-500">Lịch Sử Hoạt Động</h1>
        <p class="mt-2 text-sm text-slate-600">Theo dõi tất cả các hoạt động xác thực của người dùng trên hệ thống.</p>
    </header>   
    <div class="flex flex-col md:flex-row items-center justify-between gap-2 mb-4">
        <form id="search-form" method="GET" class="flex-grow md:flex-grow-0 w-full md:w-[320px]">
            <input type="hidden" name="page" value="ls_xacthuc">
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input
                    type="text" name="search" id="search-input"
                    placeholder="Tìm theo tên đăng nhập..."
                    value="<?= htmlspecialchars($search) ?>"
                    class="w-full pl-11 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-lg focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition focus:outline-none"
                >
            </div>
        </form>
        <div x-data="{ isOpen: false }" class="relative flex-shrink-0">
            <button @click="isOpen = !isOpen" class="flex items-center justify-center w-full md:w-auto px-4 py-2.5 bg-slate-100 border border-slate-200 font-medium text-sm rounded-lg text-slate-700 hover:bg-slate-200 transition">
                <i class="fa-solid fa-wand-magic-sparkles mr-2 text-indigo-600"></i>
                Hành động
            </button>
            <div x-show="isOpen" @click.away="isOpen = false" x-cloak x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border z-10">
                <a href="?page=ls_xacthuc&export=csv" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-t-lg">
                    <i class="fas fa-file-csv mr-3 w-4"></i>Xuất Báo Cáo (CSV)
                </a>
                <button @click="isModalOpen = true; isOpen = false" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-lg">
                    <i class="fas fa-trash-alt mr-3 w-4"></i>Xóa Log Theo Ngày...
                </button>
            </div>
        </div>
    </div>
    
    <div class="relative">
        <div class="absolute left-5 top-2 h-full w-0.5 bg-slate-200" aria-hidden="true"></div>
        
        <div class="space-y-8">
            <?php if (empty($logs)): ?>
                <div class="text-center bg-white border border-gray-200/80 rounded-lg py-12 px-6">
                    <i class="fa-solid fa-folder-open fa-3x text-slate-300"></i>
                    <h3 class="mt-4 text-sm font-semibold text-slate-900">Không tìm thấy hoạt động nào</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        <?php if (!empty($search)) : ?>
                            Hãy thử một từ khóa tìm kiếm khác.
                        <?php else : ?>
                            Chưa có hoạt động nào được ghi lại.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($logs as $log): 
                    $style = get_event_style($log['loai_su_kien']);
                ?>
                <div class="relative pl-12 timeline-item" data-log-id="<?= $log['id'] ?>">
                    <div class="absolute left-0 top-1 w-10 h-10 flex items-center justify-center rounded-full <?= $style['color'] ?>">
                        <i class="<?= $style['icon'] ?>"></i>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-200/80 shadow-sm">
                        <div class="p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <p class="font-bold text-slate-800">
                                    <?= htmlspecialchars($log['ten_dang_nhap']) ?>
                                    <span class="font-normal text-slate-500 text-sm ml-1"><?= format_event_text($log['loai_su_kien']) ?></span>
                                </p>
                                <time class="text-xs text-slate-500 mt-1 sm:mt-0"><?= date('d/m/Y H:i:s', strtotime($log['thoi_gian_bat_dau'])) ?></time>
                            </div>
                        </div>
                        <details>
                            <summary class="px-4 py-2 border-t border-gray-200/80 text-xs text-slate-500 font-medium hover:bg-slate-50 rounded-b-lg flex justify-between items-center">
                                <span>Xem chi tiết</span>
                                <i class="fa-solid fa-chevron-right chevron"></i>
                            </summary>
                            <div class="p-4 border-t border-gray-200/80 text-xs text-slate-600 space-y-2">
                                <p><strong>Địa chỉ IP:</strong> <?= htmlspecialchars($log['dia_chi_ip']) ?></p>
                                <p><strong>Ghi chú:</strong> <?= htmlspecialchars($log['ghi_chu'] ?: 'Không có') ?></p>
                                <p class="font-mono bg-slate-100 p-2 rounded"><strong>User Agent:</strong> <?= htmlspecialchars($log['user_agent']) ?></p>
                                <button type="button" class="mt-2 text-red-500 hover:text-red-700 font-semibold delete-log-btn" data-id="<?= $log['id'] ?>">
                                    Xóa bản ghi này
                                </button>
                            </div>
                        </details>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="flex items-center justify-between pt-8">
        <span class="text-sm text-slate-700">
            Trang <span class="font-semibold"><?= $page ?></span> / <span class="font-semibold"><?= $totalPages ?></span>
        </span>
        <div class="flex items-center space-x-2">
            <a href="?page=ls_xacthuc&p=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-md hover:bg-slate-50 <?= ($page <= 1) ? 'pointer-events-none opacity-50' : '' ?>">
                Trước
            </a>
            <a href="?page=ls_xacthuc&p=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-md hover:bg-slate-50 <?= ($page >= $totalPages) ? 'pointer-events-none opacity-50' : '' ?>">
                Sau
            </a>
        </div>
    </nav>
    <?php endif; ?>

    <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="isModalOpen = false" class="bg-white w-full max-w-md rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-slate-800">Xóa Lịch Sử Hoạt Động</h3>
            <p class="text-sm text-slate-600 mt-1">Chọn khoảng thời gian bạn muốn xóa. Hành động này không thể hoàn tác.</p>
            <form id="delete-range-form" class="mt-6 space-y-4">
                <div>
                    <label for="delete-from-input" class="text-sm font-medium text-slate-700">Từ ngày</label>
                    <input type="date" name="delete_from" id="delete-from-input" class="mt-1 w-full border border-slate-300 text-sm rounded-lg p-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label for="delete-to-input" class="text-sm font-medium text-slate-700">Đến ngày</label>
                    <input type="date" name="delete_to" id="delete-to-input" class="mt-1 w-full border border-slate-300 text-sm rounded-lg p-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="isModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50">Hủy</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                        <i class="fas fa-trash-alt mr-1"></i> Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            // Ngăn chặn submit nếu search input trống
            if (document.getElementById('search-input').value.trim() === '') {
                e.preventDefault();
                // Chuyển hướng về trang gốc không có param 'search'
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.delete('search');
                currentUrl.searchParams.set('p', '1');
                window.location.href = currentUrl.toString();
            }
        });
    }

    const apiUrl = '../../models/xoa_ls_xacthuc.php'; 

    // Hàm chung để xóa
    async function deleteLog(formData, confirmationMessage) {
        if (!confirm(confirmationMessage)) return;

        try {
            const response = await fetch(apiUrl, { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                return { success: true, message: result.message };
            } else {
                return { success: false, message: result.message };
            }
        } catch (error) {
            console.error('Lỗi fetch:', error);
            return { success: false, message: 'Không thể kết nối đến máy chủ.' };
        }
    }

    // Xử lý xóa từng bản ghi
    document.body.addEventListener('click', async function(e) {
        if (e.target.closest('.delete-log-btn')) {
            const button = e.target.closest('.delete-log-btn');
            const logId = button.dataset.id;
            const formData = new FormData();
            formData.append('delete_id', logId);

            const result = await deleteLog(formData, 'Bạn có chắc chắn muốn xóa bản ghi này?');
            
            if (result.success) {
                const item = button.closest('.timeline-item');
                item.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(() => item.remove(), 300);
            } else {
                alert('Lỗi: ' + result.message);
            }
        }
    });

    // Xử lý xóa theo khoảng thời gian
    const deleteRangeForm = document.getElementById('delete-range-form');
    if (deleteRangeForm) {
        deleteRangeForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const fromDate = document.getElementById('delete-from-input').value;
            const toDate = document.getElementById('delete-to-input').value;
            const formData = new FormData(deleteRangeForm);

            const result = await deleteLog(
                formData, 
                `Bạn có chắc muốn xóa tất cả log từ ${fromDate} đến ${toDate}?`
            );
            
            alert(result.message);
            if (result.success) {
                location.reload();
            }
        });
    }
});
</script>

</body>
</html>