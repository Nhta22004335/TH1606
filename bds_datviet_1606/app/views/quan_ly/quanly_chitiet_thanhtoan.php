<?php
require_once "../../../config/database.php";

try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 border border-red-200 rounded-lg'>Lỗi kết nối CSDL: " . $e->getMessage() . "</div>");
}

$id_gd = $_GET['id'] ?? '';

if (empty($id_gd)) {
    echo "<div class='p-6 text-xl text-center text-red-700 bg-red-100 border border-red-200 rounded-lg'>Không tìm thấy ID giao dịch!</div>";
    exit;
}

$sql_info = "SELECT gd.id, gd.loai, gd.ngay_giao_dich, gd.trang_thai AS trangthai_gd, khtt.tong_gia_tri, khtt.so_tien_da_tt, khtt.trang_thai_tt FROM giao_dich gd LEFT JOIN ke_hoach_thanh_toan khtt ON gd.id = khtt.id_giao_dich WHERE gd.id = :id";
$stmt = $pdo->prepare($sql_info);
$stmt->execute([':id' => $id_gd]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    echo "<div class='p-6 text-xl text-center text-orange-700 bg-orange-100 border border-orange-200 rounded-lg'>Không tìm thấy Kế hoạch Thanh toán cho giao dịch ID " . htmlspecialchars($id_gd) . "!</div>";
    exit;
}

$sql_dtt = "SELECT dtt.id, dtt.lan_tt, dtt.so_tien_tt, dtt.ngay_tt, dtt.phuong_thuc, ttct.so_luong, ttct.so_tien AS so_tien_ct, bds.dia_chi_day_du AS bds_info FROM dot_thanh_toan dtt LEFT JOIN dot_thanh_toan_ct ttct ON dtt.id = ttct.id_dot_thanh_toan LEFT JOIN bat_dong_san bds ON ttct.id_bds = bds.id WHERE dtt.id_giao_dich = :id_gd ORDER BY dtt.lan_tt ASC, ttct.id ASC";
$stmt = $pdo->prepare($sql_dtt);
$stmt->execute([':id_gd' => $id_gd]);
$dot_thanh_toan_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped_details = [];
foreach ($dot_thanh_toan_details as $detail) {
    $dtt_id = $detail['id'];
    if (!isset($grouped_details[$dtt_id])) {
        $grouped_details[$dtt_id] = [
            'lan_tt' => $detail['lan_tt'], 'so_tien_tt' => $detail['so_tien_tt'], 'ngay_tt' => $detail['ngay_tt'], 'phuong_thuc' => $detail['phuong_thuc'], 'details' => []
        ];
    }
    $grouped_details[$dtt_id]['details'][] = $detail;
}

