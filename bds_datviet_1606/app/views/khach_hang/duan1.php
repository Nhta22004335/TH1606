<?php
session_start();
if (empty($_SESSION['id_nguoi_dung'])) { header('Location: /login.php'); exit; }

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function money_vn($n){ return number_format((int)$n,0,',','.'); }

// dọn rác: auto hết hạn
$pdo->exec("UPDATE giao_dich SET trang_thai='failed'
            WHERE trang_thai='pending' AND het_han_luc IS NOT NULL AND het_han_luc<NOW()");

$uid   = $_SESSION['id_nguoi_dung'];
$state = $_GET['state'] ?? 'all';
$allow = ['all','pending','paid','canceled','failed'];
if(!in_array($state,$allow,true)) $state='all';

$where = "gd.id_nguoi_dung=:u";
$args  = [':u'=>$uid];
if($state!=='all'){ $where .= " AND gd.trang_thai=:s"; $args[':s']=$state; }

$sql = "SELECT gd.*, p.tieu_de
        FROM giao_dich gd
        LEFT JOIN bai_dang p ON p.id = gd.ref_id
        WHERE $where
        ORDER BY gd.id DESC";
$st = $pdo->prepare($sql); $st->execute($args); $rows = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html lang="vi"><head>
<meta charset="utf-8"><title>Lịch sử giao dịch</title>
<script src="https://cdn.tailwindcss.com"></script>
</head><body class="bg-gray-100">
<div class="max-w-5xl mx-auto mt-8 bg-white shadow rounded-lg p-6">
  <h2 class="text-2xl font-bold mb-4">Lịch sử giao dịch</h2>

  <div class="mb-4 space-x-2">
    <?php
      $chips=['all'=>'Tất cả','pending'=>'Chờ thanh toán','paid'=>'Đã thanh toán','canceled'=>'Đã hủy','failed'=>'Hết hạn/Lỗi'];
      foreach($chips as $k=>$lbl){
        $act = $state===$k ? 'bg-indigo-600 text-white' : 'border';
        echo '<a class="px-3 py-1 rounded '.$act.'" href="duan.php?state='.$k.'">'.$lbl.'</a>';
      }
    ?>
  </div>

  <?php if(!$rows): ?>
    <div class="text-gray-600">Không có bản ghi.</div>
  <?php else: ?>
  <div class="overflow-auto">
    <table class="min-w-full border">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-2 border">BĐS</th>
          <th class="text-left p-2 border">Mã</th>
          <th class="text-left p-2 border">Số tiền</th>
          <th class="text-left p-2 border">Trạng thái</th>
          <th class="text-left p-2 border">Hết hạn</th>
          <th class="text-left p-2 border">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr class="border-t">
          <td class="p-2 border"><?= e($r['tieu_de']) ?: '—' ?></td>
          <td class="p-2 border"><?= e($r['ma']) ?></td>
          <td class="p-2 border"><?= money_vn($r['so_tien']) ?> đ</td>
          <td class="p-2 border"><?= e($r['trang_thai']) ?></td>
          <td class="p-2 border"><?= $r['het_han_luc'] ? date('d/m/Y H:i', strtotime($r['het_han_luc'])) : '—' ?></td>
          <td class="p-2 border space-x-2">
            <?php if ($r['trang_thai']==='pending'): ?>
              <a class="px-3 py-1 bg-indigo-600 text-white rounded"
                 href="thanhtoan.php?type=<?= e($r['loai']) ?>&id=<?= (int)$r['ref_id'] ?>">
                Thanh toán
              </a>
              <form method="post" action="huy_gd.php" style="display:inline"
                    onsubmit="return confirm('Hủy giao dịch này?');">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="px-3 py-1 border rounded" type="submit">Hủy</button>
              </form>
            <?php else: ?>—
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
</body></html>
