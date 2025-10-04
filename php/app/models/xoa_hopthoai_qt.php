<?php
    header('Content-Type: application/json; charset=utf-8');
    session_start();
    require_once "../../config/database.php";

    $pdo = ketnoicsdl();

    $currentUser = $_SESSION['id_nguoi_dung'];
    $idkey = $_POST['idkey'] ?? null;
    
    // Giả sử chatId dạng "TenA_TenB" (key dùng trong DB)
    $parts = explode("_", $idkey);
    if (count($parts) !== 2) {
        echo json_encode(['status'=>'fail','msg'=>'Chat không hợp lệ']);
        exit;
    }

    // Xóa tất cả tin nhắn trong chat
    $stmt = $pdo->prepare("
        DELETE FROM hop_thoai 
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