// Map nhãn (sử dụng màu ấm áp hơn)
$label_gd = [
    'choxuly'  => ['text' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-800'],
    'dangxuly' => ['text' => 'Đang xử lý', 'class' => 'bg-blue-100 text-blue-800'],
    'hoantat'  => ['text' => 'Hoàn tất', 'class' => 'bg-green-100 text-green-800'],
    'dahuy'   => ['text' => 'Đã hủy', 'class' => 'bg-red-100 text-red-800']
];
$label_pt = ['ck' => 'Chuyển khoản', 'tienmat' => 'Tiền mặt'];
$label_loai = ['ban' => 'Bán', 'thue' => 'Thuê', 'duan' => 'Dự án'];

$gd_status = $label_gd[$info['trangthai_gd']] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-100 text-gray-800'];
$tong_da_tt = (float)($info['so_tien_da_tt'] ?? 0);
$tong_gia_tri = (float)($info['tong_gia_tri'] ?? 0);
$con_lai = $tong_gia_tri - $tong_da_tt;
?>

<!DOCTYPE html>
<html lang="vi" class="bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Giao dịch #<?= htmlspecialchars(substr($id_gd, 0, 8)) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> 
        .bg-warm-50 { background-color: #FFF7ED; }
        .text-warm-800 { color: #9A3412; }
        .border-warm-200 { border-color: #FDE68A; }
    </style>
</head>
<body>

<div class="max-w-5xl mx-auto">
    <header class="mb-5 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-500">Chi Tiết Giao Dịch</h1>
            <p class="text-sm text-gray-500 mt-1">
                Mã: <span class="font-medium text-gray-700">#<?= htmlspecialchars(substr($id_gd, 0, 8)) ?>...</span>
            </p>
        </div>
        <a href="javascript:history.back()" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left"></i>
        </a>
    </header>

    <main class="bg-white rounded-xl shadow-lg border border-gray-200/80 overflow-hidden">
        
        <div class="p-6 bg-warm-50 border-b border-warm-200">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <p class="text-sm text-warm-800 font-medium"><?= htmlspecialchars($label_loai[$info['loai']] ?? '---') ?></p>
                    <p class="text-xs text-gray-500">Ngày: <?= date('d/m/Y', strtotime($info['ngay_giao_dich'])) ?></p>
                </div>
                <span class="px-3 py-1 text-sm font-semibold rounded-full <?= htmlspecialchars($gd_status['class']) ?>">
                    <?= htmlspecialchars($gd_status['text']) ?>
                </span>
            </div>
            
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Tổng Giá Trị</p>
                    <p class="text-xl font-bold text-gray-800 mt-1"><?= number_format($tong_gia_tri, 0, ',', '.') ?>đ</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Đã Thanh Toán</p>
                    <p class="text-xl font-bold text-green-600 mt-1"><?= number_format($tong_da_tt, 0, ',', '.') ?>đ</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Còn Lại</p>
                    <p class="text-xl font-bold text-red-600 mt-1"><?= number_format($con_lai, 0, ',', '.') ?>đ</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Lịch sử Thanh toán</h3>
            
            <?php if (empty($grouped_details)): ?>
                <div class="p-8 text-center text-gray-400 border-2 border-dashed rounded-lg">
                    <i class="fas fa-receipt fa-2x"></i>
                    <p class="mt-2 text-sm font-medium">Chưa có đợt thanh toán nào được ghi nhận.</p>
                </div>
            <?php else: ?>
                <div class="relative pl-5 space-y-6 border-l-2 border-slate-200">
                    <?php foreach($grouped_details as $dtt): ?>
                    <div class="relative">
                        <div class="absolute -left-[1.30rem] top-1 w-5 h-5 bg-indigo-500 rounded-full border-4 border-white"></div>
                        <div class="pl-4">
                            <p class="text-sm font-semibold text-indigo-700">Đợt #<?= htmlspecialchars($dtt['lan_tt']) ?> - <span class="font-bold text-gray-800"><?= number_format($dtt['so_tien_tt'], 0, ',', '.') ?> VND</span></p>
                            <p class="text-xs text-gray-500">Ngày TT: <?= date('d/m/Y', strtotime($dtt['ngay_tt'])) ?> (<?= htmlspecialchars($label_pt[$dtt['phuong_thuc']] ?? '---') ?>)</p>
                            
                            <div class="mt-3 space-y-2">
                                <?php foreach($dtt['details'] as $detail): ?>
                                <div class="text-sm text-gray-600 flex justify-between p-2 bg-slate-50 rounded-md">
                                    <span class="truncate max-w-xs" title="<?= htmlspecialchars($detail['bds_info'] ?? '---') ?>">
                                        <i class="fa-solid fa-home mr-2 text-gray-400"></i>
                                        <?= htmlspecialchars($detail['bds_info'] ?? 'Chi tiết dịch vụ') ?>
                                    </span>
                                    <span class="font-medium text-gray-800 whitespace-nowrap">
                                        <?= number_format($detail['so_tien_ct'], 0, ',', '.') ?>đ
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

</body>
</html>