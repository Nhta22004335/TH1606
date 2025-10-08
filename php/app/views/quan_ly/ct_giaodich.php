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

// Lấy thông tin thanh toán & giao dịch
$sql_tt = "
    SELECT tt.id, tt.tong_tien, tt.ngay_tt, tt.phuong_thuc, tt.trang_thai,
        gd.loai, gd.ngay_giao_dich, gd.trang_thai AS trangthai_gd
    FROM thanh_toan tt
    JOIN giao_dich gd ON gd.id = tt.id_giao_dich
    WHERE gd.id = :id
";
$stmt = $pdo->prepare($sql_tt);
$stmt->execute([':id' => $id_gd]);
$tt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tt) {
    echo "<div class='p-6 text-xl text-center text-orange-700 bg-orange-100 border border-orange-200 rounded-lg'>Giao dịch ID {$id_gd} chưa có thanh toán nào!</div>";
    exit;
}

// Lấy chi tiết thanh toán
$sql_ct = "
    SELECT ttc.id, ttc.so_luong, ttc.so_tien,
        bds.tieu_de AS ten_bds, bds.dia_chi
    FROM thanh_toan_ct ttc
    LEFT JOIN bat_dong_san bds ON ttc.id_bds = bds.id
    WHERE ttc.id_thanh_toan = :id_tt
";
$stmt = $pdo->prepare($sql_ct);
$stmt->execute([':id_tt' => $tt['id']]);
$ct = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Định nghĩa labels
$label_gd = [
    'choxuly'   => ['text' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
    'dangxuly'  => ['text' => 'Đang xử lý', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
    'hoantat'   => ['text' => 'Hoàn tất', 'class' => 'bg-green-100 text-green-700 border-green-200'],
    'dahuy'     => ['text' => 'Đã hủy', 'class' => 'bg-red-100 text-red-700 border-red-200']
];

$label_tt = [
    'dathanhtoan'   => ['text' => 'Đã thanh toán', 'class' => 'bg-green-600'],
    'chuathanhtoan' => ['text' => 'Chưa thanh toán', 'class' => 'bg-red-600']
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
$gd_status = $label_gd[$tt['trangthai_gd']] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-200 text-gray-700 border-gray-300'];

// Lấy class và text cho Trạng thái Thanh toán
$tt_status_class = $label_tt[$tt['trang_thai']]['class'] ?? 'bg-gray-500';
$tt_status_text = $label_tt[$tt['trang_thai']]['text'] ?? 'Không rõ';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
 </style>
</head>
<body>
<div class="max-w-6xl mx-auto space-y-8">
 <div class="flex items-center justify-between border-b pb-4 mb-4 mt-6">
        <h1 class="text-2xl font-bold text-gray-700 flex items-center gap-3">
            <i class="fa-solid fa-receipt text-blue-600"></i>
            Chi tiết Thanh toán & Giao dịch
        </h1>
        <div class="text-sm font-medium text-gray-500">
            Mã GD: <span class="font-bold text-gray-800"><?= htmlspecialchars($id_gd) ?></span>
        </div>
    </div>

<div class="grid md:grid-cols-2 gap-6">
<div class="card-info border-l-4 border-blue-500">
            <h2 class="text-xl font-bold text-blue-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-handshake"></i> Thông tin Giao dịch
            </h2>
            <div class="space-y-3">
                <div>
                    <div class="info-label">Loại Giao dịch</div>
                    <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($label_loai[$tt['loai']] ?? '---') ?></p>
                </div>
                <div>
                    <div class="info-label">Ngày Giao dịch</div>
                    <p class="text-gray-700"><?= date('d/m/Y H:i', strtotime($tt['ngay_giao_dich'])) ?></p>
                </div>
                <div>
                    <div class="info-label">Trạng thái Giao dịch</div>
                    <span class="px-3 py-1 font-semibold text-xs rounded-full border shadow-sm <?= $gd_status['class'] ?>">
                        <?= htmlspecialchars($gd_status['text']) ?>
                    </span>
                </div>
            </div>
 </div>
        
        <div class="card-info border-l-4 border-green-500">
            <h2 class="text-xl font-bold text-green-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-money-check-dollar"></i> Chi tiết Thanh toán
            </h2>
            <div class="space-y-3">
                <div>
                    <div class="info-label">Tổng tiền Thanh toán</div>
                    <p class="text-2xl font-bold text-red-600"><?= number_format($tt['tong_tien'], 0, ',', '.') ?> VND</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="info-label">Ngày Thanh toán</div>
                        <p class="text-gray-700"><?= date('d/m/Y H:i', strtotime($tt['ngay_tt'])) ?></p>
                    </div>
                    <div>
                        <div class="info-label">Phương thức</div>
                        <p class="text-gray-700"><?= htmlspecialchars($label_pt[$tt['phuong_thuc']] ?? '---') ?></p>
                    </div>
                </div>
                <div>
                    <div class="info-label">Trạng thái Thanh toán</div>
                    <span class="px-3 py-1 font-bold text-white text-sm rounded-full <?= $tt_status_class ?> shadow-md">
                        <?= htmlspecialchars($tt_status_text) ?>
                    </span>
                </div>
            </div>
 </div>
 </div>

 <div class="card-info p-0">
        <h2 class="text-xl font-bold text-gray-700 p-5 border-b flex items-center gap-2">
            <i class="fa-solid fa-list-ul text-blue-500"></i> Danh sách Chi tiết Sản phẩm
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <th class="p-3 text-center border-b">STT</th>
                        <th class="p-3 text-left border-b w-1/4">Bất động sản</th>
                        <th class="p-3 text-left border-b w-1/3">Địa chỉ</th>
                        <th class="p-3 text-center border-b">Số lượng</th>
                        <th class="p-3 text-right border-b w-1/5">Số tiền (VND)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (count($ct) > 0): ?>
                        <?php foreach($ct as $i => $row): ?>
                            <tr class="hover:bg-blue-50 transition duration-150">
                                <td class="p-3 text-center text-gray-600"><?= $i+1 ?></td>
                                <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($row['ten_bds'] ?? '---') ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($row['dia_chi'] ?? '---') ?></td>
                                <td class="p-3 text-center font-bold"><?= number_format($row['so_luong']) ?></td>
                                <td class="p-3 text-right font-semibold text-lg text-red-500"><?= number_format($row['so_tien'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500 italic">Chưa có chi tiết thanh toán nào được ghi nhận.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-5 bg-gray-50 border-t flex justify-end">
            <div class="text-xl font-bold text-gray-800">
                TỔNG CỘNG: 
                <span class="text-red-700 ml-2"><?= number_format($tt['tong_tien'], 0, ',', '.') ?> VND</span>
            </div>
        </div>
    </div>
 </div>
</body>
</html>