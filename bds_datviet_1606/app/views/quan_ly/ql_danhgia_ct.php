<?php
    // Dữ liệu demo sản phẩm với nhiều ảnh
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    // Lấy id từ URL
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) {
        die("<p class='text-red-500 font-bold p-4'>Không có ID sản phẩm!</p>");
    }

    // Lấy thông tin sản phẩm
    $sql_sanpham = "SELECT 
                    bds.id, bds.tieu_de, bds.mo_ta, bds.gia, bds.dien_tich, bds.dia_chi, bds.loai, bds.khu_vuc, bds.trang_thai, bds.ngay_dang, bds.id_nguoi_dung,
                    info.ho_ten, nd.avt,
                    COUNT(dg.id) AS tong_so_danh_gia,
                    ROUND(AVG(dg.diem), 1) AS diem_trung_binh
                FROM bat_dong_san bds
                LEFT JOIN nguoi_dung nd ON bds.id_nguoi_dung = nd.id
                LEFT JOIN info_nguoi_dung info ON info.id_nguoi_dung = bds.id_nguoi_dung
                LEFT JOIN danh_gia_bds dg ON bds.id = dg.id_bds
                WHERE bds.id = :id
                GROUP BY bds.id, info.ho_ten, nd.avt";


    $stmt_sanpham = $pdo->prepare($sql_sanpham);
    $stmt_sanpham->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt_sanpham->execute();
    $sanpham = $stmt_sanpham->fetch(PDO::FETCH_ASSOC);

    if (!$sanpham) {
        die("<p class='text-red-500 font-bold p-4'>Sản phẩm không tồn tại!</p>");
    }
    
    $sql = "SELECT url FROM hinh_anh_bds WHERE id_bds = :id_bds";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_bds', $id, PDO::PARAM_STR);
    $stmt->execute();
    $spha = $stmt->fetchAll(PDO::FETCH_ASSOC);

   // 2. Dùng array_column để trích xuất tất cả giá trị của cột 'url' thành một mảng đơn giản
    $url_list = array_column($spha, 'url');
    // Bây giờ $url_list sẽ là: ['hinh-anh-1.jpg', 'hinh-anh-2.png', ...]

    // 3. Định nghĩa đường dẫn gốc
    $base_path = '../../../storage/pictures/bds/'; 

    // 4. Dùng array_map để thêm đường dẫn gốc vào từng tên file
    $full_image_urls = array_map(function($filename) use ($base_path) {
        return $base_path . $filename;
    }, $url_list);

    $spha = [
        'hinh_anh' => $full_image_urls
    ];

    // Lấy danh sách đánh giá
    $sql_danhgia = "
        SELECT 
            dg.id AS id_danh_gia, dg.id_bds, dg.id_nguoi_dung, dg.diem, dg.binh_luan, dg.ngay_tao, dg.trang_thai,
            info.ho_ten, nd.avt,
            (SELECT ARRAY_AGG(url) FROM hinh_anh_danh_gia_bds WHERE id_dg_bds = dg.id) as ds_hinh_anh,
            (SELECT ARRAY_AGG(url) FROM video_danh_gia_bds WHERE id_dg_bds = dg.id) as ds_video
        FROM danh_gia_bds dg
        LEFT JOIN info_nguoi_dung info ON info.id_nguoi_dung = dg.id_nguoi_dung
        LEFT JOIN nguoi_dung nd ON nd.id = dg.id_nguoi_dung
        WHERE dg.id_bds = :id
        GROUP BY dg.id, info.ho_ten, nd.avt
        ORDER BY dg.trang_thai ASC, dg.ngay_tao DESC
    ";

    $stmt_danhgia = $pdo->prepare($sql_danhgia);
    $stmt_danhgia->execute([':id' => $id]);
    $danhgia = $stmt_danhgia->fetchAll(PDO::FETCH_ASSOC);

    // Hàm render sao
    function renderStars($score, $total = 5) {
        $html = '';
        $score = round($score);
        for ($i = 1; $i <= $total; $i++) {
            $html .= $i <= $score ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
        }
        return $html;
    }
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm duyệt đánh giá - <?= htmlspecialchars($sanpham['tieu_de']) ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Tùy chỉnh cho menu dropdown của thẻ details */
        summary::marker { content: ''; }
        details[open] > summary:before {
            content: '';
            background: rgba(0,0,0,0.5);
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 20;
        }
    </style>
