<?php
require_once __DIR__ . "/../../../config/database.php"; // chuẩn đường dẫn

$pdo = ketnoicsdl();

// đảm bảo session chỉ mở 1 lần
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// giả sử id môi giới đang đăng nhập đã lưu vào session
$id_mg = $_SESSION["id_nguoi_dung"] ?? null;

if (!$id_mg) {
    echo "<div class='alert alert-danger'>Vui lòng đăng nhập!</div>";
    exit;
}

// Lấy danh sách thông báo cho môi giới này
$sql = "SELECT * FROM thong_bao WHERE id_nguoi_dung = :id_mg";
$stmt = $pdo->prepare($sql);
$stmt->execute(["id_mg" => $id_mg]);
$thongbaos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý thông báo - Môi giới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h3 class="mb-4 text-primary">📢 Quản lý thông báo</h3>

    <?php if (!empty($thongbaos)): ?>
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-secondary">
                <tr>
                    <th>#</th>
                    <th>Nội dung</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($thongbaos as $i => $tb): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($tb["noi_dung"]) ?></td>
                    <td><?= $tb["ngay_tao"] ?></td>
                    <td>
                        <?php if ($tb["trang_thai"] === "chua_doc"): ?>
                            <span class="badge bg-danger">Chưa đọc</span>
                        <?php else: ?>
                            <span class="badge bg-success">Đã đọc</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Không có thông báo nào!</div>
    <?php endif; ?>
</div>
</body>
</html>
