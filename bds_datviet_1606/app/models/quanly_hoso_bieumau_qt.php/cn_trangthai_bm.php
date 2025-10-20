<?php
// Đặt header để đảm bảo trình duyệt biết rằng phản hồi là JSON
header('Content-Type: application/json');

// --- KHAI BÁO CẤU HÌNH VÀ KẾT NỐI CSDL ---
require_once "../../config/database.php";

// Hàm phản hồi JSON và thoát script
function sendResponse($status, $message, $httpCode = 200) {
    if (!headers_sent()) {
        http_response_code($httpCode);
    }
    echo json_encode(["status" => $status, "message" => $message]);
    exit;
}

// 2. Kết nối CSDL
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    sendResponse("error", "Lỗi kết nối CSDL: " . $e->getMessage(), 500);
}

// 3. Xử lý yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // ĐỌC DỮ LIỆU TỪ $_POST (Đã được đồng bộ với JS)
    $id = trim($_POST['id'] ?? '');
    $trang_thai = trim($_POST['trang_thai'] ?? '');
    
    // Kiểm tra dữ liệu bắt buộc
    if (empty($id) || empty($trang_thai)) {
        // Sửa lỗi: Cung cấp thông báo lỗi rõ ràng hơn thay vì chỉ echo $trang_thai
        sendResponse("error", "Thiếu ID hoặc Trạng thái để cập nhật.", 400); 
    }

    try {
        // Chuẩn bị truy vấn
        $sql = "UPDATE bieu_mau 
                SET trang_thai = :trang_thai, ngay_cn = CURRENT_TIMESTAMP 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        // Thực thi với Prepared Statements
        $stmt->execute([
            ":trang_thai" => $trang_thai,
            ":id" => $id
        ]);

        if ($stmt->rowCount() > 0) {
            sendResponse("success", "Cập nhật trạng thái thành công!");
        } else {
            sendResponse("warning", "Không có biểu mẫu nào được cập nhật. Có thể ID không tồn tại hoặc trạng thái không thay đổi.", 200);
        }
        
    } catch (PDOException $e) {
        sendResponse("error", "Lỗi truy vấn CSDL: " . $e->getMessage(), 500);
    }
    
} else {
    // Nếu không phải phương thức POST
    sendResponse("error", "Phương thức không hợp lệ. Chỉ chấp nhận POST.", 405);
}
?>