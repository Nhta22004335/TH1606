<?php
// models/api_delete_log.php

// Thiết lập header để trả về định dạng JSON
header('Content-Type: application/json');

// Chỉ chấp nhận phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ.']);
    exit();
}

require_once "../../config/database.php"; // Đảm bảo đường dẫn này đúng

try {
    $pdo = ketnoicsdl();
    
    // Trường hợp 1: Xóa theo ID
    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM lich_su_xac_thuc WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Đã xóa bản ghi thành công!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy bản ghi để xóa.']);
        }

    // Trường hợp 2: Xóa theo khoảng thời gian
    } elseif (isset($_POST['delete_from'], $_POST['delete_to'])) {
        $from = $_POST['delete_from'];
        $to = $_POST['delete_to'];

        if (empty($from) || empty($to)) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng chọn đầy đủ khoảng thời gian.']);
            exit();
        }

        // Thêm giờ:phút:giây để bao trọn cả ngày 'to'
        $to_datetime = $to . ' 23:59:59';

        $stmt = $pdo->prepare("DELETE FROM lich_su_xac_thuc WHERE thoi_gian_bat_dau BETWEEN :from AND :to");
        $stmt->execute([':from' => $from, ':to' => $to_datetime]);
        
        $deleted_count = $stmt->rowCount();
        echo json_encode(['status' => 'success', 'message' => "Đã xóa thành công {$deleted_count} bản ghi."]);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Thiếu tham số xóa.']);
    }

} catch (PDOException $e) {
    // Không nên hiển thị lỗi chi tiết cho người dùng cuối
    // error_log($e->getMessage()); 
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu.']);
}

?>