<?php

// ====================================================================
// PHẦN 1: CẤU HÌNH KẾT NỐI CSDL (Sử dụng PDO cho PostgreSQL)
// ====================================================================

// THAY THẾ CÁC THÔNG SỐ KẾT NỐI CỦA BẠN TẠI ĐÂY!
    
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $id = $_SESSION['id_nguoi_dung'];


// Truy vấn JOIN để lấy thông tin Tin tức, Người dùng và Thông tin chi tiết Người dùng.
// Giả định thêm cột 'luot_xem' (lượt xem) vào bảng tin_tuc cho mục đích sắp xếp/hiển thị.
$sql = "
    SELECT 
        t.id, t.tieu_de, t.mo_ta, t.chuyen_muc, t.anh_tin, t.ngay_dang,
        -- Giả định cột luot_xem, mặc định là 0 nếu không tồn tại hoặc NULL
        COALESCE(t.luot_xem, 0) AS luot_xem, 
        
        -- Thông tin Môi giới (Người đăng)
        u.so_dt AS sdt, u.avt, 
        
        -- Thông tin cá nhân chi tiết của người đăng
        COALESCE(i.ho_ten, u.ten_dang_nhap) AS ten_moi_gioi
        
    FROM 
        tin_tuc t
    JOIN 
        nguoi_dung u ON t.id_khach_hang = u.id
    LEFT JOIN 
        info_nguoi_dung i ON u.id = i.id_nguoi_dung
    WHERE
        t.trang_thai IN ('dangban', 'daban', 'dathue') -- Chỉ lấy tin đã được duyệt
    ORDER BY 
        t.ngay_dang DESC, luot_xem DESC
";

$stmt = $pdo->query($sql);
$data = $stmt->fetchAll();

// ====================================================================
// PHẦN 3: XỬ LÝ VÀ PHÂN LOẠI DỮ LIỆU CHO GIAO DIỆN
// ====================================================================

$noibat = []; 
$tintuc = [];

if (count($data) > 0) {
    // A. Tin nổi bật (Tin mới nhất/lượt xem cao nhất)
    $first_tin = array_shift($data); // Lấy tin đầu tiên ra khỏi mảng $data

    $noibat = [
        'img'       => htmlspecialchars($first_tin['anh_tin']),
        'chuyenmuc' => htmlspecialchars($first_tin['chuyen_muc']),
        'tieude'    => htmlspecialchars($first_tin['tieu_de']),
        // Giới hạn mô tả để hiển thị gọn gàng
        'mota'      => htmlspecialchars(substr($first_tin['mo_ta'], 0, 300)) . (strlen($first_tin['mo_ta']) > 300 ? '...' : ''), 
        'moigioi'   => htmlspecialchars($first_tin['ten_moi_gioi']),
        'avt'       => htmlspecialchars($first_tin['avt']),
        'sdt'       => htmlspecialchars($first_tin['sdt']),
        'ngay'      => date('d/m/Y', strtotime($first_tin['ngay_dang'])), // Định dạng ngày tháng
        'view'      => $first_tin['luot_xem'],
    ];

    // B. Danh sách tin tức còn lại cho slider
    foreach ($data as $tin) {
        $tintuc[] = [
            'img'       => htmlspecialchars($tin['anh_tin']),
            'tieude'    => htmlspecialchars($tin['tieu_de']),
            // Giới hạn mô tả cho slider
            'mota'      => htmlspecialchars(substr($tin['mo_ta'], 0, 100)) . (strlen($tin['mo_ta']) > 100 ? '...' : ''),
            'moigioi'   => htmlspecialchars($tin['ten_moi_gioi']),
            'avt'       => htmlspecialchars($tin['avt']),
            'sdt'       => htmlspecialchars($tin['sdt']),
            'ngay'      => date('d/m/Y', strtotime($tin['ngay_dang'])),
            'view'      => $tin['luot_xem'],
        ];
    }
} else {
    // Dữ liệu mặc định nếu không có tin nào
    $noibat = [
        'img' => 'chuacapnhat.png', 'chuyenmuc' => 'Chưa cập nhật', 'tieude' => 'Hiện chưa có tin tức bất động sản nào.', 
        'mota' => 'Vui lòng kiểm tra lại trạng thái tin tức hoặc thêm dữ liệu mới vào hệ thống.', 'moigioi' => 'Hệ thống', 
        'avt' => 'avt.png', 'sdt' => '000000000', 'ngay' => date('d/m/Y'), 'view' => 0
    ];
}

