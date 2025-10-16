<?php 
require_once "../../../config/database.php"; 
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
}

$search = $_GET['search'] ?? '';
$search = trim($search); 

$sql = "
    SELECT 
        bd.id, bd.tieu_de, bd.gia, bd.ngay_dang, bd.luot_xem, bd.trang_thai,
        bds.dien_tich, bds.khu_vuc AS dia_chi,
        info.ho_ten AS ten_moigioi, nd.avt AS avatar_moigioi,
        (SELECT url FROM hinh_anh_bds WHERE id_bds = bds.id ORDER BY ngay_tao ASC LIMIT 1) AS anh_bia
    FROM bai_dang AS bd
    JOIN bat_dong_san AS bds ON bd.id_bat_dong_san = bds.id
    JOIN nguoi_dung AS nd ON bd.id_nguoi_dung = nd.id
    LEFT JOIN info_nguoi_dung AS info ON nd.id = info.id_nguoi_dung
";

$params = [];

if (!empty($search)) {
    $searchable_columns = "bd.tieu_de || ' ' || bds.khu_vuc || ' ' || COALESCE(info.ho_ten, '')";
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

foreach ($baidang as $key => $post) {
    if (empty($post['anh_bia'])) {
        $baidang[$key]['anh_bia'] = 'https://picsum.photos/300/200?random=' . $key;
    } else {
        $baidang[$key]['anh_bia'] = '../../../storage/pictures/bds/' . $post['anh_bia'];
    }
    $baidang[$key]['avatar_moigioi'] = '../../../storage/pictures/avt/' . ($post['avatar_moigioi'] ?? 'default-avatar.png');
}

function getStatusBadge($status) {
    $map = [
        'chuaduyet' => ['text' => 'Chờ duyệt', 'class' => 'bg-orange-100 text-orange-800'],
        'daduyet'   => ['text' => 'Đang hiển thị', 'class' => 'bg-green-100 text-green-800'],
        'hethan'    => ['text' => 'Hết hạn', 'class' => 'bg-red-100 text-red-800'],
        'daban'     => ['text' => 'Đã bán', 'class' => 'bg-blue-100 text-blue-800'],
        'dathue'    => ['text' => 'Đã cho thuê', 'class' => 'bg-indigo-100 text-indigo-800'],
        'an'        => ['text' => 'Đã ẩn', 'class' => 'bg-slate-100 text-slate-800'],
    ];
    $info = $map[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-800'];
    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$info['class']}'>{$info['text']}</span>";
}

$stats = [
    'pending' => count(array_filter($baidang, fn($p) => $p['trang_thai'] === 'chuaduyet')),
    'active'  => count(array_filter($baidang, fn($p) => $p['trang_thai'] === 'daduyet')),
    'expired' => count(array_filter($baidang, fn($p) => in_array($p['trang_thai'], ['hethan', 'daban', 'dathue', 'an']))),
    'total'   => count($baidang),
];

function truncate_string($string, $word_limit) {
    $words = explode(' ', $string);
    return (count($words) > $word_limit) ? implode(' ', array_slice($words, 0, $word_limit)) . '...' : $string;
}
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bài đăng</title>
    <style>
        summary::marker { content: ''; }
    </style>
</head>
<body class="h-full p-6">
    <header>
        <div class="sm:flex sm:items-center sm:justify-between mb-6 pb-4 border-b">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Quản lý Bài đăng</h1>
                <p class="mt-2 text-sm text-slate-600">Tổng quan và kiểm duyệt tất cả bài đăng của môi giới.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Chờ duyệt</dt><dd class="mt-1 text-3xl font-semibold text-orange-500"><?= $stats['pending'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Đang hiển thị</dt><dd class="mt-1 text-3xl font-semibold text-green-600"><?= $stats['active'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Hết hạn / Đã bán</dt><dd class="mt-1 text-3xl font-semibold text-red-600"><?= $stats['expired'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Tổng số bài</dt><dd class="mt-1 text-3xl font-semibold text-slate-900"><?= $stats['total'] ?></dd></dl></div></div>
        </div>
    </header>

    <main class="mt-6">
        <form action="" id="search-form" method="GET" class="mb-6">
             <input type="hidden" name="page" value="ql_baidang">
             <input type="text" id="search-input" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Tìm theo tiêu đề, địa chỉ..." class="w-full sm:w-80 border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </form>
    
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Bài đăng</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Môi giới</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Ngày đăng</th>
                            <th scope="col" class="px-6 py-3 text-center text-sm font-semibold text-slate-600">Lượt xem</th>
                            <th scope="col" class="px-6 py-3 text-center text-sm font-semibold text-slate-600">Trạng thái</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Hành động</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($baidang as $post): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4"><div class="flex items-center gap-4"><img src="<?= htmlspecialchars($post['anh_bia']) ?>" class="w-24 h-16 rounded-md object-cover flex-shrink-0"><div><p class="font-semibold text-slate-800 text-sm" title="<?= htmlspecialchars($post['tieu_de']) ?>"><?= htmlspecialchars(truncate_string($post['tieu_de'], 8)) ?></p><p class="text-xs text-slate-500"><?= number_format($post['gia'], 0, ',', '.') ?> VNĐ &bull; <?= htmlspecialchars($post['dien_tich']) ?>m²</p></div></div></td>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center gap-2"><img src="<?= htmlspecialchars($post['avatar_moigioi']) ?>" class="w-8 h-8 rounded-full"><span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($post['ten_moigioi'] ?? 'Chưa cập nhật') ?></span></div></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500"><?= date('d/m/Y H:i', strtotime($post['ngay_dang'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-slate-700"><?= number_format($post['luot_xem']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center"><?= getStatusBadge($post['trang_thai']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <details class="relative inline-block text-left">
                                        <summary class="list-none cursor-pointer p-2 text-slate-500 hover:text-slate-800"><i class="fa-solid fa-ellipsis-vertical"></i></summary>
                                        <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                            <div class="py-1" role="menu">
                                                <?php if ($post['trang_thai'] !== 'hethan'): ?>
                                                    <?php if ($post['trang_thai'] === 'chuaduyet'): ?>
                                                        <a href="#" class="block px-4 py-2 text-sm text-green-700 hover:bg-slate-100 btn-action" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="approve" role="menuitem"><i class="fa-solid fa-check mr-2"></i>Duyệt bài</a>
                                                    <?php endif; ?>

                                                    <?php if (in_array($post['trang_thai'], ['daduyet', 'daban', 'dathue', 'hethan'])): ?>
                                                        <a href="#" class="block px-4 py-2 text-sm text-red-700 hover:bg-slate-100 btn-action" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="reject" role="menuitem"><i class="fa-solid fa-ban mr-2"></i>Gỡ bài</a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($post['trang_thai'] === 'an'): ?>
                                                        <a href="#" class="block px-4 py-2 text-sm text-blue-700 hover:bg-slate-100 btn-action" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="redisplay" role="menuitem"><i class="fa-solid fa-eye mr-2"></i>Hiển thị lại</a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <a href="trangchu.php?page=chitiet_baidang&id=<?= htmlspecialchars($post['id']) ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" role="menuitem"><i class="fa-solid fa-circle-info mr-2"></i>Xem chi tiết</a>
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
                <span class="text-sm text-slate-600">Hiển thị <strong>1-<?= count($baidang) ?></strong> trên <strong><?= count($baidang) ?></strong> kết quả</span>
            </div>
        </div>
    </main>
</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- PHẦN 1: LOGIC GIAO DIỆN (MENU, TÌM KIẾM) ---

    // Tự động đóng menu <details>
    const allDetails = document.querySelectorAll('details');
    allDetails.forEach(details => {
        details.addEventListener('toggle', event => {
            if (details.open) {
                allDetails.forEach(otherDetails => {
                    if (otherDetails !== details) {
                        otherDetails.open = false;
                    }
                });
            }
        });
    });
    document.addEventListener('click', function(event) {
        if (!event.target.closest('details')) {
            allDetails.forEach(details => {
                details.open = false;
            });
        }
    });

    // Tự động tìm kiếm khi người dùng ngừng gõ
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
    const apiUrl = '../../models/duyet_baidang.php'; // Đường dẫn đến file API

    document.body.addEventListener('click', function(event) {
        const actionButton = event.target.closest('.btn-action');
        if (!actionButton) return;
        
        event.preventDefault(); // Ngăn hành vi mặc định của thẻ <a>
        
        const postId = actionButton.dataset.id;
        const action = actionButton.dataset.action;
        
        const messages = {
            approve: 'Bạn có chắc chắn muốn DUYỆT bài đăng này?',
            reject: 'Bạn có chắc chắn muốn GỠ bài đăng này? Bài đăng sẽ bị ẩn đi.',
            redisplay: 'Bạn có chắc chắn muốn HIỂN THỊ LẠI bài đăng này?'
        };

        if (!confirm(messages[action] || 'Bạn có chắc chắn muốn thực hiện hành động này?')) {
            return;
        }

        // Gửi yêu cầu đến server
        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: postId, action: action })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                location.reload(); // Tải lại trang để cập nhật giao diện
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            alert('Đã xảy ra lỗi khi thực hiện hành động. Vui lòng thử lại.');
        });
    });
});
</script>
</html>