</head>
<body class="h-full">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <header class="flex items-center justify-between mb-8">
        <div>
            <nav class="text-sm font-medium text-gray-500" aria-label="Breadcrumb">
                <a href="trangchu.php?page=ql_danhgia" class="hover:text-gray-700">Quản lý đánh giá</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700">Chi tiết</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 mt-1 truncate max-w-2xl"><?= htmlspecialchars($sanpham['tieu_de']) ?></h1>
        </div>
        <a href="trangchu.php?page=ql_danhgia" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fa-solid fa-arrow-left"></i>
            Quay lại
        </a>
    </header>

    <div class="lg:grid lg:grid-cols-12 lg:gap-x-8">

        <aside class="lg:col-span-4 lg:sticky lg:top-8 self-start">
            <div class="bg-white shadow-lg rounded-lg p-5">
                <div x-data="{ current: 0, images: <?= htmlspecialchars(json_encode($spha['hinh_anh'])) ?> }" class="mb-4">
                    <div class="relative overflow-hidden rounded-md aspect-w-16 aspect-h-9">
                        <template x-for="(image, index) in images" :key="index">
                            <div x-show="current === index" class="transition-opacity duration-300">
                                <img :src="image" alt="Ảnh sản phẩm" class="w-full h-full object-cover">
                            </div>
                        </template>
                        <div class="absolute inset-0 flex items-center justify-between px-2">
                            <button @click="current = (current > 0) ? current - 1 : images.length - 1" class="bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-black/60 transition">&lsaquo;</button>
                            <button @click="current = (current < images.length - 1) ? current + 1 : 0" class="bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-black/60 transition">&rsaquo;</button>
                        </div>
                    </div>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($sanpham['tieu_de']) ?></h3>
                
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Tổng đánh giá:</dt><dd class="font-medium text-gray-800"><?= number_format($sanpham['tong_so_danh_gia']) ?></dd></div>
                        <div class="flex justify-between items-center"><dt class="text-gray-500">Điểm trung bình:</dt>
                            <dd class="font-medium text-yellow-500 flex items-center gap-1">
                                <?= renderStars($sanpham['diem_trung_binh']) ?> <span>(<?= $sanpham['diem_trung_binh'] ?? 'N/A' ?>)</span>
                            </dd>
                        </div>
                        <div class="flex justify-between"><dt class="text-gray-500">Giá:</dt><dd class="font-medium text-red-600"><?= number_format($sanpham['gia']) ?> đ</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Khu vực:</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars($sanpham['khu_vuc']) ?></dd></div>
                    </dl>
                </div>

                <div class="mt-4 border-t border-gray-200 pt-4 flex items-center">
                    <img src="../../../storage/pictures/avt/<?= urlencode($sanpham['avt']) ?>" class="w-10 h-10 rounded-full object-cover mr-3">
                    <div>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($sanpham['ho_ten']) ?></p>
                        <p class="text-xs text-gray-500">Người đăng</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="lg:col-span-8 mt-8 lg:mt-0">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-5 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Danh sách đánh giá cần duyệt</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    <?php if (empty($danhgia)): ?>
                        <li class="p-12 text-center text-gray-500">
                             <i class="fa-solid fa-comments fa-2x mb-2"></i>
                             <p>Sản phẩm này chưa có đánh giá nào.</p>
                        </li>
                    <?php endif; ?>
                    
                    <?php foreach($danhgia as $dg): 
                        $is_hidden = $dg['trang_thai'] !== 'hien';
                    ?>
                    <li id="review-<?= $dg['id_danh_gia'] ?>" class="p-5 flex gap-4 <?= $is_hidden ? 'bg-orange-50/50 border-l-4 border-orange-400' : '' ?>">
                        <img src="../../../storage/pictures/avt/<?= urlencode($dg['avt']) ?>" class="w-12 h-12 rounded-full object-cover mt-1 flex-shrink-0">
                        
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($dg['ho_ten']) ?></p>
                                    <p class="text-xs text-gray-400 mt-0.5"><?= date("d/m/Y H:i", strtotime($dg['ngay_tao'])) ?></p>
                                </div>
                                <div class="text-yellow-500 text-sm flex items-center gap-2">
                                    <span><?= renderStars($dg['diem']) ?></span>
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold <?= $is_hidden ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>"><?= $is_hidden ? 'ĐANG ẨN' : 'HIỂN THỊ' ?></span>
                                </div>
                            </div>

                            <p class="text-sm text-gray-700 mt-2 break-words"><?= nl2br(htmlspecialchars($dg['binh_luan'])) ?></p>

                            <?php 
                                $ds_hinh_anh = $dg['ds_hinh_anh'] ? explode(',', trim($dg['ds_hinh_anh'], '{}')) : [];
                                $ds_video = $dg['ds_video'] ? explode(',', trim($dg['ds_video'], '{}')) : [];
                            ?>
                             <?php if (!empty($ds_hinh_anh) || !empty($ds_video)): ?>
                                <div class="mt-3 flex gap-2 flex-wrap">
                                    <?php foreach ($ds_hinh_anh as $img_url): ?>
                                        <a href="<?= htmlspecialchars(trim($img_url, '"')) ?>" target="_blank"><img src="<?= htmlspecialchars(trim($img_url, '"')) ?>" class="w-20 h-20 rounded-md object-cover hover:opacity-80 transition"></a>
                                    <?php endforeach; ?>
                                    </div>
                            <?php endif; ?>
                        </div>

                        <div class="relative flex-shrink-0">
                            <details class="relative">
                                <summary class="list-none cursor-pointer p-2 text-gray-500 hover:text-gray-800">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </summary>
                                <div class="absolute right-0 mt-2 w-32 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-30">
                                    <div class="py-1" role="menu" aria-orientation="vertical">
                                        <?php if ($is_hidden): ?>
                                            <a href="#" onclick="event.preventDefault(); handleAction('<?= $dg['id_danh_gia'] ?>', 'show')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Hiện đánh giá</a>
                                        <?php else: ?>
                                            <a href="#" onclick="event.preventDefault(); handleAction('<?= $dg['id_danh_gia'] ?>', 'hide')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Ẩn đánh giá</a>
                                        <?php endif; ?>
                                        <a href="#" onclick="event.preventDefault(); handleAction('<?= $dg['id_danh_gia'] ?>', 'delete')" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem">Xóa vĩnh viễn</a>
                                    </div>
                                </div>
                            </details>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    // Hàm AJAX xử lý hành động (giữ nguyên)
    function handleAction(id, action) {
        // Đóng tất cả các menu details đang mở
        document.querySelectorAll('details[open]').forEach(detail => detail.removeAttribute('open'));

        let confirmMsg = {
            'delete': 'Bạn có chắc chắn muốn XÓA vĩnh viễn đánh giá này không?',
            'hide': 'Bạn có chắc chắn muốn ẨN đánh giá này không?',
            'show': 'Bạn có chắc chắn muốn HIỆN đánh giá này không?'
        }[action];

        if (!confirm(confirmMsg)) return;

        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);

        fetch('../../models/cn_danhgia.php', {
            method: 'POST',
            body: formData,
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) throw new Error(data.message || 'Lỗi không xác định');
            alert(data.message);
            if (data.status === 'success') {
                // Tải lại trang để cập nhật giao diện hoàn chỉnh
                location.reload();
            }
        })
        .catch(err => {
            console.error('Lỗi xử lý:', err);
            alert('Lỗi: ' + err.message);
        });
    }
</script>

</body>
</html>