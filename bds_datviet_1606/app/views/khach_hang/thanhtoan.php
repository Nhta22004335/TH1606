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

// Lấy ID bài đăng (UUID)
$refId = (string)($_GET['id'] ?? '');
$type  = $_GET['type'] ?? 'bds_buy';

if ($type !== 'bds_buy' || empty($refId)) {
    header('Location: /app/views/khach_hang/duan.php');
    exit;
}

// Lấy thông tin bài đăng VÀ bất động sản
$sqlPost = "SELECT 
                bd.id AS bai_dang_id, 
                bd.tieu_de, 
                bd.gia,
                bds.dia_chi_day_du
            FROM bai_dang AS bd
            JOIN bat_dong_san AS bds ON bd.id_bat_dong_san = bds.id
            WHERE bd.id = :id
            LIMIT 1";
$st = $pdo->prepare($sqlPost);
$st->execute([':id' => $refId]);
$post = $st->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('Không tìm thấy bài đăng.');
}

// Lấy thông tin người dùng (để điền sẵn vào form)
$uid = (string)$_SESSION['id_nguoi_dung'];
$stUser = $pdo->prepare("SELECT  email, so_dt,ten_dang_nhap FROM nguoi_dung WHERE id = :uid LIMIT 1");
$stUser->execute([':uid' => $uid]);
$user = $stUser->fetch(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Xác nhận thanh toán</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-6 mt-8">
  <h2 class="text-2xl font-bold mb-4 text-center">Xác nhận thông tin Thanh toán</h2>

  <div class="mb-6 border-b pb-4">
    <h3 class="text-lg font-semibold text-indigo-700"><?= e($post['tieu_de']) ?></h3>
    <div class="text-sm text-gray-600 mt-1">
        <b>Địa chỉ:</b> <?= e($post['dia_chi_day_du']) ?>
    </div>
    <div class="text-2xl font-bold text-red-600 mt-2">
        <?= vn_money($post['gia']) ?> VNĐ
    </div>
  </div>

  <form action="tao_giaodich.php" method="POST">
    <input type="hidden" name="ref_id" value="<?= e($post['bai_dang_id']) ?>">
    <input type="hidden" name="type" value="bds_buy">

    <h3 class="text-lg font-semibold mb-3">Thông tin người mua</h3>
    <div class="space-y-4">
        <div>
            <label for="ho_ten" class="block text-sm font-medium text-gray-700">Họ và tên</label>
            <input type="text" name="ho_ten" id="ho_ten" required
                   value="<?= e($user['ho_ten'] ?? '') ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" required
                   value="<?= e($user['email'] ?? '') ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="so_dien_thoai" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
            <input type="tel" name="so_dien_thoai" id="so_dien_thoai" required
                   value="<?= e($user['so_dien_thoai'] ?? '') ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    <h3 class="text-lg font-semibold mt-6 mb-3">Chọn phương thức thanh toán</h3>
    <div class="space-y-2">
        <label class="flex items-center p-3 border rounded-md has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-400">
            <input type="radio" name="payment_method" value="vietqr" class="h-4 w-4 text-indigo-600" checked>
            <span class="ml-3 font-medium">Chuyển khoản Ngân hàng (VietQR)</span>
        </label>
        <label class="flex items-center p-3 border rounded-md text-gray-400 cursor-not-allowed">
            <input type="radio" name="payment_method" value="momo" class="h-4 w-4" disabled>
            <span class="ml-3">Ví Momo (Sắp ra mắt)</span>
        </label>
        <label class="flex items-center p-3 border rounded-md text-gray-400 cursor-not-allowed">
            <input type="radio" name="payment_method" value="vnpay" class="h-4 w-4" disabled>
            <span class="ml-3">VNPay (Sắp ra mắt)</span>
        </label>
    </div>

    <div class="mt-8 text-center">
        <button type="submit"
                class="w-full max-w-xs px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700">
            Tiếp tục thanh toán
        </button>
    </div>
  </form>

</div>

</body>
</html>