// ====================================================================
// PHẦN 4: ĐÓNG PHP VÀ TIẾP TỤC VỚI HTML
// ====================================================================

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tin tức Bất động sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="max-w-7xl mx-auto px-4">
    
    <div class="flex justify-between items-center mb-6 mt-4 px-4 py-2 rounded-lg ">
        <h1 class="flex items-center text-2xl font-bold text-gray-600">
            <img src="../../../storage/pictures/avt/avt.png" 
                 class="w-12 h-12 mr-3 ml-4 rounded-full border border-gray-300">
            Quản lý tin tức
        </h1>

        <button onclick="document.getElementById('filterPanel').classList.remove('hidden')" 
            class="md:hidden bg-blue-600 text-white px-3 py-2 rounded-lg shadow hover:bg-blue-700 transition">
            <i class="fas fa-filter"></i>
        </button>
    </div>


    <div class="flex gap-6">

    <aside id="filterPanel" 
        class="hidden md:block fixed md:static top-0 right-0 w-72 h-full md:h-fit bg-white shadow rounded-xl p-4 z-50 transition-transform">
        <div class="flex items-center justify-between md:block mb-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <span>🔍</span> Bộ lọc
            </h2>
            <button onclick="document.getElementById('filterPanel').classList.add('hidden')" 
                class="md:hidden text-gray-500 text-2xl">&times;</button>
        </div>

        <label class="block text-sm mb-2">Loại tin</label>
        <select class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring-0 focus:border-blue-500">
            <option>Tất cả</option>
            <option>Đất nền</option>
            <option>Căn hộ</option>
            <option>Biệt thự</option>
            <option>Nhà phố</option>
        </select>

        <label class="block text-sm mb-2">Môi giới</label>
        <input type="text" placeholder="Tên môi giới"
            class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring-0 focus:border-blue-500">

        <label class="block text-sm mb-2">Ngày đăng</label>
        <input type="date"
            class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring-0 focus:border-blue-500">

        <label class="block text-sm mb-2">Sắp xếp theo</label>
        <select class="w-full border rounded-lg p-2 outline-none focus:ring-0 focus:border-blue-500">
            <option>Mới nhất</option>
            <option>Lượt xem cao</option>
        </select>

        <button class="mt-4 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Áp dụng</button>
    </aside>

    <main class="flex-1 min-w-0">
        <div class="bg-white rounded-xl shadow overflow-hidden mb-6">
            <img src="../../../storage/pictures/chuacapnhat.jpg" class="w-full h-80 object-cover">
            <div class="p-6">
            <span class="text-sm text-blue-600 uppercase font-medium"><?= $noibat['chuyenmuc'] ?></span>
            <h2 class="text-2xl font-bold mt-2 mb-2"><?= $noibat['tieude'] ?></h2>
            <p class="text-gray-600 mb-3"><?= $noibat['mota'] ?></p>

            <div class="flex items-center gap-3 mb-3">
                <img src="../../../storage/pictures/avt/<?= $noibat['avt'] ?>" class="w-10 h-10 rounded-full border">
                <div>
                <p class="font-semibold"><?= $noibat['moigioi'] ?></p>
                <p class="text-xs text-gray-500"><?= $noibat['sdt'] ?></p>
                </div>
            </div>

            <div class="flex gap-2 mt-3 mb-3"> 
                <a href="#" class="flex-1 text-center px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Chi tiết</a> 
                <button class="flex-1 px-2 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600">Đánh dấu</button> 
                <button class="flex-1 px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Xóa</button> 
            </div>

            <div class="flex justify-between text-sm text-gray-500">
                <span>📅 <?= $noibat['ngay'] ?></span>
                <span>👁 <?= number_format($noibat['view']) ?> lượt xem</span>
            </div>
            </div>
        </div>
    </main>
    </div>

    <div class="relative mt-10 mb-8">
        <h2 class="text-xl font-bold text-gray-700 mb-4">Tin tức khác</h2>

        <?php if (!empty($tintuc)): // Chỉ hiển thị slider nếu có tin tức ?>
            <button onclick="slideLeft()" 
                class="absolute left-0 -translate-x-full top-1/2 -translate-y-1/2
                        bg-blue-500/80 backdrop-blur-md shadow-lg
                        p-3 rounded-full text-white text-xl
                        hover:bg-blue-600/90 hover:scale-110
                        transition transform duration-300 ease-in-out z-10 hidden md:block">
                <i class="fas fa-chevron-left"></i>
            </button>

            <button onclick="slideRight()" 
                class="absolute right-0 translate-x-full top-1/2 -translate-y-1/2
                        bg-blue-500/80 backdrop-blur-md shadow-lg
                        p-3 rounded-full text-white text-xl
                        hover:bg-blue-600/90 hover:scale-110
                        transition transform duration-300 ease-in-out z-10 hidden md:block">
                <i class="fas fa-chevron-right"></i>
            </button>
        <?php endif; ?>


        <div id="newsSlider" class="flex flex-nowrap gap-6 overflow-x-auto scroll-smooth no-scrollbar py-2">
            <?php if (empty($tintuc)): ?>
                <p class="text-gray-500 text-center w-full">Không có tin tức nào khác để hiển thị.</p>
            <?php endif; ?>
            <?php foreach ($tintuc as $tin): ?>
                <div class="bg-white rounded-xl shadow hover:shadow-lg transition w-72 flex-shrink-0 border">
                    <img src="<?= $tin['img'] ?>" class="w-full h-40 object-cover rounded-t-xl">
                    <div class="p-4">
                        <h3 class="font-semibold mt-1 mb-2 hover:text-blue-600 cursor-pointer"><?= $tin['tieude'] ?></h3>
                        <p class="text-sm text-gray-600 line-clamp-2"><?= $tin['mota'] ?></p>
                        
                        <div class="flex items-center gap-2 mt-3">
                            <img src="<?= $tin['avt'] ?>" class="w-8 h-8 rounded-full border">
                            <div>
                                <p class="text-xs font-semibold"><?= $tin['moigioi'] ?></p>
                                <p class="text-[11px] text-gray-500"><?= $tin['sdt'] ?></p>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-3 mb-3"> 
                            <a href="#" class="flex-1 text-center px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Chi tiết</a> 
                            <button class="flex-1 px-2 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600">Đánh dấu</button> 
                            <button class="flex-1 px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Xóa</button> 
                        </div>

                        <div class="flex justify-between mt-3 text-xs text-gray-500">
                            <span>📅 <?= $tin['ngay'] ?></span>
                            <span>👁 <?= number_format($tin['view']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    const slider = document.getElementById('newsSlider');
    function slideLeft() { slider.scrollBy({ left: -300, behavior: 'smooth' }); }
    function slideRight() { slider.scrollBy({ left: 300, behavior: 'smooth' }); }
</script>

<style>
    /* Ẩn thanh cuộn mặc định */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

</body>
</html>