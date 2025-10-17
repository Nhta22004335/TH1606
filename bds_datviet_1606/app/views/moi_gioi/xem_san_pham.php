<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

if (session_status() === PHP_SESSION_NONE) session_start();

// --- Lấy ID sản phẩm ---
$id_bds = $_GET['id'] ?? null;

if (!$id_bds) {
    echo "<p class='text-center text-red-600 mt-10 font-semibold'>❌ Thiếu ID sản phẩm!</p>";
    exit;
}

// --- Truy vấn thông tin sản phẩm ---
$stmt = $pdo->prepare("
    SELECT 
        b.id, b.tieu_de, b.mo_ta, b.gia, b.dien_tich, b.khu_vuc, b.dia_chi, 
        b.loai, b.trang_thai, b.ngay_dang, b.id_nguoi_dung,
        COALESCE(ha.url, 'chuacapnhat.jpg') AS anh_dai_dien
    FROM bat_dong_san b
    LEFT JOIN LATERAL (
        SELECT url FROM hinh_anh_bds WHERE id_bds = b.id ORDER BY ngay_tao DESC LIMIT 1
    ) ha ON TRUE
    WHERE b.id = :id
");
$stmt->execute(['id' => $id_bds]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sp) {
    echo "<p class='text-center text-red-600 mt-10 font-semibold'>❌ Sản phẩm không tồn tại hoặc đã bị xóa.</p>";
    exit;
}

// --- Định dạng giá ---
function format_price_vn($price) {
    if ($price >= 1000000000) return rtrim(rtrim(number_format($price / 1000000000, 2, ',', ''), '0'), ',') . ' tỷ';
    elseif ($price >= 1000000) return number_format($price / 1000000, 0, ',', '.') . ' triệu';
    elseif ($price > 0) return number_format($price, 0, ',', '.') . ' VNĐ';
    else return 'Thỏa thuận';
}

function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

// --- Lấy danh sách đánh giá ---
$stmt = $pdo->prepare("
    SELECT dg.diem, dg.binh_luan, dg.ngay_tao, i.ho_ten, ha.url AS hinh
    FROM danh_gia_bds dg
    LEFT JOIN info_nguoi_dung i ON i.id_nguoi_dung = dg.id_nguoi_dung
    LEFT JOIN hinh_anh_danh_gia_bds ha ON ha.id_dg_bds = dg.id
    WHERE dg.id_bds = :id_bds AND dg.trang_thai='hien'
    ORDER BY dg.ngay_tao DESC
");
$stmt->execute([':id_bds'=>$id_bds]);
$ds_danh_gia = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chi tiết sản phẩm - <?= e($sp['tieu_de']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-50">

<div class="max-w-6xl mx-auto mt-8 bg-white rounded-xl shadow-md overflow-hidden">
    <div class="flex flex-col md:flex-row">
        <!-- Ảnh đại diện -->
        <div class="md:w-1/2">
            <img src="../../../storage/pictures/bds/<?= e($sp['anh_dai_dien']) ?>?t=<?= time() ?>" 
                 alt="Ảnh bất động sản" 
                 class="w-full h-96 object-cover">
        </div>

        <!-- Thông tin sản phẩm -->
        <div class="md:w-1/2 p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4"><?= e($sp['tieu_de']) ?></h1>

            <div class="space-y-2 text-gray-700 text-sm">
                <p><i class="fas fa-tag text-green-500"></i> <strong>Loại:</strong> <?= e($sp['loai']) ?></p>
                <p><i class="fas fa-map-marker-alt text-red-500"></i> <strong>Khu vực:</strong> <?= e($sp['khu_vuc']) ?></p>
                <p><i class="fas fa-home text-blue-500"></i> <strong>Địa chỉ:</strong> <?= e($sp['dia_chi']) ?></p>
                <p><i class="fas fa-ruler-combined text-indigo-500"></i> <strong>Diện tích:</strong> <?= e($sp['dien_tich']) ?> m²</p>
                <p><i class="fas fa-dollar-sign text-yellow-500"></i> <strong>Giá:</strong> <?= format_price_vn((float)$sp['gia']) ?></p>
                <p><i class="fas fa-info-circle text-gray-500"></i> <strong>Trạng thái:</strong> <?= e($sp['trang_thai']) ?></p>
                <p><i class="fas fa-calendar-alt text-purple-500"></i> <strong>Ngày đăng:</strong> <?= date("d/m/Y", strtotime($sp['ngay_dang'])) ?></p>
            </div>
        </div>
    </div>

    <!-- Mô tả -->
    <div class="p-6 border-t">
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Mô tả chi tiết</h2>
        <p class="text-gray-700 whitespace-pre-line leading-relaxed"><?= nl2br(e($sp['mo_ta'])) ?></p>
    </div>

    <!-- Nút hành động -->
    <div class="flex justify-center gap-4 p-6 border-t">
        <a href="trangchu.php?page=../moi_gioi/sp_canhan" 
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg font-medium">
            ⬅ Quay lại danh sách
        </a>
        <a href="trangchu.php?page=../moi_gioi/sua_san_pham&id=<?= e($sp['id']) ?>" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">
            ✏️ Sửa sản phẩm
        </a>
    </div>
</div>

<!-- Form đánh giá -->
<div class="max-w-6xl mx-auto mt-6 bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-xl font-semibold mb-4">Đánh giá BĐS này</h3>
    <form action="trangchu.php?page=../../models/xu_ly_danh_gia" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id_bds" value="<?= $id_bds ?>">

        <div>
            <label for="diem" class="block font-medium">Điểm (1-5):</label>
            <select name="diem" id="diem" required class="border rounded px-2 py-1">
                <?php for($i=1;$i<=5;$i++): ?>
                    <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label for="binh_luan" class="block font-medium">Bình luận:</label>
            <textarea name="binh_luan" id="binh_luan" rows="4" placeholder="Viết cảm nhận của bạn..." required
                      class="border rounded w-full px-2 py-1"></textarea>
        </div>

        <div>
            <label for="hinh_anh" class="block font-medium">Ảnh (nếu có):</label>
            <input type="file" name="hinh_anh[]" id="hinh_anh" multiple class="border rounded px-2 py-1 w-full">
        </div>

        <button  type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium">
            Gửi đánh giá
        </button>
    </form>
</div>

<!-- Hiển thị danh sách đánh giá -->
<div class="max-w-6xl mx-auto mt-6 bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-xl font-semibold mb-4">Danh sách đánh giá</h3>
    <?php if($ds_danh_gia): ?>
        <?php foreach($ds_danh_gia as $dg): ?>
            <div class="border-b py-4">
                <p class="font-medium"><?= e($dg['ho_ten'] ?? 'Khách') ?> đánh giá <?= e($dg['diem']) ?> ⭐</p>
                <p class="text-gray-700"><?= nl2br(e($dg['binh_luan'])) ?></p>
                <?php if($dg['hinh']): ?>
                    <img src="<?= e($dg['hinh']) ?>" alt="Ảnh đánh giá" class="w-32 mt-2">
                <?php endif; ?>
                <p class="text-xs text-gray-500 mt-1"><?= date("d/m/Y H:i", strtotime($dg['ngay_tao'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Chưa có đánh giá nào.</p>
    <?php endif; ?>
</div>

</body>
</html>
