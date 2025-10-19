<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../../../config/database.php"; 
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
}

$search = $_GET['search'] ?? '';
$search = trim($search); 

// ==========================================================
// == THAY ĐỔI LỚN 1: CÂU LỆNH SQL ĐỒNG BỘ VỚI BẢNG MỚI ==
// ==========================================================
$sql = "
    SELECT 
        bd.id, bd.tieu_de, bd.ngay_dang, bd.luot_xem, bd.trang_thai, bd.hinh_thuc,
        bd.dia_chi_lien_he, -- Lấy cột mới
        COALESCE(bds.dien_tich_su_dung, bds.dien_tich_dat) AS dien_tich, -- Lấy diện tích từ BĐS
        bds.dia_chi_day_du AS dia_chi_bds, -- Lấy địa chỉ BĐS
        info.ho_ten AS ten_moigioi, 
        nd.avt AS avatar_moigioi,
        anh_bia.url AS anh_bia
    FROM bai_dang AS bd
    JOIN bat_dong_san AS bds ON bd.id_bat_dong_san = bds.id
    LEFT JOIN nguoi_dung AS nd ON bd.id_nguoi_dung = nd.id
    LEFT JOIN info_nguoi_dung AS info ON nd.id = info.id_nguoi_dung
    LEFT JOIN LATERAL (
        SELECT url FROM hinh_anh_bds 
        WHERE id_bds = bds.id 
        ORDER BY ngay_tao ASC 
        LIMIT 1
    ) AS anh_bia ON TRUE
";

$params = [];

if (!empty($search)) {
    // Cập nhật cột tìm kiếm
    $searchable_columns = "bd.tieu_de || ' ' || bds.dia_chi_day_du || ' ' || COALESCE(info.ho_ten, '') || ' ' || bd.dia_chi_lien_he";
    $sql .= " WHERE REPLACE(unaccent({$searchable_columns}), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
    $params[':search'] = "%" . $search . "%";
}

$sql .= " ORDER BY bd.ngay_dang DESC;";
$stmt = $pdo->prepare($sql);
if (!empty($search)) {
    $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
}
$stmt->execute();
$baidang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý đường dẫn ảnh
foreach ($baidang as $key => $post) {
    if (empty($post['anh_bia'])) {
        $baidang[$key]['anh_bia'] = 'https://picsum.photos/300/200?random=' . $key;
    } else {
        $baidang[$key]['anh_bia'] = '../../../storage/pictures/bds/' . $post['anh_bia'];
    }
    $baidang[$key]['avatar_moigioi'] = '../../../storage/pictures/avt/' . ($post['avatar_moigioi'] ?? 'default-avatar.png');
}

// ==========================================================
// == THAY ĐỔI LỚN 2: CẬP NHẬT HÀM TRẠNG THÁI ==
// ==========================================================
function getStatusBadge($status) {
    $map = [
        'chuaduyet' => ['text' => 'Chờ duyệt', 'class' => 'bg-orange-100 text-orange-800'],
        'daduyet'   => ['text' => 'Đang hiển thị', 'class' => 'bg-green-100 text-green-800'],
        'hethan'    => ['text' => 'Hết hạn', 'class' => 'bg-red-100 text-red-800'],
        'dahuy'     => ['text' => 'Đã hủy', 'class' => 'bg-gray-100 text-gray-800'], // Thêm trạng thái dahuy
    ];
    $info = $map[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-800'];
    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$info['class']}'>{$info['text']}</span>";
}

// Tính toán stats (cập nhật theo trạng thái mới)
$stats = [
    'pending' => count(array_filter($baidang, fn($p) => $p['trang_thai'] === 'chuaduyet')),
    'active'  => count(array_filter($baidang, fn($p) => $p['trang_thai'] === 'daduyet')),
    'expired' => count(array_filter($baidang, fn($p) => in_array($p['trang_thai'], ['hethan', 'dahuy']))), // Gộp hết hạn và hủy
    'total'   => count($baidang),
];

?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bài đăng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        summary::marker { content: ''; }
    </style>
