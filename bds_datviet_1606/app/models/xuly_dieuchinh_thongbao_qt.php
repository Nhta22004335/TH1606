<?php
// File: ../../models/xuly_sua_thongbao.php

require_once "../../config/database.php"; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tb = $_POST['id'] ?? null;
    $tieu_de = trim($_POST['tieu_de'] ?? '');
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    $loai = $_POST['loai'] ?? null;
    $id_nguoi_dung = $_POST['id_nguoi_dung'] ?? null;

    // Nếu id_nguoi_dung là chuỗi rỗng (từ option -- Gửi cho Hệ thống --), đổi thành NULL
    if (empty($id_nguoi_dung)) {
        $id_nguoi_dung = null;
    }

    // Validate
    if (empty($id_tb) || empty($tieu_de) || empty($noi_dung) || empty($loai)) {
        $response['message'] = 'Vui lòng điền đầy đủ các trường bắt buộc.';
    } else {
        try {
            $pdo = ketnoicsdl();
            
            // Cập nhật thông báo
            // Thêm AND trang_thai = 'chuaxem' để đảm bảo an toàn, chỉ sửa được tin chưa ai đọc
            $sql_update = "
                UPDATE thong_bao 
                SET 
                    tieu_de = ?, 
                    noi_dung = ?, 
                    loai = ?, 
                    id_nguoi_dung = ? 
                WHERE 
                    id = ? AND trang_thai = 'chuaxem'
            ";
            
            $stmt_update = $pdo->prepare($sql_update);
            
            if ($stmt_update->execute([$tieu_de, $noi_dung, $loai, $id_nguoi_dung, $id_tb])) {
                if ($stmt_update->rowCount() > 0) {
                    $response = ['success' => true, 'message' => 'Đã cập nhật thông báo thành công!'];
                } else {
                    $response['message'] = 'Không thể cập nhật thông báo (có thể đã được xem hoặc không có gì thay đổi).';
                }
            } else {
                $response['message'] = 'Lỗi CSDL khi cập nhật.';
            }
        } catch (PDOException $e) {
            error_log("PDOException in xuly_sua_thongbao.php: " . $e->getMessage());
            $response['message'] = 'Lỗi cơ sở dữ liệu.';
        }
    }
}

echo json_encode($response);
?>