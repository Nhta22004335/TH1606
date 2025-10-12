<?php
// Đặt header để đảm bảo trình duyệt biết rằng phản hồi là JSON
header('Content-Type: application/json');

// Thêm cấu hình và kết nối CSDL (Đảm bảo đường dẫn chính xác)
require_once "../../config/database.php";

// Hàm phản hồi JSON và thoát script
function sendResponse($status, $message, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode(["status" => $status, "message" => $message]);
    exit;
}

// 1. Kiểm tra Phương thức HTTP
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse("error", "Phương thức không hợp lệ. Chỉ chấp nhận POST.", 405);
}

// 2. Kết nối CSDL
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    sendResponse("error", "Lỗi kết nối CSDL.", 500);
}

// 3. Lấy dữ liệu từ $_POST (Tương thích với hàm updateStatus gửi dữ liệu form)
$id = trim($_POST['id'] ?? '');
$trang_thai = trim($_POST['newStatus'] ?? ''); // Lấy tên tham số từ JS

// 4. Kiểm tra dữ liệu bắt buộc và tính hợp lệ của trạng thái
if (empty($id) || empty($trang_thai)) {
    sendResponse("error", "Thiếu ID hoặc Trạng thái.", 400);
}

// Danh sách trạng thái hợp lệ (dựa trên CONSTRAINT CHECK của bảng yeu_cau)
$valid_statuses = ['choxuly', 'daduyet', 'dahuy'];
if (!in_array($trang_thai, $valid_statuses)) {
    sendResponse("error", "Giá trị trạng thái không hợp lệ.", 400);
}


// 5. Thực hiện truy vấn CSDL
try {
    $sql = "UPDATE yeu_cau 
            SET trang_thai = :trang_thai, ngay_tao = CURRENT_TIMESTAMP 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ":trang_thai" => $trang_thai,
        ":id" => $id
    ]);

    if ($stmt->rowCount() > 0) {
        sendResponse("success", "Cập nhật trạng thái thành công!");
    } else {
        // Có thể ID không tồn tại hoặc trạng thái không thay đổi
        sendResponse("warning", "Không có yêu cầu nào được cập nhật.", 200);
    }
    
} catch (PDOException $e) {
    sendResponse("error", "Lỗi truy vấn CSDL: " . $e->getMessage(), 500);
}
?>