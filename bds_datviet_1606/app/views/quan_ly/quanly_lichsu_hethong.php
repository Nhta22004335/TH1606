<?php
// 1. KẾT NỐI VÀ LẤY THAM SỐ TAB, TÌM KIẾM
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

$active_tab = $_GET['tab'] ?? 'tim_kiem';
$search_term = $_GET['search'] ?? ''; // Khôi phục lấy search_term

// 2. XÂY DỰNG CÂU TRUY VẤN DỰA TRÊN TAB VÀ TÌM KIẾM
$sql = "";
$params = []; // Khôi phục mảng params
$whereClauses = []; // Khôi phục mảng whereClauses

switch ($active_tab) {
    case 'xem_bds':
        $sql = "SELECT ls.id, ls.thoi_gian_xem AS thoi_gian, nd.ten_dang_nhap, bds.id AS tieu_de 
                FROM lich_su_xem_bds ls
                JOIN nguoi_dung nd ON ls.id_nguoi_dung = nd.id
                JOIN bat_dong_san bds ON ls.id_bds = bds.id";
        // Khôi phục điều kiện tìm kiếm
        if (!empty($search_term)) {
            $whereClauses[] = "REPLACE(unaccent(nd.ten_dang_nhap || ' ' || bds.dia_chi_day_du), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
            $params[':search'] = "%{$search_term}%";
        }
        break;

    case 'mua_hang':
        $sql = "SELECT ls.id, ls.ngay_mua AS thoi_gian, nd.ten_dang_nhap, bds.id AS tieu_de, ls.gia_tai_thoi_diem_mua 
                FROM lich_su_mua_hang ls
                JOIN nguoi_dung nd ON ls.id_nguoi_dung = nd.id
                JOIN bat_dong_san bds ON ls.id_bds = bds.id";
        // Khôi phục điều kiện tìm kiếm
        if (!empty($search_term)) {
            $whereClauses[] = "REPLACE(unaccent(nd.ten_dang_nhap || ' ' || bds.dia_chi_day_du), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
            $params[':search'] = "%{$search_term}%";
        }
        break;
        
    case 'tim_kiem':
    default:
        $sql = "SELECT ls.id, ls.thoi_gian_tim_kiem AS thoi_gian, ls.tu_khoa_tim_kiem, COALESCE(nd.ten_dang_nhap, 'Khách vãng lai') AS ten_dang_nhap
                FROM lich_su_tim_kiem ls
                LEFT JOIN nguoi_dung nd ON ls.id_nguoi_dung = nd.id";
        // Khôi phục điều kiện tìm kiếm
        if (!empty($search_term)) {
            $whereClauses[] = "REPLACE(unaccent(ls.tu_khoa_tim_kiem || ' ' || COALESCE(nd.ten_dang_nhap, 'Khách vãng lai')), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
            $params[':search'] = "%{$search_term}%";
        }
        break;
}

// Khôi phục thêm mệnh đề WHERE nếu có tìm kiếm
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}
$sql .= " ORDER BY thoi_gian DESC";

// Khôi phục thực thi với params
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
<div class="max-w-6xl mx-auto p-4 md:p-8">
    <header class="mb-6 border-b border-slate-200 pb-4">
        <h1 class="text-2xl font-bold text-gray-500">Lịch sử Hoạt động</h1>
        <p class="mt-1 text-sm text-slate-600">Theo dõi các hoạt động tìm kiếm, xem và mua hàng của người dùng.</p>
    </header>

    <div class="bg-white p-4 rounded-xl shadow-md border border-slate-200 mb-8">
        <form id="filter-form" method="GET">
             <input type="hidden" name="page" value="quanly_lichsu_hethong"> 
            <nav class="flex items-center border-b border-slate-200 -mx-4 px-4">
                <a href="?page=quanly_lichsu_hethong&tab=tim_kiem" class="px-4 py-3 text-sm font-medium border-b-2 <?= $active_tab === 'tim_kiem' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">Lịch sử Tìm kiếm</a>
                <a href="?page=quanly_lichsu_hethong&tab=xem_bds" class="px-4 py-3 text-sm font-medium border-b-2 <?= $active_tab === 'xem_bds' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">Lịch sử Xem</a>
                <a href="?page=quanly_lichsu_hethong&tab=mua_hang" class="px-4 py-3 text-sm font-medium border-b-2 <?= $active_tab === 'mua_hang' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">Lịch sử Mua hàng</a>
            </nav>
            <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">

            <div class="mt-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" id="search-input" placeholder="Nhập từ khóa tìm kiếm..." value="<?= htmlspecialchars($search_term) ?>" class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                 <a href="?page=quanly_lichsu_hethong&tab=<?= htmlspecialchars($active_tab) ?>" class="px-4 py-2 bg-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-300">Xóa tìm kiếm</a>
                 <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Tìm kiếm</button>
             </div>
        </form>
    </div>

     <script>
         const searchInput = document.getElementById('search-input');
         const currentTab = '<?= htmlspecialchars($active_tab) ?>'; // Lấy tab hiện tại từ PHP

         function submitSearchOnBlur() {
             const searchValue = searchInput.value.trim();
             const baseUrl = `?page=quanly_lichsu_hethong&tab=${currentTab}`;
             
             if (searchValue) {
                 window.location.href = `${baseUrl}&search=${encodeURIComponent(searchValue)}`;
             } else {
                 // Nếu ô tìm kiếm trống, quay về trang không có tham số search
                 window.location.href = baseUrl;
             }
         }

         searchInput.addEventListener('blur', submitSearchOnBlur);

         // Ngăn form submit khi nhấn Enter trong ô tìm kiếm (để dùng blur)
         searchInput.addEventListener('keypress', function(event) {
             if (event.key === 'Enter') {
                 event.preventDefault(); // Ngăn submit mặc định
                 searchInput.blur(); // Kích hoạt sự kiện blur để submit
             }
         });
     </script>

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
                        <tr><td colspan="<?= ($active_tab === 'mua_hang') ? '5' : '4' ?>" class="p-12 text-center text-slate-500">Không tìm thấy dữ liệu phù hợp.</td></tr>
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
                                    <span class="text-slate-400" title="Không thể xóa lịch sử giao dịch"><i class="fas fa-lock"></i></span>
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
    const apiUrl = '../../models/xoa_lichsu_hethong_qt.php'; 

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

                if (!response.ok) throw new Error('Yêu cầu mạng thất bại: ' + response.statusText);
                
                const result = await response.json();

                if (result.status === 'success') {
                    const rowToRemove = document.getElementById(`row-${id}`);
                    if(rowToRemove) {
                        rowToRemove.style.transition = 'opacity 0.3s ease';
                        rowToRemove.style.opacity = '0';
                        setTimeout(() => {
                            rowToRemove.remove();
                            if (tableBody.rows.length === 0) {
                                const colspan = tableBody.previousElementSibling.rows[0].cells.length; 
                                tableBody.innerHTML = `<tr><td colspan="${colspan}" class="p-12 text-center text-slate-500">Không còn dữ liệu.</td></tr>`;
                            }
                        }, 300);
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