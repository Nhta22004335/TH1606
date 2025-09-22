<?php
    // Dữ liệu demo sản phẩm với nhiều ảnh
    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    // Lấy id từ URL
    $id_bds = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $sql = "SELECT 
            bds.id,
            bds.tieu_de,
            bds.mo_ta,
            bds.gia,
            bds.dien_tich,
            bds.dia_chi,
            bds.loai,
            bds.khu_vuc,
            bds.trang_thai,
            bds.ngay_dang,
            bds.id_moi_gioi,
            mg.ho_ten
        FROM bat_dong_san bds
        LEFT JOIN moi_gioi mg ON bds.id_moi_gioi = mg.id_nguoi_dung
        WHERE bds.id = :id_bds";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_bds', $id_bds, PDO::PARAM_INT); // nếu id là số thì dùng PARAM_INT
    $stmt->execute();

    $sanpham = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!isset($sanpham)) {
        echo "<p class='text-red-500 font-bold p-4'>Sản phẩm không tồn tại!</p>";
        exit;
    }

    $sql = "SELECT COUNT(*) as dem FROM danh_gia_bds WHERE id_bds = :id_bds";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_bds', $id_bds, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $soluongdanhgiabds = $result['dem']; 

    $spha = [
        'hinh_anh' => [
            'https://picsum.photos/400/200?random=1',
            'https://picsum.photos/400/200?random=2',
            'https://picsum.photos/400/200?random=3',
        ]
    ];

    $sql = "
        SELECT 
            dg.id AS id_danh_gia,
            dg.id_bds,
            dg.id_khach_hang,
            dg.diem,
            dg.binh_luan,
            dg.ngay_dg,
            dg.trang_thai,
            kh.ho_ten,
            ARRAY_AGG(DISTINCT ha.url) FILTER (WHERE ha.url IS NOT NULL) AS ds_hinh_anh,
            ARRAY_AGG(DISTINCT vd.url) FILTER (WHERE vd.url IS NOT NULL) AS ds_video
        FROM danh_gia_bds dg
        LEFT JOIN hinh_anh_danh_gia_bds ha ON dg.id = ha.id_danh_gia_bds
        LEFT JOIN video_danh_gia_bds vd ON dg.id = vd.id_danh_gia_bds
        LEFT JOIN khach_hang kh ON kh.id_nguoi_dung = dg.id_khach_hang
        WHERE dg.id_bds = :id_bds
        GROUP BY dg.id, dg.id_bds, dg.id_khach_hang, dg.diem, dg.binh_luan, dg.ngay_dg, kh.ho_ten
        ORDER BY dg.ngay_dg DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_bds' => $id_bds]);
    $danhgia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đánh giá</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
    </style>
</head>
<body>

<header class="bg-white shadow p-4 flex items-center justify-between">
    <div class="flex items-center">
        <img src="../../public/assets/anhht/0/danhgia.gif" class="w-10 h-10 mr-3">
        <h1 class="flex items-center text-2xl font-bold text-gray-600">Chi tiết đánh giá</h1>
    </div>
    <a href="trangchu.php?page=danhgiasanpham" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition text-sm md:text-base">
        Quay lại
    </a>
</header>

