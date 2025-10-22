<?php
// app/views/khach_hang/dvmg.php
require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();

$sql = "
  SELECT nd.id, nd.ten_dang_nhap, nd.email, nd.so_dt, nd.avt,
         ind.ho_ten, ind.mo_ta, ind.dia_chi
  FROM nguoi_dung nd
  JOIN info_nguoi_dung ind ON nd.id = ind.id_nguoi_dung
  JOIN phan_quyen pq ON pq.id_nguoi_dung = nd.id
  JOIN quyen q ON q.id = pq.id_quyen
  WHERE nd.trang_thai = 'danghoatdong'
    AND (
      q.vai_tro IN ('moigioi','moi_gioi')
      OR q.vai_tro ILIKE '%môi%'
    )
  GROUP BY nd.id, ind.ho_ten, ind.mo_ta, ind.dia_chi
  ORDER BY ind.ho_ten ASC
";
$dsMoigioi = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi"><head>
<meta charset="UTF-8"><title>Dịch vụ Môi giới</title>
<style>
body{font-family:Segoe UI, sans-serif;background:#f8f9fa;margin:0;padding:20px}
h1{text-align:center;color:#222;margin-bottom:30px}
.container{display:flex;flex-wrap:wrap;justify-content:center;gap:25px}
.card{width:260px;background:#fff;border-radius:12px;box-shadow:0 2px 6px rgba(0,0,0,.15);overflow:hidden;text-align:center;transition:.3s}
.card:hover{transform:translateY(-5px);box-shadow:0 4px 10px rgba(0,0,0,.2)}
.card img{width:100%;height:200px;object-fit:cover}
.info{padding:15px}
.info h3{margin:10px 0;color:#007bff}
.info p{margin:4px 0;color:#555;font-size:14px}
.info em{color:#666;font-style:italic}
.no-data{text-align:center;font-size:18px;color:#777;margin-top:40px}
</style></head>

<body>
<h1>Dịch vụ Môi giới</h1>
<a href="/app/views/khach_hang/trangchu.php"
   style="display:inline-block;margin:10px 0 20px;padding:8px 12px;border:1px solid #ddd;border-radius:8px;color:#333;text-decoration:none;">
  ← Về trang chủ
</a>
<?php if ($dsMoigioi): ?>
  <div class="container">
    <?php foreach ($dsMoigioi as $mg): ?>
      <div class="card">
        <img src="../../../storage/pictures/avt/<?= htmlspecialchars($mg['avt']) ?>" alt="Ảnh đại diện">
        <div class="info">
          <h3><?= htmlspecialchars($mg['ho_ten']) ?></h3>
          <p><strong>Email:</strong> <?= htmlspecialchars($mg['email']) ?></p>
          <p><strong>SĐT:</strong> <?= htmlspecialchars($mg['so_dt']) ?></p>
          <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($mg['dia_chi']) ?></p>
          <p><em><?= htmlspecialchars($mg['mo_ta']) ?></em></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="no-data">Chưa có môi giới nào hoạt động.</div>
<?php endif; ?>
<?php include __DIR__ . '/../partials/foot_ai.php'; ?>
</body></html>
