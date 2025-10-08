<?php
    // Đảm bảo file này luôn trả về JSON
    header('Content-Type: application/json');
    
    // --- KHAI BÁO CẤU HÌNH VÀ KẾT NỐI CSDL ---
    require_once "../../config/database.php";
    
    // Thêm khối try-catch để bắt lỗi kết nối CSDL và xử lý chung
    try {
        $pdo = ketnoicsdl();
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(["status" => "error", "message" => "Lỗi kết nối CSDL: " . $e->getMessage()]);
        exit;
    }

    // --- XỬ LÝ DỮ LIỆU ĐẦU VÀO ---
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        
        // ĐỌC DỮ LIỆU TỪ $_POST thay vì đọc JSON từ php://input
        $id = trim($_POST['id'] ?? '');
        $trang_thai = trim($_POST['trang_thai'] ?? '');
        
        // Kiểm tra dữ liệu bắt buộc
        if (empty($id) || empty($trang_thai)) {
            http_response_code(400); // Bad Request
            echo json_encode(["status" => "error", "message" => "Thiếu dữ liệu ID hoặc Trạng thái."]);
            exit;
        }

        try {
            // Chuẩn bị truy vấn
            $sql = "UPDATE bieu_mau 
                    SET trang_thai = :trang_thai, ngay_cn = CURRENT_TIMESTAMP 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            // Thực thi với Prepared Statements
            $stmt->execute([
                ":trang_thai" => $trang_thai,
                ":id" => $id
            ]);

            if ($stmt->rowCount() > 0) {
                 echo json_encode(["status" => "success", "message" => "Cập nhật trạng thái thành công!"]);
            } else {
                 echo json_encode(["status" => "warning", "message" => "Không có biểu mẫu nào được cập nhật. Có thể ID không tồn tại hoặc trạng thái không thay đổi."]);
            }
            
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode(["status" => "error", "message" => "Lỗi truy vấn CSDL: " . $e->getMessage()]);
        }
        
    } else {
        // Nếu không phải phương thức POST
        http_response_code(405); // Method Not Allowed
        echo json_encode(["status" => "error", "message" => "Phương thức không hợp lệ. Chỉ chấp nhận POST."]);
    }
?>