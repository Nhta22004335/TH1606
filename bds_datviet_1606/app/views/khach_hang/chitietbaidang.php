<?php
// BẮT BUỘC: session_start() phải là dòng đầu tiên
session_start(); 

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();

// Hàm tiện ích để hiển thị HTML an toàn
function e($s){ 
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); 
}

// -----------------------------------------------------------------
// PHẦN XỬ LÝ GỬI BÌNH LUẬN MỚI
// -----------------------------------------------------------------
$comment_error = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_comment'])) {
    
    $id_bai_dang_form = $_POST['id_bai_dang'] ?? null;
    $id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null; // Giả định user ID được lưu trong session
    $id_cha = $_POST['id_cha'] ?? null;
    $noi_dung = trim($_POST['noi_dung'] ?? '');

    // Xác thực
    if (!$id_nguoi_dung) {
        $comment_error = "Vui lòng đăng nhập để bình luận.";
    } elseif (empty($noi_dung)) {
        $comment_error = "Nội dung bình luận không được để trống.";
    } elseif ($id_bai_dang_form !== ($_GET['id'] ?? null)) {
        $comment_error = "Lỗi: ID bài đăng không khớp.";
    } else {
        // Mọi thứ hợp lệ, tiến hành INSERT
        try {
            $sql = "INSERT INTO binh_luan (id_bai_dang, id_nguoi_dung, id_cha, noi_dung) 
                    VALUES (:id_bai_dang, :id_nguoi_dung, :id_cha, :noi_dung)";
            $stmt = $pdo->prepare($sql);
            
            // Xử lý id_cha (nếu rỗng thì phải là NULL)
            if (empty($id_cha)) {
                $id_cha = null;
            }
            
            $stmt->execute([
                ':id_bai_dang' => $id_bai_dang_form,
                ':id_nguoi_dung' => $id_nguoi_dung,
                ':id_cha' => $id_cha,
                ':noi_dung' => $noi_dung
            ]);
            
            // Chuyển hướng để tránh gửi lại form khi F5
            // Thêm #comment-section để tự động cuộn đến khu vực bình luận
            header("Location: " . $_SERVER['REQUEST_URI'] . "#comment-section");
            exit;

        } catch (PDOException $e) {
            $comment_error = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
        }
    }
}


// -----------------------------------------------------------------
// PHẦN LẤY DỮ LIỆU BÀI ĐĂNG (Như cũ)
// -----------------------------------------------------------------
$id_bai_dang = $_GET['id'] ?? null;
if (!$id_bai_dang) {
    die("Lỗi: Không tìm thấy ID bài đăng.");
}

$sql = "
    SELECT 
        p.id, p.tieu_de, p.mo_ta, p.gia, p.ngay_dang, p.hinh_thuc,
        b.id AS id_bds, b.dia_chi_day_du, b.dien_tich_dat, b.dien_tich_su_dung, 
        b.mat_tien, b.duong_vao, b.huong_nha, b.so_tang, 
        b.so_phong_ngu, b.so_phong_tam, b.thong_tin_phap_ly, 
        b.vi_do, b.kinh_do, b.dac_diem_chi_tiet,
        dm.ten_danh_muc,
        u.so_dt, u.avt,
        info.ho_ten,
        (
            SELECT json_agg(json_build_object('url', url, 'mo_ta', mo_ta) ORDER BY ngay_tao ASC) 
            FROM hinh_anh_bds 
            WHERE id_bds = b.id
        ) AS danh_sach_anh
    FROM bai_dang p
    JOIN bat_dong_san b ON p.id_bat_dong_san = b.id
    JOIN danh_muc dm ON b.id_danh_muc = dm.id
    LEFT JOIN nguoi_dung u ON u.id = p.id_nguoi_dung
    LEFT JOIN info_nguoi_dung info ON info.id_nguoi_dung = u.id
    WHERE 
        p.id = :id_bai_dang 
        AND p.trang_thai = 'daduyet'
        AND b.trang_thai = 'daduyet'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id_bai_dang' => $id_bai_dang]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Bài đăng không tồn tại hoặc chưa được duyệt.");
}

