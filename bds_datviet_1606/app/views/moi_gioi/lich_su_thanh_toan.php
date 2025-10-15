<?php
require_once "../../../config/database.php";
session_start();

$pdo = ketnoicsdl();
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;

if (!$id_nguoi_dung) {
    header("Location: trangchu.php");
    exit;
}

// Lấy lịch sử thanh toán
$stmt = $pdo->prepare("
    SELECT tt.*, b.tieu_de 
    FROM thanh_toan tt
    JOIN bat_dong_san b ON tt.id_bds = b.id
    WHERE tt.id_nguoi_dung = :id_nguoi_dung
    ORDER BY tt.ngay_thanh_toan DESC
");
$stmt->execute([':id_nguoi_dung' => $id_nguoi_dung]);
$lich_su = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4">Lịch sử thanh toán</h2>

    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-3 py-2 text-left">BĐS</th>
                <th class="border px-3 py-2">Số tiền</th>
                <th class="border px-3 py-2">Phương thức</th>
                <th class="border px-3 py-2">Ngày</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lich_su as $item): ?>
                <tr>
                    <td class="border px-3 py-2"><?= htmlspecialchars($item['tieu_de']) ?></td>
                    <td class="border px-3 py-2 text-right"><?= number_format($item['so_tien']) ?></td>
                    <td class="border px-3 py-2"><?= htmlspecialchars($item['phuong_thuc']) ?></td>
                    <td class="border px-3 py-2"><?= date('d/m/Y H:i', strtotime($item['ngay_thanh_toan'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
