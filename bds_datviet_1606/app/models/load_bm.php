<?php
require_once "../../config/database.php";
header("Content-Type: application/json; charset=UTF-8");

$pdo = ketnoicsdl();

try {
    // =======================
    // 1️⃣ LẤY DỮ LIỆU THEO ID (GET)
    // =======================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = $_GET['id'] ?? '';

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "Thiếu ID biểu mẫu!"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, tieu_de, loai, tep_dk FROM bieu_mau WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $bm = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($bm) {
            echo json_encode(["status" => "success", "data" => $bm]);
        } else {
            echo json_encode(["status" => "error", "message" => "Không tìm thấy biểu mẫu!"]);
        }
        exit;
    }

    // =======================
    // 2️⃣ CẬP NHẬT BIỂU MẪU (POST)
    // =======================
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id_bm'] ?? '';
        $tieu_de = $_POST['tieu_de'] ?? '';
        $loai = $_POST['loai'] ?? '';

        if (!$id || !$tieu_de) {
            echo json_encode(["status" => "error", "message" => "Thiếu dữ liệu cần thiết!"]);
            exit;
        }

        // Nếu có file mới
        if (!empty($_FILES['tep_dk']['name'])) {
            $fileName = time() . "_" . basename($_FILES['tep_dk']['name']);
            $targetDir = "../../storage/documents/";
            $targetPath = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['tep_dk']['tmp_name'], $targetPath)) {
                $stmt = $pdo->prepare("
                    UPDATE bieu_mau 
                    SET tieu_de = :tieu_de, loai = :loai, tep_dk = :tep_dk, ngay_cn = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':tieu_de' => $tieu_de,
                    ':loai' => $loai,
                    ':tep_dk' => $fileName,
                    ':id' => $id
                ]);

                echo json_encode(["status" => "success", "message" => "Cập nhật biểu mẫu thành công (đã thay tệp mới)."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Không thể tải lên tệp mới!"]);
            }
        } 
        // Nếu không có file mới
        else {
            $stmt = $pdo->prepare("
                UPDATE bieu_mau 
                SET tieu_de = :tieu_de, loai = :loai, ngay_cn = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':tieu_de' => $tieu_de,
                ':loai' => $loai,
                ':id' => $id
            ]);

            echo json_encode(["status" => "success", "message" => "Cập nhật biểu mẫu thành công (không thay tệp)."]);
        }
    } 

    // =======================
    // 3️⃣ TRƯỜNG HỢP KHÁC
    // =======================
    else {
        echo json_encode(["status" => "error", "message" => "Phương thức không hợp lệ!"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Lỗi CSDL: " . $e->getMessage()]);
}