</head>
<body class="h-full p-4 md:p-6">
<div class="max-w-7xl mx-auto">
    <header>
        <div class="sm:flex sm:items-center sm:justify-between mb-6 pb-4 border-b">
            <div>
                <h1 class="text-2xl font-bold text-gray-500">Quản lý Bài đăng</h1>
                <p class="mt-1 text-sm text-slate-600">Tổng quan và kiểm duyệt tất cả bài đăng của môi giới.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Chờ duyệt</dt><dd class="mt-1 text-3xl font-semibold text-orange-500"><?= $stats['pending'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Đang hiển thị</dt><dd class="mt-1 text-3xl font-semibold text-green-600"><?= $stats['active'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Hết hạn / Đã hủy</dt><dd class="mt-1 text-3xl font-semibold text-red-600"><?= $stats['expired'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Tổng số bài</dt><dd class="mt-1 text-3xl font-semibold text-slate-900"><?= $stats['total'] ?></dd></dl></div></div>
        </div>
    </header>

    <main class="mt-6">
        <form action="" id="search-form" method="GET" class="mb-6">
             <input type="hidden" name="page" value="ql_baidang">
             <input type="text" id="search-input" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Tìm theo tiêu đề, địa chỉ, môi giới..." class="w-full sm:w-80 border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </form>
    
        <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200/80">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Bài đăng</th>
                            <th scope="col" class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Môi giới</th>
                            <th scope="col" class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Ngày đăng</th>
                            <th scope="col" class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Lượt xem</th>
                            <th scope="col" class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                            <th scope="col" class="p-4 text-left text-xs font-bold text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody id="post-table-body" class="divide-y divide-slate-100"> <?php if (empty($baidang)): ?>
                            <tr><td colspan="6" class="p-8 text-center text-gray-500">Không tìm thấy bài đăng nào.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($baidang as $post): ?>
                            <tr id="post-row-<?= $post['id'] ?>" class="hover:bg-slate-50 transition-colors"> <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img src="<?= htmlspecialchars($post['anh_bia']) ?>" class="w-24 h-16 rounded-md object-cover flex-shrink-0">
                                        <div>
                                            <p class="font-semibold text-slate-800 text-sm line-clamp-2" title="<?= htmlspecialchars($post['tieu_de']) ?>">
                                                <?= htmlspecialchars($post['tieu_de']) ?>
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                <?= htmlspecialchars($post['dien_tich'] ?? 'N/A') ?>m² &bull; <?= htmlspecialchars($post['hinh_thuc'] == 'ban' ? 'Bán' : 'Cho thuê') ?>
                                            </p>
                                            <p class="text-xs text-slate-500 italic line-clamp-1" title="<?= ($post['dia_chi_lien_he']) ?>">
                                                <i class="fa-solid fa-phone-volume mr-1"></i><?= ($post['dia_chi_lien_he'] ?? 'Chưa có SĐT liên hệ') ?>
                                            </p>
                                            <p class="text-xs text-slate-400 italic line-clamp-1" title="<?= ($post['dia_chi_bds']) ?>">
                                                <i class="fa-solid fa-location-dot mr-1"></i><?= ($post['dia_chi_bds']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <img src="<?= htmlspecialchars($post['avatar_moigioi']) ?>" class="w-8 h-8 rounded-full">
                                        <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($post['ten_moigioi'] ?? 'Chưa cập nhật') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500"><?= date('d/m/Y H:i', strtotime($post['ngay_dang'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-slate-700"><?= ($post['luot_xem']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center status-cell">
                                    <?= getStatusBadge($post['trang_thai']) ?>
                                </td>
                                <td class="px-6 py-4 text-center actions-cell">
                                    <details class="relative inline-block text-left">
                                        <summary class="list-none cursor-pointer p-2 text-slate-500 hover:text-slate-800 rounded-full hover:bg-slate-100"><i class="fa-solid fa-ellipsis-vertical"></i></summary>
                                        <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                            <div class="py-1" role="menu">
                                                <?php if ($post['trang_thai'] === 'chuaduyet'): ?>
                                                    <button class="action-btn w-full text-left block px-4 py-2 text-sm text-green-700 hover:bg-slate-100" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="daduyet" role="menuitem"><i class="fa-solid fa-check mr-2"></i>Duyệt bài</button>
                                                    <button class="action-btn w-full text-left block px-4 py-2 text-sm text-red-700 hover:bg-slate-100" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="dahuy" role="menuitem"><i class="fa-solid fa-ban mr-2"></i>Hủy</button>
                                                <?php endif; ?>
                                                
                                                <?php if ($post['trang_thai'] === 'daduyet'): ?>
                                                    <button class="action-btn w-full text-left block px-4 py-2 text-sm text-yellow-700 hover:bg-slate-100" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="hethan" role="menuitem"><i class="fa-solid fa-calendar-times mr-2"></i>Đánh dấu Hết hạn</button>
                                                    <button class="action-btn w-full text-left block px-4 py-2 text-sm text-red-700 hover:bg-slate-100" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="dahuy" role="menuitem"><i class="fa-solid fa-ban mr-2"></i>Hủy</button>
                                                <?php endif; ?>
                                                
                                                <?php if ($post['trang_thai'] === 'hethan' || $post['trang_thai'] === 'dahuy'): ?>
                                                    <button class="action-btn w-full text-left block px-4 py-2 text-sm text-blue-700 hover:bg-slate-100" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="chuaduyet" role="menuitem"><i class="fa-solid fa-rotate-left mr-2"></i>Đăng lại (Chờ duyệt)</button>
                                                <?php endif; ?>

                                                <a href="trangchu.php?page=quanly_chitiet_baidang&id=<?= htmlspecialchars($post['id']) ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" role="menuitem"><i class="fa-solid fa-circle-info mr-2"></i>Xem chi tiết</a>
                                            </div>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 flex items-center justify-between border-t border-slate-200">
                <span class="text-sm text-slate-600">Hiển thị <strong><?= count($baidang) ?></strong> trên <strong><?= count($baidang) ?></strong> kết quả</span>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- PHẦN 1: LOGIC GIAO DIỆN (MENU, TÌM KIẾM) ---
    const allDetails = document.querySelectorAll('details');
    allDetails.forEach(details => {
        details.addEventListener('toggle', event => {
            if (details.open) {
                allDetails.forEach(otherDetails => {
                    if (otherDetails !== details) otherDetails.open = false;
                });
            }
        });
    });
    document.addEventListener('click', function(event) {
        if (!event.target.closest('details')) {
            allDetails.forEach(details => details.open = false);
        }
    });

    const searchForm = document.getElementById('search-form');
    if(searchForm){
        const searchInput = document.getElementById('search-input');
        let searchTimeout;
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchForm.submit();
            }, 500); 
        });
        searchForm.addEventListener('submit', function() {
            clearTimeout(searchTimeout);
        });
    }

    // --- PHẦN 2: LOGIC XỬ LÝ HÀNH ĐỘNG VỚI FETCH ---
    const apiUrl = '../../models/quanly_baidang_qt/cn_trangthai_baidang_qt.php'; // Đảm bảo đường dẫn này đúng
    const tableBody = document.getElementById('post-table-body');

    tableBody.addEventListener('click', async function(event) {
        const actionButton = event.target.closest('.action-btn');
        if (!actionButton) return;
        
        event.preventDefault(); 
        
        const postId = actionButton.dataset.id;
        const newStatus = actionButton.dataset.action; 
        
        const messages = {
            daduyet: 'Bạn có chắc chắn muốn DUYỆT bài đăng này?',
            dahuy: 'Bạn có chắc chắn muốn HỦY bài đăng này?',
            hethan: 'Bạn có chắc muốn đánh dấu bài đăng này là HẾT HẠN?',
            chuaduyet: 'Bạn có chắc chắn muốn ĐĂNG LẠI (chờ duyệt) bài đăng này?'
        };

        if (!confirm(messages[newStatus] || 'Bạn có chắc chắn?')) {
            return;
        }

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: postId, status: newStatus })
            });

            if (!response.ok) throw new Error('Yêu cầu mạng thất bại.');
            const result = await response.json();

            if (result.status === 'success') {
                // Cập nhật "live" không cần reload
                const row = document.getElementById(`post-row-${postId}`);
                if (row) {
                    // Cập nhật trạng thái
                    row.querySelector('.status-cell').innerHTML = result.newStatusHtml;
                    // Cập nhật các nút hành động
                    row.querySelector('.actions-cell .py-1').innerHTML = result.newActionsHtml;
                }
            } else {
                alert('Lỗi: ' + result.message);
            }
        } catch (error) {
            console.error('Lỗi:', error);
            alert('Đã xảy ra lỗi. Vui lòng thử lại.');
        }
    });
});
</script>
</body>
</html>