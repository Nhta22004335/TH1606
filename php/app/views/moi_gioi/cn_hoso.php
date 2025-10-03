<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();


$id_ben_ban = $_SESSION['id_nguoi_dung'] ?? '';
if (!$id_ben_ban) {
    header("Location: ../auth/dangnhap.html");
    exit;
}

// Xử lý form POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tao_hoso'])) {
    $tieu_de = $_POST['tieu_de'] ?? '';
    $loai = $_POST['loai'] ?? '';
    $ben_mua = $_POST['ben_mua'] ?? '';
    
    // Xử lý upload tệp đính kèm
    $tep_dk = null;
    if (isset($_FILES['tep_dk']) && $_FILES['tep_dk']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../../../storage/documents/";
        $fileName = time() . "_" . basename($_FILES["tep_dk"]["name"]);
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES["tep_dk"]["tmp_name"], $filePath)) {
            $tep_dk = $fileName;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO bieu_mau (tieu_de, loai, ben_mua, ben_ban, tep_dk, trang_thai, ngay_tao)
        VALUES (:tieu_de, :loai, :ben_mua, :ben_ban, :tep_dk, 'choduyet', CURRENT_TIMESTAMP)
    ");
    $stmt->execute([
        ':tieu_de' => $tieu_de,
        ':loai' => $loai,
        ':ben_mua' => $ben_mua,
        ':ben_ban' => $id_ben_ban,
        ':tep_dk' => $tep_dk
    ]);

    echo "<script>alert('Tạo hồ sơ thành công!'); window.location.href='trangchu.php?page=../moi_gioi/cn_hoso';</script>";
    exit;
}


$usersStmt = $pdo->query("SELECT * FROM info_nguoi_dung");
$nguoi_mua = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo hồ sơ mới</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 ">
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow mt-4">
        <h1 class="text-2xl font-bold mb-4">📝 Tạo hồ sơ mới</h1>
        <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block font-semibold">Tiêu đề:</label>
                <input type="text" name="tieu_de" class="w-full border px-3 py-2 rounded" required>
            </div>
            <div>
                <label class="block font-semibold">Loại:</label>
                <select name="loai" class="w-full border px-3 py-2 rounded" required>
                    <option value="">-- Chọn loại hồ sơ --</option>
                    <option value="hosomuaban">Hồ sơ mua bán</option>
                    <option value="hosothue">Hồ sơ thuê</option>
                    <option value="bienban">Biểu mẫu đăng ký</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold">Người mua:</label>
                <select name="ben_mua" class="w-full border px-3 py-2 rounded" required>
                    <option value="">-- Chọn người mua --</option>
                    <?php foreach($nguoi_mua as $user): ?>
                        <option value="<?= $user['id_nguoi_dung'] ?>"><?= htmlspecialchars($user['ho_ten']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold">Tệp đính kèm:</label>
                <input type="file" name="tep_dk" class="w-full border px-3 py-2 rounded">
            </div>
            <button type="submit" name="tao_hoso" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Tạo hồ sơ</button>
        </form>
    </div>
</body>
</html>
