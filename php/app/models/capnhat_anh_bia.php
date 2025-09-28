<?php
    session_start();
    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $id = $_POST['idnguoidung'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bia'])) {
        $file = $_FILES['bia'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $tenFileMoi = uniqid("bia_") . "." . strtolower($ext);

            $uploadDir = "../../storage/pictures/bia/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Di chuyển file upload vào thư mục
            $duongDan = $uploadDir . $tenFileMoi;
            if (move_uploaded_file($file['tmp_name'], $duongDan)) {
                $stmt = $pdo->prepare("UPDATE moi_gioi SET anh_bia = :anh_bia WHERE id_nguoi_dung = :id");
                $stmt->execute([
                    ':anh_bia' => $tenFileMoi,
                    ':id' => $id
                ]);

                echo "Cập nhật ảnh bìa thành công!";
            } else {
                echo "Lỗi khi lưu file.";
            }
        } else {
            echo "Lỗi upload file.";
        }
    }
?>