<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $id_gd = $_GET['id'] ?? '';

    if (empty($id_gd)) {
        echo "<div class='p-4 text-red-600'>Không tìm thấy giao dịch!</div>";
        exit;
    }

    // Lấy thông tin thanh toán
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
        echo "<div class='p-4 text-red-600'>Giao dịch chưa có thanh toán nào!</div>";
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

    $label_gd = [
        'choxuly'   => 'Chờ xử lý',
        'dangxuly'  => 'Đang xử lý',
        'hoantat'   => 'Hoàn tất',
        'dahuy'     => 'Đã hủy'
    ];

    $label_tt = [
        'dathanhtoan' => 'Đã thanh toán',
        'chuathanhtoan' => 'Chưa thanh toán'
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chi tiết giao dịch</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
  <div class="bg-blue-50 max-w-5xl mx-auto rounded-lg shadow-lg p-6 mt-4">
    <!-- Header -->
    <h1 class="flex text-2xl font-bold text-gray-600 mb-4">
        <img src="../../../public/assets/anhht/0/footprints-search.gif" class="w-10 h-10 mr-2 rounded-lg"> Chi tiết giao dịch
    </h1>

    <!-- Thông tin giao dịch & thanh toán -->
    <div class="grid grid-cols-2 gap-4 mb-6">
      <div class="p-4 border rounded-lg bg-gray-50">
        <p><strong>Loại GD:</strong> <?= htmlspecialchars($label_loai[$tt['loai']]) ?></p>
        <p><strong>Ngày GD:</strong> <?= htmlspecialchars($tt['ngay_giao_dich']) ?></p>
        <p>
          <strong>Trạng thái GD:</strong> 
          <span class="px-2 py-1 rounded text-white 
            <?= $tt['trangthai_gd']=='choxuly'?'bg-yellow-500':
                 ($tt['trangthai_gd']=='dangxuly'?'bg-blue-500':
                 ($tt['trangthai_gd']=='hoantat'?'bg-green-600':'bg-red-600')) ?>">
            <?= htmlspecialchars($label_gd[$tt['trangthai_gd']]) ?>
          </span>
        </p>
      </div>
      <div class="p-4 border rounded-lg bg-gray-50">
        <p><strong>Tổng tiền:</strong> <?= number_format($tt['tong_tien'], 0, ',', '.') ?> VND</p>
        <p><strong>Ngày TT:</strong> <?= htmlspecialchars($tt['ngay_tt']) ?></p>
        <p><strong>Phương thức:</strong> <?= htmlspecialchars($label_pt[$tt['phuong_thuc']]) ?></p>
        <p>
          <strong>Trạng thái TT:</strong> 
          <span class="px-2 py-1 rounded text-white 
            <?= $tt['trang_thai']=='dathanhtoan'?'bg-green-600':'bg-gray-500' ?>">
            <?= htmlspecialchars($label_tt[$tt['trang_thai']]) ?>
          </span>
        </p>
      </div>
    </div>

    <!-- Bảng chi tiết thanh toán -->
    <h2 class="text-xl font-semibold text-gray-700 mb-3">Danh sách chi tiết</h2>
    <div class="overflow-x-auto">
      <table class="w-full border-collapse bg-white shadow-sm rounded-lg">
        <thead>
          <tr class="bg-gray-200 text-left">
            <th class="p-3 border">STT</th>
            <th class="p-3 border">Bất động sản</th>
            <th class="p-3 border">Địa chỉ</th>
            <th class="p-3 border">Số lượng</th>
            <th class="p-3 border">Số tiền</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($ct) > 0): ?>
            <?php foreach($ct as $i => $row): ?>
              <tr class="hover:bg-gray-50">
                <td class="p-3 border text-center"><?= $i+1 ?></td>
                <td class="p-3 border"><?= htmlspecialchars($row['ten_bds'] ?? '---') ?></td>
                <td class="p-3 border"><?= htmlspecialchars($row['dia_chi'] ?? '---') ?></td>
                <td class="p-3 border text-center"><?= htmlspecialchars($row['so_luong']) ?></td>
                <td class="p-3 border text-right"><?= number_format($row['so_tien'], 0, ',', '.') ?> VND</td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="p-4 text-center text-gray-500">Chưa có chi tiết thanh toán</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Tổng cộng -->
    <div class="mt-6 text-right">
      <p class="text-lg font-semibold">
        Tổng cộng: <span class="text-red-600"><?= number_format($tt['tong_tien'], 0, ',', '.') ?> VND</span>
      </p>
    </div>
  </div>
</body>
</html>
