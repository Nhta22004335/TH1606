<?php
// BẬT HIỂN THỊ LỖI ĐỂ DEBUG (NÊN XÓA KHI ĐƯA LÊN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../../../config/database.php";

// Thêm khối try-catch để bắt lỗi kết nối CSDL
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

// 1. LẤY THÔNG TIN CHUNG VÀ KẾ HOẠCH THANH TOÁN
$sql_info = "
  SELECT 
    gd.id, gd.loai, gd.ngay_giao_dich, gd.trang_thai AS trangthai_gd,
    khtt.tong_gia_tri, khtt.so_tien_da_tt, khtt.trang_thai_tt
  FROM 
    giao_dich gd
  JOIN 
    ke_hoach_thanh_toan khtt ON gd.id = khtt.id_giao_dich
  WHERE 
    gd.id = :id
";
$stmt = $pdo->prepare($sql_info);
$stmt->execute([':id' => $id_gd]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) {
  echo "<div class='p-6 text-xl text-center text-orange-700 bg-orange-100 border border-orange-200 rounded-lg'>Không tìm thấy Kế hoạch Thanh toán cho giao dịch ID {$id_gd}!</div>";
  exit;
}

// 2. LẤY CHI TIẾT CÁC ĐỢT THANH TOÁN (dot_thanh_toan)
$sql_dtt = "
  SELECT 
    dtt.id, dtt.lan_tt, dtt.so_tien_tt, dtt.ngay_tt, dtt.phuong_thuc,
    ttct.so_luong, ttct.so_tien AS so_tien_ct,
    bds.tieu_de AS ten_bds, bds.dia_chi
  FROM 
    dot_thanh_toan dtt
  JOIN 
    dot_thanh_toan_ct ttct ON dtt.id = ttct.id_dot_thanh_toan
  LEFT JOIN 
    bat_dong_san bds ON ttct.id_bds = bds.id
  WHERE 
    dtt.id_giao_dich = :id_gd
  ORDER BY
    dtt.lan_tt ASC, ttct.id ASC
";
$stmt = $pdo->prepare($sql_dtt);
$stmt->execute([':id_gd' => $id_gd]);
$dot_thanh_toan_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gộp các chi tiết theo từng đợt thanh toán (để hiển thị theo nhóm)
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


