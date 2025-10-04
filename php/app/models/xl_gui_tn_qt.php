<?php
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    header('Content-Type: application/json; charset=utf-8');
    session_start();

    require_once "../../config/database.php";
    $pdo = ketnoicsdl();
    
    $id_gui = $_SESSION['id_nguoi_dung'];
    $id_nhan = $_POST['nguoi_nhan'] ?? null;
    $noi_dung = trim($_POST['message'] ?? '');
    
    $anh = null;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../../storage/pictures/messages/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $anh = $fileName;   
            
        }
    }

    if (isset($_POST['like']) && $_POST['like'] == "1") {
        $noi_dung = "👍";
    }

    if ($id_nhan && ($noi_dung || $anh)) {
        $sql = "INSERT INTO hop_thoai (noi_dung, anh_tn, tg_gui, nguoi_gui, nguoi_nhan) 
                VALUES (:noi_dung, :anh, NOW(), :nguoi_gui, :nguoi_nhan) RETURNING id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':noi_dung' => $noi_dung,
            ':anh' => $anh,
            ':nguoi_gui' => $id_gui,
            ':nguoi_nhan' => $id_nhan,
        ]);

        $id = $stmt->fetchColumn(); 

        echo json_encode([
            "status" => "ok",
            "message" => [
                "id" => $id,
                "noi_dung" => $noi_dung,
                "anh" => $anh,
                "tg_gui" => date("Y-m-d H:i:s"),
                "id_gui" => $id_gui
            ]
        ]);
        
    } else {
        echo json_encode(["status" => "error", "msg" => "Thiếu dữ liệu"]);
    }
?>