<?php
    session_start();
    require_once "../../config/database.php";
    $pdo = ketnoicsdl();

    $id = $_POST['idnguoidung'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avt'])) {
        $file = $_FILES['avt'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $tenFileMoi = uniqid("avt_") . "." . strtolower($ext);

            $uploadDir = "../../storage/pictures/avt/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Di chuyển file upload vào thư mục
            $duongDan = $uploadDir . $tenFileMoi;
            if (move_uploaded_file($file['tmp_name'], $duongDan)) {
                $stmt = $pdo->prepare("UPDATE nguoi_dung SET avt = :avt WHERE id = :id");
                $stmt->execute([
                    ':avt' => $tenFileMoi,
                    ':id' => $id
                ]);

                header("Location: ../views/quan_ly/trangchu.php?page=ct_nguoidung&id=$id");
                exit();
            } else {
                echo "Lỗi khi lưu file.";
            }
        } else {
            echo "Lỗi upload file.";
        }
    }
?>