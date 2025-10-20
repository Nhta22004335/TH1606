<?php
// Bắt đầu session để có thể lấy ID người dùng (nếu đã đăng nhập)
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../../../config/database.php";

// Lấy ID người dùng hiện tại (Quản trị viên)
$current_user_id = $_SESSION['id_nguoi_dung'] ?? null;

// Lấy ID bài đăng từ URL
$post_id = $_GET['id'] ?? null;
if (!$post_id) {
    die("Lỗi: Không tìm thấy ID bài đăng.");
}

try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
}

// ==========================================================
// == THAY ĐỔI LỚN 1: TRUY VẤN THÔNG TIN ADMIN TỪ SESSION ID ==
// ==========================================================
$current_user_ten = 'Quản trị viên';
$current_user_avatar_url = '../../../storage/pictures/avt/default-avatar.png';

if ($current_user_id) {
    $sql_admin = "SELECT info.ho_ten, nd.avt 
                  FROM nguoi_dung nd 
                  LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
                  WHERE nd.id = ?";
    $stmt_admin = $pdo->prepare($sql_admin);
    $stmt_admin->execute([$current_user_id]);
    $admin_info = $stmt_admin->fetch(PDO::FETCH_ASSOC);

    if ($admin_info) {
        $current_user_ten = $admin_info['ho_ten'] ?? 'Quản trị viên';
        $current_user_avatar_url = '../../../storage/pictures/avt/' . ($admin_info['avt'] ?? 'default-avatar.png');
    }
}
// ==========================================================

// --- CÁC HÀM HELPER ---
function getVietnameseLabel($key) {
    $map = [ 'ten_du_an' => 'Tên dự án', 'tang_so' => 'Tầng số', 'huong_ban_cong' => 'Hướng ban công', 'noi_that' => 'Nội thất', 'view' => 'Tầm nhìn (View)', 'tien_ich' => 'Tiện ích', 'loai_hinh_dat' => 'Loại hình đất', 'loai_hinh_nha' => 'Loại hình nhà', 'tinh_trang_nha' => 'Tình trạng nhà' ];
    return $map[$key] ?? ucfirst(str_replace('_', ' ', $key));
}
function getPostStatusInfo($status) {
    $map = [
        'chuaduyet' => ['text' => 'Chờ duyệt', 'icon' => 'fa-solid fa-clock', 'classes' => "text-yellow-800 bg-yellow-100"],
        'daduyet'   => ['text' => 'Đã duyệt', 'icon' => 'fa-solid fa-check-circle', 'classes' => "text-green-800 bg-green-100"],
        'hethan'    => ['text' => 'Hết hạn', 'icon' => 'fa-solid fa-calendar-times', 'classes' => "text-red-800 bg-red-100"],
        'dahuy'     => ['text' => 'Đã hủy', 'icon' => 'fa-solid fa-ban', 'classes' => "text-gray-800 bg-gray-100"],
    ];
    return $map[$status] ?? ['text' => ucfirst($status), 'icon' => 'fa-solid fa-question', 'classes' => "bg-gray-100 text-gray-800"];
}

// === SQL 1: LẤY THÔNG TIN CHÍNH CỦA BÀI ĐĂNG & BĐS ===
$sql_main = "
    SELECT 
        bd.id AS bai_dang_id, bd.tieu_de, bd.mo_ta, bd.dia_chi_lien_he, bd.hinh_thuc, 
        bd.ngay_dang, bd.luot_xem, bd.trang_thai, bd.ngay_het_han,
        bds.id AS bds_id, bds.dia_chi_day_du, bds.vi_do, bds.kinh_do,
        COALESCE(bds.dien_tich_su_dung, bds.dien_tich_dat) AS dien_tich,
        bds.so_phong_ngu, bds.so_phong_tam, bds.huong_nha, bds.thong_tin_phap_ly, 
        bds.dac_diem_chi_tiet, bds.so_tang, bds.mat_tien, bds.duong_vao,
        info.ho_ten AS ten_nguoi_dang, nd.avt AS avt_nguoi_dang, nd.so_dt, nd.email,
        dm.ten_danh_muc,
        COALESCE(
            (SELECT JSONB_AGG(jsonb_build_object('url', ha.url) ORDER BY ha.ngay_tao ASC) 
             FROM hinh_anh_bds ha WHERE ha.id_bds = bds.id),
            '[]'::jsonb
        ) AS hinh_anh_list
    FROM bai_dang bd
    JOIN bat_dong_san bds ON bd.id_bat_dong_san = bds.id
    LEFT JOIN nguoi_dung nd ON bd.id_nguoi_dung = nd.id
    LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
    LEFT JOIN danh_muc dm ON bds.id_danh_muc = dm.id
    WHERE bd.id = :id_bai_dang
    GROUP BY bd.id, bds.id, info.id, nd.id, dm.id
