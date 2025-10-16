<?php 
// =================================================================
// 1. KẾT NỐI CƠ SỞ DỮ LIỆU VÀ CÁC LOGIC PHP (Giữ nguyên)
// =================================================================
require_once "../../../config/database.php"; 
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
}

$post_id = $_GET['id'] ?? null;
if (!$post_id) {
    die("Lỗi: Không tìm thấy ID bài đăng.");
}

$sql_detail = "
    SELECT 
        bd.id, bd.tieu_de, bd.mo_ta, bd.gia, bd.hinh_thuc, bd.ngay_dang, bd.ngay_het_han, bd.trang_thai, bd.luot_xem,
        bds.dien_tich, bds.dia_chi, bds.khu_vuc, bds.loai,
        info.ho_ten AS ten_moigioi, nd.email AS email_moigioi, nd.so_dt AS sdt_moigioi, nd.avt AS avatar_moigioi,
        (SELECT array_agg(url ORDER BY ngay_tao ASC) FROM hinh_anh_bds WHERE id_bds = bds.id) AS anh_bds_list
    FROM bai_dang AS bd
    JOIN bat_dong_san AS bds ON bd.id_bat_dong_san = bds.id
    JOIN nguoi_dung AS nd ON bd.id_nguoi_dung = nd.id
    LEFT JOIN info_nguoi_dung AS info ON nd.id = info.id_nguoi_dung
    WHERE bd.id = :id LIMIT 1;
";

$stmt_detail = $pdo->prepare($sql_detail);
$stmt_detail->bindValue(':id', $post_id, PDO::PARAM_STR);
$stmt_detail->execute();
$post = $stmt_detail->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Không tìm thấy bài đăng có ID: " . htmlspecialchars($post_id));
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

$post['avatar_moigioi'] = '../../../storage/pictures/avt/' . ($post['avatar_moigioi'] ?? 'default-avatar.png');
$anh_bds_list = (isset($post['anh_bds_list']) && $post['anh_bds_list'] !== null) 
    ? array_map('trim', explode(',', trim($post['anh_bds_list'], '{}'))) 
    : [];

$final_images = [];
if (!empty($anh_bds_list)) {
    foreach ($anh_bds_list as $img_url) { $final_images[] = '../../../storage/pictures/bds/' . $img_url; }
} else {
    $final_images[] = 'https://picsum.photos/800/600?random=1';
}

