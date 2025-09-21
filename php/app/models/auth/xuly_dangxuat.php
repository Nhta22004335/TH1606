<?php
require_once '../../config.php';

$db   = new Database();
$conn = $db->connect();

$id   = $_SESSION['idtaikhoan'] ?? null;

if ($id) {
    try {
        $sql = "DELETE FROM phien_dang_nhap WHERE idtaikhoan = :id";
        $stmt = $conn->prepare($sql);
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

header("Location: ../html/dangnhap.html");
exit;
?>