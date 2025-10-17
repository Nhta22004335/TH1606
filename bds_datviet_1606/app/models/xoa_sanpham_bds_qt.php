<?php
// File: ../../models/xoa_bds.php

require_once "../../config/database.php"; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

// Chỉ chấp nhận phương thức POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $pdo = ketnoicsdl();
            
            // Chuẩn bị và thực thi lệnh DELETE
            // Lưu ý: ON DELETE CASCADE ở khóa ngoại sẽ tự động xóa các bản ghi liên quan (vd: bai_dang, hinh_anh_bds)
            $stmt = $pdo->prepare("DELETE FROM bat_dong_san WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR); // Nên dùng PARAM_STR cho UUID
            
            if ($stmt->execute()) {
                // Kiểm tra xem có hàng nào bị xóa không
                if ($stmt->rowCount() > 0) {
                    $response = ['success' => true, 'message' => 'Đã xóa tài sản thành công.'];
                } else {
                    $response['message'] = 'Không tìm thấy tài sản để xóa.';
                }
            } else {
                $response['message'] = 'Lỗi khi thực thi lệnh xóa.';
            }
        } catch (PDOException $e) {
            // Ghi log lỗi thay vì hiển thị chi tiết cho người dùng
            error_log("PDOException in xoa_bds.php: " . $e->getMessage());
            $response['message'] = 'Lỗi cơ sở dữ liệu. Không thể xóa.';
             // Bạn có thể thêm mã lỗi cụ thể nếu cần, ví dụ kiểm tra $e->getCode()
            if ($e->getCode() == '23503') { // Lỗi khóa ngoại (nếu không có ON DELETE CASCADE)
                 $response['message'] = 'Không thể xóa tài sản này vì có dữ liệu liên quan (ví dụ: bài đăng).';
            }
        }
    } else {
        $response['message'] = 'Thiếu ID của tài sản cần xóa.';
    }
}

echo json_encode($response);
?>