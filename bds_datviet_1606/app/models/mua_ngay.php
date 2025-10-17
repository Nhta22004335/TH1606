<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

if(session_status() === PHP_SESSION_NONE) session_start();
$id_khach = $_SESSION["id_nguoi_dung"] ?? null;
if(!$id_khach) exit("Bạn chưa đăng nhập!");

$id_bds = $_POST['id_bds'] ?? null;
$so_luong = max(1,(int)($_POST['so_luong'] ?? 1));

if(!$id_bds) exit("BĐS không hợp lệ!");

// Lấy thông tin BĐS
$stmt = $pdo->prepare("SELECT * FROM bat_dong_san WHERE id=:id");
$stmt->execute(['id'=>$id_bds]);
$bds = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$bds) exit("BĐS không tồn tại!");

// Thêm vào giỏ hàng nếu chưa có
$stmt = $pdo->prepare("SELECT * FROM gio_hang_bds WHERE id_khach_hang=:id_khach AND id_bds=:id_bds AND trang_thai='active'");
$stmt->execute(['id_khach'=>$id_khach,'id_bds'=>$id_bds]);
$exists = $stmt->fetch(PDO::FETCH_ASSOC);

if($exists){
    $new_qty = $exists['so_luong'] + $so_luong;
    $stmt = $pdo->prepare("UPDATE gio_hang_bds SET so_luong=:so_luong WHERE id=:id");
    $stmt->execute(['so_luong'=>$new_qty, 'id'=>$exists['id']]);
} else {
    $stmt = $pdo->prepare("INSERT INTO gio_hang_bds(id_khach_hang, id_bds, so_luong) VALUES(:id_khach, :id_bds, :so_luong) RETURNING id");
    $stmt->execute(['id_khach'=>$id_khach, 'id_bds'=>$id_bds, 'so_luong'=>$so_luong]);
    $giohang_id = $stmt->fetchColumn();
}

// Tạo giao dịch mua ngay
$id_gd = uniqid(); // tạm thời, sau nên dùng UUID
$stmt = $pdo->prepare("INSERT INTO giao_dich(id_nguoi_dung, id_nguoi_ban, id_bds, loai, trang_thai) 
                       VALUES(:id_khach, :id_ban, :id_bds, 'mua', 'choxuly') RETURNING id");
$stmt->execute([
    'id_khach' => $id_khach,
    'id_ban' => $bds['id_nguoi_dung'],
    'id_bds' => $id_bds
]);
$id_gd = $stmt->fetchColumn();

// Tạo kế hoạch thanh toán
$tong = $so_luong * $bds['gia'];
$stmt = $pdo->prepare("INSERT INTO ke_hoach_thanh_toan(id_giao_dich, tong_gia_tri) VALUES(:id_gd, :tong)");
$stmt->execute(['id_gd'=>$id_gd, 'tong'=>$tong]);

// Chuyển tới trang thanh toán
header("Location: checkout.php?id_gd=$id_gd");
exit;
