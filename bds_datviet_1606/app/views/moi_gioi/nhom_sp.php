<?php
require_once "config/database.php"; // Kết nối PDO PostgreSQL

$loai = $_GET['loai'] ?? 'canho';

// Lấy danh sách BĐS theo loại
$stmt = $pdo->prepare("
    SELECT b.*, u.ten_dang_nhap, i.ho_ten
    FROM bat_dong_san b
    LEFT JOIN nguoi_dung u ON b.id_nguoi_dung = u.id
    LEFT JOIN info_nguoi_dung i ON i.id_nguoi_dung = u.id
    WHERE b.loai = :loai AND b.trang_thai = 'daduyet'
    ORDER BY b.ngay_dang DESC
");
$stmt->execute(['loai' => $loai]);
$batdongsan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Danh sách BĐS loại: <?= htmlspecialchars($loai) ?></h2>
<div class="grid grid-cols-3 gap-4">
<?php foreach($batdongsan as $bds): ?>
    <div class="border p-4 rounded shadow">
        <h3><?= htmlspecialchars($bds['tieu_de']) ?></h3>
        <p>Người đăng: <?= htmlspecialchars($bds['ho_ten'] ?? $bds['ten_dang_nhap']) ?></p>
        <p>Giá: <?= number_format($bds['gia'], 0, ',', '.') ?> VND</p>
        <p>Địa chỉ: <?= htmlspecialchars($bds['dia_chi']) ?></p>
        <a href="chitiet_bds.php?id=<?= $bds['id'] ?>">Xem chi tiết</a>
    </div>
<?php endforeach; ?>
</div>
