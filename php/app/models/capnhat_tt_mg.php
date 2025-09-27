<?php
    session_start();
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    header('Content-Type: application/json');

    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $id    = trim($data['id']    ?? '');
    $field = trim($data['field'] ?? '');
    $value = trim($data['value'] ?? '');

    if (!$id || !$field || !$value) {
        echo json_encode($response);
        exit;
    }

    $mapfield = [
        'vaitro' => 'vai_tro',
        'trangthai' => 'trang_thai'
    ];

    $vaitromap = [
        'moigioi' => 'Môi giới',
        'quantri' => 'Quản trị',
        'khachhang' => 'Khách hàng'
    ];

    try {
        $stmt = $pdo->prepare("UPDATE nguoi_dung SET {$mapfield[$field]} = :value WHERE id = :id");
        $stmt->execute([':value' => $value, ':id' => $id]);

        if ($stmt->rowCount() > 0) {
            // Map giá trị hiển thị
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật thành công!',
                'newValue' => $vaitromap[$value]
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
