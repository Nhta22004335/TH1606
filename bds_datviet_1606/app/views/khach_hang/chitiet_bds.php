<?php
// app/views/khach_hang/chitiet_bds.php
// Hiển thị chi tiết bất động sản + nút Mua / Thuê (modal form)

// KẾT NỐI DB
require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();

// helper escape
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Lấy ID từ GET và validate
$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit('Yêu cầu không hợp lệ (thiếu id).');
}

try {
    // 1) Lấy product + agent
    $sql = "
        SELECT b.*, u.ten_dang_nhap, u.email, u.so_dt, u.avt AS avt_moigioi
        FROM bat_dong_san b
        JOIN nguoi_dung u ON u.id = b.id_nguoi_dung
        WHERE b.id = :id
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        exit('Không tìm thấy tin bất động sản.');
    }

    // 2) Ảnh sản phẩm
    $stmt = $pdo->prepare("SELECT url FROM hinh_anh_bds WHERE id_bds = :id ORDER BY ngay_tao ASC");
    $stmt->execute([':id' => $id]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($images)) $images = ['chuacapnhat.jpg'];

    // 3) Đánh giá trung bình & số lượng
    $stmt = $pdo->prepare("SELECT AVG(diem) AS avg_score, COUNT(*) AS cnt FROM danh_gia_bds WHERE id_bds = :id");
    $stmt->execute([':id' => $id]);
    $ratingInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $avgScore = $ratingInfo['avg_score'] ? round((float)$ratingInfo['avg_score'], 1) : 0;
    $ratingCount = (int)($ratingInfo['cnt'] ?? 0);

    // CSRF token nhẹ (session-based)
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    $csrf = $_SESSION['csrf_token'];

} catch (PDOException $ex) {
    error_log($ex->getMessage());
    http_response_code(500);
    exit('Lỗi server, thử lại sau.');
}

