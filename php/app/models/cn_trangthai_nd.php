<?php
    session_start();
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    header('Content-Type: application/json');

    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $id = trim($data['id'] ?? '');
    $trang_thai = trim($data['trangthai'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE nguoi_dung SET trang_thai = :trang_thai WHERE id = :id");
        $stmt->execute([':trang_thai' => $trang_thai, ':id' => $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật thành công!'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật không thành công!'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Cập nhật không thành công!'
        ]);
    }
?>  