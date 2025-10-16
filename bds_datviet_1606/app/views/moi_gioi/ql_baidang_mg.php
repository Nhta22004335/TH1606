<?php
// =================================================================
// PHẦN 1 & 2: KẾT NỐI CSDL & TRUY VẤN DỮ LIỆU (GIỮ NGUYÊN)
// =================================================================
// Bắt buộc phải khởi động session để sử dụng $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "../../../config/database.php";
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
}
$current_user_id = $_SESSION['id_nguoi_dung'] ?? null;
if (!$current_user_id) {
    die("Lỗi: Vui lòng đăng nhập để xem các bài đăng của bạn.");
}
$search = $_GET['search'] ?? '';
$search = trim($search);
$sql = "
    SELECT 
        bd.id, bd.tieu_de, bd.gia, bd.ngay_dang, bd.luot_xem, bd.trang_thai, bd.hinh_thuc,
        bds.dien_tich, bds.khu_vuc AS dia_chi,
        info.ho_ten AS ten_moigioi,
        nd.avt AS avatar_moigioi,
        anhbia.url AS anh_bia
    FROM bai_dang AS bd
    JOIN bat_dong_san AS bds ON bd.id_bat_dong_san = bds.id
    JOIN nguoi_dung AS nd ON bd.id_nguoi_dung = nd.id
    LEFT JOIN info_nguoi_dung AS info ON nd.id = info.id_nguoi_dung
    LEFT JOIN LATERAL (
        SELECT url FROM hinh_anh_bds 
        WHERE id_bds = bds.id ORDER BY ngay_tao ASC LIMIT 1
    ) AS anhbia ON TRUE
    WHERE bd.id_nguoi_dung = :user_id 
";
if (!empty($search)) {
    $sql .= " AND (LOWER(bd.tieu_de) LIKE LOWER(:search) OR LOWER(bds.khu_vuc) LIKE LOWER(:search)) ";
}
$sql .= " ORDER BY bd.ngay_dang DESC;";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_STR);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$baidang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =================================================================
// PHẦN 3: XỬ LÝ DỮ LIỆU & HÀM HỖ TRỢ (TINH CHỈNH LẠI HÀM BADGE)
// =================================================================
foreach ($baidang as $key => $post) {
    if (empty($post['anh_bia'])) {
        $baidang[$key]['anh_bia'] = 'https://picsum.photos/400/300?random=' . $key;
    } else {
        $baidang[$key]['anh_bia'] = '../../../storage/pictures/bds/' . $post['anh_bia'];
    }
}

