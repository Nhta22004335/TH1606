<?php
    header("Location: /app/views/quan_tri/trangchu.php");
    exit();
?>
<?php

require_once "config/config.php";
    $pdo = ketnoicsdl();

if (!isset($_SESSION['id_nguoi_dung']) && isset($_COOKIE['token'], $_COOKIE['id_nguoi_dung'])) {

    $stmt = $conn->prepare("SELECT id, id_nguoi_dung, token
                            FROM phien_dang_nhap 
                            WHERE id_nguoi_dung = :id_nguoi_dung 
                            AND token = :token 
                            AND het_han > NOW()");
    $stmt->execute([
        ':id_nguoi_dung' => $_COOKIE['idtaikhoan'],
        ':token' => $_COOKIE['token']
    ]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Khôi phục session
        $_SESSION['id_phien']    = $row['id'];
        $_SESSION['id_nguoi_dung'] = $row['id_nguoi_dung'];
        $_SESSION['token'] = $row['token'];
    } else {
        // Token hết hạn hoặc sai → xóa cookie
        setcookie("token", "", time() - 3600, "/");
        setcookie("id_nguoi_dung", "", time() - 3600, "/");
    }
}

$idtaikhoan = $_SESSION['id_nguoi_dung'] ?? $_COOKIE['id_nguoi_dung'] ?? null;

// Nếu vẫn chưa có session thì bắt buộc đăng nhập lại
if (empty($_SESSION['id-phien']) || empty($_SESSION['token'])) {
    header("Location: auth/html/dangnhap.html");
    exit;
}

function ckQuyenTaiKhoan($conn, $id_nguoi_dung) {
    $sql = "SELECT vai_tro FROM nguoi_dung WHERE id_nguoi_dung = :id_nguoi_dung LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_nguoi_dung', $id_nguoi_dung, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn(); 
}

try {
    $stmt = $conn->prepare("SELECT 1 
                            FROM phien_dang_nhap 
                            WHERE id = :idphien 
                              AND token = :token
                              AND hoatdong = TRUE 
                              AND (hethan IS NULL OR hethan > NOW())");
    $stmt->bindParam(':idphien', $_SESSION['id_phien'], PDO::PARAM_INT);
    $stmt->bindParam(':token', $_SESSION['token'], PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetch()) {
        if (ckQuyenTaiKhoan($conn, $idtaikhoan) === 'admin') {
            header("Location: zolux/admin.php");
            exit;
        } else {
            header("Location: zolux/user.php");
            exit;
        }
    } else {
        header("Location: auth/html/dangnhap.html");
        exit;
    }
} catch (PDOException $e) {
    echo "Lỗi truy vấn CSDL: " . $e->getMessage();
}
?>