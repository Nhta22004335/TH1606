<?php
    require_once '../../../config/database.php';
    $pdo = ketnoicsdl();

    $id_moi_gioi = $_SESSION['id_nguoi_dung'] ?? null;

    $id_khachhang = $_GET['id'] ?? null;

    try {
        $sqlInsert = "INSERT INTO hop_thoai (id_nguoi_1, id_nguoi_2) 
                  VALUES (?, ?) 
                  ON CONFLICT (id_nguoi_1, id_nguoi_2) DO NOTHING";
        
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([$id_moi_gioi, $id_khachhang]);

        if ($stmtInsert->rowCount() > 0) {
            $_SESSION['action_message'] = ['type' => 'success', 'text' => 'Đã tạo cuộc trò chuyện mới!'];
        } else {
            $_SESSION['action_message'] = ['type' => 'info', 'text' => 'Cuộc trò chuyện đã tồn tại.'];
        }
    } catch (PDOException $e) {
        $_SESSION['action_message'] = ['type' => 'error', 'text' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()];
    }

    echo "<script>window.location.href = 'trangchu.php?page=../moi_gioi/ql_yeucau_khachhang&trang_thai=daduyet';</script>";
?>