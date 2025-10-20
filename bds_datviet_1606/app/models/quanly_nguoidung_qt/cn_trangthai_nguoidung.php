<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ.']);
    exit();
}

$user_id = $_POST['id'] ?? null;
$new_status = $_POST['new_status'] ?? '';

if (empty($user_id) || empty($new_status)) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu dữ liệu cần thiết.']);
    exit();
}

$labeltrangthai = [
    'danghoatdong' => 'Đang hoạt động',
    'tamngung' => 'Tạm ngưng',
];
$statusColors = [
    'danghoatdong' => 'text-green-800 bg-green-100 border-green-200',
    'tamngung' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
];


require_once "../../../config/database.php";

try {
    $pdo = ketnoicsdl();
    
    $sql = "UPDATE nguoi_dung SET trang_thai = :new_status WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $user_id, 'new_status' => $new_status]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Cập nhật thành công!',
            'newState' => $new_status,
            'newLabel' => $labeltrangthai[$new_status] ?? 'Không xác định', 
            'newClasses' => $statusColors[$new_status] ?? ''
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy người dùng.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu.']);
}
?>