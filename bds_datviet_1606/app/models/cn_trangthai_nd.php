<?php
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("Lỗi: Không có ID người dùng được cung cấp.");
    }

    $user_id = $_GET['id'];
    $tt = $_GET['new_status'] ?? '';
    
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    try {
        
        $sql = "UPDATE nguoi_dung SET trang_thai = :tt WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $user_id, 'tt' => $tt]);

        if ($tt === 'danghoatdong') {
            $redirect_url = "trangchu.php?page=ds_nguoidung&status=unlock_success";
        } else {
            $redirect_url = "trangchu.php?page=ds_nguoidung&status=lock_success";
        }

        echo "<script>window.location.href = '{$redirect_url}';</script>";
        exit();

    } catch (PDOException $e) {
        die("Lỗi kết nối hoặc cập nhật CSDL: " . $e->getMessage());
    }
?>