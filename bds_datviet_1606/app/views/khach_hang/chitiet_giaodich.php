<?php
session_start();
if (empty($_SESSION['id_nguoi_dung'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function vn_money($n){ return number_format((int)$n,0,',','.'); }

$txId = (string)($_GET['id'] ?? ''); // Lấy ID Giao Dịch
$uid  = (string)($_SESSION['id_nguoi_dung'] ?? '');

if (empty($txId) || empty($uid)) {
    die('Không tìm thấy giao dịch.');
}

// Lấy thông tin giao dịch
$sqlTx = "SELECT id, so_tien, noi_dung, ma, token, trang_thai,
                 EXTRACT(EPOCH FROM het_han_luc)::bigint AS exp_ts
          FROM giao_dich
          WHERE id = :id AND id_nguoi_dung = :u
          LIMIT 1";
$stTx = $pdo->prepare($sqlTx);
$stTx->execute([':id' => $txId, ':u' => $uid]);
$tx = $stTx->fetch(PDO::FETCH_ASSOC);

if (!$tx) {
    die('Không tìm thấy giao dịch hoặc giao dịch không thuộc về bạn.');
}

// Nếu giao dịch đã hết hạn hoặc hoàn thành
if ($tx['trang_thai'] !== 'pending' || $tx['exp_ts'] <= time()) {
    // Chuyển về trang lịch sử
    header('Location: duan.php?state=' . $tx['trang_thai']);
    exit;
}

$exp_ts = (int)$tx['exp_ts'];
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thanh toán Giao dịch</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6 mt-8 text-center">
  <h2 class="text-2xl font-bold mb-2">Thanh toán Giao dịch</h2>
  
  <div class="text-sm text-gray-600 mb-4">
    <?php if ($exp_ts > time()): ?>
        Giao dịch sẽ hết hạn sau: 
        <b class="text-lg text-red-600"><span id="countdown">--:--</span></b>
    <?php endif; ?>
  </div>

  <div class="text-left text-sm text-gray-700 space-y-2 mb-4">
      <div>Mã giao dịch: <b class="text-base"><?= e($tx['ma']) ?></b></div>
      <div>Số tiền: <b class="text-xl text-red-600 font-bold"><?= vn_money($tx['so_tien']) ?> đ</b></div>
      <div class="p-2 bg-gray-100 rounded">
          <div>Nội dung chuyển khoản:</div>
          <code class="text-base font-bold text-indigo-700 break-all"><?= e($tx['noi_dung']) ?></code>
      </div>
      <div class="text-xs text-red-500">
          (Vui lòng chuyển khoản chính xác số tiền và nội dung trên)
      </div>
  </div>
  
  <img src="<?= e($tx['token']) ?>" alt="VietQR" class="w-72 max-w-full mx-auto border rounded-lg bg-white mb-4">
  
  <div class="space-x-2">
      <a class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" 
         href="duan.php?state=pending">
         Xem đơn đang chờ
      </a>
      <a class="px-4 py-2 border rounded hover:bg-gray-100" 
         href="duan.php">
         « Lịch sử giao dịch
      </a>
  </div>
</div>

<?php if ($exp_ts > time()): ?>
<script>
(function(){
  const exp = <?= $exp_ts ?>;
  const countdownEl = document.getElementById('countdown');
  
  function tick() {
    const now = Math.floor(Date.now() / 1000);
    let s = Math.max(0, exp - now);
    
    if (s === 0) {
      if(countdownEl) countdownEl.textContent = '00:00';
      // Tự động tải lại trang khi hết giờ (sẽ bị chuyển hướng về lịch sử)
      location.reload(); 
      return;
    }
    
    const m = Math.floor(s / 60);
    s = s % 60;
    
    if (countdownEl) {
      countdownEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }
  }
  
  tick();
  setInterval(tick, 1000);
})();
</script>
<?php endif; ?>

</body>
</html>