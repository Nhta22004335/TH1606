<?php
session_start();
require 'db.php';

$id_khach_hang = $_SESSION['user_id'] ?? null;
$id_gd = $_POST['id_giao_dich'] ?? null;
$so_tien_tt = floatval($_POST['so_tien_tt'] ?? 0);
$phuong_thuc = $_POST['phuong_thuc'] ?? '';

if (!$id_khach_hang || !$id_gd || $so_tien_tt <= 0) {
    die("Dữ liệu không hợp lệ.");
}

try {
    $pdo->beginTransaction();

    // Lấy kế hoạch thanh toán
    $stmt = $pdo->prepare("SELECT tong_gia_tri, so_tien_da_tt FROM ke_hoach_thanh_toan WHERE id_giao_dich = ? FOR UPDATE");
    $stmt->execute([$id_gd]);
    $khtt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$khtt) throw new Exception("Giao dịch không tồn tại.");

    if ($so_tien_tt + $khtt['so_tien_da_tt'] > $khtt['tong_gia_tri']) {
        throw new Exception("Số tiền thanh toán vượt quá tổng giá trị.");
    }

    // Lấy lần thanh toán kế tiếp
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(lan_tt),0)+1 FROM dot_thanh_toan WHERE id_giao_dich=?");
    $stmt->execute([$id_gd]);
    $lan_tt = $stmt->fetchColumn();

    // Thêm đợt thanh toán
    $stmt = $pdo->prepare("
        INSERT INTO dot_thanh_toan (id_giao_dich, lan_tt, so_tien_tt, phuong_thuc)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$id_gd, $lan_tt, $so_tien_tt, $phuong_thuc]);

    // Cập nhật tổng số tiền đã thanh toán
    $new_tt = $khtt['so_tien_da_tt'] + $so_tien_tt;
    $trang_thai_tt = ($new_tt >= $khtt['tong_gia_tri']) ? 'hoantat' : 'dangthanhtoan';

    $stmt = $pdo->prepare("UPDATE ke_hoach_thanh_toan SET so_tien_da_tt=?, trang_thai_tt=? WHERE id_giao_dich=?");
    $stmt->execute([$new_tt, $trang_thai_tt, $id_gd]);

    $pdo->commit();
    echo "Thanh toán thành công! Đã thanh toán $so_tien_tt VNĐ.";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Thanh toán thất bại: " . $e->getMessage();
}