// Hàm getStatusBadge được cập nhật class để phù hợp với giao diện mới
function getStatusBadge($status)
{
$map = [
    'chuaduyet' => ['text' => 'Chờ duyệt', 'class' => 'bg-amber-100 text-amber-800 border border-amber-200'],
    'daduyet'   => ['text' => 'Đang hiển thị', 'class' => 'bg-green-100 text-green-800 border border-green-200'],
    'hienthi'   => ['text' => 'Đang hiển thị', 'class' => 'bg-green-100 text-green-800 border border-green-200'], // <-- Đã thêm
    'hethan'    => ['text' => 'Hết hạn', 'class' => 'bg-red-100 text-red-800 border border-red-200'],
    'daban'     => ['text' => 'Đã bán', 'class' => 'bg-blue-100 text-blue-800 border border-blue-200'],
    'dathue'    => ['text' => 'Đã cho thuê', 'class' => 'bg-indigo-100 text-indigo-800 border border-indigo-200'],
    'an'        => ['text' => 'Đã ẩn', 'class' => 'bg-gray-100 text-gray-800 border border-gray-200'],
];
    $info = $map[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-800'];
    return "<span class='absolute top-3 left-3 text-xs font-semibold px-2.5 py-1 rounded-full {$info['class']}'>{$info['text']}</span>";
}

$stats = [
    'pending' => count(array_filter($baidang, fn ($p) => $p['trang_thai'] === 'chuaduyet')),
    'active'  => count(array_filter($baidang, fn ($p) => $p['trang_thai'] === 'daduyet')),
    'expired' => count(array_filter($baidang, fn ($p) => in_array($p['trang_thai'], ['hethan', 'daban', 'dathue', 'an']))),
    'total'   => count($baidang),
];
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bài đăng Của Tôi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Xóa marker mặc định của <summary> */
        summary::-webkit-details-marker { display: none; }
        summary::marker { content: ''; }
        /* Style cho dropdown menu */
        details[open] > summary + div {
            animation: slide-down 0.2s ease-out;
        }
        @keyframes slide-down {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="h-full">
    <div class="max-w-7xl mx-auto">
        <header class="mb-8">
            <div class="border-b border-gray-200 pb-5 mb-8">
                <h1 class="text-2xl font-bold leading-tight text-gray-800">Bài đăng của tôi</h1>
                <p class="mt-2 max-w-4xl text-sm text-gray-600">Xem, quản lý và theo dõi hiệu suất các tin đăng bất động sản của bạn.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200"><div class="p-5"><dl><dt class="text-sm font-medium text-gray-500 truncate flex items-center"><i class="fa-solid fa-hourglass-half mr-2 text-amber-500"></i>Chờ duyệt</dt><dd class="mt-1 text-3xl font-semibold text-gray-900 tracking-tight"><?= $stats['pending'] ?></dd></dl></div></div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200"><div class="p-5"><dl><dt class="text-sm font-medium text-gray-500 truncate flex items-center"><i class="fa-solid fa-circle-check mr-2 text-green-500"></i>Đang hiển thị</dt><dd class="mt-1 text-3xl font-semibold text-gray-900 tracking-tight"><?= $stats['active'] ?></dd></dl></div></div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200"><div class="p-5"><dl><dt class="text-sm font-medium text-gray-500 truncate flex items-center"><i class="fa-solid fa-box-archive mr-2 text-red-500"></i>Lưu trữ</dt><dd class="mt-1 text-3xl font-semibold text-gray-900 tracking-tight"><?= $stats['expired'] ?></dd></dl></div></div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200"><div class="p-5"><dl><dt class="text-sm font-medium text-gray-500 truncate flex items-center"><i class="fa-solid fa-layer-group mr-2 text-gray-500"></i>Tổng số bài</dt><dd class="mt-1 text-3xl font-semibold text-gray-900 tracking-tight"><?= $stats['total'] ?></dd></dl></div></div>
            </div>
        </header>

        <main>
            <div class="mb-6">
                <form action="" id="search-form" method="GET" class="flex items-center gap-4">
                    <div class="relative flex-grow">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        </div>
                        <input type="text" id="search-input" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm theo tiêu đề, địa chỉ..." class="block w-full rounded-lg outline-none border border-gray-300 bg-white py-2.5 pl-10 text-sm text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 transition">
                    </div>
                    <button type="submit" id="search-button" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Tìm kiếm
                    </button>
                </form>
            </div>

            <script>
                // 1. Lấy các phần tử HTML cần thiết qua ID
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const searchButton = document.getElementById('search-button');

        // 2. Hàm để thực hiện submit
        function submitSearch() {
            const searchValue = searchInput.value;

            const encodedSearchValue = encodeURIComponent(searchValue.trim());

            const newUrl = `trangchu.php?page=../moi_gioi/ql_baidang_mg&search=${encodedSearchValue}`;
            const trove = `trangchu.php?page=../moi_gioi/ql_baidang_mg`;
            if (searchValue) {
                window.location.href = newUrl;          
            } else {
                window.location.href = trove;
            }
        }

        // 3. Gán sự kiện nhấn cho nút bấm
        searchButton.addEventListener('click', function(event) {
            event.preventDefault(); 
            submitSearch();
        });

        // 4. Gán sự kiện bỏ focus cho ô tìm kiếm
        searchInput.addEventListener('blur', function() {
            submitSearch(); // thực hiện tìm kiếm khi rời khỏi ô input
        });
            </script>

            <?php if (empty($baidang)) : ?>
                <div class="text-center bg-white border border-gray-200 rounded-lg py-12 px-6">
                    <i class="fa-solid fa-folder-open fa-3x text-gray-300"></i>
                    <h3 class="mt-4 text-sm font-semibold text-gray-900">Không tìm thấy bài đăng nào</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <?php if (!empty($search)) : ?>
                            Hãy thử một từ khóa tìm kiếm khác.
                        <?php else : ?>
                            Bạn chưa có bài đăng nào. Hãy tạo một bài mới!
                        <?php endif; ?>
                    </p>
                </div>
            <?php else : ?>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <?php foreach ($baidang as $post) : ?>
                        <div class="group relative flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-shadow duration-300">
                            <div class="relative aspect-[4/3] overflow-hidden">
                                <img src="<?= htmlspecialchars($post['anh_bia']) ?>" alt="<?= htmlspecialchars($post['tieu_de']) ?>" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <?= getStatusBadge($post['trang_thai']) ?>
                                <span class="absolute top-3 right-3 text-xs font-semibold px-2.5 py-1 rounded-full text-white bg-black/50 backdrop-blur-sm">
                                    <?= $post['hinh_thuc'] === 'ban' ? 'Bán' : 'Cho Thuê' ?>
                                </span>
                            </div>

                            <div class="flex flex-1 flex-col p-4">
                                <h3 class="text-base font-bold text-gray-900 hover:text-indigo-600 transition-colors">
                                    <a href="#" title="<?= htmlspecialchars($post['tieu_de']) ?>">
                                        <?= htmlspecialchars(mb_strimwidth($post['tieu_de'], 0, 50, "...")) ?>
                                    </a>
                                </h3>
                                <p class="mt-1 text-lg font-semibold text-indigo-600">
                                    <?= number_format($post['gia'], 0, ',', '.') ?> VNĐ
                                </p>

                                <div class="mt-3 space-y-2 text-sm text-gray-500">
                                    <p class="flex items-center">
                                        <i class="fa-solid fa-ruler-combined w-4 mr-2 text-gray-400"></i>
                                        <span>Diện tích: <strong><?= htmlspecialchars($post['dien_tich']) ?> m²</strong></span>
                                    </p>
                                    <p class="flex items-start">
                                        <i class="fa-solid fa-location-dot w-4 mr-2 mt-0.5 text-gray-400"></i>
                                        <span class="flex-1" title="<?= htmlspecialchars($post['dia_chi']) ?>"><?= htmlspecialchars(mb_strimwidth($post['dia_chi'], 0, 45, "...")) ?></span>
                                    </p>
                                </div>
                            </div>

                            <div class="mt-auto border-t border-gray-200 bg-gray-50 px-4 py-3 flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center gap-4">
                                    <span title="Ngày đăng"><i class="fa-regular fa-calendar mr-1.5"></i><?= date('d/m/Y', strtotime($post['ngay_dang'])) ?></span>
                                    <span title="Lượt xem"><i class="fa-regular fa-eye mr-1.5"></i><?= number_format($post['luot_xem']) ?></span>
                                </div>
                                
                                <details class="relative">
                                    <summary class="list-none cursor-pointer rounded-full p-2 text-gray-500 hover:bg-gray-200 hover:text-gray-800 transition">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </summary>
                                    <div class="absolute right-0 bottom-full mb-2 w-48 origin-bottom-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                                        <div class="py-1" role="menu">
                                            <a href="trangchu.php?page=../moi_gioi/chitiet_baidang_mg&id=<?= htmlspecialchars($post['id']) ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                <i class="fa-solid fa-circle-info w-5 mr-2"></i>Xem chi tiết
                                            </a>
                                            <?php if ($post['trang_thai'] !== 'chuaduyet') : ?>
                                                <a href="" class="btn-action flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="edit" role="menuitem">
                                                    <i class="fa-solid fa-pen-to-square w-5 mr-2"></i>Chỉnh sửa
                                                </a>
                                                <?php if ($post['trang_thai'] !== 'an') : ?>
                                                    <a href="#" class="btn-action flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="unpublish" role="menuitem">
                                                        <i class="fa-solid fa-eye-slash w-5 mr-2"></i>Gỡ/Ẩn bài
                                                    </a>
                                                <?php else: ?>
                                                    <a href="#" class="btn-action flex items-center px-4 py-2 text-sm text-green-600 hover:bg-green-50" data-id="<?= htmlspecialchars($post['id']) ?>" data-action="hienthi" role="menuitem">
                                                        <i class="fa-solid fa-eye-slash w-5 mr-2"></i>Hiển thị
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', function(event) {
            document.querySelectorAll('details').forEach(detail => {
                if (detail.open && !detail.contains(event.target)) {
                    detail.removeAttribute('open');
                }
            });
        });

        // Xử lý hành động
        document.body.addEventListener('click', function(event) {
            const actionButton = event.target.closest('.btn-action');
            if (actionButton) {
                event.preventDefault();
                const postId = actionButton.dataset.id;
                const action = actionButton.dataset.action;

                if (action === 'edit') {
                    // Chuyển hướng đến trang chỉnh sửa
                    window.location.href = `trangchu.php?page=../moi_gioi/chinhsua_baidang&id=${postId}`;
                } 
                if (action === 'unpublish') {
                    if (confirm('Bạn có chắc chắn muốn GỠ bài đăng này không? Bài đăng sẽ được chuyển vào mục lưu trữ.')) {
                        
                        // ===== PHẦN MỚI THAY THẾ CHO ALERT =====
                        fetch('../../models/cn_tt_baidang_mg.php', { // <-- **QUAN TRỌNG**: Thay đổi đường dẫn này cho đúng!
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                post_id: postId,
                                action: 'an'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message); // Thông báo thành công
                                location.reload();   // Tải lại trang để cập nhật giao diện
                            } else {
                                // Hiển thị lỗi từ server
                                alert('Lỗi: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.');
                        });
                        // =======================================

                    }
                } 
                if (action === 'hienthi') {
                     
                    if (confirm('Bạn có muốn hiển thị lại bài đăng này không?')) {
                        
                        // ===== PHẦN MỚI THAY THẾ CHO ALERT =====
                        fetch('../../models/cn_tt_baidang_mg.php', { // <-- **QUAN TRỌNG**: Thay đổi đường dẫn này cho đúng!
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                post_id: postId,
                                action: 'hienthi'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message); // Thông báo thành công
                                location.reload();   // Tải lại trang để cập nhật giao diện
                            } else {
                                // Hiển thị lỗi từ server
                                alert('Lỗi: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.');
                        });
                        // =======================================

                    
                }
                }
            }
        });
    });
</script>
</body>
</html>