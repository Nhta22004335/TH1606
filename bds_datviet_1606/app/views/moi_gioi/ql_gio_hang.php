<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

if(session_status() === PHP_SESSION_NONE) session_start();

// Lấy ID khách từ session
$id_khach = $_SESSION["id_nguoi_dung"] ?? null;
if (!$id_khach) {
    echo "<p>❌ Bạn chưa đăng nhập!</p>";
    exit;
}

// Lấy danh sách giỏ hàng khách
$stmt = $pdo->prepare("
    SELECT g.id AS id_giohang, b.tieu_de, b.gia, g.so_luong
    FROM gio_hang_bds g
    JOIN bat_dong_san b ON b.id = g.id_bds
    WHERE g.id_khach_hang = :id_khach AND g.trang_thai = 'active'
    ORDER BY g.ngay_tao
");
$stmt->execute(['id_khach' => $id_khach]);
$gio_hang = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Giỏ hàng của tôi</title>
<style>
table {border-collapse: collapse; width: 100%;}
th, td {padding: 8px; border: 1px solid #ccc; text-align:center;}
th {background-color: #f0f0f0;}
input[type=number]{width:60px;}
.btn {padding:4px 8px; margin:2px; cursor:pointer;}
</style>
</head>
<body>
<h1>Giỏ hàng của tôi</h1>

<?php if(empty($gio_hang)): ?>
<p>Chưa có sản phẩm nào trong giỏ.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>Sản phẩm</th>
<th>Giá</th>
<th>Số lượng</th>
<th>Tổng tiền</th>
<th>Thao tác</th>
</tr>
</thead>
<tbody>
<?php foreach($gio_hang as $item): ?>
<tr>
<td><?= htmlspecialchars($item['tieu_de']) ?></td>
<td><?= number_format($item['gia'],0,',','.') ?> VND</td>
<td>
<form method="post" action="xuly_giohang.php" style="display:inline;">
<input type="hidden" name="id_giohang" value="<?= $item['id_giohang'] ?>">
<input type="number" name="so_luong" value="<?= $item['so_luong'] ?>" min="1">
<button class="btn" name="action" value="update">Cập nhật</button>
</form>
</td>
<td><?= number_format($item['so_luong'] * $item['gia'],0,',','.') ?> VND</td>
<td>
<form method="post" action="xuly_giohang.php" style="display:inline;">
<input type="hidden" name="id_giohang" value="<?= $item['id_giohang'] ?>">
<button class="btn" name="action" value="remove">Xóa</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<form method="post" action="xuly_giohang.php">
<button class="btn" name="action" value="checkout">Thanh toán tất cả</button>
</form>
<?php endif; ?>
</body>
</html>
