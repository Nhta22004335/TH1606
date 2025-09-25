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

    function luuLichSuNguoiDung($pdo) {
        if (!empty($_SESSION['id_lich_su'])) {
            $stmt = $pdo->prepare("
                UPDATE lich_su_dn_dx
                SET thoi_gian_dang_xuat = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $stmt->execute([':id' => $_SESSION['id_lich_su']]);
            unset($_SESSION['id_lich_su']);
        }
    }

    luuLichSuNguoiDung($pdo);

    header("Location: ../../views/auth/dangnhap.html");
    exit;

    
?>