<main class="p-4 md:p-6 max-w-4xl mx-auto">

    <!-- Card sản phẩm -->
    <div class="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row mb-6">
        <!-- Carousel ảnh -->
        <div class="relative w-full md:w-80 h-48 md:h-64 mb-4 md:mb-0 md:mr-4">
            <?php foreach($spha['hinh_anh'] as $index => $img): ?>
                <img src="<?= $img ?>" class="w-full h-full object-cover rounded absolute top-0 left-0 transition-opacity duration-300 <?= $index === 0 ? 'opacity-100' : 'opacity-0' ?>" data-index="<?= $index ?>">
            <?php endforeach; ?>
            <!-- Nút prev/next -->
            <button id="prevBtn" class="absolute left-1 top-1/2 -translate-y-1/2 bg-gray-700 text-white px-2 py-1 rounded opacity-70 hover:opacity-100">‹</button>
            <button id="nextBtn" class="absolute right-1 top-1/2 -translate-y-1/2 bg-gray-700 text-white px-2 py-1 rounded opacity-70 hover:opacity-100">›</button>
        </div>

        <div class="flex-1">
            <h2 class="text-lg md:text-xl font-semibold mb-1"><?= $sanpham['tieu_de'] ?></h2>
            <p class="text-gray-600 text-sm mb-1"><?= $sanpham['mo_ta'] ?></p>
            <div class="text-gray-700 text-sm mb-1"><span class="font-medium">Giá:</span> <?= $sanpham['gia'] ?></div>
            <div class="text-gray-700 text-sm mb-1"><span class="font-medium">Diện tích:</span> <?= $sanpham['dien_tich'] ?></div>
            <div class="text-gray-700 text-sm mb-1"><span class="font-medium">Địa chỉ:</span> <?= $sanpham['dia_chi'] ?></div>
            <div class="text-gray-700 text-sm mb-1"><span class="font-medium">Loại:</span> <?= $sanpham['loai'] ?></div>
            <div class="text-gray-700 text-sm mb-1"><span class="font-medium">Khu vực:</span> <?= $sanpham['khu_vuc'] ?></div>
            <div class="text-gray-700 text-sm"><span class="font-medium">Tổng số đánh giá:</span> <?= $soluongdanhgiabds ?></div>

            <!-- Môi giới: avatar + tên -->
            <div class="flex items-center mt-3">
                <img src="https://i.pravatar.cc/40?u=<?= urlencode($sanpham['id']) ?>" class="w-10 h-10 rounded-full object-cover mr-2">
                <span class="text-gray-800 font-medium"><?= $sanpham['ho_ten'] ?></span>
            </div>
        </div>
    </div>

    <!-- Danh sách đánh giá -->
<div class="space-y-4">
    <div class="flex items-center mb-4">
        <img src="../../public/assets/anhht/0/danhgia01.gif" alt="Đánh giá" class="w-10 h-10 mr-2">
        <h3 class="text-lg font-semibold">Đánh giá của khách hàng</h3>
    </div>

    <!-- Container đánh giá có thể cuộn dọc -->
    <div class="bg-white shadow rounded-lg p-4 max-h-[400px] overflow-y-auto space-y-4 scrollbar-hide">
        <?php foreach($danhgia as $dg): ?>
            <div class="flex space-x-4 items-start">
                <div class="flex-shrink-0">
                    <img src="https://i.pravatar.cc/50?u=<?= urlencode($dg['id_khach_hang']) ?>" class="w-12 h-12 rounded-full object-cover">
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-medium text-gray-800"><?= $dg['ho_ten'] ?></span>
                        <span class="text-yellow-500 text-sm">
                            <?= str_repeat('★', $dg['diem']) . str_repeat('☆', 5 - $dg['diem']) ?>
                        </span>
                    </div>
                    <p class="text-gray-700 text-sm mb-1"><?= $dg['binh_luan'] ?></p>
                    <div class="flex justify-between items-center">
                        <p class="text-gray-400 text-xs"><?= htmlspecialchars($dg['ngay_dg']) ?></p>
                        <span class="px-2 py-0.5 rounded text-xs 
                            <?= $dg['trang_thai'] === 'hiện' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= ucfirst($dg['trang_thai']) ?>
                        </span>
                    </div>
                </div>
                <!-- Nút hành động -->
                <div class="flex space-x-2 mt-2 md:mt-0">
                    <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">Xóa</button>
                    <?php if ($dg['trang_thai'] === 'hiện'): ?>
                        <a class="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm">Ẩn</a>
                    <?php else: ?>
                        <a class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm">Hiện</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</main>

<script>
    // JavaScript đơn giản cho carousel
    let currentIndex = 0;
    const images = document.querySelectorAll('[data-index]');
    const total = images.length;

    function showImage(index) {
        images.forEach(img => img.classList.add('opacity-0'));
        images[index].classList.remove('opacity-0');
    }

    document.getElementById('prevBtn').addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + total) % total;
        showImage(currentIndex);
    });

    document.getElementById('nextBtn').addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % total;
        showImage(currentIndex);
    });
</script>

</body>
</html>
