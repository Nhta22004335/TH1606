<?php
// Bắt buộc phải khởi động session để sử dụng $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Thiết lập header để trả về JSON
header('Content-Type: application/json');

// 1. KẾT NỐI CSDL & XÁC THỰC
require_once "../../config/database.php";

// Chuẩn bị mảng phản hồi
$response = ['success' => false, 'message' => 'Đã có lỗi xảy ra.'];

// 2. KIỂM TRA PHƯƠNG THỨC VÀ ĐĂNG NHẬP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    $response['message'] = 'Phương thức không hợp lệ.';
    echo json_encode($response);
    exit();
}

$current_user_id = $_SESSION['id_nguoi_dung'] ?? null;
if (!$current_user_id) {
    http_response_code(403); // Forbidden
    $response['message'] = 'Vui lòng đăng nhập để thực hiện hành động này.';
    echo json_encode($response);
    exit();
}

// 3. LẤY DỮ LIỆU TỪ REQUEST
// Dữ liệu được gửi từ fetch() dưới dạng JSON
$data = json_decode(file_get_contents('php://input'), true);

$post_id = $data['post_id'] ?? null;
$action = $data['action'] ?? null;


// 4. THỰC HIỆN CẬP NHẬT
try {
    $pdo = ketnoicsdl();

    // Câu lệnh SQL có điều kiện `id_nguoi_dung` để đảm bảo bảo mật
    // Người dùng chỉ có thể ẩn bài đăng của chính họ
    $sql = "UPDATE bai_dang 
            SET trang_thai = :hd 
            WHERE id = :post_id AND id_nguoi_dung = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':post_id' => $post_id,
        ':user_id' => $current_user_id,
        ':hd' => $action
    ]);

    // Kiểm tra xem có hàng nào được cập nhật không
    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Gỡ bài đăng thành công!';
    } else {
        // Lỗi này xảy ra khi post_id không tồn tại HOẶC không thuộc về người dùng
        http_response_code(404); // Not Found or Forbidden
        $response['message'] = 'Không tìm thấy bài đăng hoặc bạn không có quyền thực hiện hành động này.';
    }

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    // Không nên hiển thị $e->getMessage() cho người dùng cuối trong môi trường production
    $response['message'] = 'Lỗi cơ sở dữ liệu.';
}

// 5. TRẢ VỀ PHẢN HỒI JSON
echo json_encode($response);