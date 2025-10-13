<?php
// Báo cáo lỗi (giữ lại để debug)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Thiết lập header để trình duyệt hiểu đây là phản hồi JSON
header('Content-Type: application/json');

require_once "../../config/database.php";

// Chuẩn bị mảng phản hồi
$response = ['success' => false, 'message' => 'Có lỗi xảy ra.'];

// 1. Kiểm tra tham số
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    $response['message'] = 'Lỗi: Thiếu tham số cần thiết.';
    echo json_encode($response);
    exit();
}

$image_id = $_GET['id'];
$new_status = $_GET['status'];
$allowed_statuses = ['binhthuong', 'nhe', 'trungbinh', 'nang'];

// 2. Xác thực trạng thái
if (!in_array($new_status, $allowed_statuses)) {
    $response['message'] = 'Lỗi: Trạng thái không hợp lệ.';
    echo json_encode($response);
    exit();
}

try {
    $pdo = ketnoicsdl();
    
    // 3. Chuẩn bị và thực thi câu lệnh UPDATE
    $sql = "UPDATE hinh_anh_bds SET trang_thai = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => $new_status, ':id' => $image_id]);
    
    // 4. Kiểm tra và tạo phản hồi thành công
    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Cập nhật trạng thái thành công!';
        $response['new_status'] = $new_status; // Gửi lại trạng thái mới để JS cập nhật UI
    } else {
        // Có thể ID không tồn tại hoặc trạng thái không thay đổi
        $response['message'] = 'Không có thay đổi nào được thực hiện.';
    }

} catch (PDOException $e) {
    $response['message'] = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
}

// 5. Trả về kết quả dưới dạng JSON
echo json_encode($response);
exit();
?>