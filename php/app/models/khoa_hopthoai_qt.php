<?php
    header('Content-Type: application/json');
    session_start();

    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $currentUser = $_SESSION['id_nguoi_dung'];
    $idkey = $_POST['idkey'] ?? null;

    // Cập nhật trạng thái khóa
    $stmt = $pdo->prepare("
        UPDATE hop_thoai
        SET da_khoa = 1
        WHERE (nguoi_gui = :uid OR nguoi_nhan = :uid)
        AND CONCAT(LEAST(nguoi_gui, nguoi_nhan),'_',GREATEST(nguoi_gui, nguoi_nhan)) = :idkey
    ");

    try {
        $stmt->execute([
            ':uid' => $currentUser,
            ':idkey' => $idkey
        ]);
        echo json_encode(['status'=>'ok']);
    } catch (Exception $e) {
        echo json_encode(['status'=>'fail','msg'=>$e->getMessage()]);
    }
?>