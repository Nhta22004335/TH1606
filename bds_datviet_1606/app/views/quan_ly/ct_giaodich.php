<?php
// PHẦN LOGIC PHP CỦA BẠN - GIỮ NGUYÊN HOÀN TOÀN
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// ... (Toàn bộ logic PHP của bạn từ dòng 22 đến 124 được giữ nguyên) ...
$sql_info = "
  SELECT 
    gd.id, gd.loai, gd.ngay_giao_dich, gd.trang_thai AS trangthai_gd,
    khtt.tong_gia_tri, khtt.so_tien_da_tt, khtt.trang_thai_tt
  FROM giao_dich gd
  JOIN ke_hoach_thanh_toan khtt ON gd.id = khtt.id_giao_dich
  WHERE gd.id = :id
";
$stmt = $pdo->prepare($sql_info);
$stmt->execute([':id' => $id_gd]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) {
  echo "<div class='p-6 text-xl text-center text-orange-700 bg-orange-100 border border-orange-200 rounded-lg'>Không tìm thấy Kế hoạch Thanh toán cho giao dịch ID " . htmlspecialchars($id_gd) . "!</div>";
  exit;
}

$sql_dtt = "
  SELECT 
    dtt.id, dtt.lan_tt, dtt.so_tien_tt, dtt.ngay_tt, dtt.phuong_thuc,
    ttct.so_luong, ttct.so_tien AS so_tien_ct,
    bds.tieu_de AS ten_bds, bds.dia_chi
  FROM dot_thanh_toan dtt
  JOIN dot_thanh_toan_ct ttct ON dtt.id = ttct.id_dot_thanh_toan
  LEFT JOIN bat_dong_san bds ON ttct.id_bds = bds.id
  WHERE dtt.id_giao_dich = :id_gd
  ORDER BY dtt.lan_tt ASC, ttct.id ASC
";
$stmt = $pdo->prepare($sql_dtt);
$stmt->execute([':id_gd' => $id_gd]);
$dot_thanh_toan_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped_details = [];
foreach ($dot_thanh_toan_details as $detail) {
    $dtt_id = $detail['id'];
    if (!isset($grouped_details[$dtt_id])) {
        $grouped_details[$dtt_id] = [
            'lan_tt' => $detail['lan_tt'],
            'so_tien_tt' => $detail['so_tien_tt'],
            'ngay_tt' => $detail['ngay_tt'],
            'phuong_thuc' => $detail['phuong_thuc'],
            'details' => []
        ];
    }
    $grouped_details[$dtt_id]['details'][] = $detail;
}

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
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Giao dịch #<?= htmlspecialchars($id_gd) ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100">

<div class="space-y-6">

    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 ">Chi Tiết Giao Dịch</h1>
            <p class="text-sm text-gray-600 mt-1">
                Mã giao dịch: <span class="font-medium text-gray-700">#<?= htmlspecialchars($id_gd) ?></span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="javascript:history.back()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
            <button onclick="window.print()" class="px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-900 transition">
                <i class="fas fa-print mr-2"></i> In Hóa Đơn
            </button>
        </div>
    </header>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-8">
            <div class="space-y-1">
                <p class="text-sm text-gray-500">Loại Giao Dịch</p>
                <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($label_loai[$info['loai']] ?? '---') ?></p>
            </div>
            <div class="space-y-1">
                <p class="text-sm text-gray-500">Ngày Giao Dịch</p>
                <p class="text-lg font-semibold text-gray-900"><?= date('d/m/Y', strtotime($info['ngay_giao_dich'])) ?></p>
            </div>
            <div class="space-y-1 col-span-2 sm:col-span-1">
                <p class="text-sm text-gray-500">Trạng thái</p>
                <span class="px-2.5 py-0.5 text-sm font-medium rounded-full <?= htmlspecialchars($gd_status['class']) ?>">
                    <?= htmlspecialchars($gd_status['text']) ?>
                </span>
            </div>
            <div class="space-y-1">
                <p class="text-sm text-gray-500">Tổng Giá Trị</p>
                <p class="text-xl font-bold text-gray-900"><?= number_format($tong_gia_tri, 0, ',', '.') ?> <span class="text-base font-medium text-gray-500">VND</span></p>
            </div>
            
            <div class="col-span-full border-t border-gray-200 my-2"></div>

            <div class="space-y-1 col-span-2">
                <p class="text-sm text-gray-500">Đã Thanh Toán</p>
                <p class="text-2xl font-bold text-green-600"><?= number_format($tong_da_tt, 0, ',', '.') ?> <span class="text-base font-medium text-gray-500">VND</span></p>
            </div>
            <div class="space-y-1 col-span-2">
                <p class="text-sm text-gray-500">Còn Lại</p>
                <p class="text-2xl font-bold text-red-600"><?= number_format($con_lai, 0, ',', '.') ?> <span class="text-base font-medium text-gray-500">VND</span></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left font-medium text-gray-500 uppercase tracking-wider">Sản Phẩm/Dịch Vụ</th>
                    <th class="p-3 text-center font-medium text-gray-500 uppercase tracking-wider">Số Lượng</th>
                    <th class="p-3 text-right font-medium text-gray-500 uppercase tracking-wider">Số Tiền (VND)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($grouped_details)): ?>
                    <tr>
                        <td colspan="3" class="p-8 text-center text-gray-500">
                            <i class="fas fa-folder-open text-4xl mb-2"></i>
                            <p>Chưa có đợt thanh toán nào được ghi nhận.</p>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach($grouped_details as $dtt): ?>
                    <tr class="bg-gray-100">
                        <td class="p-3 font-semibold text-gray-800" colspan="2">
                            Đợt #<?= htmlspecialchars($dtt['lan_tt']) ?> - 
                            <span class="font-normal text-gray-600">Ngày TT: <?= date('d/m/Y', strtotime($dtt['ngay_tt'])) ?></span>
                            <span class="font-normal text-gray-600 ml-2">(<?= htmlspecialchars($label_pt[$dtt['phuong_thuc']] ?? '---') ?>)</span>
                        </td>
                        <td class="p-3 text-right font-bold text-gray-900 text-base">
                            <?= number_format($dtt['so_tien_tt'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php foreach($dtt['details'] as $detail): ?>
                        <tr>
                            <td class="p-3">
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($detail['ten_bds'] ?? '---') ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($detail['dia_chi'] ?? '---') ?></p>
                            </td>
                            <td class="p-3 text-center text-gray-600"><?= number_format($detail['so_luong']) ?></td>
                            <td class="p-3 text-right text-gray-600"><?= number_format($detail['so_tien_ct'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>