<?php
// Bắt đầu session và kết nối CSDL
session_start();
require_once "../../../config/database.php"; // Đảm bảo đường dẫn này đúng
$pdo = ketnoicsdl();

// Lấy ID admin (để kiểm tra quyền nếu cần)
$id_admin_dang_nhap = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_admin_dang_nhap) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện.']);
    exit;
}

// Lấy hành động và ID từ URL
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

if (!$action || !$id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin hành động.']);
    exit;
}

$newStatus = "active"; // Trạng thái mới sau khi thực thi
$sql = "";

// Xây dựng câu SQL dựa trên hành động
switch ($action) {
    case 'lock':
        $sql = "UPDATE hop_thoai SET da_khoa = TRUE, da_xoa = FALSE WHERE id = :id";
        $newStatus = "locked";
        break;
    case 'unlock':
        $sql = "UPDATE hop_thoai SET da_khoa = FALSE, da_xoa = FALSE WHERE id = :id";
        $newStatus = "active";
        break;
    case 'delete':
        $sql = "UPDATE hop_thoai SET da_xoa = TRUE WHERE id = :id";
        $newStatus = "deleted";
        break;
    case 'restore':
        $sql = "UPDATE hop_thoai SET da_xoa = FALSE, da_khoa = FALSE WHERE id = :id";
        $newStatus = "active";
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
        exit;
}

try {
    // Thực thi câu lệnh
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    
    // Trả về kết quả thành công và trạng thái mới
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'newStatus' => $newStatus,
        'id' => $id
    ]);
    exit;

} catch (PDOException $e) {
    // Trả về lỗi
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>