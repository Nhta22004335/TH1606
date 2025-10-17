<?php
require_once "../../../config/database.php"; 
$pdo = ketnoicsdl();

// Lấy nhóm sản phẩm BĐS với 1 hình ảnh đại diện
$stmt = $pdo->query("
    SELECT b.loai, MIN(h.url) AS hinh_dai_dien, COUNT(*) AS so_luong
    FROM bat_dong_san b
    JOIN bai_dang bd ON bd.id_bat_dong_san = b.id
    LEFT JOIN hinh_anh_bds h ON h.id_bds = b.id
    WHERE bd.trang_thai = 'daduyet'
    GROUP BY b.loai
    ORDER BY so_luong DESC
");
$nhom_sp = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-2xl font-bold mb-6">Nhóm sản phẩm BĐS</h2>
<div class="grid grid-cols-4 gap-6">
<?php foreach($nhom_sp as $nsp): ?>
    <a href="trangchu.php?page=../moi_gioi/nhom_sp.php?loai=<?= urlencode($nsp['loai']) ?>" class="block border rounded-lg shadow hover:shadow-lg transition p-4">
        <div class="w-full h-40 bg-gray-200 rounded-lg overflow-hidden mb-3">
            <?php if($nsp['hinh_dai_dien']): ?>
                <img src="<?= htmlspecialchars($nsp['hinh_dai_dien']) ?>" alt="<?= htmlspecialchars($nsp['loai']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="flex items-center justify-center h-full text-gray-400">Chưa có ảnh</div>
            <?php endif; ?>
        </div>
        <div class="text-lg font-semibold text-center"><?= htmlspecialchars(ucfirst($nsp['loai'])) ?></div>
        <div class="text-gray-500 text-center"><?= $nsp['so_luong'] ?> BĐS</div>
    </a>
<?php endforeach; ?>
</div>
