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
function format_status($status) {
    switch ($status) {
        case 'pending':
            return '<span class="text-yellow-600 font-semibold">Đang chờ</span>';
        case 'completed':
            return '<span class="text-green-600 font-semibold">Thành công</span>';
        case 'failed':
            return '<span class="text-red-600 font-semibold">Thất bại</span>';
        case 'cancelled':
            return '<span class="text-gray-600 font-semibold">Đã hủy</span>';
        default:
            return '<span class="text-gray-500 font-semibold">' . e($status) . '</span>';
    }
}

$uid = (string)$_SESSION['id_nguoi_dung'];
$state = (string)($_GET['state'] ?? 'all'); // 'all', 'pending', 'completed', ...

// ... (code từ đầu) ...

// Xây dựng câu truy vấn
// Chúng ta JOIN 4 bảng để lấy tất cả thông tin
$sql = "SELECT 
            gd.id AS giao_dich_id, gd.ma, gd.so_tien, gd.trang_thai, gd.tao_luc,
            bd.tieu_de,
            bds.dia_chi_day_du,
            nd.ten_dang_nhap, nd.email, nd.so_dt
        FROM 
            giao_dich AS gd
        JOIN 
            nguoi_dung AS nd ON gd.id_nguoi_dung = nd.id::uuid  -- <<< SỬA Ở ĐÂY
        JOIN 
            bai_dang AS bd ON gd.ref_id = bd.id
        JOIN 
            bat_dong_san AS bds ON bd.id_bat_dong_san = bds.id
        WHERE 
            gd.id_nguoi_dung = :uid";

$params = [':uid' => $uid];

// Thêm bộ lọc trạng thái nếu có
if ($state !== 'all') {
    $sql .= " AND gd.trang_thai = :state";
    $params[':state'] = $state;
}

$sql .= " ORDER BY gd.tao_luc DESC";

$st = $pdo->prepare($sql);
$st->execute($params); // Dòng 60 sẽ hết báo lỗi
$transactions = $st->fetchAll(PDO::FETCH_ASSOC);

// ... (code phần HTML giữ nguyên) ...

$pageTitle = "Lịch sử giao dịch";
if ($state === 'pending') $pageTitle = "Giao dịch đang chờ";
if ($state === 'completed') $pageTitle = "Giao dịch thành công";

?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<?php include __DIR__ . '/../partials/foot_ai.php'; ?>
<body class="bg-gray-100">

<div class="max-w-4xl mx-auto p-4 mt-8">
    <h2 class="text-3xl font-bold mb-6 text-center"><?= e($pageTitle) ?></h2>

    <div class="flex justify-center space-x-2 mb-6">
        <a href="duan.php" 
           class="px-4 py-2 rounded-lg <?= $state === 'all' ? 'bg-indigo-600 text-white' : 'bg-white border' ?>">
           Tất cả
        </a>
        <a href="duan.php?state=pending" 
           class="px-4 py-2 rounded-lg <?= $state === 'pending' ? 'bg-indigo-600 text-white' : 'bg-white border' ?>">
           Đang chờ
        </a>
        <a href="duan.php?state=completed" 
           class="px-4 py-2 rounded-lg <?= $state === 'completed' ? 'bg-indigo-600 text-white' : 'bg-white border' ?>">
           Thành công
        </a>
        <a href="duan.php?state=failed" 
           class="px-4 py-2 rounded-lg <?= $state === 'failed' ? 'bg-indigo-600 text-white' : 'bg-white border' ?>">
           Thất bại
        </a>
    </div>

    <div class="space-y-6">
        <?php if (empty($transactions)): ?>
            <p class="text-center text-gray-600 text-lg">Không tìm thấy giao dịch nào.</p>
        <?php else: ?>
            <?php foreach ($transactions as $tx): ?>
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="p-5">
                        <div class="flex justify-between items-center border-b pb-3 mb-4">
                            <div>
                                <div class="text-xs text-gray-500">Mã giao dịch</div>
                                <div class="font-mono font-bold text-sm text-gray-800"><?= e($tx['ma']) ?></div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Trạng thái</div>
                                <div class="text-lg"><?= format_status($tx['trang_thai']) ?></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">Thông tin người mua</h4>
                                <div class="text-sm space-y-1">
                                    <p><b>Họ tên:</b> <?= e($tx['ten_dang_nhap']) ?></p>
                                    <p><b>Email:</b> <?= e($tx['email']) ?></p>
                                    <p><b>SĐT:</b> <?= e($tx['so_dt']) ?></p>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">Chi tiết BĐS</h4>
                                <div class="text-sm space-y-1">
                                    <p><b>BĐS:</b> <?= e($tx['tieu_de']) ?></p>
                                    <p><b>Địa chỉ:</b> <?= e($tx['dia_chi_day_du']) ?></p>
                                    <p><b>Ngày tạo:</b> <?= e(date('d/m/Y H:i', strtotime($tx['tao_luc']))) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t mt-4 pt-4 flex justify-between items-center">
                            <div>
                                <div class="text-sm text-gray-500">Tổng tiền</div>
                                <div class="text-2xl font-bold text-red-600"><?= vn_money($tx['so_tien']) ?> đ</div>
                            </div>
                            
                            <?php if ($tx['trang_thai'] === 'pending'): ?>
                                <a href="chitiet_giaodich.php?id=<?= e($tx['giao_dich_id']) ?>" 
                                   class="px-5 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">
                                   Thanh toán ngay
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>