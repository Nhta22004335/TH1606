<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

if (session_status() === PHP_SESSION_NONE) session_start();

// --- Kiểm tra đã đăng nhập khách hàng ---
if (!isset($_SESSION['id_khachhang'])) {
    echo "<p class='text-center text-red-600 mt-10 font-semibold'>❌ Bạn cần đăng nhập để đánh giá sản phẩm.</p>";
    exit;
}

$id_khachhang = $_SESSION['id_khachhang'];
$id_bds = $_POST['id_bds'] ?? null;
$diem = $_POST['diem'] ?? null;
$binh_luan = $_POST['binh_luan'] ?? null;

if (!$id_bds || !$diem) {
    echo "<p class='text-center text-red-600 mt-10 font-semibold'>❌ Thiếu thông tin đánh giá!</p>";
    exit;
}

try {
    // --- Chèn đánh giá vào CSDL ---
    $stmt = $pdo->prepare("
        INSERT INTO danh_gia_bds (id_bds, id_khachhang, diem, binh_luan, ngay_tao)
        VALUES (:id_bds, :id_khachhang, :diem, :binh_luan, NOW())
    ");
    $stmt->execute([
        'id_bds' => $id_bds,
        'id_khachhang' => $id_khachhang,
        'diem' => $diem,
        'binh_luan' => $binh_luan
    ]);

    // --- Chuyển hướng về trang chi tiết sản phẩm ---
    header("Location: xem_chi_tiet.php?id=" . $id_bds);
    exit;

} catch (Exception $e) {
    echo "<p class='text-center text-red-600 mt-10 font-semibold'>❌ Lỗi khi gửi đánh giá: " . $e->getMessage() . "</p>";
    exit;
}
