<?php
    header('Content-Type: application/json');
    session_start();

    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    // $currentUser = $_SESSION['id_nguoi_dung'];
    // $idkey = $_POST['idkey'] ?? null;
    $id_hop_thoai = $_POST['id_hop_thoai'] ?? null;
    // Cập nhật trạng thái khóa
    $stmt = $pdo->prepare("
        UPDATE hop_thoai 
        SET da_khoa = 1
        WHERE (id = :id_hop_thoai)
    ");

    try {
        $stmt->execute([
            ':id_hop_thoai' => $id_hop_thoai
        ]);
        echo json_encode(['status'=>'ok', 'msg'=>$id_hop_thoai]);
    } catch (Exception $e) {
        echo json_encode(['status'=>'fail','msg'=>$e->getMessage()]);
    }
?>