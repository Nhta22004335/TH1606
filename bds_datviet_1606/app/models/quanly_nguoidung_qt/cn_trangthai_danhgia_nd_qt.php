<?php
// 1. Thiết lập Header để trả về JSON
header('Content-Type: application/json');

// 2. Nhúng file cấu hình và kết nối CSDL
require_once "../../../config/database.php";

// 3. Khởi tạo mảng phản hồi mặc định
$response = [
    'status' => 'error',
    'message' => 'Yêu cầu không hợp lệ hoặc thiếu dữ liệu.'
];

// 4. Lấy dữ liệu từ body của request (do được gửi bằng fetch với JSON)
$data = json_decode(file_get_contents('php://input'), true);

// 5. Kiểm tra xem dữ liệu cần thiết có tồn tại không
$review_id = $data['review_id'] ?? null;
$action = $data['action'] ?? null;

if ($review_id && $action) {
    try {
        // 6. Kết nối CSDL
        $pdo = ketnoicsdl();
        $sql = "";

        // 7. Xác định câu lệnh SQL dựa trên hành động ('action')
        switch ($action) {
            case 'an':
                // Cập nhật trạng thái thành 'an'
                $sql = "UPDATE danh_gia_mg SET trang_thai = 'an' WHERE id = ?";
                break;
            case 'hien':
                // Cập nhật trạng thái thành 'hien'
                $sql = "UPDATE danh_gia_mg SET trang_thai = 'hien' WHERE id = ?";
                break;
            case 'xoa':
                // Xóa vĩnh viễn bản ghi
                $sql = "DELETE FROM danh_gia_mg WHERE id = ?";
                break;
            default:
                // Nếu hành động không hợp lệ
                $response['message'] = 'Hành động không được hỗ trợ.';
                echo json_encode($response);
                exit; // Dừng thực thi
        }

        // 8. Chuẩn bị và thực thi câu lệnh
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$review_id]);

        // 9. Kiểm tra xem có dòng nào bị ảnh hưởng không
        if ($stmt->rowCount() > 0) {
            // Nếu thành công, cập nhật mảng phản hồi
            $response['status'] = 'success';
            $response['message'] = 'Thao tác thành công!';
        } else {
            // Nếu không có dòng nào thay đổi (ví dụ: ID không tồn tại)
            $response['message'] = 'Không tìm thấy đánh giá hoặc không có gì thay đổi.';
        }

    } catch (PDOException $e) {
        // 10. Bắt lỗi nếu có vấn đề với CSDL
        // (Trong môi trường production, bạn có thể muốn ghi log thay vì hiển thị lỗi chi tiết)
        $response['message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
    }
}

// 11. Trả về kết quả cuối cùng dưới dạng chuỗi JSON
echo json_encode($response);
?>