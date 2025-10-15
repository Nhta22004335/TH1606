<?php
require_once "../../../config/database.php";
session_start();

$pdo = ketnoicsdl();
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;

if (!$id_nguoi_dung) {
    header("Location: trangchu.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bds = $_POST['id_bds'] ?? '';
    $so_tien = $_POST['so_tien'] ?? '';
    $phuong_thuc = $_POST['phuong_thuc'] ?? '';

    if ($id_bds && $so_tien && $phuong_thuc) {
        $sql = "INSERT INTO thanh_toan (id_nguoi_dung, id_bds, so_tien, phuong_thuc, ngay_thanh_toan) 
                VALUES (:id_nguoi_dung, :id_bds, :so_tien, :phuong_thuc, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_nguoi_dung' => $id_nguoi_dung,
            ':id_bds' => $id_bds,
            ':so_tien' => $so_tien,
            ':phuong_thuc' => $phuong_thuc
        ]);
        $message = "Thanh toán thành công!";
    } else {
        $message = "Vui lòng điền đầy đủ thông tin!";
    }
}

// Lấy danh sách BĐS của môi giới
$stmt = $pdo->prepare("SELECT id, tieu_de FROM bat_dong_san WHERE id_nguoi_dung = :id_nguoi_dung");
$stmt->execute([':id_nguoi_dung' => $id_nguoi_dung]);
$ds_bds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4">Thanh toán Bất động sản</h2>
    
    <?php if ($message): ?>
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label class="block mb-2 text-sm font-medium">Chọn BĐS:</label>
        <select name="id_bds" class="w-full mb-4 p-2 border rounded">
            <option value="">-- Chọn BĐS --</option>
            <?php foreach ($ds_bds as $bds): ?>
                <option value="<?= $bds['id'] ?>"><?= htmlspecialchars($bds['tieu_de']) ?></option>
            <?php endforeach; ?>
        </select>

        <label class="block mb-2 text-sm font-medium">Số tiền (VNĐ):</label>
        <input type="number" name="so_tien" class="w-full mb-4 p-2 border rounded" required>

        <label class="block mb-2 text-sm font-medium">Phương thức thanh toán:</label>
        <select name="phuong_thuc" class="w-full mb-4 p-2 border rounded" required>
            <option value="">-- Chọn phương thức --</option>
            <option value="Chuyển khoản">Chuyển khoản</option>
            <option value="Tiền mặt">Tiền mặt</option>
            <option value="Ví điện tử">Ví điện tử</option>
        </select>

        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Thanh toán</button>
    </form>
</div>
