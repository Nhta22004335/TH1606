<?php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

try {
    // ✅ Lấy danh sách người dùng có vai trò "môi giới"
    $sql = "
        SELECT 
            nd.id,
            nd.ten_dang_nhap,
            nd.email,
            nd.so_dt,
            nd.avt,
            ind.ho_ten,
            ind.mo_ta,
            ind.dia_chi
        FROM nguoi_dung nd
        JOIN info_nguoi_dung ind ON nd.id = ind.id_nguoi_dung
        WHERE nd.trang_thai = 'danghoatdong'
        ORDER BY ind.ho_ten ASC
    ";
    $stmt = $pdo->query($sql);
    $dsMoigioi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dịch vụ Môi giới</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #222;
            margin-bottom: 30px;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
        }
        .card {
            width: 260px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            overflow: hidden;
            text-align: center;
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .info {
            padding: 15px;
        }
        .info h3 {
            margin: 10px 0;
            color: #007bff;
        }
        .info p {
            margin: 4px 0;
            color: #555;
            font-size: 14px;
        }
        .info em {
            color: #666;
            font-style: italic;
        }
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #777;
            margin-top: 40px;
        }
    </style>
</head>
<body>

    <h1>Dịch vụ Môi giới</h1>

    <?php if (count($dsMoigioi) > 0): ?>
        <div class="container">
            <?php foreach ($dsMoigioi as $mg): ?>
                <div class="card">
                    <img src="../../../public/uploads/<?php echo htmlspecialchars($mg['avt']); ?>" alt="Ảnh đại diện">
                    <div class="info">
                        <h3><?php echo htmlspecialchars($mg['ho_ten']); ?></h3>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($mg['email']); ?></p>
                        <p><strong>SĐT:</strong> <?php echo htmlspecialchars($mg['so_dt']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($mg['dia_chi']); ?></p>
                        <p><em><?php echo htmlspecialchars($mg['mo_ta']); ?></em></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-data">Chưa có môi giới nào hoạt động.</div>
    <?php endif; ?>

</body>
</html>
