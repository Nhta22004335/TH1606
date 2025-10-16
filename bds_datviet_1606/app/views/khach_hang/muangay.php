<?php
session_start(); // Khởi tạo session

// muangay.php - Xử lý yêu cầu mua bất động sản
include_once __DIR__ . '/../../config/database.php'; // Sửa đường dẫn

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $pdo = ketnoicsdl(); // Hàm từ config/database.php
    $id_bds = isset($_POST['id_bds']) ? (int)$_POST['id_bds'] : 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mua'])) {
        $user_id = $_SESSION['user_id'];
        $ngay_mua = date('Y-m-d H:i:s');
        $trang_thai = 'Chờ xử lý';

        // Bắt đầu transaction
        $pdo->beginTransaction();

        try {
            // Kiểm tra xem bất động sản đã được mua chưa
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM don_hang WHERE id_bds = ? AND trang_thai NOT IN ('Đã hủy', 'Chờ xử lý')");
            $check_stmt->execute([$id_bds]);
            if ($check_stmt->fetchColumn() > 0) {
                throw new Exception("Bất động sản này đã được mua hoặc đang xử lý.");
            }

            // Thêm đơn hàng vào CSDL
            $insert_stmt = $pdo->prepare("INSERT INTO don_hang (id, id_nguoi_dung, id_bds, ngay_mua, trang_thai) VALUES (uuid_generate_v4(), ?, ?, ?, ?)");
            $insert_stmt->execute([$user_id, $id_bds, $ngay_mua, $trang_thai]);

            // Commit transaction
            $pdo->commit();

            // Thông báo và chuyển hướng
            echo "<script>alert('Yêu cầu mua đã được gửi thành công!'); window.location.href='duan.php';</script>";
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('Lỗi khi mua: " . addslashes($e->getMessage()) . "'); window.location.href='chitiet.php?id=$id_bds';</script>";
            exit();
        }
    }
} catch (PDOException $e) {
    error_log("Lỗi xử lý mua: " . $e->getMessage());
    echo "<script>alert('Lỗi hệ thống, vui lòng thử lại sau.'); window.location.href='chitiet.php?id=$id_bds';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xử lý Mua Ngay - Đất Việt BDS</title>
</head>
<body>
    <!-- Không cần nội dung HTML vì sẽ chuyển hướng ngay -->
</body>
</html>