// Định nghĩa labels
$label_gd = [
  'choxuly'  => ['text' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
  'dangxuly' => ['text' => 'Đang xử lý', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
  'hoantat'  => ['text' => 'Hoàn tất', 'class' => 'bg-green-100 text-green-700 border-green-200'],
  'dahuy'   => ['text' => 'Đã hủy', 'class' => 'bg-red-100 text-red-700 border-red-200']
];

$label_tt_tong = [
  'chuathanhtoan' => ['text' => 'Chưa TT', 'class' => 'bg-red-600'],
    'dangthanhtoan' => ['text' => 'Đang TT', 'class' => 'bg-orange-500'],
    'hoantat'       => ['text' => 'Đã TT Xong', 'class' => 'bg-green-600']
];

$label_loai = [
  'ban' => 'Bán',
  'thue' => 'Thuê',
  'duan' => 'Dự án'
];

$label_pt = [
  'ck' => 'Chuyển khoản',
  'tienmat' => 'Tiền mặt'
];

// Lấy class và text cho Trạng thái Giao dịch
$gd_status = $label_gd[$info['trangthai_gd']] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-200 text-gray-700 border-gray-300'];

// Lấy class và text cho Trạng thái Thanh toán Tổng thể
$tt_tong_status_data = $label_tt_tong[$info['trang_thai_tt']] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-500'];

// Sử dụng tổng tiền đã thanh toán (so_tien_da_tt) cho footer
$tong_tien_thanh_toan = (float)($info['so_tien_da_tt'] ?? 0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-content=1.0">
<title>Chi tiết Giao dịch & Thanh toán</title>
<script src="https://cdn.tailwindcss.com"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
/* Custom styles for professional look */
  .card-info {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    padding: 20px;
  }
  .info-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    color: #6b7280; /* gray-500 */
    font-weight: 600;
    margin-bottom: 4px;
  }
    .table-header {
        background-color: #f3f4f6; /* gray-100 */
    }
</style>
</head>
<body class="bg-gray-50">
<div class="max-w-6xl mx-auto space-y-8 p-4">
<div class="flex items-center justify-between border-b pb-4 mb-4 mt-6">
    <h1 class="text-2xl font-bold text-gray-700 flex items-center gap-3">
      <i class="fa-solid fa-receipt text-blue-600"></i>
      Chi tiết Thanh toán & Giao dịch
    </h1>
    <div class="text-sm font-medium text-gray-500">
      Mã GD: <span class="font-bold text-gray-800"><?= htmlspecialchars($id_gd) ?></span>
    </div>
  </div>

<div class="grid md:grid-cols-3 gap-6">
    
    <div class="card-info border-l-4 border-blue-500 md:col-span-1">
    <h2 class="text-xl font-bold text-blue-700 mb-4 flex items-center gap-2">
      <i class="fa-solid fa-handshake"></i> Thông tin Giao dịch
    </h2>
    <div class="space-y-3">
      <div>
        <div class="info-label">Loại Giao dịch</div>
        <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($label_loai[$info['loai']] ?? '---') ?></p>
      </div>
      <div>
        <div class="info-label">Ngày Giao dịch</div>
        <p class="text-gray-700"><?= date('d/m/Y H:i', strtotime($info['ngay_giao_dich'])) ?></p>
      </div>
      <div>
        <div class="info-label">Trạng thái Giao dịch</div>
        <span class="px-3 py-1 font-semibold text-xs rounded-full border shadow-sm <?= $gd_status['class'] ?>">
          <?= htmlspecialchars($gd_status['text']) ?>
        </span>
      </div>
    </div>
    </div>
    
      <div class="card-info border-l-4 border-green-500 md:col-span-2">
    <h2 class="text-xl font-bold text-green-700 mb-4 flex items-center gap-2">
      <i class="fa-solid fa-calculator"></i> Kế hoạch & Tổng tiền Thanh toán
    </h2>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <div class="info-label">Tổng giá trị Hợp đồng</div>
        <p class="text-2xl font-bold text-gray-900"><?= number_format($info['tong_gia_tri'], 0, ',', '.') ?> VND</p>
      </div>
      <div>
        <div class="info-label">Tổng đã Thanh toán</div>
        <p class="text-2xl font-bold text-red-600"><?= number_format($info['so_tien_da_tt'], 0, ',', '.') ?> VND</p>
      </div>
      <div class="col-span-2">
        <div class="info-label">Trạng thái Thanh toán Tổng thể</div>
        <span class="px-3 py-1 font-bold text-white text-sm rounded-full <?= $tt_tong_status_data['class'] ?> shadow-md">
          <?= htmlspecialchars($tt_tong_status_data['text']) ?>
        </span>
      </div>
    </div>
    </div>
</div>

<div class="card-info p-0 mt-8">
    <h2 class="text-xl font-bold text-gray-700 p-5 border-b flex items-center gap-2">
      <i class="fa-solid fa-list-check text-blue-500"></i> Chi tiết các Đợt Thanh toán đã hoàn thành
    </h2>
        
        <?php if (empty($grouped_details)): ?>
            <div class="p-6 text-center text-gray-500 italic">
                <i class="fa-solid fa-exclamation-circle mr-2"></i> Giao dịch này chưa có đợt thanh toán nào được ghi nhận.
            </div>
        <?php endif; ?>

        <?php 
        // Lặp qua từng Đợt Thanh toán (dot_thanh_toan)
        foreach($grouped_details as $dtt_id => $dtt): 
        ?>
            <div class="border-b p-4 bg-gray-50/50">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-extrabold text-blue-800">
                        ĐỢT THANH TOÁN #<?= htmlspecialchars($dtt['lan_tt']) ?>
                    </h3>
                    <div class="text-sm font-semibold text-gray-600">
                        <i class="fa-regular fa-calendar-alt mr-1"></i> Ngày TT: <?= date('d/m/Y H:i', strtotime($dtt['ngay_tt'])) ?>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 text-sm mb-4">
                    <div class="font-bold text-red-600 text-2xl md:col-span-2">
                        <?= number_format($dtt['so_tien_tt'], 0, ',', '.') ?> VND
                    </div>
                    <div class="text-gray-600 md:col-span-2">
                        Phương thức: <span class="font-semibold text-gray-800"><?= htmlspecialchars($label_pt[$dtt['phuong_thuc']] ?? '---') ?></span>
                    </div>
                </div>
                
                <div class="overflow-x-auto border rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="table-header">
                            <tr class="text-gray-600 uppercase text-xs">
                                <th class="p-2 text-left">Sản phẩm</th>
                                <th class="p-2 text-left">Địa chỉ</th>
                                <th class="p-2 text-center">SL</th>
                                <th class="p-2 text-right">Số tiền Phân bổ (VND)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php foreach($dtt['details'] as $detail): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-2 font-medium text-gray-800"><?= htmlspecialchars($detail['ten_bds'] ?? '---') ?></td>
                                    <td class="p-2 text-gray-600"><?= htmlspecialchars($detail['dia_chi'] ?? '---') ?></td>
                                    <td class="p-2 text-center font-bold"><?= number_format($detail['so_luong']) ?></td>
                                    <td class="p-2 text-right font-semibold text-red-500">
                                        <?= number_format($detail['so_tien_ct'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>


    <div class="p-5 bg-gray-50 border-t flex justify-between items-center">
            <a href="javascript:history.back()" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
            </a>
      <div class="text-2xl font-bold text-gray-800">
        TỔNG ĐÃ THU: 
        <span class="text-red-700 ml-2"><?= number_format($tong_tien_thanh_toan, 0, ',', '.') ?> VND</span>
      </div>
    </div>
  </div>
</div>
</body>
</html>