<?php
    require_once "../../../config/database.php";

    // --- CÁC HÀM HELPER ---
    function renderStars($score, $total = 5) {
        $html = ''; $score = round($score ?? 0); 
        for ($i = 1; $i <= $total; $i++) { $html .= $i <= $score ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>'; }
        return $html;
    }
    function getLoaiBDS($id_danh_muc, $pdo) { 
        static $categories = null; 
        if ($categories === null) { $stmt = $pdo->query("SELECT id, ten_danh_muc FROM danh_muc"); $categories = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); }
        return $categories[$id_danh_muc] ?? 'Chưa phân loại';
    }

    // Lấy ID Bất động sản từ URL
    if (!isset($_GET['id']) || empty($_GET['id'])) { die("<p class='text-red-500 font-bold p-4'>Không có ID bất động sản!</p>"); }
    $bds_id = $_GET['id']; 

    try { $pdo = ketnoicsdl(); } catch (PDOException $e) { die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 rounded-lg'>Lỗi kết nối CSDL: " . $e->getMessage() . "</div>"); }

    // === SQL 1: LẤY THÔNG TIN BẤT ĐỘNG SẢN & ĐÁNH GIÁ TRUNG BÌNH ===
    $sql_bds_details = "SELECT bds.id AS bds_id, bds.dia_chi_day_du, bds.id_danh_muc, info.ho_ten AS ten_chu_so_huu, nd.avt AS avt_chu_so_huu, (SELECT ROUND(AVG(diem), 1) FROM danh_gia_bds WHERE id_bds = bds.id) AS diem_trung_binh_bds, (SELECT COUNT(id) FROM danh_gia_bds WHERE id_bds = bds.id) AS tong_so_danh_gia_bds FROM bat_dong_san bds LEFT JOIN nguoi_dung nd ON bds.id_chu_so_huu = nd.id LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung WHERE bds.id = :bds_id"; 
    $stmt_bds = $pdo->prepare($sql_bds_details); $stmt_bds->execute([':bds_id' => $bds_id]); $property = $stmt_bds->fetch(PDO::FETCH_ASSOC);
    if (!$property) { die("<p class='text-red-500 font-bold p-4'>Bất động sản không tồn tại!</p>"); }

    // === SQL 2: LẤY HÌNH ẢNH CỦA BẤT ĐỘNG SẢN ===
    $sql_bds_images = "SELECT url FROM hinh_anh_bds WHERE id_bds = :id_bds ORDER BY ngay_tao ASC";
    $stmt_bds_images = $pdo->prepare($sql_bds_images); $stmt_bds_images->execute([':id_bds' => $bds_id]); $bds_image_urls = $stmt_bds_images->fetchAll(PDO::FETCH_COLUMN);
    $bds_full_image_urls = array_map(function($filename) { return '../../../storage/pictures/bds/' . ($filename ?: 'placeholder.jpg'); }, $bds_image_urls);

    // === SQL 3: LẤY DANH SÁCH ĐÁNH GIÁ CHO BẤT ĐỘNG SẢN ===
    $sql_reviews = "SELECT dg.id AS id_danh_gia, dg.diem, dg.binh_luan, dg.ngay_tao, dg.trang_thai, info.ho_ten AS ten_nguoi_danh_gia, nd.avt AS avt_nguoi_danh_gia FROM danh_gia_bds dg LEFT JOIN nguoi_dung nd ON dg.id_nguoi_dung = nd.id LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung WHERE dg.id_bds = :id_bds ORDER BY dg.trang_thai ASC, dg.ngay_tao DESC";
    $stmt_reviews = $pdo->prepare($sql_reviews); $stmt_reviews->execute([':id_bds' => $bds_id]); $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm duyệt đánh giá - <?= htmlspecialchars($property['dia_chi_day_du']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; } 
        #review-list::-webkit-scrollbar { width: 6px; }
        #review-list::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        #review-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        #review-list::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="p-4 md:p-6" 
      x-data="{ 
          showToast: false, toastMessage: '', toastType: 'success', 
          displayToast(detail) { 
              this.toastMessage = detail.message; 
              this.toastType = detail.type || 'success'; 
              this.showToast = true; 
              setTimeout(() => this.showToast = false, 3000); 
          }
      }">
