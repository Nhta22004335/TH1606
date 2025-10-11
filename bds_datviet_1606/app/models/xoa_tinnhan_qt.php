<?php
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    header('Content-Type: application/json; charset=utf-8');
    session_start();

    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $msgId = $_POST['id'] ?? null;
    $currentUser = $_SESSION['id_nguoi_dung'] ?? 0;

    if ($msgId && $currentUser) {
        $stmt = $pdo->prepare("DELETE FROM tin_nhan WHERE id = ? AND nguoi_gui = ?");
        if ($stmt->execute([$msgId, $currentUser])) {
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['success'=>false]);
        }
    } else {
        echo json_encode(['success'=>false]);
    }
?>
