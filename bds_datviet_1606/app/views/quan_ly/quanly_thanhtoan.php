<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

$status_map = [
    'choxuly' => ['text' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
    'dangxuly' => ['text' => 'Đang xử lý', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
    'hoantat' => ['text' => 'Hoàn tất', 'class' => 'bg-green-100 text-green-800 border-green-200'],
    'dahuy' => ['text' => 'Đã hủy', 'class' => 'bg-red-100 text-red-800 border-red-200']
];

// ==========================================================
// == THAY ĐỔI SQL ĐỂ ĐỒNG BỘ VỚI BẢNG bat_dong_san MỚI ==
// ==========================================================
$search = $_GET['search'] ?? '';
$sql = "
    SELECT 
        gd.id, 
        nd.ten_dang_nhap, 
        bds.dia_chi_day_du, -- Lấy dia_chi_day_du thay vì tieu_de
        gd.loai, 
        gd.ngay_giao_dich, 
        gd.trang_thai
    FROM giao_dich gd
    LEFT JOIN nguoi_dung nd ON gd.id_nguoi_dung = nd.id
    LEFT JOIN bat_dong_san bds ON gd.id_bds = bds.id -- Join bảng bat_dong_san
";
$params = [];
$where = [];

if (!empty($search)) {
    // Cập nhật cột tìm kiếm
    $searchable_columns = "nd.ten_dang_nhap || ' ' || bds.dia_chi_day_du"; 
    $where[] = "REPLACE(unaccent({$searchable_columns}), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
    $params[':search'] = "%$search%";
}

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY gd.ngay_giao_dich DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$giaodich = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Giao dịch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .overflow-visible { overflow: visible; }
    </style>
</head>
<body>
<div class="max-w-7xl mx-auto">
    
    <?php if (!empty($_GET['message'])): ?>
        <div id="alertBox" class="p-4 rounded-lg border <?= ($_GET['msg_type'] ?? 'error') === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' ?> mb-6">
            <?= htmlspecialchars(urldecode($_GET['message'])) ?>
        </div>
    <?php endif; ?>

    <header class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-500">Quản lý Giao dịch</h1>
            <p class="mt-1 text-sm text-slate-600">Theo dõi và quản lý tất cả các giao dịch trên hệ thống.</p>
        </div>
        <form id="search-form" method="GET" class="w-full sm:w-72">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="search-input" name="search" placeholder="Tìm người dùng, địa chỉ BĐS..." 
                       value="<?= htmlspecialchars($search) ?>"
                       class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none transition">
            </div>
        </form>
    </header>
    <script>
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');

        // 2. Hàm để thực hiện submit
        function submitSearch() {
            const searchValue = searchInput.value;

            const encodedSearchValue = encodeURIComponent(searchValue.trim());

            const newUrl = `trangchu.php?page=quanly_thanhtoan&search=${encodedSearchValue}`;
            const trove = `trangchu.php?page=quanly_thanhtoan`;
            if (searchValue) {
                window.location.href = newUrl;          
            } else {
                window.location.href = trove;
            }
        }

        // 4. Gán sự kiện bỏ focus cho ô tìm kiếm
        searchInput.addEventListener('blur', function() {
            submitSearch();
        });
    </script>

    <main id="main-card" class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
        <div id="table-container" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Khách hàng</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Bất động sản (Địa chỉ)</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Ngày Giao Dịch</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase"></th>
                    </tr>
                </thead>
                <tbody id="transactions-table-body" class="divide-y divide-slate-200">
                    <?php if (empty($giaodich)): ?>
                        <tr>
                            <td colspan="5" class="p-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <h3 class="mt-2 text-lg font-medium text-slate-900">Không tìm thấy giao dịch</h3>
                                <p class="mt-1 text-sm text-slate-500">Hãy thử lại với một từ khóa tìm kiếm khác.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($giaodich as $gd): ?>
                        <tr id="transaction-row-<?= $gd['id'] ?>" class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900"><?= htmlspecialchars($gd['ten_dang_nhap'] ?? 'N/A') ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-800 max-w-xs truncate">
                                    <?= htmlspecialchars($gd['dia_chi_day_du'] ?? 'Chưa cập nhật') ?>
                                </p>
                                <?php $loai_map = ['mua' => 'Mua', 'ban' => 'Bán', 'thue' => 'Thuê']; ?>
                                <p class="text-xs text-slate-500 capitalize"><?= htmlspecialchars($loai_map[$gd['loai']] ?? 'Không xác định') ?></p>
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap"><?= date("d/m/Y H:i", strtotime($gd['ngay_giao_dich'])) ?></td>
                            <td class="px-6 py-4 status-cell">
                                <?php $status_info = $status_map[$gd['trang_thai']] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-100 text-gray-800']; ?>
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full border <?= $status_info['class'] ?>"><?= $status_info['text'] ?></span>
                            </td>
                            <td class="px-6 py-4 text-center actions-cell">
                                <div class="relative inline-block text-left" data-menu>
                                    <button type="button" data-menu-button class="p-2 rounded-full text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"><i class="fas fa-ellipsis-v"></i></button>
                                    <div data-menu-items class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden z-10">
                                        <div class="py-1" role="none">
                                            <?php if ($gd['trang_thai'] === 'choxuly'): ?>
                                                <button type="button" class="action-btn w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" data-id="<?= $gd['id'] ?>" data-action="dangxuly">Bắt đầu xử lý</button>
                                                <button type="button" class="action-btn w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" data-id="<?= $gd['id'] ?>" data-action="dahuy">Hủy giao dịch</button>
                                            <?php elseif ($gd['trang_thai'] === 'dangxuly'): ?>                                                
                                                <button type="button" class="action-btn w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" data-id="<?= $gd['id'] ?>" data-action="dahuy">Hủy giao dịch</button>
                                            <?php endif; ?>
                                            <a href="trangchu.php?page=quanly_chitiet_thanhtoan&id=<?= $gd['id'] ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Xem chi tiết</a>
                                            <div class="border-t my-1 border-slate-100"></div>
                                            <button type="button" class="action-btn w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700" data-id="<?= $gd['id'] ?>" data-action="xoa">Xóa</d>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>

document.addEventListener("DOMContentLoaded", function() {
    const mainCard = document.getElementById('main-card');
    const tableContainer = document.getElementById('table-container');

    document.querySelectorAll('[data-menu-button]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const menu = button.nextElementSibling;
            const isOpening = menu.classList.contains('hidden');
            document.querySelectorAll('[data-menu-items]').forEach(m => m.classList.add('hidden'));
            if (tableContainer) tableContainer.classList.remove('overflow-visible');
            if (mainCard) mainCard.classList.remove('overflow-visible');
            if (isOpening) {
                menu.classList.remove('hidden');
                if (tableContainer) tableContainer.classList.add('overflow-visible');
                if (mainCard) mainCard.classList.add('overflow-visible');
            }
        });
    });
    window.addEventListener('click', () => {
        document.querySelectorAll('[data-menu-items]').forEach(menu => menu.classList.add('hidden'));
        if (tableContainer) tableContainer.classList.remove('overflow-visible');
        if (mainCard) mainCard.classList.remove('overflow-visible');
    });

    const tableBody = document.getElementById('transactions-table-body');
    const apiUrl = '../../models/cn_trangthai_giaodich_qt.php'; 

    if(tableBody) {
        tableBody.addEventListener('click', async function(event) {
            const targetButton = event.target.closest('.action-btn');
            if (!targetButton) return;

            const id = targetButton.dataset.id;
            const action = targetButton.dataset.action;

            const messages = {
                dangxuly: 'Bạn có chắc muốn bắt đầu xử lý giao dịch này?',
                hoantat: 'Bạn có chắc muốn đánh dấu giao dịch này là hoàn tất?',
                dahuy: 'Bạn có chắc muốn hủy giao dịch này?',
                xoa: 'Bạn có chắc chắn muốn XÓA vĩnh viễn giao dịch này?'
            };

            if (!confirm(messages[action] || 'Bạn có chắc chắn?')) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', action);

            try {
                const response = await fetch(apiUrl, { method: 'POST', body: formData });
                if (!response.ok) throw new Error('Yêu cầu mạng thất bại.');
                
                const result = await response.json();

                if (result.status === 'success') {
                    const row = document.getElementById(`transaction-row-${id}`);
                    if (action === 'xoa') {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    } else {
                        // Cập nhật "live"
                        row.querySelector('.status-cell').innerHTML = result.newStatusHtml;
                        row.querySelector('.actions-cell .py-1').innerHTML = result.newActionsHtml;
                    }
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch(error) {
                console.error('Lỗi Fetch:', error);
                alert('Đã xảy ra lỗi khi gửi yêu cầu đến máy chủ.');
            }
        });
    }
});

</script>
</body>
</html>