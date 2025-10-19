<?php
    require_once "../../../config/database.php";

    // --- CÁC HÀM HELPER VÀ LOGIC PHP GIỮ NGUYÊN ---
    if (!isset($_GET['id']) || empty($_GET['id'])) { die("Lỗi: Không tìm thấy ID của bất động sản."); }
    $bds_id = $_GET['id'];
    try { $pdo = ketnoicsdl(); } catch (PDOException $e) { die("Lỗi kết nối CSDL: " . $e->getMessage()); }
    function formatPrice($price) {
        if ($price >= 1000000000) return round($price / 1000000000, 2) . ' tỷ';
        if ($price >= 1000000) return round($price / 1000000, 2) . ' triệu';
        return number_format($price);
    }
    function getVietnameseLabel($key) {
        $map = [
            'ten_du_an' => 'Tên dự án', 'ma_can_ho' => 'Mã căn hộ', 'tang_so' => 'Tầng số', 'huong_ban_cong' => 'Hướng ban công', 'noi_that' => 'Nội thất', 'view' => 'Tầm nhìn (View)', 'tien_ich' => 'Tiện ích', 'loai_hinh_dat' => 'Loại hình đất', 'chieu_dai' => 'Chiều dài', 'chieu_rong' => 'Chiều rộng', 'hinh_dang' => 'Hình dáng', 'loai_hinh_nha' => 'Loại hình nhà', 'tinh_trang_nha' => 'Tình trạng nhà', 'co_ham' => 'Có hầm', 'loai_hinh_biet_thu' => 'Loại hình biệt thự', 'vi_tri' => 'Vị trí', 'ha_tang' => 'Hạ tầng', 'loai_hinh_can_ho' => 'Loại hình căn hộ'
        ];
        return $map[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
    
    // Câu lệnh truy vấn gộp (giữ nguyên)
    $sql_combined = "
        SELECT 
            bds.*, dm.ten_danh_muc, info.ho_ten AS ten_chu_so_huu,
            nd.email, nd.so_dt, nd.avt,
            COALESCE(
                JSONB_AGG(jsonb_build_object('id', ha.id, 'url', ha.url) ORDER BY ha.ngay_tao ASC) FILTER (WHERE ha.id IS NOT NULL), 
                '[]'::jsonb
            ) AS hinh_anh
        FROM bat_dong_san bds
        LEFT JOIN danh_muc dm ON bds.id_danh_muc = dm.id
        LEFT JOIN nguoi_dung nd ON bds.id_chu_so_huu = nd.id
        LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
        LEFT JOIN hinh_anh_bds ha ON bds.id = ha.id_bds
        WHERE bds.id = :id
        GROUP BY bds.id, dm.id, info.id, nd.id";

    $stmt = $pdo->prepare($sql_combined);
    $stmt->execute([':id' => $bds_id]);
    $bds = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$bds) { die("Không tìm thấy bất động sản với ID này."); }
    
    $dac_diem_chi_tiet = $bds['dac_diem_chi_tiet'] ? json_decode($bds['dac_diem_chi_tiet'], true) : [];
    $images = json_decode($bds['hinh_anh'], true); 

    $sql_listings = "SELECT * FROM bai_dang WHERE id_bat_dong_san = :id_bds ORDER BY ngay_dang DESC";
    $stmt_listings = $pdo->prepare($sql_listings);
    $stmt_listings->execute([':id_bds' => $bds_id]);
    $listings = $stmt_listings->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết: <?= htmlspecialchars($bds['dia_chi_day_du']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        #map { height: 280px; z-index: 10; background-color: #e2e8f0; } 
        .thumbnail.active { border-color: #4f46e5; }
    </style>
</head>
<body class="p-4 sm:p-5"> 
    <div class="max-w-7xl mx-auto">
        <header class="mb-4"> 
            <nav class="text-sm mb-3"> 
                <a href="javascript:history.back()" class="text-indigo-600 hover:underline flex items-center gap-2 w-fit">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                </a>
            </nav>
            <h1 class="text-2xl font-bold text-gray-500"> 
                <?= htmlspecialchars($bds['ten_danh_muc'] ?? 'Bất động sản') ?>
            </h1>
            <p class="text-sm text-slate-600 mt-1 flex items-center"> 
                <i class="fas fa-map-marker-alt mr-2 text-slate-400"></i>
                <?= htmlspecialchars($bds['dia_chi_day_du']) ?>
            </p>
        </header>

        <div class="relative lg:flex lg:gap-8">
            <div class="lg:w-1/2 lg:sticky lg:top-5 self-start">
                <section id="image-gallery-wrapper">
                    <div id="gallery-placeholder" class="aspect-[16/9] w-full bg-slate-200 rounded-xl shadow-lg border flex items-center justify-center">
                        <div class="text-center text-slate-400">
                             <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                             <p class="mt-2 text-sm font-medium">Đang tải thư viện ảnh...</p>
                        </div>
                    </div>
                    <template id="gallery-template">
                        <div x-data="{ images: <?= htmlspecialchars(json_encode($images)) ?>, current: 0 }">
                            <div class="aspect-[16/9] w-full bg-slate-200 rounded-xl shadow-lg border relative overflow-hidden flex items-center justify-center">
                                <template x-if="images.length > 0">
                                    <img :src="`../../../storage/pictures/bds/${images[current].url}`" :key="images[current].id" class="max-w-full max-h-full object-contain">
                                </template>
                                <template x-if="images.length === 0">
                                    <div class="text-slate-500 text-center"><i class="fa-solid fa-image fa-3x"></i><p class="mt-2 font-semibold text-sm">Không có ảnh</p></div>
                                </template>
                                <template x-if="images.length > 1">
                                    <div>
                                        <button @click="current = (current > 0) ? current - 1 : images.length - 1" class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-black/60 transition z-10"><i class="fas fa-chevron-left"></i></button>
                                        <button @click="current = (current < images.length - 1) ? current + 1 : 0" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-black/60 transition z-10"><i class="fas fa-chevron-right"></i></button>
                                        <div class="absolute top-3 right-3 bg-black/50 text-white text-xs font-semibold px-2 py-0.5 rounded-full z-10" x-text="`${current + 1} / ${images.length}`"></div>
                                    </div>
                                </template>
                            </div>
                            <template x-if="images.length > 1">
                                <div class="mt-3 grid grid-cols-5 sm:grid-cols-6 md:grid-cols-7 gap-1.5"> 
                                    <template x-for="(img, index) in images">
                                        <div @click="current = index" class="thumbnail aspect-square cursor-pointer rounded-md overflow-hidden border-2 hover:border-indigo-500 transition-all" :class="{ 'active': current === index }">
                                            <img :src="`../../../storage/pictures/bds/${img.url}`" loading="lazy" decoding="async" class="h-full w-full object-cover">
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </section>
            </div>

            <div class="lg:w-1/2 space-y-4 mt-5 lg:mt-0">
                <section class="bg-white p-4 rounded-xl shadow-md border">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-slate-700">Trạng thái tài sản</h3>
                        <div id="property-status-badge"></div>
                    </div>
                    <div id="property-action-buttons" class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-200"></div>
                </section>
                <section class="bg-white p-4 rounded-xl shadow-md border"> 
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                        <div><p class="text-xs text-slate-500">Diện tích đất</p><p class="font-bold text-base"><?= htmlspecialchars($bds['dien_tich_dat'] ?? 'N/A') ?> m²</p></div>
                        <div><p class="text-xs text-slate-500">Phòng ngủ</p><p class="font-bold text-base"><?= htmlspecialchars($bds['so_phong_ngu'] ?? 'N/A') ?></p></div>
                        <div><p class="text-xs text-slate-500">Phòng tắm</p><p class="font-bold text-base"><?= htmlspecialchars($bds['so_phong_tam'] ?? 'N/A') ?></p></div>
                        <div><p class="text-xs text-slate-500">Hướng nhà</p><p class="font-bold text-base"><?= htmlspecialchars($bds['huong_nha'] ?? 'N/A') ?></p></div>
                    </div>
                </section>
                
                <section class="bg-white p-4 rounded-xl shadow-md border"> 
                    <h2 class="text-lg font-semibold mb-3 border-b pb-2">Chi tiết bất động sản</h2> 
                    <div class="space-y-3 text-sm">
                        <div>
                            <h3 class="text-sm font-bold text-indigo-700 mb-2">TỔNG QUAN</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2">
                                <?php $details = ['thong_tin_phap_ly' => ['label' => 'Pháp lý', 'icon' => 'fa-gavel'], 'so_tang' => ['label' => 'Số tầng', 'icon' => 'fa-layer-group'], 'mat_tien' => ['label' => 'Mặt tiền', 'icon' => 'fa-road', 'unit' => ' m'], 'duong_vao' => ['label' => 'Đường vào', 'icon' => 'fa-arrows-left-right-to-line', 'unit' => ' m']]; 
                                foreach($details as $key => $info): if(!empty($bds[$key])): ?>
                                <div class="flex items-center gap-3"><i class="fa-solid <?= $info['icon'] ?> w-4 text-slate-400"></i><span class="text-slate-500 flex-1"><?= $info['label'] ?>:</span><span class="font-semibold text-slate-800"><?= htmlspecialchars($bds[$key]) . ($info['unit'] ?? '') ?></span></div>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>

                        <?php if(!empty($dac_diem_chi_tiet)): ?>
                        <div>
                            <h3 class="text-sm font-bold text-indigo-700 mb-2 mt-4">ĐẶC ĐIỂM KHÁC</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2">
                                <?php foreach($dac_diem_chi_tiet as $key => $value): ?>
                                <div class="flex justify-between border-b pb-1"><span class="text-slate-500"><?= getVietnameseLabel($key) ?></span><span class="font-semibold text-slate-800"><?= is_bool($value) ? ($value ? 'Có' : 'Không') : htmlspecialchars($value) ?></span></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <?php if (!empty($bds['vi_do']) && !empty($bds['kinh_do'])): ?>
                <section class="bg-white p-4 rounded-xl shadow-md border"> 
                    <h2 class="text-lg font-semibold mb-3">Vị trí trên bản đồ</h2> 
                    <div id="map-container" class="relative rounded-lg overflow-hidden">
                        <div id="map-placeholder" class="absolute inset-0 bg-slate-200 flex items-center justify-center z-20">
                            <div class="text-center text-slate-500"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p class="mt-2 text-sm font-medium">Đang tải bản đồ...</p></div>
                        </div>
                        <div id="map"></div>
                    </div>
                </section>
                <?php endif; ?>

                <div class="space-y-4"> 
                    <div class="bg-white p-4 rounded-xl shadow-md border"> 
                        <h3 class="text-base font-semibold text-slate-700 mb-3">Các bài đăng liên quan</h3> 
                        <div class="space-y-3"> 
                            <?php if(empty($listings)): ?>
                                <p class="text-sm text-slate-500 italic">Chưa có bài đăng nào.</p>
                            <?php else: foreach($listings as $listing): ?>
                                <a href="trangchu.php?page=chitiet_baidang&id=<?= $listing['id'] ?>" class="block p-2.5 bg-slate-50 rounded-lg hover:bg-slate-100 transition"> 
                                    <p class="font-semibold text-sm text-indigo-700 truncate"><?= htmlspecialchars($listing['tieu_de']) ?></p>
                                </a>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-md border"> 
                        <h3 class="text-base font-semibold text-slate-700 mb-3">Chủ sở hữu</h3> 
                        <div class="flex items-center gap-3"> 
                            <img class="h-10 w-10 rounded-full object-cover" src="../../../storage/pictures/avt/<?= htmlspecialchars($bds['avt'] ?? 'avt.png') ?>" alt="Avatar"> 
                            <div>
                                <p class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars($bds['ten_chu_so_huu'] ?? 'Chưa cập nhật') ?></p> 
                                <p class="text-xs text-slate-500"><?= htmlspecialchars($bds['email'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                         <div class="mt-3 flex flex-col gap-2"> 
                            <a href="tel:<?= htmlspecialchars($bds['so_dt']) ?>" class="w-full text-center px-4 py-1.5 font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 text-sm"><i class="fas fa-phone-alt mr-2"></i>Gọi điện</a> 
                            <a href="mailto:<?= htmlspecialchars($bds['email']) ?>" class="w-full text-center px-4 py-1.5 font-semibold rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 text-sm"><i class="fas fa-envelope mr-2"></i>Gửi Email</a> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>

    const bdsData = <?= json_encode($bds) ?>;

    const propertyStatusMap = {
        'chuaduyet': { text: "Chờ duyệt", icon: 'fa-solid fa-clock', classes: "text-yellow-800 bg-yellow-100" },
        'daduyet':   { text: "Đã duyệt", icon: 'fa-solid fa-check-circle', classes: "text-green-800 bg-green-100" },
        'huy':       { text: "Đã hủy", icon: 'fa-solid fa-ban', classes: "text-red-800 bg-red-100" }
    };

    function renderActionButtons(newStatus) {
        const container = document.getElementById('property-action-buttons');
        let buttonsHTML = '';
        if (newStatus === 'chuaduyet') {
            buttonsHTML = `<button data-action="huy" class="action-btn bg-red-100 hover:bg-red-200 text-sm text-red-700 font-bold px-4 py-2 rounded-lg transition">Hủy</button><button data-action="daduyet" class="action-btn bg-green-600 hover:bg-green-700 text-sm text-white font-bold px-4 py-2 rounded-lg transition">Duyệt</button>`;
        } else if (newStatus === 'daduyet' || newStatus === 'huy') {
            buttonsHTML = `<button data-action="chuaduyet" class="action-btn bg-yellow-400 hover:bg-yellow-500 text-sm text-white font-bold px-4 py-2 rounded-lg transition flex items-center gap-2"><i class="fa-solid fa-rotate-left"></i> Hoàn tác</button>`;
        }
        container.innerHTML = buttonsHTML;
    }

    function renderStatusBadge(newStatus) {
        const container = document.getElementById('property-status-badge');
        const statusInfo = propertyStatusMap[newStatus];
        if (container && statusInfo) {
            container.innerHTML = `<span class="px-3 py-1 text-sm font-bold rounded-full flex items-center gap-2 ${statusInfo.classes}"><i class="${statusInfo.icon}"></i><span>${statusInfo.text}</span></span>`;
        }
    }

    async function updatePropertyStatus(bdsId, newStatus) {
        const actionText = newStatus === 'chuaduyet' ? 'hoàn tác' : (newStatus === 'huy' ? 'hủy' : 'duyệt');
        if (!confirm(`Bạn có chắc chắn muốn ${actionText} tài sản này không?`)) return;
        try {
            const formData = new FormData();
            formData.append('id', bdsId);
            formData.append('trang_thai', newStatus);
            const response = await fetch('../../models/quanly_sanpham_bds/capnhat_trangthai_bds_qt.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                renderStatusBadge(newStatus);
                renderActionButtons(newStatus);
                alert('Cập nhật thành công!');
            } else { alert('Lỗi: ' + (result.message || 'Không thể cập nhật.')); }
        } catch (error) { console.error('Lỗi Fetch:', error); alert('Đã xảy ra lỗi kết nối.'); }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Render trạng thái & nút bấm ban đầu
        renderStatusBadge(bdsData.trang_thai);
        renderActionButtons(bdsData.trang_thai);

        document.getElementById('property-action-buttons').addEventListener('click', function(e) {
            const button = e.target.closest('.action-btn');
            if (button) {
                updatePropertyStatus(bdsData.id, button.dataset.action);
            }
        });
    });

    // Phần script trì hoãn và chống giật lag giữ nguyên
    document.addEventListener('DOMContentLoaded', function() {
        const galleryWrapper = document.getElementById('image-gallery-wrapper');
        const galleryPlaceholder = document.getElementById('gallery-placeholder');
        const galleryTemplate = document.getElementById('gallery-template');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    galleryWrapper.innerHTML = galleryTemplate.innerHTML;
                    Alpine.initTree(galleryWrapper);
                    observer.unobserve(galleryWrapper);
                }
            });
        }, { rootMargin: '50px' });
        observer.observe(galleryWrapper);

        <?php if (!empty($bds['vi_do']) && !empty($bds['kinh_do'])): ?>
        setTimeout(function() {
            const lat = <?= (float)$bds['vi_do'] ?>;
            const lng = <?= (float)$bds['kinh_do'] ?>;
            const mapPlaceholder = document.getElementById('map-placeholder');
            try {
                const map = L.map('map').setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                L.marker([lat, lng]).addTo(map).bindPopup('<?= htmlspecialchars($bds['dia_chi_day_du']) ?>').openPopup();
                if(mapPlaceholder) mapPlaceholder.style.display = 'none';
            } catch (e) {
                console.error("Lỗi khi tải bản đồ Leaflet:", e);
                if(mapPlaceholder) mapPlaceholder.innerHTML = '<p class="text-red-500 text-sm">Không thể tải bản đồ</p>';
            }
        }, 100);
        <?php endif; ?>
    });
</script>

</body>
</html>