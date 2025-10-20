<?php
// Bật session và kết nối CSDL
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "../../../config/database.php";

// Thiết lập header là JSON
header('Content-Type: application/json');

// Chuẩn bị phản hồi
$response = ['success' => false, 'message' => 'Đã xảy ra lỗi không xác định.'];

// Kiểm tra bảo mật
$id_nguoi_dung_hien_tai = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_nguoi_dung_hien_tai) {
    $response['message'] = 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.';
    echo json_encode($response);
    exit;
}

// Chỉ chấp nhận phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Yêu cầu không hợp lệ.';
    echo json_encode($response);
    exit;
}

// Lấy dữ liệu JSON từ request
$data = json_decode(file_get_contents('php://input'), true);
$id_giao_dich = $data['id_giao_dich'] ?? null;
$trang_thai_moi = $data['trang_thai_moi'] ?? null;

// Kiểm tra dữ liệu đầu vào
$trang_thai_hop_le = ['choxuly', 'dangxuly', 'hoantat', 'dahuy'];
if (empty($id_giao_dich) || empty($trang_thai_moi) || !in_array($trang_thai_moi, $trang_thai_hop_le)) {
    $response['message'] = 'Dữ liệu gửi lên không hợp lệ.';
    echo json_encode($response);
    exit;
}

try {
    $pdo = ketnoicsdl();
    
    // Câu lệnh UPDATE CÓ KIỂM TRA BẢO MẬT
    // Chỉ cập nhật nếu:
    // 1. ID giao dịch đúng
    // 2. Giao dịch đó thuộc quyền quản lý của người bán (môi giới) đang đăng nhập
    $sql = "UPDATE giao_dich 
            SET trang_thai = :trang_thai_moi
            WHERE id = :id_giao_dich 
            AND id_nguoi_ban = :id_nguoi_ban_hien_tai";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':trang_thai_moi' => $trang_thai_moi,
        ':id_giao_dich' => $id_giao_dich,
        ':id_nguoi_ban_hien_tai' => $id_nguoi_dung_hien_tai
    ]);
    
    // Kiểm tra xem có hàng nào được cập nhật không
    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Cập nhật trạng thái thành công.';
    } else {
        // Không có hàng nào được cập nhật:
        // Hoặc là ID giao dịch sai, hoặc là môi giới này không có quyền sửa giao dịch đó
        $response['message'] = 'Không tìm thấy giao dịch hoặc bạn không có quyền cập nhật.';
    }
    
} catch (PDOException $e) {
    error_log("Lỗi Cập nhật trạng thái: " . $e->getMessage());
    $response['message'] = 'Lỗi máy chủ CSDL: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>