<?php
// Tên file: ../../models/cn_danhgia.php

// VÔ HIỆU HÓA HIỂN THỊ LỖI ĐỂ NGĂN OUTPUT HTML VÀ TRÁNH LỖI SyntaxError
ini_set('display_errors', 0); 
error_reporting(0); 

header('Content-Type: application/json');
session_start();

require_once "../../config/database.php";

$id_danh_gia = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null; // 'delete', 'hide', 'show'

// Hàm phản hồi JSON (đã tối ưu hóa)
function sendJsonResponse($status, $message, $httpCode = 200) {
    if (!headers_sent()) {
        http_response_code($httpCode);
    }
    echo json_encode(["status" => $status, "message" => $message]);
    exit;
}

// --- KIỂM TRA ĐẦU VÀO NGHIÊM NGẶT ---
// Sửa lỗi: Đảm bảo biến không phải là null hoặc rỗng sau khi trim()
if (empty(trim($id_danh_gia)) || empty(trim($action))) {
    sendJsonResponse("error", "Thiếu ID đánh giá hoặc Hành động để xử lý.", 400);
}

// Chỉ sử dụng giá trị đã trim()
$id_danh_gia = trim($id_danh_gia);
$action = trim($action);

// --- XỬ LÝ CSDL ---
try {
    $pdo = ketnoicsdl();
    $message = "";

    switch ($action) {
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM danh_gia_bds WHERE id = :id");
            $stmt->execute([':id' => $id_danh_gia]);
            $message = "Đã xóa đánh giá thành công.";
            break;

        case 'hide':
            $stmt = $pdo->prepare("UPDATE danh_gia_bds SET trang_thai = 'an' WHERE id = :id AND trang_thai = 'hien'");
            $stmt->execute([':id' => $id_danh_gia]);
            $message = "Đã ẩn đánh giá thành công.";
            break;

        case 'show':
            $stmt = $pdo->prepare("UPDATE danh_gia_bds SET trang_thai = 'hien' WHERE id = :id AND trang_thai = 'an'");
            $stmt->execute([':id' => $id_danh_gia]);
            $message = "Đã hiện đánh giá thành công.";
            break;

        default:
            sendJsonResponse("error", "Hành động không hợp lệ.", 400);
    }
    
    // Kiểm tra xem có bản ghi nào bị ảnh hưởng không
    if ($stmt->rowCount() > 0) {
        sendJsonResponse("success", $message);
    } else {
        // Thông báo nếu ID hợp lệ nhưng trạng thái đã đúng hoặc không có ID đó
        sendJsonResponse("warning", "Không có thay đổi nào được thực hiện.", 200);
    }

} catch (PDOException $e) {
    // Bắt lỗi CSDL
    error_log("Lỗi CSDL trong cn_danhgia.php: " . $e->getMessage());
    sendJsonResponse("error", "Lỗi CSDL khi xử lý yêu cầu.", 500);
}
?>