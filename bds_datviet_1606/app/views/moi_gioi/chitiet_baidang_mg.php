<?php
// Bắt buộc phải khởi động session để sử dụng $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// =================================================================
// 1. KẾT NỐI CSDL & LẤY DỮ LIỆU
// =================================================================
require_once "../../../config/database.php";
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: ". $e->getMessage());
}

// Lấy ID người dùng hiện tại từ session
$current_user_id = $_SESSION['id_nguoi_dung'] ?? null;
if (!$current_user_id) {
    // Nếu chưa đăng nhập, chuyển hướng hoặc hiển thị lỗi
    header("Location: /login.php"); // Ví dụ chuyển hướng
    exit();
}

// Lấy ID bài đăng từ URL, đảm bảo nó là số nguyên
$post_id = $_GET['id']??'';


// =================================================================
// 2. TRUY VẤN DỮ LIỆU CHI TIẾT CỦA BÀI ĐĂNG
// =================================================================

// **QUAN TRỌNG**: Thêm điều kiện `bd.id_nguoi_dung = :user_id` vào câu truy vấn
// để đảm bảo người dùng chỉ xem được bài đăng của chính họ.
$sql_post = "
    SELECT 
        bd.*,
        bds.dien_tich, bds.khu_vuc,
        info.ho_ten AS ten_moigioi, nd.so_dt, nd.email,
        nd.avt AS avatar_moigioi
    FROM bai_dang AS bd
    JOIN bat_dong_san AS bds ON bd.id_bat_dong_san = bds.id
    JOIN nguoi_dung AS nd ON bd.id_nguoi_dung = nd.id
    LEFT JOIN info_nguoi_dung AS info ON nd.id = info.id_nguoi_dung
    WHERE bd.id = :post_id AND bd.id_nguoi_dung = :user_id
";

$stmt_post = $pdo->prepare($sql_post);
$stmt_post->execute([':post_id' => $post_id, ':user_id' => $current_user_id]);
$post = $stmt_post->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy bài đăng (hoặc bài đăng không thuộc về người dùng này), hiển thị lỗi
if (!$post) {
    die("Không tìm thấy bài đăng hoặc bạn không có quyền truy cập.");
}

// Lấy tất cả hình ảnh của bất động sản
$sql_images = "SELECT url FROM hinh_anh_bds WHERE id_bds = :bds_id ORDER BY ngay_tao ASC";
$stmt_images = $pdo->prepare($sql_images);
$stmt_images->execute([':bds_id' => $post['id_bat_dong_san']]);
$images = $stmt_images->fetchAll(PDO::FETCH_COLUMN);

// Xử lý đường dẫn ảnh
$post['avatar_moigioi'] = '../../../storage/pictures/avt/'. ($post['avatar_moigioi'] ?? 'default-avatar.png');
$processed_images = array_map(function ($img) {
    return '../../../storage/pictures/bds/'. $img;
}, $images);

