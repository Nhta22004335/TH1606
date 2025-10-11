<?php
    session_start();
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    header('Content-Type: application/json');

    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $id = $data['id'] ?? '';
    $quyenIds    = $data['quyen_ids'] ?? [];

    if (empty($id) || !is_array($quyenIds) || count($quyenIds) === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu dữ liệu hoặc không có quyền nào được chọn!'
        ]);
        exit;
    }

    try {
         
        $pdo->beginTransaction();

        // Xóa quyền cũ
        $stmtDel = $pdo->prepare("DELETE FROM phan_quyen WHERE id_nguoi_dung = :id");
        $stmtDel->execute([':id' => $id]);

       // Thêm quyền mới
        $stmtIns = $pdo->prepare("INSERT INTO phan_quyen (id_nguoi_dung, id_quyen) VALUES (:id_nguoi_dung, :id_quyen)");
        foreach ($quyenIds as $qId) {
            $stmtIns->execute([
                ':id_nguoi_dung' => $id,
                ':id_quyen' => $qId
            ]);
        }
        
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật quyền thành công!'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ]);
    }
?>