// format price helper
function format_price_vietnamese($price) {
    if ($price === null || $price === 0) return 'Thỏa thuận';
    $price = (float)$price;
    if ($price >= 1000000000) {
        $r = $price / 1000000000;
        return rtrim(rtrim(number_format($r, 2, ',', ''), '0'), ',') . ' tỷ';
    } elseif ($price >= 1000000) {
        $r = $price / 1000000;
        return number_format($r, 0, ',', '.') . ' triệu';
    } else {
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($product['tieu_de']) ?> — Chi tiết</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-6xl mx-auto py-8 px-4">

  <!-- Back -->
  <a href="/app/views/khach_hang/nhao.php" class="text-sm text-blue-600 hover:underline inline-block mb-4">&larr; Quay lại</a>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- LEFT: images + description (col-span 2) -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
      <!-- Carousel main -->
      <div class="w-full h-80 bg-gray-200 rounded overflow-hidden mb-3 flex items-center justify-center">
        <img id="mainImg" src="/storage/bds/<?= e($images[0]) ?>" alt="img" class="w-full h-full object-cover">
      </div>

      <!-- thumbnails -->
      <div class="flex gap-2 overflow-x-auto mb-4">
        <?php foreach ($images as $i => $img): ?>
          <button class="thumb focus:outline-none" data-src="/storage/bds/<?= e($img) ?>">
            <img src="/storage/bds/<?= e($img) ?>" alt="thumb<?= $i ?>" class="w-24 h-16 object-cover rounded border <?= $i===0 ? 'ring-2 ring-blue-500' : '' ?>">
          </button>
        <?php endforeach; ?>
      </div>

      <h1 class="text-2xl font-bold mb-2"><?= e($product['tieu_de']) ?></h1>
      <div class="flex items-center gap-4 text-sm text-gray-600 mb-4">
        <div><i class="fas fa-map-marker-alt text-red-500 mr-1"></i><?= e($product['khu_vuc'] . ($product['dia_chi'] ? ' - ' . $product['dia_chi'] : '')) ?></div>
        <div>•</div>
        <div><?= e($product['dien_tich']) ?> m²</div>
        <div>•</div>
        <div><?= date('d/m/Y', strtotime($product['ngay_dang'] ?? date('Y-m-d'))) ?></div>
      </div>

      <div class="text-xl font-extrabold text-red-600 mb-4"><?= format_price_vietnamese($product['gia']) ?></div>

      <div class="pro-desc text-gray-700 whitespace-pre-line"><?= e($product['mo_ta'] ?? 'Chưa có mô tả') ?></div>

      <!-- Ratings -->
      <div class="mt-6">
        <div class="flex items-center gap-3">
          <div class="text-yellow-400">
            <?php for ($i=1;$i<=5;$i++): ?>
              <i class="<?= $i <= round($avgScore) ? 'fas' : 'far' ?> fa-star"></i>
            <?php endfor; ?>
          </div>
          <div class="text-sm text-gray-600"><?= $avgScore ?> (<?= $ratingCount ?> đánh giá)</div>
        </div>
      </div>
    </div>

    <!-- RIGHT: agent + actions -->
    <aside class="bg-white rounded-lg shadow p-4 flex flex-col gap-4">
      <div class="flex items-center gap-3">
        <img src="/storage/pictures/avt/<?= e($product['avt_moigioi'] ?? 'avt.png') ?>" alt="avatar" class="w-16 h-16 rounded-full object-cover border">
        <div>
          <div class="font-semibold"><?= e($product['ten_dang_nhap']) ?></div>
          <div class="text-sm text-gray-500">Môi giới</div>
        </div>
      </div>

      <div class="text-sm text-gray-700">
        <div class="mb-1"><strong>Điện thoại:</strong> <a class="text-green-600 font-medium" href="tel:<?= e($product['so_dt']) ?>"><?= e($product['so_dt']) ?></a></div>
        <div class="mb-1"><strong>Email:</strong> <a href="mailto:<?= e($product['email']) ?>" class="text-blue-600"><?= e($product['email']) ?></a></div>
      </div>

      <!-- Actions -->
      <div class="mt-4 space-y-2">
        <button id="btnBuy" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded font-semibold">Mua ngay</button>
        <button id="btnRent" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded font-semibold">Đặt thuê</button>
        <a href="trangchu.php?page=khach_hang/chitiet_moigioi&id=<?= e($product['id_nguoi_dung']) ?>" class="block w-full text-center py-2 border rounded text-sm hover:bg-gray-50">Xem hồ sơ môi giới</a>
      </div>

      <div class="text-xs text-gray-500 pt-3 border-t">
        <div>Mã tin: <span class="font-mono"><?= e($product['id']) ?></span></div>
      </div>
    </aside>

  </div>
</div>

<!-- BUY modal -->
<div id="modalBuy" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg w-full max-w-md p-6">
    <h3 class="text-lg font-semibold mb-3">Đặt mua: <?= e($product['tieu_de']) ?></h3>
    <form id="formBuy" method="POST" action="/app/models/dat_mua.php">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="id_bds" value="<?= e($product['id']) ?>">
      <div class="mb-2">
        <label class="block text-sm">Họ tên</label>
        <input name="ten" required class="w-full border px-3 py-2 rounded" />
      </div>
      <div class="mb-2">
        <label class="block text-sm">SĐT</label>
        <input name="sdt" required class="w-full border px-3 py-2 rounded" />
      </div>
      <div class="mb-2">
        <label class="block text-sm">Ghi chú (tuỳ chọn)</label>
        <textarea name="ghi_chu" class="w-full border px-3 py-2 rounded" rows="3"></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" class="px-4 py-2 rounded border" onclick="closeModal('modalBuy')">Hủy</button>
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Gửi yêu cầu mua</button>
      </div>
    </form>
  </div>
</div>

<!-- RENT modal -->
<div id="modalRent" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg w-full max-w-md p-6">
    <h3 class="text-lg font-semibold mb-3">Đặt thuê: <?= e($product['tieu_de']) ?></h3>
    <form id="formRent" method="POST" action="/app/models/dat_thue.php">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="id_bds" value="<?= e($product['id']) ?>">
      <div class="mb-2">
        <label class="block text-sm">Họ tên</label>
        <input name="ten" required class="w-full border px-3 py-2 rounded" />
      </div>
      <div class="mb-2">
        <label class="block text-sm">SĐT</label>
        <input name="sdt" required class="w-full border px-3 py-2 rounded" />
      </div>
      <div class="mb-2">
        <label class="block text-sm">Thời gian thuê (tháng)</label>
        <input name="thoi_gian" type="number" min="1" value="12" class="w-full border px-3 py-2 rounded" />
      </div>
      <div class="mb-2">
        <label class="block text-sm">Ghi chú (tuỳ chọn)</label>
        <textarea name="ghi_chu" class="w-full border px-3 py-2 rounded" rows="3"></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" class="px-4 py-2 rounded border" onclick="closeModal('modalRent')">Hủy</button>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Gửi yêu cầu thuê</button>
      </div>
    </form>
  </div>
</div>

<script>
// thumbnail -> main image
document.querySelectorAll('.thumb').forEach(btn => {
  btn.addEventListener('click', () => {
    const src = btn.getAttribute('data-src');
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.thumb img').forEach(i => i.classList.remove('ring-2','ring-blue-500'));
    btn.querySelector('img').classList.add('ring-2','ring-blue-500');
  });
});

// modal functions
function openModal(id){
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('hidden');
  el.classList.add('flex');
}
function closeModal(id){
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('flex');
  el.classList.add('hidden');
}

document.getElementById('btnBuy').addEventListener('click', ()=> openModal('modalBuy'));
document.getElementById('btnRent').addEventListener('click', ()=> openModal('modalRent'));
</script>

</body>
</html>
