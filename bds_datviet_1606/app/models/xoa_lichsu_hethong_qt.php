<?php
session_start();
header('Content-Type: application/json');

// 1. KẾT NỐI CSDL
require_once "../../config/database.php"; // Đảm bảo đường dẫn này đúng
$pdo = ketnoicsdl();

// (Tùy chọn) Kiểm tra quyền admin
$id_admin_dang_nhap = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_admin_dang_nhap) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện hành động này.']);
    exit;
}

// 2. NHẬN VÀ KIỂM TRA DỮ LIỆU ĐẦU VÀO
// Dữ liệu được gửi dạng JSON trong body
$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? null;
$type = $input['type'] ?? null; // 'tim_kiem', 'xem_bds'

if (!$id || !$type) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ. Thiếu ID hoặc loại lịch sử.']);
    exit;
}

// 3. XÁC ĐỊNH BẢNG VÀ CÂU LỆNH SQL
$tableName = '';
switch ($type) {
    case 'tim_kiem':
        $tableName = 'lich_su_tim_kiem';
        break;
    case 'xem_bds':
        $tableName = 'lich_su_xem_bds';
        break;
    // Thêm các loại lịch sử khác nếu có
    // case 'mua_hang': // Không xóa được theo logic của bạn
    //     echo json_encode(['status' => 'error', 'message' => 'Không thể xóa lịch sử mua hàng.']);
    //     exit;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Loại lịch sử không được hỗ trợ để xóa.']);
        exit;
}

// 4. THỰC THI XÓA
try {
    // Sử dụng tên bảng động một cách an toàn (không dùng trực tiếp biến $tableName trong SQL)
    // Thay vào đó, kiểm tra nó trong switch case
    $sql = "DELETE FROM {$tableName} WHERE id = :id"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    // Kiểm tra xem có dòng nào bị xóa không
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa bản ghi thành công.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy bản ghi để xóa.']);
    }
    exit;

} catch (PDOException $e) {
    // Ghi log lỗi nếu cần thiết
    // error_log("Lỗi xóa lịch sử: " . $e->getMessage()); 
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
    exit;
}

?>