// -----------------------------------------------------------------
// PHẦN LẤY DỮ LIỆU BÌNH LUẬN (MỚI)
// -----------------------------------------------------------------
$sqlComments = "
    SELECT 
        bl.id, bl.id_cha, bl.noi_dung, bl.ngay_tao,
        info.ho_ten,
        u.avt
    FROM binh_luan bl
    LEFT JOIN nguoi_dung u ON bl.id_nguoi_dung = u.id
    LEFT JOIN info_nguoi_dung info ON u.id = info.id_nguoi_dung
    WHERE bl.id_bai_dang = :id_bai_dang AND bl.trang_thai = 'hienthi'
    ORDER BY bl.ngay_tao ASC
";
$stmtComments = $pdo->prepare($sqlComments);
$stmtComments->execute([':id_bai_dang' => $id_bai_dang]);
$commentsFlat = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

// Hàm đệ quy để xây dựng cây bình luận
function buildCommentTree(array $elements, $parentId = null) {
    $branch = [];
    foreach ($elements as $element) {
        if ($element['id_cha'] == $parentId) {
            $children = buildCommentTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }
    return $branch;
}

$commentsTree = buildCommentTree($commentsFlat);

// Hàm đệ quy để render (hiển thị) cây bình luận ra HTML
function renderCommentTree(array $comments, $userImgPrefix) {
    // Dùng $userImgPrefix đã định nghĩa ở ngoài
    echo '<ul class="space-y-6">';
    foreach ($comments as $comment) {
        $avatar = $userImgPrefix . (e($comment['avt']) ?: 'default_avatar.png');
        $ngay_tao_formatted = date('d/m/Y H:i', strtotime($comment['ngay_tao']));

        echo '<li class="flex space-x-4">';
        echo '<img class="w-10 h-10 rounded-full object-cover flex-shrink-0" src="../../../storage/pictures/avt/' . $avatar . '" alt="' . e($comment['ho_ten']) . '">';
        echo '<div class="flex-1">';
        echo '<div class="bg-gray-50 p-4 rounded-lg">';
        echo '<p class="font-semibold text-gray-900">' . e($comment['ho_ten']) . '</p>';
        echo '<p class="text-gray-700 mt-1">' . nl2br(e($comment['noi_dung'])) . '</p>';
        echo '</div>';
        echo '<div class="mt-2 flex items-center space-x-4 text-sm">';
        echo '<span class="text-gray-500">' . $ngay_tao_formatted . '</span>';
        
        // Nút trả lời, gọi hàm JS
        echo '<a href="#comment-form" onclick="setReplyTo(\'' . e($comment['id']) . '\', \'' . e($comment['ho_ten']) . '\')" class="font-medium text-blue-600 hover:underline">Trả lời</a>';
        
        echo '</div>';
        
        // Render các bình luận con (nếu có)
        if (!empty($comment['children'])) {
            echo '<div class="mt-6 ml-6 lg:ml-8">'; // Thụt vào cho đẹp
            renderCommentTree($comment['children'], $userImgPrefix);
            echo '</div>';
        }
        echo '</div>';
        echo '</li>';
    }
    echo '</ul>';
}

// -----------------------------------------------------------------
// XỬ LÝ DỮ LIỆU HIỂN THỊ (Như cũ)
// -----------------------------------------------------------------
$imgPrefix = '';
$userImgPrefix = ''; 

$hinh_anh = $post['danh_sach_anh'] ? json_decode($post['danh_sach_anh'], true) : [];
$dac_diem = $post['dac_diem_chi_tiet'] ? json_decode($post['dac_diem_chi_tiet'], true) : [];

$anh_dai_dien = $imgPrefix . 'chuacapnhat.jpg';
if (!empty($hinh_anh) && !empty($hinh_anh[0]['url'])) {
    $anh_dai_dien = $imgPrefix . e($hinh_anh[0]['url']);
}

$avt_nguoi_dung = $userImgPrefix . 'avt.png';
if (!empty($post['avt'])) {
    $avt_nguoi_dung = $userImgPrefix . e($post['avt']);
}

$dien_tich_hien_thi = $post['dien_tich_su_dung'] ?? $post['dien_tich_dat'];
$don_gia_m2 = ($dien_tich_hien_thi > 0 && $post['gia'] > 0) 
                ? ($post['gia'] / $dien_tich_hien_thi) 
                : 0;

$pageTitle = e($post['tieu_de']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <style>
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .gallery-thumbnails img {
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .gallery-thumbnails img:hover,
        .gallery-thumbnails img.active {
            opacity: 1.0;
            border-width: 2px;
            border-color: #2563eb;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

<nav class="bg-white border-gray-200 shadow-md">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
        <div class="flex md:order-2">
            <a href="nhao.php" class="text-gray-700 hover:bg-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center">
                ← Quay lại
            </a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 bg-white rounded-lg shadow-xl overflow-hidden">
            
            <div class="p-4 border-b">
                <?php if (!empty($hinh_anh)): ?>
                    <div class="mb-4">
                        <img id="mainImage" src="../../../../storage/pictures/bds/<?= $imgPrefix . e($hinh_anh[0]['url']) ?>" alt="<?= e($hinh_anh[0]['mo_ta'] ?? $pageTitle) ?>" class="w-full h-[500px] object-cover rounded-lg">
                    </div>
                    <div class="gallery-thumbnails grid grid-cols-5 gap-2">
                        <?php foreach ($hinh_anh as $index => $img): ?>
                            <img src="../../../../storage/pictures/bds/<?= $imgPrefix . e($img['url']) ?>" 
                                 alt="<?= e($img['mo_ta'] ?? 'Ảnh ' . ($index + 1)) ?>" 
                                 class="w-full h-24 object-cover rounded <?= $index == 0 ? 'active' : '' ?>"
                                 onclick="changeMainImage(this)">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <img src="../../../../storage/pictures/bds/<?= $anh_dai_dien ?>" alt="Chưa có ảnh" class="w-full h-[500px] object-cover rounded-lg">
                <?php endif; ?>
            </div>

            <div class="p-6">
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2"><?= $pageTitle ?></h1>
                <div class="flex items-center text-lg text-gray-600 mb-6">
                    <svg class="w-5 h-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 10a7 7 0 10-14 0c0 2.493 1.698 4.988 3.355 6.59C7.18 17.43 8.72 18.292 9.69 18.933zM10 11.75a1.75 1.75 0 100-3.5 1.75 1.75 0 000 3.5z" clip-rule="evenodd" />
                    </svg>
                    <span><?= e($post['dia_chi_day_du']) ?></span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 border-y py-4">
                    <div class="text-center">
                        <span class="text-sm text-gray-500">Diện tích</span>
                        <p class="text-xl font-bold"><?= e($dien_tich_hien_thi) ?> m²</p>
                    </div>
                    <div class="text-center">
                        <span class="text-sm text-gray-500">Phòng ngủ</span>
                        <p class="text-xl font-bold"><?= e($post['so_phong_ngu'] ?? 'N/A') ?></p>
                    </div>
                    <div class="text-center">
                        <span class="text-sm text-gray-500">Phòng tắm</span>
                        <p class="text-xl font-bold"><?= e($post['so_phong_tam'] ?? 'N/A') ?> </p>
                    </div>
                    <div class="text-center">
                        <span class="text-sm text-gray-500">Hướng nhà</span>
                        <p class="text-xl font-bold"><?= e($post['huong_nha'] ?? 'N/A') ?></p>
                    </div>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mb-4">Mô tả chi tiết</h2>
                <div class="prose max-w-none text-gray-700 leading-relaxed">
                    <?= nl2br(e($post['mo_ta'])) ?>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mt-8 mb-4">Đặc điểm bất động sản</h2>
                <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                    <li class="flex justify-between border-b py-2"><span class="font-medium text-gray-600">Loại hình:</span> <span class="text-gray-800"><?= e($post['ten_danh_muc']) ?></span></li>
                    <li class="flex justify-between border-b py-2"><span class="font-medium text-gray-600">Hình thức:</span> <span class="text-gray-800 capitalize"><?= e($post['hinh_thuc']) ?></span></li>
                    <li class="flex justify-between border-b py-2"><span class="font-medium text-gray-600">Mặt tiền:</span> <span class="text-gray-800"><?= e($post['mat_tien'] ? $post['mat_tien'] . ' m' : 'N/A') ?></span></li>
                    <li class="flex justify-between border-b py-2"><span class="font-medium text-gray-600">Đường vào:</span> <span class="text-gray-800"><?= e($post['duong_vao'] ? $post['duong_vao'] . ' m' : 'N/A') ?></span></li>
                    <li class="flex justify-between border-b py-2"><span class="font-medium text-gray-600">Số tầng:</span> <span class="text-gray-800"><?= e($post['so_tang'] ?? 'N/A') ?></span></li>
                    <li class="flex justify-between border-b py-2"><span class="font-medium text-gray-600">Pháp lý:</span> <span class="text-gray-800"><?= e($post['thong_tin_phap_ly'] ?? 'N/A') ?></span></li>
                </ul>

                <?php if (!empty($dac_diem) && is_array($dac_diem)): ?>
                <h2 class="text-2xl font-bold text-gray-800 mt-8 mb-4">Tiện ích</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <?php foreach ($dac_diem as $key => $value): ?>
                        <?php if ($value === true || strtolower($value) === 'có'): ?>
                            <div class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                <span><?= e(ucfirst(str_replace('_', ' ', $key))) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($post['vi_do'] && $post['kinh_do']): ?>
                <h2 class="text-2xl font-bold text-gray-800 mt-8 mb-4">Vị trí trên bản đồ</h2>
                <div class="w-full h-96 rounded-lg overflow-hidden">
                    <iframe
                        width="100%" height="100%" style="border:0"
                        loading="lazy" allowfullscreen
                        src="https://maps.google.com/maps?q=<?= e($post['vi_do']) ?>,<?= e($post['kinh_do']) ?>&t=&z=15&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
                <?php endif; ?>
            
                <div id="comment-section" class="pt-8 mt-8 border-t">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Bình luận (<?= count($commentsFlat) // Tổng số bình luận ?>)</h2>

                    <form id="comment-form" method="POST" action="chitietbaidang.php?id=<?= e($id_bai_dang) ?>#comment-form" class="mb-8 bg-gray-50 p-5 rounded-lg">
                        <?php if (isset($_SESSION['id_nguoi_dung'])): // Chỉ hiển thị form nếu đã đăng nhập ?>
                            
                            <input type="hidden" name="id_bai_dang" value="<?= e($id_bai_dang) ?>">
                            <input type="hidden" name="id_cha" id="commentParentId" value="">
                            
                            <div id="replyingToWrapper" class="hidden mb-2 text-sm text-gray-600">
                                <span>Đang trả lời <strong><span id="replyingToName"></span></strong>...</span>
                                <button type="button" onclick="cancelReply()" class="ml-2 text-red-500 hover:underline font-medium">(Hủy)</button>
                            </div>

                            <label for="noi_dung" class="block text-sm font-medium text-gray-700 sr-only">Viết bình luận</label>
                            <textarea id="noi_dung" name="noi_dung" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Viết bình luận của bạn..." required></textarea>
                            
                            <?php if ($comment_error): ?>
                                <p class="text-red-500 text-sm mt-1"><?= e($comment_error) ?></p>
                            <?php endif; ?>

                            <div class="mt-3 text-right">
                                <button type="submit" name="submit_comment" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Gửi bình luận
                                </button>
                            </div>
                        <?php else: // Nếu chưa đăng nhập ?>
                            <p class="text-center text-gray-600">
                                Vui lòng <a href="/login.php" class="text-blue-600 hover:underline font-medium">đăng nhập</a> để bình luận.
                            </p>
                        <?php endif; ?>
                    </form>

                    <div class="comment-list-wrapper">
                        <?php if (empty($commentsTree)): ?>
                            <p class="text-gray-500">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                        <?php else: ?>
                            <?php renderCommentTree($commentsTree, $userImgPrefix); ?>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-white rounded-lg shadow-xl p-6 sticky top-8">
                <p class="text-sm text-gray-500">Mức giá</p>
                <p class="text-4xl font-extrabold text-blue-700 mb-2">
                    <?= e(number_format((float)$post['gia'], 0, ',', '.')) ?> VNĐ
                </p>
                <?php if ($don_gia_m2 > 0): ?>
                <p class="text-lg text-gray-600 font-medium">
                    ~ <?= e(number_format($don_gia_m2 / 1000000, 2, ',', '.')) ?> triệu/m²
                </p>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-xl p-6 sticky top-48">
                <h3 class="text-xl font-bold mb-4">Thông tin liên hệ</h3>
                <div class="flex items-center space-x-4">
                  
                    <img class="w-16 h-16 rounded-full object-cover" src="../../../../storage/pictures/avt/<?= e($avt_nguoi_dung) ?>" alt="Avatar người đăng">
                    <div>
                        <p class="text-lg font-semibold text-gray-900"><?= e($post['ho_ten'] ?? 'Chưa cập nhật') ?></p>
                        <p class="text-sm text-gray-500">Người đăng</p>
                    </div>
                </div>
                <div class="mt-6 space-y-3">
                    <a href="tel:<?= e($post['so_dt']) ?>" class="flex items-center justify-center w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-colors">
                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5h-1.5A13.5 13.5 0 012 3.5z" clip-rule="evenodd" /></svg>
                        <span><?= e($post['so_dt']) ?></span>
                    </a>
                    <button type="button" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                        Gửi tin nhắn
                    </button>
                </div>
            </div>

        </div>

    </div>
</div>

<footer class="bg-white rounded-lg shadow dark:bg-gray-900 m-4">
    <div class="w-full max-w-screen-xl mx-auto p-4 md:py-8">
        <span class="block text-sm text-gray-500 sm:text-center dark:text-gray-400">© 2025 <a href="/" class="hover:underline">BDS Portal™</a>. All Rights Reserved.</span>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
<script>
    // JavaScript cho Gallery ảnh
    function changeMainImage(thumbnailElement) {
        const mainImage = document.getElementById('mainImage');
        mainImage.src = thumbnailElement.src;
        
        const thumbnails = document.querySelectorAll('.gallery-thumbnails img');
        thumbnails.forEach(thumb => thumb.classList.remove('active'));
        
        thumbnailElement.classList.add('active');
    }

    // ---------------------------------------------------
    // JAVASCRIPT CHO BÌNH LUẬN (MỚI)
    // ---------------------------------------------------
    const commentForm = document.getElementById('comment-form');
    const parentIdInput = document.getElementById('commentParentId');
    const replyWrapper = document.getElementById('replyingToWrapper');
    const replyNameSpan = document.getElementById('replyingToName');
    const commentTextarea = document.getElementById('noi_dung');

    /**
     * Thiết lập form để trả lời một bình luận
     * @param {string} commentId ID của bình luận cha
     * @param {string} userName Tên của người bị trả lời
     */
    function setReplyTo(commentId, userName) {
        parentIdInput.value = commentId; // Set ID của bình luận cha
        replyNameSpan.innerText = userName; // Hiển thị tên người đang trả lời
        replyWrapper.style.display = 'block'; // Hiển thị thông báo "Đang trả lời..."
        
        // Cuộn đến form và focus vào ô nhập liệu
        commentForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        commentTextarea.focus();
    }

    /**
     * Hủy chế độ trả lời, quay về bình luận gốc
     */
    function cancelReply() {
        parentIdInput.value = ''; // Xóa ID cha
        replyWrapper.style.display = 'none'; // Ẩn thông báo
        replyNameSpan.innerText = '';
    }
</script>
</body>
</html>