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

    $stmt = $pdo->prepare("INSERT INTO lich_su_xac_thuc (id_nguoi_dung, loai_su_kien, dia_chi_ip, user_agent, ghi_chu)
             VALUES (:id_nguoi_dung, :loai_su_kien, :dia_chi_ip, :user_agent, :ghi_chu)
    ");

    $stmt->execute([
            ':id_nguoi_dung' => $id,
            ':loai_su_kien'  => 'dangxuat',
            ':dia_chi_ip'    => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            ':user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            ':ghi_chu'       => 'Đăng xuất thành công!'
        ]);
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