<?php
require_once "../../config/database.php";
$pdo = ketnoicsdl();

// Lấy tin đã duyệt + ảnh đại diện đầu tiên của từng BĐS
$sql = "
    SELECT b.id, b.tieu_de, b.gia, b.dien_tich, b.khu_vuc, b.dia_chi,
           COALESCE(ha.url, 'no-image.png') AS anh_dai_dien
    FROM bat_dong_san b
    LEFT JOIN LATERAL (
        SELECT url 
        FROM hinh_anh_bds 
        WHERE id_bds = b.id 
        ORDER BY ngay_tao ASC 
        LIMIT 1
    ) ha ON TRUE
    WHERE b.trang_thai = 'daduyet'
    ORDER BY b.ngay_dang DESC
";
$stmt = $pdo->query($sql);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php foreach ($listings as $item): ?>
        <?php
        // 🔹 Xác định đường dẫn vật lý & đường dẫn hiển thị ảnh
        $file_name = htmlspecialchars($item['anh_dai_dien']);
        $file_path = "../../storage/pictures/bds/" . $file_name;
        $display_path = "../../storage/pictures/bds/" . $file_name;

        // 🔸 Nếu file không tồn tại, dùng ảnh mặc định
        if (empty($file_name) || !file_exists($file_path)) {
            $display_path = "../../public/assets/no-image.png";
        }
        ?>
        
        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition">
            <img src="<?= $display_path ?>" alt="Ảnh BĐS" class="w-full h-48 object-cover">
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-800 truncate">
                    <?= htmlspecialchars($item['tieu_de']) ?>
                </h3>
                <p class="text-slate-600 text-sm mt-1 line-clamp-1">
                    <?= htmlspecialchars($item['khu_vuc']) ?> – <?= htmlspecialchars($item['dia_chi']) ?>
                </p>
                <p class="text-sky-600 font-bold mt-2"><?= number_format($item['gia']) ?> VNĐ</p>
                <p class="text-sm text-gray-500">Diện tích: <?= htmlspecialchars($item['dien_tich']) ?> m²</p>
                <a href="chitiet_bds.php?id=<?= urlencode($item['id']) ?>" 
                   class="inline-block mt-3 text-sky-600 hover:text-sky-800 font-medium">
                   Xem chi tiết →
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
