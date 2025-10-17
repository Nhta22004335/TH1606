<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

if(session_status() === PHP_SESSION_NONE) session_start();
$id_khach = $_SESSION["id_nguoi_dung"] ?? null;
if (!$id_khach) exit("Chưa đăng nhập!");

$action = $_POST['action'] ?? null;

if($action == 'update') {
    $id_giohang = $_POST['id_giohang'];
    $so_luong = max(1, (int)$_POST['so_luong']);
    $stmt = $pdo->prepare("UPDATE gio_hang_bds SET so_luong=:so_luong WHERE id=:id AND id_khach_hang=:id_khach");
    $stmt->execute(['so_luong'=>$so_luong, 'id'=>$id_giohang, 'id_khach'=>$id_khach]);
    header("Location: ql_gio_hang.php");
}

elseif($action == 'remove') {
    $id_giohang = $_POST['id_giohang'];
    $stmt = $pdo->prepare("UPDATE gio_hang_bds SET trang_thai='huy' WHERE id=:id AND id_khach_hang=:id_khach");
    $stmt->execute(['id'=>$id_giohang, 'id_khach'=>$id_khach]);
    header("Location: ql_gio_hang.php");
}

elseif($action == 'checkout') {
    // Lấy tất cả sản phẩm trong giỏ
    $stmt = $pdo->prepare("SELECT * FROM gio_hang_bds WHERE id_khach_hang=:id_khach AND trang_thai='active'");
    $stmt->execute(['id_khach'=>$id_khach]);
    $gio_hang = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($gio_hang as $item){
        // Tạo giao dịch mới
        $stmt = $pdo->prepare("INSERT INTO giao_dich(id_nguoi_dung, id_bds, loai, ngay_giao_dich) 
                               VALUES (:id_khach, :id_bds, 'mua', NOW()) RETURNING id");
        $stmt->execute(['id_khach'=>$id_khach,'id_bds'=>$item['id_bds']]);
        $id_gd = $stmt->fetchColumn();

        // Thanh toán 1 lần luôn
        $stmt2 = $pdo->prepare("INSERT INTO ke_hoach_thanh_toan(id_giao_dich, tong_gia_tri, so_tien_da_tt, trang_thai_tt) 
                                VALUES (:id_gd, :tong, :da_tt, 'hoantat')");
        $tong = $item['so_luong'] * $item['gia'];
        $stmt2->execute(['id_gd'=>$id_gd,'tong'=>$tong,'da_tt'=>$tong]);

        // Cập nhật giỏ hàng
        $stmt3 = $pdo->prepare("UPDATE gio_hang_bds SET trang_thai='huy' WHERE id=:id");
        $stmt3->execute(['id'=>$item['id']]);
    }

    header("Location: ql_gio_hang.php");
}

else {
    exit("Không hợp lệ!");
}
