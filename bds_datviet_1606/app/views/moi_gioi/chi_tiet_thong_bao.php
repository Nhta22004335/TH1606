<?php
require_once __DIR__ . "/../../../config/database.php";
$pdo = ketnoicsdl();

if (session_status() === PHP_SESSION_NONE) session_start();
$id_nguoi_dung = $_SESSION["id_nguoi_dung"] ?? null;

if (!$id_nguoi_dung) {
    die("<p>Bạn chưa đăng nhập. <a href='dangnhap.php'>Đăng nhập ngay</a></p>");
}

$id_tb = $_GET['id'] ?? null;
if (!$id_tb) {
    die("<p>Thiếu ID thông báo.</p>");
}

try {
    // Lấy thông tin chi tiết
    $sql = "SELECT * FROM thong_bao WHERE id = :id_tb AND id_nguoi_gui = :id_nguoi_dung";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_tb' => $id_tb, 'id_nguoi_dung' => $id_nguoi_dung]);
    $tb = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tb) {
        die("<p>Không tìm thấy thông báo hoặc bạn không có quyền xem thông báo này.</p>");
    }

    // Cập nhật trạng thái "đã xem"
    if ($tb['trang_thai'] === 'chuaxem') {
        $update = $pdo->prepare("UPDATE thong_bao SET trang_thai = 'daxem' WHERE id = :id_tb");
        $update->execute(['id_tb' => $id_tb]);
    }

} catch (PDOException $e) {
    die("Lỗi khi truy vấn CSDL: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết thông báo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-lg border">
        <a href="trangchu.php?page=../moi_gioi/ql_thongbao" class="text-indigo-600 hover:underline">&larr; Quay lại danh sách</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-4 mb-2"><?= htmlspecialchars($tb['tieu_de']) ?></h1>
        <p class="text-sm text-gray-500 mb-6">Gửi lúc <?= date("H:i, d/m/Y", strtotime($tb['thoi_gian_gui'])) ?></p>
        <div class="prose max-w-none text-gray-800 leading-relaxed">
            <?= nl2br(htmlspecialchars($tb['noi_dung'])) ?>
        </div>

        <div class="mt-8">
            <span class="inline-block bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-medium">
                Loại: <?= htmlspecialchars($tb['loai']) ?>
            </span>
            <span
