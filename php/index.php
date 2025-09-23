<?php
    session_start();
    require_once "config/database.php";
    $pdo = ketnoicsdl();

    if (!isset($_SESSION['id_nguoi_dung']) && isset($_COOKIE['token_phien'], $_COOKIE['id_nguoi_dung'])) {

        $stmt = $pdo->prepare("SELECT id, id_nguoi_dung, token_phien
                                FROM phien_dang_nhap 
                                WHERE id_nguoi_dung = :id_nguoi_dung 
                                AND token_phien = :token_phien 
                                AND het_han > NOW()");
        $stmt->execute([
            ':id_nguoi_dung' => $_COOKIE['id_nguoi_dung'],
            ':token_phien' => $_COOKIE['token_phien']
        ]);


        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Khôi phục session
            $_SESSION['id_phien']    = $row['id'];
            $_SESSION['id_nguoi_dung'] = $row['id_nguoi_dung'];
            $_SESSION['token_phien'] = $row['token_phien'];
        } else {
            // Token hết hạn hoặc sai → xóa cookie
            setcookie("token_phien", "", time() - 3600, "/");
            setcookie("id_nguoi_dung", "", time() - 3600, "/");
        }
    }

    $id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? $_COOKIE['id_nguoi_dung'] ?? null;

    // Nếu vẫn chưa có session thì bắt buộc đăng nhập lại
    if (empty($_SESSION['id_phien']) || empty($_SESSION['token_phien'])) {
        header("Location: app/views/auth/dangnhap.html");
        exit;
    }

    function ckQuyenTaiKhoan($pdo, $id_nguoi_dung) {
        $sql = "SELECT vai_tro FROM nguoi_dung WHERE id = :id_nguoi_dung LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_nguoi_dung', $id_nguoi_dung, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn(); 
    }

    try {
        $stmt = $pdo->prepare("SELECT 1 
                                FROM phien_dang_nhap 
                                WHERE id = :id
                                AND token_phien = :token_phien
                                AND dang_hoat_dong = TRUE 
                                AND (het_han IS NULL OR het_han > NOW())");
        $stmt->bindParam(':id', $_SESSION['id_phien'], PDO::PARAM_STR);
        $stmt->bindParam(':token_phien', $_SESSION['token_phien'], PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetch()) {
            if (ckQuyenTaiKhoan($pdo, $id_nguoi_dung) === 'quantri') {
                header("Location: app/views/quan_tri/trangchu.php");
                exit;
            } 
            if (ckQuyenTaiKhoan($pdo, $id_nguoi_dung) === 'moigioi')
            {
                header("Location: app/views/moigioi/trangchu.php");
                exit;
            } else {
                header("Location: app/views/khachhang/trangchu.php");
                exit;
            }
        } else {
            header("Location: app/views/auth/dangnhap.html");
            exit;
        }
    } catch (PDOException $e) {
        echo "Lỗi truy vấn CSDL: " . $e->getMessage();
    }
?>