<?php
session_start();
// Yêu cầu đăng nhập
if (empty($_SESSION['id_nguoi_dung'])) {
    header('Location: /login.php');
    exit;
}

// Chỉ chấp nhận phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Phương thức không hợp lệ.');
}

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();
$cfg = require __DIR__ . '/../../../config/payment.php';

// Lấy dữ liệu từ FORM
$refId         = (string)($_POST['ref_id'] ?? '');
$type          = (string)($_POST['type'] ?? '');
$paymentMethod = (string)($_POST['payment_method'] ?? '');
$uid           = (string)($_SESSION['id_nguoi_dung'] ?? '');

// Kiểm tra dữ liệu cơ bản
if ($type !== 'bds_buy' || empty($refId) || empty($uid) || $paymentMethod !== 'vietqr') {
    die('Dữ liệu không hợp lệ hoặc phương thức thanh toán chưa được hỗ trợ.');
}

// 1. Kiểm tra xem đã có giao dịch pending còn hạn không
$sqlFind = "SELECT id FROM giao_dich
            WHERE id_nguoi_dung = :u AND loai = :t AND ref_id = :r
            AND trang_thai = 'pending' AND het_han_luc > NOW()
            ORDER BY tao_luc DESC LIMIT 1";
$stFind = $pdo->prepare($sqlFind);
$stFind->execute([':u' => $uid, ':t' => $type, ':r' => $refId]);
$existingTx = $stFind->fetch(PDO::FETCH_ASSOC);

// Nếu có, chuyển thẳng đến trang chi tiết giao dịch đó
if ($existingTx) {
    header('Location: chitiet_giaodich.php?id=' . $existingTx['id']);
    exit;
}

// 2. Nếu không có, tạo giao dịch mới
// Lấy thông tin giá tiền
$stPost = $pdo->prepare("SELECT gia FROM bai_dang WHERE id = :id");
$stPost->execute([':id' => $refId]);
$post = $stPost->fetch(PDO::FETCH_ASSOC);

$amount = (int)($post['gia'] ?? 0);
if ($amount <= 0) {
    die('Giá không hợp lệ.');
}

// Tạo mã đơn hàng, nội dung...
$order = (string)(floor(microtime(true) * 1000));
$noi   = ($cfg['content_prefix'] ?? 'Thanh toan') . ' ' . $order;

// Tạo link VietQR
$v    = $cfg['vietqr'];
$qr   = "https://img.vietqr.io/image/" . rawurlencode($v['bank_code']) . "-" . rawurlencode($v['account']) . "-qr_only.png"
      . "?amount=" . $amount . "&addInfo=" . rawurlencode($noi)
      . "&accountName=" . rawurlencode($v['account_name']);

$ttl = (int)($cfg['order_timeout_sec'] ?? 600);

// Thêm giao dịch mới vào CSDL
// **QUAN TRỌNG**: Dùng "RETURNING id" để lấy UUID vừa tạo (dùng cho PostgreSQL)
$sqlIns = "INSERT INTO giao_dich
            (id_nguoi_dung, loai, ref_id, ma, so_tien, noi_dung, phuong_thuc, trang_thai,
             tao_luc, het_han_luc, provider, provider_txn_id, token)
           VALUES (:u, :t, :r, :ma, :amt, :noi, 'vietqr', 'pending',
                   NOW(), NOW() + (:ttl || ' seconds')::interval, 'vietqr', :ma, :qr)
           RETURNING id"; // Lấy lại ID vừa INSERT

$ins = $pdo->prepare($sqlIns);
$ins->execute([
    ':u'    => $uid,
    ':t'    => $type,
    ':r'    => $refId,
    ':ma'   => $order,
    ':amt'  => $amount,
    ':noi'  => $noi,
    ':ttl'  => $ttl,
    ':qr'   => $qr
]);

// Lấy ID của giao dịch vừa tạo
$newTxId = $ins->fetchColumn();

if (!$newTxId) {
    die('Không thể tạo giao dịch. Vui lòng thử lại.');
}

// 3. Chuyển hướng người dùng đến trang chi tiết giao dịch
header('Location: chitiet_giaodich.php?id=' . $newTxId);
exit;

?>