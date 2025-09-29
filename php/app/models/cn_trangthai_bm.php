<?php
    header('Content-Type: application/json');

    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $id = trim($data['id'] ?? '');
    $trang_thai = trim($data['trang_thai'] ?? '');

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $sql = "UPDATE bieu_mau 
                SET trang_thai = :trang_thai, ngay_cn = CURRENT_TIMESTAMP 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":trang_thai" => $trang_thai,
            ":id" => $id
        ]);

        echo json_encode(["status" => "success", "message" => "Cập nhật trạng thái thành công"]);
        
    } else {
        echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ"]);
    }
?>