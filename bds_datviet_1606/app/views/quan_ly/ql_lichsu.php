<?php
// 1. KẾT NỐI VÀ LẤY THAM SỐ
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

$active_tab = $_GET['tab'] ?? 'tim_kiem';
$search_term = $_GET['search'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

// 2. XÂY DỰNG CÂU TRUY VẤN DỰA TRÊN TAB ĐANG HOẠT ĐỘNG
$sql = "";
$params = [];
$whereClauses = [];

switch ($active_tab) {
    case 'xem_bds':
        $sql = "SELECT ls.id, ls.thoi_gian_xem AS thoi_gian, nd.ten_dang_nhap, bds.tieu_de 
                FROM lich_su_xem_bds ls
                JOIN nguoi_dung nd ON ls.id_nguoi_dung = nd.id
                JOIN bat_dong_san bds ON ls.id_bds = bds.id";
        if (!empty($search_term)) {
            $whereClauses[] = "REPLACE(unaccent(nd.ten_dang_nhap || ' ' || bds.tieu_de), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
            $params[':search'] = "%{$search_term}%";
        }
        if (!empty($from_date)) {
            $whereClauses[] = "ls.thoi_gian_xem >= :from_date";
            $params[':from_date'] = $from_date;
        }
        if (!empty($to_date)) {
            $whereClauses[] = "ls.thoi_gian_xem <= :to_date_end";
            $params[':to_date_end'] = $to_date . ' 23:59:59';
        }
        break;

    case 'mua_hang':
        $sql = "SELECT ls.id, ls.ngay_mua AS thoi_gian, nd.ten_dang_nhap, bds.tieu_de, ls.gia_tai_thoi_diem_mua 
                FROM lich_su_mua_hang ls
                JOIN nguoi_dung nd ON ls.id_nguoi_dung = nd.id
                JOIN bat_dong_san bds ON ls.id_bds = bds.id";
        if (!empty($search_term)) {
            $whereClauses[] = "REPLACE(unaccent(nd.ten_dang_nhap || ' ' || bds.tieu_de), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
            $params[':search'] = "%{$search_term}%";
        }
        if (!empty($from_date)) {
            $whereClauses[] = "ls.ngay_mua >= :from_date";
            $params[':from_date'] = $from_date;
        }
        if (!empty($to_date)) {
            $whereClauses[] = "ls.ngay_mua <= :to_date_end";
            $params[':to_date_end'] = $to_date . ' 23:59:59';
        }
        break;
        
    case 'tim_kiem':
    default:
        $sql = "SELECT ls.id, ls.thoi_gian_tim_kiem AS thoi_gian, ls.tu_khoa_tim_kiem, COALESCE(nd.ten_dang_nhap, 'Khách vãng lai') AS ten_dang_nhap
                FROM lich_su_tim_kiem ls
                LEFT JOIN nguoi_dung nd ON ls.id_nguoi_dung = nd.id";
        if (!empty($search_term)) {
            $whereClauses[] = "REPLACE(unaccent(ls.tu_khoa_tim_kiem || ' ' || COALESCE(nd.ten_dang_nhap, 'Khách vãng lai')), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
            $params[':search'] = "%{$search_term}%";
        }
        if (!empty($from_date)) {
            $whereClauses[] = "ls.thoi_gian_tim_kiem >= :from_date";
            $params[':from_date'] = $from_date;
        }
        if (!empty($to_date)) {
            $whereClauses[] = "ls.thoi_gian_tim_kiem <= :to_date_end";
            $params[':to_date_end'] = $to_date . ' 23:59:59';
        }
        break;
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}
$sql .= " ORDER BY thoi_gian DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Lịch sử Hoạt động</title>
</head>
<body class="p-4 sm:p-6 lg:p-8">
<div class="max-w-7xl mx-auto">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Lịch sử Hoạt động</h1>
        <p class="mt-1 text-sm text-slate-500">Theo dõi các hoạt động tìm kiếm, xem và mua hàng của người dùng.</p>
    </header>

    <div class="bg-white p-4 rounded-xl shadow-md border border-slate-200 mb-8">
        <form id="filter-form" method="GET">
            <input type="hidden" name="page" value="ql_lichsu">
            <nav class="flex items-center border-b border-slate-200 -mx-4 px-4">
                <a href="?page=ql_lichsu&tab=tim_kiem" class="px-4 py-3 text-sm font-medium border-b-2 <?= $active_tab === 'tim_kiem' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">Lịch sử Tìm kiếm</a>
                <a href="?page=ql_lichsu&tab=xem_bds" class="px-4 py-3 text-sm font-medium border-b-2 <?= $active_tab === 'xem_bds' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">Lịch sử Xem</a>
                <a href="?page=ql_lichsu&tab=mua_hang" class="px-4 py-3 text-sm font-medium border-b-2 <?= $active_tab === 'mua_hang' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">Lịch sử Mua hàng</a>
            </nav>
            <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <div class="relative md:col-span-2">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" placeholder="Nhập từ khóa tìm kiếm..." value="<?= htmlspecialchars($search_term) ?>" class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none transition">
                </div>
                <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none transition">
                <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none transition">
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <a href="?page=ql_lichsu&tab=<?= htmlspecialchars($active_tab) ?>" class="px-4 py-2 bg-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-300">Xóa bộ lọc</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Áp dụng</button>
            </div>
        </form>
    </div>

    <main class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Người dùng</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= ($active_tab === 'tim_kiem') ? 'Từ khóa tìm kiếm' : 'Nội dung' ?></th>
                        <?php if ($active_tab === 'mua_hang'): ?>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Giá giao dịch</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Thời gian</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase"></th>
                    </tr>
                </thead>
                <tbody id="history-table-body" class="divide-y divide-slate-200">
                    <?php if (empty($data)): ?>
                        <tr><td colspan="5" class="p-12 text-center text-slate-500">Không tìm thấy dữ liệu phù hợp.</td></tr>
                    <?php else: ?>
                        <?php foreach($data as $row): ?>
                        <tr id="row-<?= htmlspecialchars($row['id']) ?>" class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-medium text-slate-900"><?= htmlspecialchars($row['ten_dang_nhap']) ?></td>
                            <td class="px-6 py-4 text-slate-800"><?= htmlspecialchars(($active_tab === 'tim_kiem') ? $row['tu_khoa_tim_kiem'] : $row['tieu_de']) ?></td>
                            <?php if ($active_tab === 'mua_hang'): ?>
                                <td class="px-6 py-4 font-semibold text-green-600"><?= number_format($row['gia_tai_thoi_diem_mua'], 0, ',', '.') ?> VNĐ</td>
                            <?php endif; ?>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap"><?= date("H:i, d/m/Y", strtotime($row['thoi_gian'])) ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($active_tab !== 'mua_hang'): ?>
                                    <button class="delete-btn text-slate-400 hover:text-red-600 transition" title="Xóa" data-id="<?= htmlspecialchars($row['id']) ?>" data-type="<?= htmlspecialchars($active_tab) ?>"><i class="fas fa-trash-can"></i></button>
                                <?php else: ?>
                                     <span class="text-slate-400" title="Không thể xóa lịch sử giao dịch để đảm bảo toàn vẹn dữ liệu"><i class="fas fa-lock"></i></span>
                                <?php endif; ?>
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
    const tableBody = document.getElementById('history-table-body');
    const apiUrl = '/app/models/api_lichsu_delete.php'; // Đảm bảo đường dẫn này chính xác

    if(tableBody) {
        tableBody.addEventListener('click', async function(event) {
            const deleteButton = event.target.closest('.delete-btn');
            if (!deleteButton) return;

            const id = deleteButton.dataset.id;
            const type = deleteButton.dataset.type;

            if (!confirm('Bạn có chắc chắn muốn xóa bản ghi lịch sử này?')) return;

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, type })
                });

                if (!response.ok) throw new Error('Yêu cầu mạng thất bại.');
                
                const result = await response.json();

                if (result.status === 'success') {
                    const rowToRemove = document.getElementById(`row-${id}`);
                    if(rowToRemove) {
                        rowToRemove.style.transition = 'opacity 0.3s ease';
                        rowToRemove.style.opacity = '0';
                        setTimeout(() => rowToRemove.remove(), 300);
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