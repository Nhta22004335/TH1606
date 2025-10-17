<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

if(session_status() === PHP_SESSION_NONE) session_start();
$id_khach = $_SESSION["id_nguoi_dung"] ?? null;
if(!$id_khach) exit("Bạn chưa đăng nhập!");

$id_bds = $_POST['id_bds'] ?? $_GET['id'] ?? null;

$so_luong = max(1,(int)($_POST['so_luong'] ?? 1));

if(!$id_bds) exit("BĐS không hợp lệ!");

// Kiểm tra BĐS đã tồn tại trong giỏ chưa
$stmt = $pdo->prepare("SELECT * FROM gio_hang_bds WHERE id_khach_hang=:id_khach AND id_bds=:id_bds AND trang_thai='active'");
$stmt->execute(['id_khach'=>$id_khach,'id_bds'=>$id_bds]);
$exists = $stmt->fetch(PDO::FETCH_ASSOC);

if($exists){
    // Nếu đã có, cập nhật số lượng
    $new_qty = $exists['so_luong'] + $so_luong;
    $stmt = $pdo->prepare("UPDATE gio_hang_bds SET so_luong=:so_luong WHERE id=:id");
    $stmt->execute(['so_luong'=>$new_qty, 'id'=>$exists['id']]);
} else {
    // Thêm mới
    $stmt = $pdo->prepare("INSERT INTO gio_hang_bds(id_khach_hang, id_bds, so_luong) VALUES(:id_khach, :id_bds, :so_luong)");
    $stmt->execute(['id_khach'=>$id_khach, 'id_bds'=>$id_bds, 'so_luong'=>$so_luong]);
}

echo "<script>window.location.href='../../views/quan_ly/trangchu.php?page=../moi_gioi/ql_gio_hang';</script>";
exit;

exit;


