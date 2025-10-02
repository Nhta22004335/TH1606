<?php
session_start();
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Nếu chưa login, redirect về login
if(!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.html");
    exit;
}

// Xử lý form POST
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten    = trim($_POST['ho_ten']);
    $gioi_tinh = $_POST['gioi_tinh'];
    $dia_chi   = trim($_POST['dia_chi']);
    $ngay_sinh = $_POST['ngay_sinh'];
    $mo_ta     = trim($_POST['mo_ta']);
    $so_dt     = trim($_POST['so_dt']);

    try {
        $pdo->beginTransaction();

        // Update bảng info_nguoi_dung
        $stmt = $pdo->prepare("
            UPDATE info_nguoi_dung
            SET ho_ten=:ho_ten, gioi_tinh=:gioi_tinh, dia_chi=:dia_chi, ngay_sinh=:ngay_sinh, mo_ta=:mo_ta
            WHERE id_nguoi_dung=:id
        ");
        $stmt->execute([
            'ho_ten'=>$ho_ten,
            'gioi_tinh'=>$gioi_tinh,
            'dia_chi'=>$dia_chi,
            'ngay_sinh'=>$ngay_sinh,
            'mo_ta'=>$mo_ta,
            'id'=>$_SESSION['id_nguoi_dung']
        ]);

        // Update số điện thoại bảng nguoi_dung
        $stmt = $pdo->prepare("UPDATE nguoi_dung SET so_dt=:so_dt WHERE id=:id");
        $stmt->execute(['so_dt'=>$so_dt, 'id'=>$_SESSION['id_nguoi_dung']]);

        $pdo->commit();
        $_SESSION['flash_success'] = "Cập nhật thông tin thành công!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = "Cập nhật thất bại: " . $e->getMessage();
    }

    // Redirect để tránh submit lại form khi F5
    header("Location: ql_hoso_canhan.php");
    exit;
}

// Nếu truy cập trực tiếp file này bằng GET -> redirect về form
header("Location: ql_hoso_canhan.php");
exit;
