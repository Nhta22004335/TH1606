<?php
// Bắt đầu session để lưu trữ thông báo
session_start();

// Tệp này chứa thông tin kết nối đến cơ sở dữ liệu (host, dbname, user, password)
// Hãy chắc chắn bạn đã tạo tệp này và thay đổi cho phù hợp
require_once "../../config/database.php";
$pdo = ketnoicsdl();

// 1. Kiểm tra xem ID có được gửi qua URL không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Nếu không có ID, đặt thông báo lỗi và chuyển hướng
    $_SESSION['message'] = "Lỗi: ID lịch trình không được cung cấp.";
    $_SESSION['msg_type'] = "danger"; // Dùng để định dạng màu cho thông báo (Bootstrap)
    // header("Location: trang_danh_sach_lich_trinh.php"); // <-- Thay bằng trang danh sách của bạn
    // exit();
}

$id = $_GET['id'];

try {
    // 2. Chuẩn bị câu lệnh SQL để xóa
    // Sử dụng prepared statement để tránh SQL Injection
    $sql = "DELETE FROM lich_trinh WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    // 3. Gán giá trị cho tham số trong câu lệnh
    $stmt->bindParam(':id', $id, PDO::PARAM_STR);

    // 4. Thực thi câu lệnh
    if ($stmt->execute()) {
        // Kiểm tra xem có dòng nào thực sự bị xóa không
        if ($stmt->rowCount() > 0) {
            // Xóa thành công
            $_SESSION['message'] = "Đã xóa lịch trình thành công!";
            $_SESSION['msg_type'] = "success";
        } else {
            // Không tìm thấy lịch trình với ID tương ứng
            $_SESSION['message'] = "Không tìm thấy lịch trình để xóa. Có thể nó đã được xóa trước đó.";
            $_SESSION['msg_type'] = "warning";
        }
    } else {
        // Lỗi khi thực thi
        $_SESSION['message'] = "Lỗi: Không thể xóa lịch trình.";
        $_SESSION['msg_type'] = "danger";
    }
} catch (PDOException $e) {
    // Bắt lỗi nếu có vấn đề với CSDL
    $_SESSION['message'] = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
    $_SESSION['msg_type'] = "danger";
}

// 5. Chuyển hướng người dùng trở lại trang danh sách
header("Location: ../views/quan_ly/trangchu.php?page=../moi_gioi/lt_canhan");
exit();
?>