";
$stmt_main = $pdo->prepare($sql_main);
$stmt_main->execute([':id_bai_dang' => $post_id]);
$post = $stmt_main->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 rounded-lg'>Bài đăng không tồn tại.</div>");
}

// Xử lý dữ liệu
$images = json_decode($post['hinh_anh_list'], true);
$final_images = [];
if (!empty($images)) {
    foreach ($images as $img) { $final_images[] = '../../../storage/pictures/bds/' . $img['url']; }
} else { $final_images[] = 'https://picsum.photos/800/600?random=' . $post['bds_id']; }
$dac_diem_chi_tiet = $post['dac_diem_chi_tiet'] ? json_decode($post['dac_diem_chi_tiet'], true) : [];
$avatar_nguoi_dang = '../../../storage/pictures/avt/' . ($post['avt_nguoi_dang'] ?? 'default-avatar.png');
$ngay_dang_formatted = date('d/m/Y H:i', strtotime($post['ngay_dang']));
$ngay_het_han_formatted = $post['ngay_het_han'] ? date('d/m/Y', strtotime($post['ngay_het_han'])) : 'Không xác định';
$luot_xem_formatted = number_format($post['luot_xem']);
$status_info = getPostStatusInfo($post['trang_thai']);

// === SQL 2: LẤY BÌNH LUẬN ===
$sql_comments = "SELECT b.id, b.id_cha, b.noi_dung, b.ngay_tao, i.ho_ten, nd.avt FROM binh_luan b JOIN nguoi_dung nd ON b.id_nguoi_dung = nd.id LEFT JOIN info_nguoi_dung i ON nd.id = i.id_nguoi_dung WHERE b.id_bai_dang = :id_bai_dang AND b.trang_thai = 'hienthi' ORDER BY b.ngay_tao ASC";
$stmt_comments = $pdo->prepare($sql_comments);
$stmt_comments->execute([':id_bai_dang' => $post_id]);
$flat_comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);

