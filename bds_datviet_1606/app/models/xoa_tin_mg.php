<?php 
    require_once '../../../config/database.php'; 

    // Kiểm tra đăng nhập
    if (!isset($_SESSION['id_nguoi_dung'])) {
        header("Location: ../auth/dangnhap.html");
        exit;
    }

    $id_tin = $_GET['id'] ?? '';
    
    if (!$id_tin) {
        echo "Không tìm thấy tin đăng!";
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM tin_tuc WHERE id=:id ");
            $stmt->execute([
                ':id' => $id_tin
            ]);

            
    $deletedRows = $stmt->rowCount();
 echo "<script>
        alert('Cập nhật tin thành công!');
        window.location.href = 'trangchu.php?page=../moi_gioi/ql_tintuc_mg';
    </script>";
?>
