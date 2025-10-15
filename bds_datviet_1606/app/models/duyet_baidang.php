<?php
// Thiết lập header để trả về JSON
header('Content-Type: application/json');

// Kết nối đến cơ sở dữ liệu
require_once "../../config/database.php";

// Kiểm tra phương thức yêu cầu phải là POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
    exit;
}

// Lấy dữ liệu từ body của request
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$action = $data['action'] ?? null;

// Kiểm tra dữ liệu đầu vào
if (empty($id) || empty($action)) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu ID hoặc hành động']);
    exit;
}

// Xác định trạng thái mới dựa trên hành động
$new_status = '';
if ($action === 'approve') {
    $new_status = 'daduyet';
} elseif ($action === 'reject' || $action === 'gobai') {
    $new_status = 'an'; // Khi từ chối, chuyển sang trạng thái "ẩn"
} else {
    echo json_encode(['status' => 'error', 'message' => 'Hành động không được hỗ trợ']);
    exit;
}

try {
    $pdo = ketnoicsdl();

    // Chuẩn bị câu lệnh SQL cập nhật
    $sql = "UPDATE bai_dang SET trang_thai = :trang_thai WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    // Thực thi câu lệnh
    $stmt->execute([
        ':trang_thai' => $new_status,
        ':id' => $id
    ]);

    // Kiểm tra xem có hàng nào được cập nhật không
    if ($stmt->rowCount() > 0) {
        $message = ($action === 'approve') ? 'Duyệt bài đăng thành công!' : 'Đã từ chối và ẩn bài đăng.';
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy bài đăng hoặc không có gì thay đổi.']);
    }

} catch (PDOException $e) {
    // Trả về lỗi nếu có sự cố
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}
?>