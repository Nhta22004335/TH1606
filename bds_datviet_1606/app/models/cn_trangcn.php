<?php
// Tệp: ../../models/cn_trangthai_bm.php
header('Content-Type: application/json');
require_once "../../../config/database.php";

$pdo = null;
try {
    // 1. Kiểm tra và lấy dữ liệu POST
    $id = $_POST['id'] ?? null;
    $trang_thai = $_POST['trang_thai'] ?? null;

    if (!$id || !$trang_thai) {
        throw new Exception("Dữ liệu không hợp lệ: Thiếu ID hoặc Trạng thái.");
    }
    
    // 2. Kết nối CSDL
    $pdo = ketnoicsdl(); 
    
    // 3. Chuẩn bị và thực thi câu lệnh UPDATE
    // Cập nhật trạng thái và ngày cập nhật (ngay_cn)
    $stmt = $pdo->prepare("UPDATE bieu_mau SET trang_thai = :trang_thai, ngay_cn = NOW() WHERE id = :id");
    $stmt->execute([':trang_thai' => $trang_thai, ':id' => $id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Không tìm thấy bản ghi ID {$id} để cập nhật hoặc trạng thái đã được cập nhật trước đó.");
    }

    // 4. Phản hồi thành công
    echo json_encode([
        'status' => 'success', 
        'message' => 'Cập nhật trạng thái thành công!'
    ]);

} catch (Exception $e) {
    // Xử lý lỗi (Đảm bảo $pdo được kiểm tra như đề xuất ở câu trả lời trước)
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack(); 
    }
    http_response_code(400);
    error_log("Lỗi cập nhật trạng thái: " . $e->getMessage()); 
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
?>