// Nếu không có ảnh nào, thêm một ảnh placeholder
if (empty($processed_images)) {
    $processed_images[] = 'https://picsum.photos/800/600';
}
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết: <?= htmlspecialchars($post['tieu_de']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .prose { max-width: none; } /* Tailwind typography helper */
        .sticky-sidebar { position: sticky; top: 2rem; }
    </style>
</head>
<body class="h-full">
    <div class="max-w-7xl mx-auto">

        <nav class="mb-6 text-sm font-medium text-gray-500" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="trangchu.php?page=../moi_gioi/ql_baidang_mg" class="hover:text-indigo-600">Quản lý bài đăng</a>
                </li>
                <li class="flex items-center mx-2">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </li>
                <li class="flex items-center text-gray-800">
                    Chi tiết bài đăng
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 lg:gap-8">
            <main class="lg:col-span-2">
                <section class="mb-8">
                    <div class="aspect-[4/3] w-full overflow-hidden rounded-xl shadow-lg border border-gray-200">
                        <img id="main-image" src="<?= htmlspecialchars($processed_images[0]) ?>" alt="Ảnh chính" class="h-full w-full object-cover transition-all duration-300">
                    </div>
                    <?php if (count($processed_images) > 1): ?>
                    <div class="mt-4 grid grid-cols-4 sm:grid-cols-6 gap-3">
                        <?php foreach ($processed_images as $index => $img_url): ?>
                        <div class="aspect-square cursor-pointer rounded-lg overflow-hidden border-2 border-transparent hover:border-indigo-500 transition-all <?= $index == 0 ? 'border-indigo-500' : '' ?>" onclick="changeImage('<?= htmlspecialchars($img_url) ?>', this)">
                            <img src="<?= htmlspecialchars($img_url) ?>" alt="Thumbnail <?= $index + 1 ?>" class="h-full w-full object-cover">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>

                <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-8">
                    <span class="inline-block bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-1 rounded-full mb-3">
                        <?= $post['hinh_thuc'] === 'ban' ? 'Bất động sản bán' : 'Bất động sản cho thuê' ?>
                    </span>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight"><?= htmlspecialchars($post['tieu_de']) ?></h1>
                    <p class="mt-2 text-base text-gray-500 flex items-start">
                        <i class="fa-solid fa-location-dot mt-1 mr-2 text-gray-400"></i>
                        <span><?= htmlspecialchars($post['khu_vuc']) ?></span>
                    </p>

                    <div class="mt-6 border-t border-gray-200 pt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div class="p-2"><i class="fa-solid fa-ruler-combined text-indigo-500 text-xl mb-2"></i><p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($post['dien_tich']) ?> m²</p><p class="text-xs text-gray-500">Diện tích</p></div>
                        <div class="p-2"><i class="fa-solid fa-bed text-indigo-500 text-xl mb-2"></i><p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($post['so_phong_ngu'] ?? 'N/A') ?></p><p class="text-xs text-gray-500">Phòng ngủ</p></div>
                        <div class="p-2"><i class="fa-solid fa-bath text-indigo-500 text-xl mb-2"></i><p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($post['so_phong_tam'] ?? 'N/A') ?></p><p class="text-xs text-gray-500">Phòng tắm</p></div>
                        <div class="p-2"><i class="fa-regular fa-compass text-indigo-500 text-xl mb-2"></i><p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($post['huong_nha'] ?? 'N/A') ?></p><p class="text-xs text-gray-500">Hướng nhà</p></div>
                    </div>
                </section>

                <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Mô tả chi tiết</h2>
                    <div class="prose text-gray-700 leading-relaxed">
                        <?= !empty($post['mo_ta']) ? nl2br(htmlspecialchars($post['mo_ta'])) : '<p class="italic text-gray-500">Chưa có mô tả cho bài đăng này.</p>' ?>
                    </div>
                </section>

                
            </main>

            <aside class="mt-8 lg:mt-0">
                <div class="sticky-sidebar space-y-6">
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center">
                        <p class="text-sm font-medium text-gray-500">Mức giá</p>
                        <p class="text-3xl font-bold text-indigo-600 my-2"><?= number_format($post['gia'], 0, ',', '.') ?> VNĐ</p>
                        <div class="mt-4 text-xs text-gray-500 flex justify-between">
                            <span><i class="fa-regular fa-calendar mr-1"></i> Ngày đăng: <?= date('d/m/Y', strtotime($post['ngay_dang'])) ?></span>
                            <span><i class="fa-regular fa-eye mr-1"></i> <?= number_format($post['luot_xem']) ?> lượt xem</span>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                         <div class="flex items-center">
                            <img class="h-14 w-14 rounded-full object-cover" src="<?= htmlspecialchars($post['avatar_moigioi']) ?>" alt="Avatar">
                            <div class="ml-4">
                                <p class="text-base font-bold text-gray-900"><?= htmlspecialchars($post['ten_moigioi']) ?></p>
                                <p class="text-sm text-gray-500">Người đăng</p>
                            </div>
                        </div>
                        <div class="mt-6 space-y-3">
                             <a href="tel:<?= htmlspecialchars($post['so_dt']) ?>" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fa-solid fa-phone mr-2"></i> Gọi điện
                            </a>
                             <a href="mailto:<?= htmlspecialchars($post['email']) ?>" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fa-solid fa-envelope mr-2"></i> Gửi Email
                            </a>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Hành động</h3>
                        <div class="space-y-3">
                             <a href="#" class="btn-action w-full flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors" data-id="<?= $post_id ?>" data-action="edit">
                                <i class="fa-solid fa-pen-to-square mr-2"></i> Chỉnh sửa bài đăng
                            </a>
                             <a href="#" class="btn-action w-full flex items-center justify-center px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-semibold hover:bg-red-200 transition-colors" data-id="<?= $post_id ?>" data-action="unpublish">
                                <i class="fa-solid fa-eye-slash mr-2"></i> Gỡ/Ẩn bài đăng
                            </a>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <script>
        // Hàm thay đổi ảnh chính khi click vào thumbnail
        function changeImage(imageUrl, clickedElement) {
            document.getElementById('main-image').src = imageUrl;
            
            // Cập nhật viền active cho thumbnail
            const thumbnails = document.querySelectorAll('.aspect-square');
            thumbnails.forEach(thumb => thumb.classList.remove('border-indigo-500'));
            clickedElement.classList.add('border-indigo-500');
        }

        // Xử lý các nút hành động (tương tự trang quản lý)
        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('click', function(event) {
                const actionButton = event.target.closest('.btn-action');
                if (actionButton) {
                    event.preventDefault();
                    const postId = actionButton.dataset.id;
                    const action = actionButton.dataset.action;

                    if (action === 'edit') {
                        alert(`Chuyển đến trang chỉnh sửa bài đăng ID: ${postId}`);
                        // THỰC TẾ: window.location.href = `chinhsua_baidang.php?id=${postId}`;
                    } else if (action === 'unpublish') {
                        if (confirm('Bạn có chắc chắn muốn GỠ bài đăng này không?')) {
                            alert(`Gửi yêu cầu gỡ/ẩn bài ID: ${postId} (Chưa triển khai backend)`);
                            // THỰC TẾ: Gửi yêu cầu fetch và có thể chuyển hướng về trang quản lý
                            // window.location.href = 'quanly_baidang.php';
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>