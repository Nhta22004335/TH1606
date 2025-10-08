<?php
    session_start();
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $id   = $_SESSION['id_nguoi_dung'] ?? null;

    if ($id) {
        try {
            $sql = "DELETE FROM phien_dang_nhap WHERE id_nguoi_dung = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
        echo "<script>console.error('Lỗi khi xóa phiên đăng nhập: " . addslashes($e->getMessage()) . "');</script>";
        }
    }

    $stmt = $pdo->prepare("
        UPDATE lich_su_xac_thuc
        SET thoi_gian_ket_thuc = CURRENT_TIMESTAMP
        WHERE id = :id
    ");
    $stmt->execute([':id' => $_SESSION['id_lich_su']]);
    unset($_SESSION['id_lich_su']);

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }    

    header("Location: ../../views/auth/dangnhap.html");
    exit;
   
?>