<div class="max-w-7xl mx-auto">
    <header class="flex items-start justify-between mb-2">
        <nav class="text-sm font-medium text-gray-500" aria-label="Breadcrumb">
            <a href="trangchu.php?page=quanly_danhgia_bds" class="hover:text-gray-700">Tổng quan đánh giá</a>
            <span class="mx-2">></span>
            <span class="text-gray-700">Chi tiết</span>
        </nav>
        <a href="trangchu.php?page=quanly_danhgia_bds" class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> <i class="fa-solid fa-arrow-left"></i> Quay lại </a>
    </header>

    <div class="lg:grid lg:grid-cols-12 lg:gap-x-8">

        <aside class="lg:col-span-4 lg:sticky lg:top-6 self-start">
             <div class="bg-white shadow-lg rounded-lg p-5 space-y-4">
                <div x-data="{ current: 0, images: <?= htmlspecialchars(json_encode($bds_full_image_urls)) ?> }">
                    <div class="relative overflow-hidden rounded-md aspect-w-16 aspect-h-9 bg-slate-200">
                        <template x-if="images.length > 0"><img :src="images[current]" alt="Ảnh sản phẩm" class="w-full h-full object-cover"></template>
                        <template x-if="images.length === 0"><div class="w-full h-full flex items-center justify-center text-slate-400"><i class="fa-solid fa-image fa-2x"></i></div></template>
                        <template x-if="images.length > 1"><div class="absolute inset-0 flex items-center justify-between px-2"><button @click="current = (current > 0) ? current - 1 : images.length - 1" class="bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-black/60 transition">&lsaquo;</button><button @click="current = (current < images.length - 1) ? current + 1 : 0" class="bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-black/60 transition">&rsaquo;</button></div></template>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 pt-2"><?= htmlspecialchars($property['dia_chi_day_du']) ?></h3>
                <div class="border-t border-gray-200 pt-3"><dl class="space-y-2 text-sm">
                    <div class="flex justify-between items-center"><dt class="text-gray-500">Loại hình:</dt><dd class="font-medium text-gray-800"><?= htmlspecialchars(getLoaiBDS($property['id_danh_muc'], $pdo)) ?></dd></div>
                    <div class="flex justify-between items-center"><dt class="text-gray-500">Tổng đánh giá:</dt><dd id="total-reviews-count" class="font-medium text-gray-800"><?= number_format($property['tong_so_danh_gia_bds']) ?></dd></div>
                    <div class="flex justify-between items-center"><dt class="text-gray-500">Điểm TB:</dt>
                        <dd id="average-rating-stars" class="font-medium text-yellow-500 flex items-center gap-1">
                            <?= renderStars($property['diem_trung_binh_bds']) ?> <span>(<?= $property['diem_trung_binh_bds'] ?? 'N/A' ?>)</span>
                        </dd>
                    </div>
                </dl></div>
                <div class="mt-4 border-t border-gray-200 pt-4 flex items-center"><img src="../../../storage/pictures/avt/<?= htmlspecialchars($property['avt_chu_so_huu'] ?? 'avt.png') ?>" class="w-10 h-10 rounded-full object-cover mr-3"><div><p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($property['ten_chu_so_huu'] ?? 'N/A') ?></p><p class="text-xs text-gray-500">Chủ sở hữu</p></div></div>
             </div>
        </aside>

        <div class="lg:col-span-8 mt-8 lg:mt-0">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-5 border-b border-gray-200">
                    <h3 id="review-list-title" class="text-lg font-semibold text-gray-900">Danh sách đánh giá (<?= count($reviews) ?>)</h3>
                </div>
                <ul id="review-list" class="divide-y divide-gray-200 max-h-[75vh] overflow-y-auto">
                    <?php if (empty($reviews)): ?>
                        <li id="no-reviews-placeholder" class="p-12 text-center text-gray-500"><i class="fa-solid fa-comments fa-2x mb-2"></i><p>Chưa có đánh giá nào.</p></li>
                    <?php endif; ?>
                    
                    <?php foreach($reviews as $dg): 
                        $is_hidden = $dg['trang_thai'] !== 'hien';
                    ?>
                    <li id="review-<?= $dg['id_danh_gia'] ?>" class="p-5 flex gap-4 transition-colors duration-300 <?= $is_hidden ? 'bg-orange-50/50 hover:bg-orange-100/70' : 'hover:bg-slate-50' ?>">
                        <img src="../../../storage/pictures/avt/<?= htmlspecialchars($dg['avt_nguoi_danh_gia'] ?? 'avt.png') ?>" class="w-10 h-10 rounded-full object-cover mt-1 flex-shrink-0">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($dg['ten_nguoi_danh_gia'] ?? 'Người dùng ẩn danh') ?></p>
                                    <p class="text-xs text-gray-400 mt-0.5"><?= date("d/m/Y H:i", strtotime($dg['ngay_tao'])) ?></p>
                                </div>
                                <div class="text-yellow-500 text-sm flex items-center gap-2">
                                    <span><?= renderStars($dg['diem']) ?></span>
                                    <span class="status-label px-2 py-0.5 rounded text-xs font-semibold <?= $is_hidden ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                        <?= $is_hidden ? 'ĐANG ẨN' : 'HIỂN THỊ' ?>
                                    </span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 mt-2 break-words"><?= nl2br(htmlspecialchars($dg['binh_luan'])) ?></p>
                        </div>
                        <div class="review-actions flex-shrink-0 flex flex-col items-end gap-2 text-xs font-medium">
                            <?php if ($is_hidden): ?>
                                <button onclick="handleAction('<?= $dg['id_danh_gia'] ?>', 'show')" class="action-btn text-blue-600 hover:text-blue-800 transition-colors">Hiện</button>
                            <?php else: ?>
                                <button onclick="handleAction('<?= $dg['id_danh_gia'] ?>', 'hide')" class="action-btn text-gray-500 hover:text-gray-700 transition-colors">Ẩn</button>
                            <?php endif; ?>
                            <button onclick="handleAction('<?= $dg['id_danh_gia'] ?>', 'delete')" class="action-btn text-red-500 hover:text-red-700 transition-colors">Xóa</button>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div x-show="showToast" x-cloak @show-toast.window="displayToast($event.detail)" ...> ... </div>
