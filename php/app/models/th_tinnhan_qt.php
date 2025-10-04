<?php
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    header('Content-Type: application/json; charset=utf-8');
    session_start();
    
    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $msgId = $_POST['id'] ?? null;
    $currentUser = $_SESSION['id_nguoi_dung'] ?? null;

    if ($msgId && $currentUser) {
        $stmt = $pdo->prepare("UPDATE hop_thoai
                            SET noi_dung='Tin nhắn đã được thu hồi'
                            WHERE id = ? AND nguoi_gui = ?");
        if ($stmt->execute([$msgId, $currentUser])) {
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['success'=>false]);
        }
    } else {
        echo json_encode(['success'=>false]);
    }
?>