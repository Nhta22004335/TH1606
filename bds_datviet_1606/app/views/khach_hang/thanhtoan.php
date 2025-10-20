<?php
session_start();
if (empty($_SESSION['id_nguoi_dung'])) { header('Location: /login.php'); exit; }

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();
$cfg = require __DIR__ . '/../../../config/payment.php';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function vn_money($n){ return number_format((int)$n,0,',','.'); }

// dọn pending hết hạn
$pdo->exec("UPDATE giao_dich SET trang_thai='failed'
            WHERE trang_thai='pending' AND het_han_luc IS NOT NULL AND het_han_luc<NOW()");

$type  = $_GET['type'] ?? 'bds_buy';
$refId = (string)($_GET['id'] ?? '');
if ($type !== 'bds_buy' || $refId <= 0) { header('Location: /app/views/khach_hang/duan.php'); exit; }

// bài đăng
$st = $pdo->prepare("SELECT id,tieu_de,gia FROM bai_dang WHERE id=:id LIMIT 1");
$st->execute([':id'=>$refId]);
$post = $st->fetch(PDO::FETCH_ASSOC);
if (!$post) { die('Không tìm thấy bài đăng.'); }

$uid = (string)($_SESSION['id_nguoi_dung'] ?? '');

// giao dịch pending còn hạn
$sqlTx = "SELECT id,so_tien,noi_dung,ma,token,
                 EXTRACT(EPOCH FROM het_han_luc)::bigint AS exp_ts
          FROM giao_dich
          WHERE id_nguoi_dung =:u
            AND loai=:t
            AND ref_id=(:r)::bigint
            AND trang_thai='pending' AND het_han_luc>NOW()
          ORDER BY tao_luc DESC
          LIMIT 1";
$stTx = $pdo->prepare($sqlTx);
$stTx->execute([':u'=>$uid, ':t'=>'bds_buy', ':r'=>$refId]);
$tx = $stTx->fetch(PDO::FETCH_ASSOC);

// tạo mới nếu chưa có hoặc yêu cầu tạo lại
if (!$tx || (isset($_GET['create']) && $_GET['create']==='1')) {
    $amount = (int)$post['gia'];
    if ($amount<=0) { die('Giá không hợp lệ.'); }
    $order = (string)(floor(microtime(true)*1000));
    $noi   = ($cfg['content_prefix'] ?? 'Thanh toán').' '.$post['tieu_de'].' - '.$order;

    $v  = $cfg['vietqr'];
    $qr = "https://img.vietqr.io/image/".rawurlencode($v['bank_code'])."-".rawurlencode($v['account'])."-qr_only.png"
         ."?amount=".$amount."&addInfo=".rawurlencode($noi)
         ."&accountName=".rawurlencode($v['account_name']);
    $ttl = (int)($cfg['order_timeout_sec'] ?? 600);

    $ins = $pdo->prepare("INSERT INTO giao_dich
        (id_nguoi_dung,loai,ref_id,ma,so_tien,noi_dung,phuong_thuc,trang_thai,
         tao_luc,het_han_luc,provider,provider_txn_id,token)
        VALUES (:u,'bds_buy',(:r)::bigint,:ma,:amt,:noi,'vietqr','pending',
                NOW(),NOW()+(:ttl || ' seconds')::interval,'vietqr',:ma,:qr)");
    $ins->execute([
        ':u'=>$uid, ':r'=>$refId, ':ma'=>$order, ':amt'=>$amount,
        ':noi'=>$noi, ':ttl'=>$ttl, ':qr'=>$qr
    ]);

    


    $stTx->execute([':u'=>$uid, ':t'=>'bds_buy', ':r'=>$refId]);
    $tx = $stTx->fetch(PDO::FETCH_ASSOC);
}

$exp_ts = isset($tx['exp_ts']) ? (int)$tx['exp_ts'] : 0;
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8"><title>Thanh toán</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6 mt-8 text-center">
  <h2 class="text-2xl font-bold mb-2">Thanh toán BĐS</h2>
  <div class="text-sm text-gray-600 mb-4">
    BĐS: <b><?= e($post['tieu_de']) ?></b> • ID: <?= (int)$post['id'] ?>
    <?php if($exp_ts>time()): ?> • Còn <b><span id="countdown">--:--</span></b><?php endif; ?>
  </div>

  <?php if(!$tx): ?>
    <div class="text-gray-600 my-6">Chưa có giao dịch chờ.</div>
    <a class="px-4 py-2 bg-indigo-600 text-white rounded"
       href="thanhtoan.php?type=bds_buy&id=<?= (int)$post['id'] ?>&create=1">Tạo giao dịch</a>
  <?php else: ?>
    <div class="text-left text-sm text-gray-700 space-y-1 mb-3">
      <div>Mã giao dịch: <b><?= e($tx['ma']) ?></b></div>
      <div>Số tiền: <b><?= vn_money($tx['so_tien']) ?></b> đ</div>
      <div>Nội dung CK: <code><?= e($tx['noi_dung']) ?></code></div>
    </div>
    <img src="<?= e($tx['token']) ?>" alt="VietQR" class="w-72 max-w-full mx-auto border rounded-lg bg-white mb-4">
    <div class="space-x-2">
      <a class="px-4 py-2 bg-indigo-600 text-white rounded" href="duan.php?state=pending">Xem đơn đang chờ</a>
      <a class="px-4 py-2 border rounded" href="duan.php">« Lịch sử</a>
    </div>
  <?php endif; ?>
</div>

<?php if($exp_ts>time()): ?>
<script>
(function(){
  const exp = <?= $exp_ts ?>;
  function tick(){
    const now = Math.floor(Date.now()/1000);
    let s = Math.max(0, exp-now);
    const m = Math.floor(s/60); s = s%60;
    const el = document.getElementById('countdown');
    if(el) el.textContent = (m<10?'0':'')+m+':'+(s<10?'0':'')+s;
    if(exp<=now) location.reload();
  }
  tick(); setInterval(tick,1000);
})();
</script>
<?php endif; ?>
</body>
</html>
