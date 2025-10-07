<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Lấy id tin từ GET
$id_tin = $_GET['id_tin'] ?? '';

if (!$id_tin) {
    echo "Không tìm thấy tin đăng!";
    exit;
}

// Lấy danh sách khách hàng quan tâm
$sql = "
    SELECT kh.ho_ten, kh.email, kqt.ngay_tao
    FROM khach_quan_tam kqt
    JOIN khach_hang kh ON kqt.id_khach_hang = kh.id
    WHERE kqt.id_tin = :id_tin
    ORDER BY kqt.ngay_tao DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id_tin' => $id_tin]);
$khachhang = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khách hàng quan tâm</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-100">

<h1 class="text-2xl font-bold mb-4">Khách hàng quan tâm tin: <?= htmlspecialchars($id_tin) ?></h1>

<table class="w-full border-collapse bg-white shadow rounded">
    <thead>
        <tr class="bg-gray-200 text-gray-700">
            <th class="p-3 text-left">Họ tên</th>
            <th class="p-3 text-left">Email</th>
            <th class="p-3 text-left">Ngày quan tâm</th>
        </tr>
    </thead>
    <tbody>
        <?php if($khachhang): ?>
            <?php foreach($khachhang as $kh): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3"><?= htmlspecialchars($kh['ho_ten']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($kh['email']) ?></td>
                    <td class="p-3"><?= date("d/m/Y H:i", strtotime($kh['ngay_tao'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" class="p-3 text-center text-gray-500">Chưa có khách hàng quan tâm</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