$mo_ta_html = nl2br(htmlspecialchars($post['mo_ta']));
$gia_formatted = number_format($post['gia'], 0, ',', '.') . ' VNĐ';
$dien_tich_formatted = number_format($post['dien_tich'], 2, ',', '.') . ' m²';
$ngay_dang_formatted = date('H:i d/m/Y', strtotime($post['ngay_dang']));
$ngay_het_han_formatted = $post['ngay_het_han'] ? date('H:i d/m/Y', strtotime($post['ngay_het_han'])) : 'Không xác định';
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Bài đăng - <?= htmlspecialchars($post['tieu_de']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-4 sm:p-8">
    <div class="max-w-7xl mx-auto">
        <a href="javascript:history.back()" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại trang Quản lý
        </a>

        <div class="bg-white shadow-xl rounded-lg overflow-hidden p-6 lg:p-10">
            <header class="mb-8 border-b pb-4">
                <div class="flex items-start justify-between">
                    <h1 class="text-3xl font-extrabold text-slate-900 leading-tight">
                        <?= htmlspecialchars($post['tieu_de']) ?>
                    </h1>
                    <div id="status-badge-container">
                        <?= getStatusBadge($post['trang_thai']) ?>
                    </div>
                </div>
                <p class="mt-2 text-sm text-slate-500 flex items-center gap-4">
                    <span title="Lượt xem"><i class="fa-solid fa-eye mr-1"></i> <?= number_format($post['luot_xem']) ?></span>
                    <span title="Ngày đăng"><i class="fa-solid fa-calendar-days mr-1"></i> <?= $ngay_dang_formatted ?></span>
                    <span title="Mã bài đăng"><i class="fa-solid fa-hashtag mr-1"></i> <?= substr($post['id'], 0, 8) ?>...</span>
                </p>
            </header>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="mb-8">
                        <div class="grid grid-cols-2 gap-4">
                            <?php foreach ($final_images as $index => $img_url): ?>
                            <div class="<?= $index === 0 ? 'col-span-2' : 'col-span-1 h-36' ?>">
                                <img src="<?= htmlspecialchars($img_url) ?>" alt="Ảnh BĐS <?= $index + 1 ?>" class="w-full <?= $index === 0 ? 'h-96' : 'h-full' ?> object-cover rounded-lg shadow-md">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="text-slate-700 leading-relaxed space-y-4 text-justify"><?= $mo_ta_html ?></div>
                </div>
                
                <div class="lg:col-span-1 space-y-8">
                    <div class="bg-indigo-50 p-6 rounded-lg shadow-inner">
                        <ul class="space-y-3 text-sm text-slate-700">
                             <li class="flex justify-between items-center border-b pb-2"><span class="font-medium flex items-center"><i class="fa-solid fa-tag w-5 mr-2 text-indigo-600"></i>Hình thức:</span><span class="font-semibold text-indigo-700"><?= $post['hinh_thuc'] === 'ban' ? 'Bán' : 'Cho Thuê' ?></span></li>
                             <li class="flex justify-between items-center border-b pb-2"><span class="font-medium flex items-center"><i class="fa-solid fa-dollar-sign w-5 mr-2 text-indigo-600"></i>Giá:</span><span class="font-semibold text-xl text-green-600"><?= $gia_formatted ?></span></li>
                             <li class="flex justify-between items-center border-b pb-2"><span class="font-medium flex items-center"><i class="fa-solid fa-chart-area w-5 mr-2 text-indigo-600"></i>Diện tích:</span><span class="font-semibold"><?= $dien_tich_formatted ?></span></li>
                             <li class="flex justify-between items-center border-b pb-2"><span class="font-medium flex items-center"><i class="fa-solid fa-location-dot w-5 mr-2 text-indigo-600"></i>Khu vực:</span><span class="font-semibold"><?= htmlspecialchars($post['khu_vuc']) ?></span></li>
                             <li class="flex justify-between items-center"><span class="font-medium flex items-center"><i class="fa-solid fa-house w-5 mr-2 text-indigo-600"></i>Loại BĐS:</span><span class="font-semibold"><?= htmlspecialchars(ucfirst($post['loai'])) ?></span></li>
                        </ul>
                    </div>

                    <div class="bg-red-50 p-6 rounded-lg border border-red-200">
                        <ul class="space-y-3 text-sm text-red-800">
                             <li class="flex justify-between"><span class="font-medium flex items-center"><i class="fa-solid fa-clock-rotate-left w-5 mr-2"></i>Ngày hết hạn:</span><span><?= $ngay_het_han_formatted ?></span></li>
                             <li class="flex justify-between"><span class="font-medium flex items-center"><i class="fa-solid fa-location-crosshairs w-5 mr-2"></i>Địa chỉ chi tiết:</span><span class="text-right"><?= htmlspecialchars($post['dia_chi']) ?></span></li>
                         </ul>
                        
                        <div id="action-box" class="mt-4 pt-4 border-t border-red-200 space-y-2">
                            <p class="font-semibold text-base text-red-700">Hành động kiểm duyệt:</p>
                            <?php if ($post['trang_thai'] !== 'hethan'): ?>
                                <?php if ($post['trang_thai'] === 'chuaduyet'): ?>
                                    <button class="btn-action w-full bg-green-600 text-white py-2 rounded-md text-sm font-semibold hover:bg-green-700" data-id="<?= $post['id'] ?>" data-action="approve"><i class="fa-solid fa-check mr-2"></i>Duyệt bài</button>
                                    <button class="btn-action w-full bg-red-600 text-white py-2 mt-2 rounded-md text-sm font-semibold hover:bg-red-700" data-id="<?= $post['id'] ?>" data-action="reject"><i class="fa-solid fa-ban mr-2"></i>Từ chối & Ẩn</button>
                                <?php elseif ($post['trang_thai'] === 'an'): ?>
                                    <button class="btn-action w-full bg-blue-600 text-white py-2 rounded-md text-sm font-semibold hover:bg-blue-700" data-id="<?= $post['id'] ?>" data-action="redisplay"><i class="fa-solid fa-eye mr-2"></i>Hiển thị lại</button>
                                <?php else: ?>
                                    <button class="btn-action w-full bg-slate-600 text-white py-2 rounded-md text-sm font-semibold hover:bg-slate-700" data-id="<?= $post['id'] ?>" data-action="reject"><i class="fa-solid fa-eye-slash mr-2"></i>Gỡ bài (Ẩn)</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const actionBox = document.getElementById('action-box');
        const apiUrl = '../../models/duyet_baidang.php'; // Đường dẫn đến file API

        if (actionBox) {
            actionBox.addEventListener('click', async function(event) {
                const targetButton = event.target.closest('.btn-action');
                if (!targetButton) return;

                event.preventDefault();

                const postId = targetButton.dataset.id;
                const action = targetButton.dataset.action;

                const messages = {
                    approve: 'Bạn có chắc chắn muốn DUYỆT bài đăng này?',
                    reject: 'Bạn có chắc chắn muốn GỠ/ẨN bài đăng này?',
                    redisplay: 'Bạn có chắc chắn muốn HIỂN THỊ LẠI bài đăng này?'
                };

                if (!confirm(messages[action] || 'Bạn có chắc chắn?')) {
                    return;
                }

                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: postId, action: action })
                    });

                    if (!response.ok) throw new Error('Yêu cầu mạng thất bại.');
                    const result = await response.json();

                    alert(result.message); // Hiển thị thông báo kết quả

                    if (result.status === 'success') {
                        // Cập nhật giao diện động mà không cần tải lại trang
                        const statusContainer = document.getElementById('status-badge-container');
                        if (statusContainer) {
                            statusContainer.innerHTML = result.newStatusHtml;
                        }
                        actionBox.innerHTML = result.newActionsHtml;
                    }
                } catch (error) {
                    console.error('Lỗi:', error);
                    alert('Đã xảy ra lỗi khi thực hiện hành động. Vui lòng thử lại.');
                }
            });
        }
    });
    </script>
</body>
</html>