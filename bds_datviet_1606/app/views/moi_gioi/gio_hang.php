<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();



$id_nguoi_dung_hien_tai = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_nguoi_dung_hien_tai) {
    header("Location: ../auth/dangnhap.php");
    exit;
}


// Lấy danh sách giỏ hàng tất cả khách
$stmt = $pdo->query("
    SELECT g.id AS id_giohang,
           k.id AS id_khach,
           i.ho_ten AS ten_khach,
           b.id AS id_bds,
           b.tieu_de,
           b.gia,
           g.so_luong
    FROM gio_hang_bds g
    JOIN nguoi_dung k ON k.id = g.id_khach_hang
    LEFT JOIN info_nguoi_dung i ON i.id_nguoi_dung = k.id
    JOIN bat_dong_san b ON b.id = g.id_bds
    ORDER BY i.ho_ten, g.ngay_tao
");
$gio_hang = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý giỏ hàng khách</title>
    <style>
        table {border-collapse: collapse; width: 100%;}
        th, td {padding: 8px; border: 1px solid #ccc; text-align:center;}
        th {background-color: #f0f0f0;}
        input[type=number]{width:60px;}
        .btn {padding: 4px 8px; margin:2px; cursor:pointer;}
        .tong {font-weight:bold;}
    </style>
</head>
<body>
<h1>Quản lý giỏ hàng khách</h1>

<?php if(empty($gio_hang)): ?>
    <p>Chưa có giỏ hàng nào.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Khách</th>
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
                <td><?= htmlspecialchars($item['ten_khach'] ?? 'Chưa có tên') ?></td>
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
<?php endif; ?>

</body>
</html>