// --- Hàm PHP để xây dựng và render cây bình luận (Giữ nguyên) ---
function buildCommentTree(array $elements, $parentId = null) {
    $branch = [];
    foreach ($elements as $element) {
        if ($element['id_cha'] === $parentId) {
            $children = buildCommentTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }
    return $branch;
}
function renderComments(array $comments) {
    $html = '<ul class="space-y-6">';
    foreach ($comments as $comment) {
        $avatar = '../../../storage/pictures/avt/' . ($comment['avt'] ?? 'default-avatar.png');
        $ngay_tao = date('H:i d/m/Y', strtotime($comment['ngay_tao']));
        
        $html .= '<li id="comment-'.htmlspecialchars($comment['id']).'" class="flex gap-4">';
        $html .= '<img src="'.htmlspecialchars($avatar).'" class="w-10 h-10 rounded-full object-cover flex-shrink-0">';
        $html .= '<div class="flex-1">';
        $html .= '<p class="font-semibold text-sm text-slate-800">'.htmlspecialchars($comment['ho_ten']).' <span class="font-normal text-xs text-slate-400 ml-2">'.$ngay_tao.'</span></p>';
        $html .= '<p class="text-sm text-slate-700 mt-1">'.nl2br(htmlspecialchars($comment['noi_dung'])).'</p>';
        $html .= '<button class="reply-btn text-xs font-semibold text-indigo-600 hover:underline mt-2" data-comment-id="'.htmlspecialchars($comment['id']).'" data-user-name="'.htmlspecialchars($comment['ho_ten']).'">Trả lời</button>';
        
        if (!empty($comment['children'])) {
            $html .= '<div class="mt-4 pl-8 border-l border-slate-200">';
            $html .= renderComments($comment['children']); // Đệ quy
            $html .= '</div>';
        }
        $html .= '</div></li>';
    }
    $html .= '</ul>';
    return $html;
}
$comment_tree = buildCommentTree($flat_comments);
?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết: <?= htmlspecialchars($post['tieu_de']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        #map { height: 350px; z-index: 10; background-color: #e2e8f0; }
        .thumbnail.active { border-color: #4f46e5; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="p-4 sm:p-5"
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
        <header class="mb-4"> 
            <nav class="text-sm mb-3"> 
                <a href="javascript:history.back()" class="text-indigo-600 hover:underline flex items-center gap-2 w-fit"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </nav>
        </header>

        <div class="lg:flex lg:gap-8">
            <main class="lg:w-2/3 space-y-5">
                <section id="image-gallery-wrapper">
                    <div id="gallery-placeholder" class="aspect-[16/9] w-full bg-slate-200 rounded-xl shadow-lg border flex items-center justify-center">
                        <div class="text-center text-slate-400"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p class="mt-2 text-sm font-medium">Đang tải ảnh...</p></div>
                    </div>
                    <template id="gallery-template">
                        <div x-data="{ images: <?= htmlspecialchars(json_encode($final_images)) ?>, current: 0 }">
                            <div class="aspect-[16/9] w-full bg-slate-200 rounded-xl shadow-lg border relative overflow-hidden flex items-center justify-center">
                                <template x-if="images.length > 0"><img :src="images[current]" :key="current" loading="lazy" decoding="async" class="max-w-full max-h-full object-contain"></template>
                                <template x-if="images.length === 0"><div class="text-slate-500 text-center"><i class="fa-solid fa-image fa-3x"></i><p class="mt-2 text-sm">Không có ảnh</p></div></template>
                                <template x-if="images.length >= 1">
                                    <div>
                                        <button @click="current = (current > 0) ? current - 1 : images.length - 1" class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-black/60 transition z-10"><i class="fas fa-chevron-left"></i></button>
                                        <button @click="current = (current < images.length - 1) ? current + 1 : 0" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-black/40 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-black/60 transition z-10"><i class="fas fa-chevron-right"></i></button>
                                        <div class="absolute top-3 right-3 bg-black/50 text-white text-xs font-semibold px-2 py-0.5 rounded-full z-10" x-text="`${current + 1} / ${images.length}`"></div>
                                    </div>
                                </template>
                            </div>
                            <template x-if="images.length > 1">
                                <div class="mt-3 grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-1.5"> 
                                    <template x-for="(img, index) in images">
                                        <div @click="current = index" class="thumbnail aspect-square cursor-pointer rounded-md overflow-hidden border-2 hover:border-indigo-500 transition-all" :class="{ 'active': current === index }">
                                            <img :src="img" loading="lazy" decoding="async" class="h-full w-full object-cover">
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </section>
                
                <section class="bg-white p-5 rounded-xl shadow-md border">
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-sm font-semibold text-indigo-700"><?= htmlspecialchars($post['ten_danh_muc'] ?? 'Bất động sản') ?></span>
                        </div>
                    <h1 class="text-2xl font-bold text-slate-800 leading-tight"><?= htmlspecialchars($post['tieu_de']) ?></h1>
                    <div class="text-sm text-slate-500 mt-3 flex items-center gap-4 flex-wrap border-b pb-4">
                        <span><i class="fa-solid fa-calendar-days mr-1.5 text-slate-400"></i>Đăng: <strong><?= $ngay_dang_formatted ?></strong></span>
                        <span><i class="fa-solid fa-eye mr-1.5 text-slate-400"></i>Xem: <strong><?= $luot_xem_formatted ?></strong></span>
                        <span><i class="fa-solid fa-clock mr-1.5 text-slate-400"></i>Hết hạn: <strong><?= $ngay_het_han_formatted ?></strong></span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center mt-4">
                        <div><p class="text-xs text-slate-500">Hình thức</p><p class="font-bold text-base"><?= htmlspecialchars($post['hinh_thuc'] == 'ban' ? 'Bán' : 'Cho thuê') ?></p></div>
                        <div><p class="text-xs text-slate-500">Diện tích</p><p class="font-bold text-base"><?= htmlspecialchars($post['dien_tich'] ?? 'N/A') ?> m²</p></div>
                        <div><p class="text-xs text-slate-500">Phòng ngủ</p><p class="font-bold text-base"><?= htmlspecialchars($post['so_phong_ngu'] ?? 'N/A') ?></p></div>
                        <div><p class="text-xs text-slate-500">Phòng tắm</p><p class="font-bold text-base"><?= htmlspecialchars($post['so_phong_tam'] ?? 'N/A') ?></p></div>
                    </div>
                </section>
                
                <section class="bg-white p-5 rounded-xl shadow-md border">
                    <h2 class="text-lg font-semibold mb-3 border-b pb-2">Mô tả bài đăng</h2> 
                    <div class="text-slate-700 leading-relaxed space-y-4 text-justify"><?= nl2br(htmlspecialchars($post['mo_ta'])) ?></div>
                </section>
                
                <section class="bg-white p-5 rounded-xl shadow-md border"> 
                    <h2 class="text-lg font-semibold mb-3 border-b pb-2">Chi tiết bất động sản</h2> 
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-sm"> 
                        <?php $details = ['thong_tin_phap_ly' => ['label' => 'Pháp lý', 'icon' => 'fa-gavel'], 'so_tang' => ['label' => 'Số tầng', 'icon' => 'fa-layer-group'], 'huong_nha' => ['label' => 'Hướng nhà', 'icon' => 'fa-compass'], 'mat_tien' => ['label' => 'Mặt tiền', 'icon' => 'fa-road', 'unit' => ' m'], 'duong_vao' => ['label' => 'Đường vào', 'icon' => 'fa-arrows-left-right-to-line', 'unit' => ' m']]; 
                        foreach($details as $key => $info): if(!empty($post[$key])): ?>
                        <div class="flex items-center gap-3 border-b pb-1.5"><i class="fa-solid <?= $info['icon'] ?> w-5 text-slate-400 text-center"></i><span class="text-slate-500 flex-1"><?= $info['label'] ?>:</span><span class="font-semibold text-slate-800"><?= htmlspecialchars($post[$key]) . ($info['unit'] ?? '') ?></span></div>
                        <?php endif; endforeach; ?>
                        <?php foreach($dac_diem_chi_tiet as $key => $value): ?>
                        <div class="flex items-center gap-3 border-b pb-1.5"><i class="fa-solid fa-star w-5 text-slate-400 text-center"></i><span class="text-slate-500 flex-1"><?= getVietnameseLabel($key) ?>:</span><span class="font-semibold text-slate-800"><?= is_bool($value) ? ($value ? 'Có' : 'Không') : htmlspecialchars($value) ?></span></div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <?php if (!empty($post['vi_do']) && !empty($post['kinh_do'])): ?>
                <section class="bg-white p-4 rounded-xl shadow-md border"> 
                    <h2 class="text-lg font-semibold mb-3">Vị trí trên bản đồ</h2> 
                    <div id="map-container" class="relative rounded-lg overflow-hidden"><div id="map-placeholder" class="absolute inset-0 bg-slate-200 ..."><div class="text-center text-slate-500">...Đang tải bản đồ...</div></div><div id="map"></div></div>
                </section>
                <?php endif; ?>

                <section id="comment-section" class="bg-white p-5 rounded-xl shadow-md border">
                    <h2 class="text-xl font-semibold mb-5 border-b pb-3">Bình luận (<span id="comment-count"><?= count($flat_comments) ?></span>)</h2>
                    <div id="comment-form-container" class="mb-6">
                        <?php if($current_user_id): // Chỉ hiện form nếu đã đăng nhập ?>
                        <form id="comment-form" class="flex items-start gap-3">
                            <img src="<?= htmlspecialchars($current_user_avatar_url) ?>" class="w-10 h-10 rounded-full object-cover">
                            <div class="flex-1">
                                <input type="hidden" name="id_bai_dang" value="<?= htmlspecialchars($post_id) ?>">
                                <input type="hidden" name="id_cha" value="">
                                
                                <input type="hidden" name="ho_ten_nguoi_dung" value="<?= htmlspecialchars($current_user_ten) ?>">
                                <input type="hidden" name="avt_nguoi_dung" value="<?= htmlspecialchars(basename($current_user_avatar_url)) ?>">
                                
                                <textarea name="noi_dung" class="w-full outline-none border border-slate-300 rounded-lg p-3 text-sm focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Viết bình luận của bạn..." required></textarea>
                                <div id="reply-to-info" class="hidden mt-2 text-sm text-slate-500">
                                    Đang trả lời <span id="reply-to-user" class="font-semibold"></span>
                                    <button type="button" id="cancel-reply-btn" class="ml-2 text-xs text-red-500 hover:underline">(Hủy)</button>
                                </div>
                                <button type="submit" class="mt-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">Gửi bình luận</button>
                            </div>
                        </form>
                        <?php else: ?>
                        <p class="text-sm text-slate-600 p-4 bg-slate-100 rounded-lg text-center">
                            Vui lòng <a href="/login.php" class="font-semibold text-indigo-600 hover:underline">đăng nhập</a> để bình luận.
                        </p>
                        <?php endif; ?>
                    </div>
                    <div id="comment-list-container">
                        <?= renderComments($comment_tree) ?>
                    </div>
                </section>
            </main>

            <aside class="lg:w-1/3">
                <div class="sticky top-5 space-y-5"> 
                    
                    <div id="admin-action-box" class="bg-white p-5 rounded-xl shadow-md border border-indigo-200">
                        <h3 class="text-base font-semibold text-slate-700 mb-4">Quản trị viên</h3>
                        <div class="flex items-center gap-3"> 
                            <img class="h-10 w-10 rounded-full object-cover" src="<?= htmlspecialchars($current_user_avatar_url) ?>" alt="Admin Avatar"> 
                            <div>
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($current_user_ten) ?></p> 
                                <p class="text-xs text-slate-500">Đang xem với tư cách Quản trị</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-slate-500">Trạng thái bài đăng:</span>
                                <div id="status-badge-container">
                                    <span class="px-3 py-1 text-sm font-bold rounded-full flex items-center gap-2 <?= $status_info['classes'] ?>">
                                        <i class="<?= $status_info['icon'] ?>"></i>
                                        <span><?= $status_info['text'] ?></span>
                                    </span>
                                </div>
                            </div>
                            <div id="action-buttons-container" class="space-y-2">
                                </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-5 rounded-xl shadow-md border"> 
                        <h3 class="text-base font-semibold text-slate-700 mb-4">Thông tin Môi giới (Người đăng)</h3>
                        <div class="flex items-center gap-3"> 
                            <img class="h-12 w-12 rounded-full object-cover" src="<?= htmlspecialchars($avatar_nguoi_dang) ?>" alt="Avatar"> 
                            <div>
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($post['ten_nguoi_dang'] ?? 'Chưa cập nhật') ?></p> 
                                <p class="text-sm text-slate-500"><?= htmlspecialchars($post['email'] ?? 'Chưa cập nhật') ?></p>
                            </div>
                        </div>
                         <div class="mt-4 flex flex-col gap-2"> 
                            <a href="tel:<?= htmlspecialchars($post['so_dt']) ?>" class="w-full text-center px-4 py-2 font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700 text-sm"><i class="fas fa-phone-alt mr-2"></i><?= htmlspecialchars($post['sdt_moigioi'] ?? 'Gọi điện') ?></a> 
                        </div>
                        <div class="mt-4 border-t pt-3">
                             <p class="text-sm text-slate-500 font-medium">Địa chỉ liên hệ (bài đăng):</p>
                             <p class="text-sm text-slate-700"><?= htmlspecialchars($post['dia_chi_lien_he'] ?? 'Không có') ?></p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div x-show="showToast" x-cloak @show-toast.window="displayToast($event.detail)"
         class="fixed bottom-5 right-5 w-full max-w-sm p-4 rounded-xl shadow-2xl text-white font-semibold z-50" 
         :class="{ 'bg-gradient-to-r from-green-500 to-green-600': toastType === 'success', 'bg-gradient-to-r from-red-500 to-red-600': toastType === 'error' }"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4">
        <div class="flex items-center">
            <i class="fas fa-2x mr-4" :class="{ 'fa-check-circle': toastType === 'success', 'fa-exclamation-triangle': toastType === 'error' }"></i>
            <span x-text="toastMessage"></span>
        </div>
    </div>

<script>
    // --- JavaScript ---
    
    // Lưu trữ dữ liệu bài đăng ban đầu
    const postData = <?= json_encode($post) ?>;

    // Từ điển trạng thái cho JS
    const postStatusMap = {
        'chuaduyet': { text: "Chờ duyệt", icon: 'fa-solid fa-clock', classes: "text-yellow-800 bg-yellow-100" },
        'daduyet':   { text: "Đã duyệt", icon: 'fa-solid fa-check-circle', classes: "text-green-800 bg-green-100" },
        'hethan':    { text: 'Hết hạn', icon: 'fa-solid fa-calendar-times', classes: "text-red-800 bg-red-100" },
        'dahuy':     { text: 'Đã hủy', icon: 'fa-solid fa-ban', classes: "text-gray-800 bg-gray-100" }
    };

    // Hàm render lại các nút hành động
    function renderActionButtons(newStatus) {
        const container = document.getElementById('action-buttons-container');
        if (!container) return;
        let buttonsHTML = '';
        const postId = postData.bai_dang_id;

        if (newStatus === 'chuaduyet') {
            buttonsHTML = `
                <button class="action-btn w-full flex justify-center items-center gap-2 bg-green-600 text-white py-2 rounded-md text-sm font-semibold hover:bg-green-700" data-id="${postId}" data-action="daduyet"><i class="fa-solid fa-check"></i>Duyệt bài</button>
                <button class="action-btn w-full flex justify-center items-center gap-2 bg-red-600 text-white py-2 mt-2 rounded-md text-sm font-semibold hover:bg-red-700" data-id="${postId}" data-action="dahuy"><i class="fa-solid fa-ban"></i>Hủy</button>
            `;
        } else if (newStatus === 'daduyet') {
            buttonsHTML = `
                <button class="action-btn w-full flex justify-center items-center gap-2 bg-yellow-600 text-white py-2 rounded-md text-sm font-semibold hover:bg-yellow-700" data-id="${postId}" data-action="hethan"><i class="fa-solid fa-calendar-times"></i>Hết hạn</button>
                <button class="action-btn w-full flex justify-center items-center gap-2 bg-red-600 text-white py-2 mt-2 rounded-md text-sm font-semibold hover:bg-red-700" data-id="${postId}" data-action="dahuy"><i class="fa-solid fa-ban"></i>Hủy</button>
            `;
        } else if (newStatus === 'hethan' || newStatus === 'dahuy') {
            buttonsHTML = `<button class="action-btn w-full flex justify-center items-center gap-2 bg-blue-600 text-white py-2 rounded-md text-sm font-semibold hover:bg-blue-700" data-id="${postId}" data-action="chuaduyet"><i class="fa-solid fa-rotate-left"></i>Đăng lại (Chờ duyệt)</button>`;
        } else {
            buttonsHTML = `<p class="text-sm text-slate-500 italic">Không có hành động nào cho trạng thái này.</p>`;
        }
        container.innerHTML = buttonsHTML;
    }
    
    // Hàm render lại nhãn trạng thái
    function renderStatusBadge(newStatus) {
        const container = document.getElementById('status-badge-container');
        const statusInfo = postStatusMap[newStatus] || { text: newStatus, icon: 'fa-solid fa-question', classes: "bg-gray-100 text-gray-800" };
        if (container) {
            container.innerHTML = `<span class="px-3 py-1 text-sm font-bold rounded-full flex items-center gap-2 ${statusInfo.classes}"><i class="${statusInfo.icon}"></i><span>${statusInfo.text}</span></span>`;
        }
    }

    // Hàm gọi API để cập nhật
    async function updatePostStatus(postId, newStatus) {
        const messages = {
            daduyet: 'Bạn có chắc chắn muốn DUYỆT bài đăng này?',
            dahuy: 'Bạn có chắc chắn muốn HỦY bài đăng này?',
            hethan: 'Bạn có chắc muốn đánh dấu bài đăng này là HẾT HẠN?',
            chuaduyet: 'Bạn có chắc chắn muốn HOÀN TÁC (Chờ duyệt) bài đăng này?'
        };
        if (!confirm(messages[newStatus] || 'Bạn có chắc chắn?')) return;

        try {
            const response = await fetch('../../models/quanly_baidang_qt/cn_trangthai_baidang_qt.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: postId, status: newStatus }) // Gửi status
            });
            const result = await response.json();
            
            const toastDetail = { message: result.message || (result.success ? 'Thành công!' : 'Thất bại!'), type: result.success ? 'success' : 'error' };
            window.dispatchEvent(new CustomEvent('show-toast', { detail: toastDetail, bubbles: true }));

            if (result.success) {
                renderStatusBadge(newStatus);
                renderActionButtons(newStatus);
                postData.trang_thai = newStatus; 
            }
        } catch (error) {
            console.error('Lỗi Fetch:', error);
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Lỗi kết nối.', type: 'error' }, bubbles: true }));
        }
    }

    // --- KHỞI CHẠY KHI TẢI TRANG ---
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Render các nút hành động ban đầu
        renderActionButtons(postData.trang_thai);

        // 2. Gán sự kiện click cho các nút hành động
        document.getElementById('admin-action-box').addEventListener('click', function(e) {
            const button = e.target.closest('.action-btn');
            if (button) {
                updatePostStatus(button.dataset.id, button.dataset.action);
            }
        });

        // 3. Tối ưu tải ảnh (Lazy load)
        const galleryWrapper = document.getElementById('image-gallery-wrapper');
        const galleryPlaceholder = document.getElementById('gallery-placeholder');
        const galleryTemplate = document.getElementById('gallery-template');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if(document.getElementById('gallery-placeholder')) {
                        galleryWrapper.innerHTML = galleryTemplate.innerHTML;
                    }
                    observer.unobserve(galleryWrapper);
                }
            });
        }, { rootMargin: '50px' });
        if (galleryWrapper && galleryPlaceholder) {
            observer.observe(galleryWrapper);
        }

        // 4. Tối ưu tải bản đồ (Trì hoãn)
        <?php if (!empty($post['vi_do']) && !empty($post['kinh_do'])): ?>
        const mapContainer = document.getElementById('map-container');
        const mapPlaceholder = document.getElementById('map-placeholder');
        let mapInitialized = false;
        const mapObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !mapInitialized) {
                    const lat = <?= (float)$post['vi_do'] ?>;
                    const lng = <?= (float)$post['kinh_do'] ?>;
                    try {
                        const map = L.map('map').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                        L.marker([lat, lng]).addTo(map).bindPopup('<?= htmlspecialchars($post['dia_chi_day_du']) ?>').openPopup();
                        if(mapPlaceholder) mapPlaceholder.style.display = 'none';
                        mapInitialized = true;
                    } catch (e) {
                        console.error("Lỗi khi tải bản đồ Leaflet:", e);
                        if(mapPlaceholder) mapPlaceholder.innerHTML = '<p class="text-red-500 text-sm">Không thể tải bản đồ</p>';
                    }
                    mapObserver.unobserve(mapContainer);
                }
            });
        }, { rootMargin: '0px 0px -100px 0px' }); 
        if (mapContainer) mapObserver.observe(mapContainer);
        <?php endif; ?>

        // 5. XỬ LÝ BÌNH LUẬN
        const commentForm = document.getElementById('comment-form');
        const commentListContainer = document.getElementById('comment-list-container');
        const commentFormContainer = document.getElementById('comment-form-container');
        const originalFormParent = commentFormContainer ? commentFormContainer.parentNode : null; 
        const originalFormNextSibling = commentFormContainer ? commentFormContainer.nextElementSibling : null; 

        if(commentForm) {
            const hiddenParentIdInput = commentForm.querySelector('input[name="id_cha"]');
            const replyToInfo = document.getElementById('reply-to-info');
            const replyToUser = document.getElementById('reply-to-user');
            const cancelReplyBtn = document.getElementById('cancel-reply-btn');

            commentForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(commentForm);
                const noiDung = formData.get('noi_dung').trim();
                if (!noiDung) {
                    dispatchToast('Vui lòng nhập nội dung bình luận.', 'error');
                    return;
                }
                try {
                    const response = await fetch('../../models/quanly_baidang_qt/ph_binhluan_baidang_qt.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.success) {
                        dispatchToast(result.message || 'Bình luận thành công!', 'success');
                        const newCommentHtml = createCommentHtml(result.newComment);
                        if (result.newComment.id_cha) {
                            const parentComment = document.getElementById(`comment-${result.newComment.id_cha}`);
                            let childrenContainer = parentComment.querySelector('ul');
                            if (!childrenContainer) {
                                const newDiv = document.createElement('div');
                                newDiv.className = 'mt-4 pl-8 border-l border-slate-200';
                                childrenContainer = document.createElement('ul');
                                childrenContainer.className = 'space-y-6';
                                newDiv.appendChild(childrenContainer);
                                parentComment.querySelector('.flex-1').appendChild(newDiv);
                            }
                            childrenContainer.insertAdjacentHTML('beforeend', newCommentHtml);
                        } else {
                            const mainList = commentListContainer.querySelector('ul') || createMainCommentList();
                            mainList.insertAdjacentHTML('beforeend', newCommentHtml);
                        }
                        commentForm.reset(); 
                        resetCommentForm(); 
                        const countEl = document.getElementById('comment-count');
                        countEl.textContent = parseInt(countEl.textContent) + 1;
                    } else {
                        dispatchToast(result.message || 'Không thể gửi bình luận.', 'error');
                    }
                } catch (error) {
                    console.error('Lỗi Fetch:', error);
                    dispatchToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
                }
            });

            commentListContainer.addEventListener('click', function(e) {
                const replyButton = e.target.closest('.reply-btn');
                if (!replyButton) return;
                const commentId = replyButton.dataset.commentId;
                const userName = replyButton.dataset.userName;
                hiddenParentIdInput.value = commentId;
                replyToUser.textContent = userName;
                replyToInfo.classList.remove('hidden');
                const targetComment = document.getElementById(`comment-${commentId}`);
                targetComment.querySelector('.flex-1').appendChild(commentFormContainer);
                commentForm.querySelector('textarea').focus();
            });

            cancelReplyBtn.addEventListener('click', function() {
                resetCommentForm();
            });

            function resetCommentForm() {
                hiddenParentIdInput.value = '';
                replyToInfo.classList.add('hidden');
                commentForm.reset(); 
                if (originalFormParent) {
                    originalFormParent.insertBefore(commentFormContainer, originalFormNextSibling);
                }
            }

            function createMainCommentList() {
                commentListContainer.innerHTML = ''; 
                const mainList = document.createElement('ul');
                mainList.className = 'space-y-6';
                commentListContainer.appendChild(mainList);
                return mainList;
            }

            function createCommentHtml(comment) {
                const avatar = `../../../storage/pictures/avt/${comment.avt || 'default-avatar.png'}`;
                const ngay_tao = new Date(comment.ngay_tao).toLocaleString('vi-VN');
                return `
                    <li id="comment-${comment.id}" class="flex gap-4">
                        <img src="${avatar}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                        <div class="flex-1">
                            <p class="font-semibold text-sm text-slate-800">${escapeHTML(comment.ho_ten)} <span class="font-normal text-xs text-slate-400 ml-2">${ngay_tao}</span></p>
                            <p class="text-sm text-slate-700 mt-1">${escapeHTML(comment.noi_dung).replace(/\n/g, '<br>')}</p>
                            <button class="reply-btn text-xs font-semibold text-indigo-600 hover:underline mt-2" data-comment-id="${comment.id}" data-user-name="${escapeHTML(comment.ho_ten)}">Trả lời</button>
                        </div>
                    </li>`;
            }
        } 

        function dispatchToast(message, type = 'success') {
             window.dispatchEvent(new CustomEvent('show-toast', { detail: { message, type }, bubbles: true }));
        }
        
        function escapeHTML(str) {
            return str.replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }
    });
</script>

</body>
</html>