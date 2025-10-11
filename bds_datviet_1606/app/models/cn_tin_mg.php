<?php
    require_once '../../../config/database.php'; 

    // Kiểm tra đăng nhập
    if (!isset($_SESSION['id_nguoi_dung'])) {
        header("Location: ../auth/dangnhap.html");
        exit;
    }

    $id_tin = $_GET['id'] ?? '';

    if (!$id_tin) {
        echo "Không tìm thấy tin đăng!";
        exit;
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['capnhattintuc'])) {
        if(isset($_FILES['anh_tin'])){
        $file = $_FILES['anh_tin'];
        }

        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $tenFileMoi = uniqid("anh_tin_") . "." . strtolower($ext);

            $uploadDir = "../../../storage/pictures/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Di chuyển file upload vào thư mục
            $duongDan = $uploadDir . $tenFileMoi;
            if (move_uploaded_file($file['tmp_name'], $duongDan)) {
                $stmt = $pdo->prepare("UPDATE tin_tuc SET anh_tin = :anh_tin WHERE id = :id");
                    $stmt->execute([
                        ':anh_tin' => $tenFileMoi,
                        ':id' => $id_tin
                    ]);
            } else {
                echo "Lỗi khi lưu file.";
            }
        } else {
            echo "Lỗi upload file.";
        }

        $id = $_POST['id_tin'] ??'';

        $tieu_de = $_POST['tieu_de'] ??'';
        $chuyen_muc = $_POST['chuyen_muc'] ??'';
        $mo_ta = $_POST['mo_ta'] ??'';
        $trang_thai = $_POST['trang_thai'] ??'';
        $stmt = $pdo->prepare("UPDATE tin_tuc 
                            SET tieu_de = ?, mo_ta = ?, chuyen_muc = ?, trang_thai = ? 
                            WHERE id = ?");
        $result = $stmt->execute([$tieu_de, $mo_ta, $chuyen_muc, $trang_thai, $id]);
        
    }
    // Lấy thông tin tin đăng
    $stmt = $pdo->prepare("SELECT * FROM tin_tuc WHERE id = ?");
    $stmt->execute([$id_tin]);
    $tin = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tin) {
        echo "Tin đăng không tồn tại!";
        exit;
    }

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa tin đăng</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow mt-4">
        <h1 class="text-2xl font-bold mb-4">Sửa tin đăng</h1>
        <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
    <input type="hidden" name="id_tin" value="<?= htmlspecialchars($tin['id']) ?>">

    <div>
        <label class="block font-semibold">Tiêu đề:</label>
        <input type="text" name="tieu_de" value="<?= htmlspecialchars($tin['tieu_de']) ?>" class="w-full border px-3 py-2 rounded">
    </div>

    <div>
        <label class="block font-semibold">Mô tả:</label>
        <textarea name="mo_ta" class="w-full border px-3 py-2 rounded"><?= htmlspecialchars($tin['mo_ta']) ?></textarea>
    </div>

    <div>
        <label class="block font-semibold">Chuyên mục:</label>
        <input type="text" name="chuyen_muc" value="<?= htmlspecialchars($tin['chuyen_muc']) ?>" class="w-full border px-3 py-2 rounded">
    </div>

    <div>
        <label class="block font-semibold">Trạng thái:</label>
        <select name="trang_thai" class="w-full border px-3 py-2 rounded">
            <?php 
            $trangthai_options = ['choduyet','dangban','daban','dathue'];
            foreach ($trangthai_options as $option): 
                $selected = $tin['trang_thai'] === $option ? 'selected' : '';
            ?>
                <option value="<?= $option ?>" <?= $selected ?>><?= $option ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label class="block font-semibold">Ảnh tin:</label>
        <input type="file" name="anh_tin" class="w-full border px-3 py-2 rounded">
        <?php if (!empty($tin['anh_tin'])): ?>
            <p class="mt-2">Ảnh hiện tại: 
            <img src="../../../storage/pictures/<?= htmlspecialchars($tin['anh_tin']) ?>" alt="ảnh tin" class="w-32 h-20 object-cover"></p>
        <?php endif; ?>
    </div>

    <button type="submit" id="capnhattintuc" name="capnhattintuc" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Cập nhật</button>
</form>

    </div>
</body>
</html>