</div>

<script>
    // ==========================================================
    // == THAY ĐỔI 2: THÊM HÀM RENDER SAO BẰNG JAVASCRIPT ==
    // ==========================================================
    function renderStarsJS(score, total = 5) {
        let html = '';
        score = Math.round(score || 0);
        for (let i = 1; i <= total; i++) {
            html += (i <= score) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
        }
        return html;
    }

    async function handleAction(id, action) {
        let confirmMsg = {'delete': 'XÓA vĩnh viễn?', 'hide': 'ẨN?', 'show': 'HIỆN?'}[action];
        if (!confirm(`Bạn chắc chắn muốn ${confirmMsg} đánh giá này không?`)) return;

        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);
        const apiUrl = '../../models/quanly_danhgia_qt/cn_trangthai_danhgia_bds_qt.php'; // Đảm bảo đường dẫn đúng

        try {
            const response = await fetch(apiUrl, { method: 'POST', body: formData });
            const result = await response.json();
            const alpineRoot = document.querySelector('[x-data]'); 

            if (result.success) {
                const reviewElement = document.getElementById(`review-${id}`);
                
                // Cập nhật giao diện của dòng đánh giá (như cũ)
                if (action === 'delete') {
                    reviewElement.style.transition = 'opacity 0.3s';
                    reviewElement.style.opacity = '0';
                    setTimeout(() => reviewElement.remove(), 300);
                } else {
                    // ... (logic cập nhật trạng thái, nút bấm, ... giữ nguyên như code trước) ...
                    const newStatusText = (action === 'show') ? 'HIỂN THỊ' : 'ĐANG ẨN';
                    const newStatusClass = (action === 'show') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                    const newLiClass = (action === 'show') ? 'hover:bg-slate-50' : 'bg-orange-50/50 hover:bg-orange-100/70';
                    const statusLabel = reviewElement.querySelector('.status-label');
                    statusLabel.textContent = newStatusText;
                    statusLabel.className = `status-label px-2 py-0.5 rounded text-xs font-semibold ${newStatusClass}`;
                    reviewElement.classList.toggle('bg-orange-50/50', action === 'hide');
                    reviewElement.classList.toggle('hover:bg-orange-100/70', action === 'hide');
                    reviewElement.classList.toggle('hover:bg-slate-50', action === 'show');
                    const actionContainer = reviewElement.querySelector('.review-actions');
                    let newActionButtonsHTML = '';
                    if (action === 'show') {
                        newActionButtonsHTML = `<button onclick="handleAction('${id}', 'hide')" class="action-btn text-gray-500 hover:text-gray-700 transition-colors">Ẩn</button><button onclick="handleAction('${id}', 'delete')" class="action-btn text-red-500 hover:text-red-700 transition-colors">Xóa</button>`;
                    } else {
                         newActionButtonsHTML = `<button onclick="handleAction('${id}', 'show')" class="action-btn text-blue-600 hover:text-blue-800 transition-colors">Hiện</button><button onclick="handleAction('${id}', 'delete')" class="action-btn text-red-500 hover:text-red-700 transition-colors">Xóa</button>`;
                    }
                    actionContainer.innerHTML = newActionButtonsHTML;
                }
                
                // ==========================================================
                // == THAY ĐỔI LỚN 3: CẬP NHẬT "LIVE" THÔNG TIN TÓM TẮT ==
                // ==========================================================
                if (result.new_total_count !== undefined && result.new_avg_score !== undefined) {
                    const totalCountEl = document.getElementById('total-reviews-count');
                    const avgStarsEl = document.getElementById('average-rating-stars');
                    const listTitleEl = document.getElementById('review-list-title');

                    const newTotal = result.new_total_count;
                    const newAvg = result.new_avg_score;

                    totalCountEl.textContent = newTotal;
                    avgStarsEl.innerHTML = `${renderStarsJS(newAvg)} <span>(${newAvg !== null ? newAvg : 'N/A'})</span>`;
                    listTitleEl.textContent = `Danh sách đánh giá (${newTotal})`;
                    
                    // Ẩn/hiện placeholder nếu không còn đánh giá nào
                    const placeholder = document.getElementById('no-reviews-placeholder');
                    if(placeholder) placeholder.style.display = newTotal > 0 ? 'none' : 'block';
                }

                // Hiển thị toast thành công
                alpineRoot.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Thao tác thành công!', type: 'success' }, bubbles: true }));

            } else {
                 alpineRoot.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Lỗi không xác định.', type: 'error' }, bubbles: true }));
            }
        } catch(err) {
            console.error('Lỗi xử lý:', err);
            const alpineRoot = document.querySelector('[x-data]');
            alpineRoot.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Lỗi kết nối: ' + err.message, type: 'error' }, bubbles: true }));
        }
    }
</script>

</body>
</html>