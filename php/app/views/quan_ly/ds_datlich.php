<?php
// Demo dữ liệu (có thêm thoigianbatdau & thoigiankt)
$datlich = [
    [
        "id" => 1,
        "khachhang" => "Phạm Minh Khoa",
        "broker" => "Nguyễn Văn A",
        "avatar" => "https://i.pravatar.cc/100?img=1",
        "thoigianbatdau" => "2025-09-27 14:00",
        "thoigiankt"     => "2025-09-27 15:00",
        "trangthai"      => "Đã xác nhận",
        "ghichu"         => "Trao đổi về căn hộ Q7"
    ],
    [
        "id" => 2,
        "khachhang" => "Ngô Thị Hoa",
        "broker" => "Nguyễn Văn A",
        "avatar" => "https://i.pravatar.cc/100?img=1",
        "thoigianbatdau" => "2025-09-27 14:30", // trùng id=1
        "thoigiankt"     => "2025-09-27 15:00",
        "trangthai"      => "Chờ xác nhận",
        "ghichu"         => "Tư vấn biệt thự Phú Mỹ Hưng"
    ],
    [
        "id" => 3,
        "khachhang" => "Lê Quốc Huy",
        "broker" => "Trần Thị B",
        "avatar" => "https://i.pravatar.cc/100?img=2",
        "thoigianbatdau" => "2025-09-28 09:00",
        "thoigiankt"     => "2025-09-28 10:00",
        "trangthai"      => "Đã hủy",
        "ghichu"         => "Khách hủy vì bận công tác"
    ],
    [
        "id" => 4,
        "khachhang" => "Nguyễn Văn Dũng",
        "broker" => "Trần Thị B",
        "avatar" => "https://i.pravatar.cc/100?img=2",
        "thoigianbatdau" => "2025-09-28 09:30", // trùng id=3
        "thoigiankt"     => "2025-09-28 10:15",
        "trangthai"      => "Đã xác nhận",
        "ghichu"         => "Xem nhà phố Thủ Đức"
    ]
    ,
    [
        "id" => 5,
        "khachhang" => "Nguyễn Văn Dũng",
        "broker" => "Trần Thị B",
        "avatar" => "https://i.pravatar.cc/100?img=2",
        "thoigianbatdau" => "2025-09-28 11:30", // trùng id=3
        "thoigiankt"     => "2025-09-28 12:15",
        "trangthai"      => "Đã xác nhận",
        "ghichu"         => "Xem nhà phố Thủ Đức"
    ]
];

// Thêm timestamp để dễ so sánh
foreach ($datlich as &$lich) {
    $lich['start'] = strtotime($lich['thoigianbatdau']);
    $lich['end']   = strtotime($lich['thoigiankt']);
}
unset($lich);

// Phát hiện trùng + đánh dấu cái đến sau
$duplicatedIds = [];
$needNotifyIds = [];

foreach ($datlich as $i => $lich1) {
    foreach ($datlich as $j => $lich2) {
        if ($i >= $j) continue;
        if ($lich1['broker'] === $lich2['broker']) {
            if ($lich1['start'] < $lich2['end'] && $lich2['start'] < $lich1['end']) {
                // cả hai trùng
                $duplicatedIds[] = $lich1['id'];
                $duplicatedIds[] = $lich2['id'];

                // lịch đến sau thì cần hiển thị nút thông báo
                if ($lich1['start'] < $lich2['start']) {
                    $needNotifyIds[] = $lich2['id'];
                } else {
                    $needNotifyIds[] = $lich1['id'];
                }
            }
        }
    }
}
$duplicatedIds = array_unique($duplicatedIds);
$needNotifyIds = array_unique($needNotifyIds);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý lịch hẹn</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
</head>
<body>

<!-- Header -->
<header class="flex items-center gap-4 bg-white p-4 border-b-2">
    <img src="../../../public/assets/anhht/0/calendar.gif" alt="Chat" class="w-12 h-12">
    <h1 class="text-2xl font-bold text-gray-600">Danh sách lịch hẹn</h1>
</header>

<div class="overflow-x-auto px-2 mt-2">
  <!-- Header bảng -->
  <table class="min-w-full text-sm text-gray-700 table-fixed border-collapse">
    <thead class="bg-gray-200 sticky top-0 z-5">
      <tr>
        <th class="py-3 px-4 text-left">Khách hàng</th>
        <th class="py-3 px-4 text-left">Môi giới</th>
        <th class="py-3 px-4 text-left">Khoảng thời gian</th>
        <th class="py-3 px-4 text-left">Trạng thái</th>
        <th class="py-3 px-4 text-center">Hành động</th>
      </tr>
    </thead>
  </table>

  <!-- Nội dung cuộn -->
  <div class="max-h-[500px] overflow-y-auto">
    <table class="min-w-full text-sm text-gray-700 table-fixed border border-collapse">
      <tbody class="divide-y">
        <?php foreach ($datlich as $lich): ?>
          <tr class="hover:bg-gray-50 transition">
            <!-- Khách hàng -->
            <td class="py-3 px-4 font-medium"><?= $lich['khachhang'] ?></td>

            <!-- Môi giới -->
            <td class="py-3 px-4 flex items-center gap-3">
              <img src="<?= $lich['avatar'] ?>" alt="avatar" class="w-10 h-10 rounded-full border">
              <span class="font-medium"><?= $lich['broker'] ?></span>
            </td>

            <!-- Thời gian -->
            <td class="py-3 px-4">
              <?= date("d/m/Y H:i", $lich['start']) ?> - <?= date("H:i", $lich['end']) ?>
              <?php if (in_array($lich['id'], $duplicatedIds)): ?>
                <span class="ml-2 px-0 py-0.5 text-xs rounded-full bg-red-100 text-red-700 font-semibold">
                  ⚠ Trùng lịch
                </span>
              <?php endif; ?>
            </td>

            <!-- Trạng thái -->
            <td class="py-3 px-4 w-[20%]">
              <?php if ($lich['trangthai'] === "Đã xác nhận"): ?>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                  <?= $lich['trangthai'] ?>
                </span>
              <?php elseif ($lich['trangthai'] === "Chờ xác nhận"): ?>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                  <?= $lich['trangthai'] ?>
                </span>
              <?php else: ?>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                  <?= $lich['trangthai'] ?>
                </span>
              <?php endif; ?>
            </td>

            <!-- Hành động -->
            <td class="py-3 px-4 text-center space-x-2">
              <!-- Xem chi tiết -->
              <button 
                class="px-3 py-1 text-sm rounded-lg bg-blue-500 text-white hover:bg-blue-600"
                onclick='openModal(<?= json_encode($lich) ?>)'>
                <i class="fas fa-eye"></i>
              </button>

              <!-- Xóa -->
              <button class="px-3 py-1 text-sm rounded-lg bg-red-500 text-white hover:bg-red-600"> 
                <i class="fas fa-trash"></i>
              </button>

              <!-- Thông báo nếu trùng -->
              <?php if (in_array($lich['id'], $needNotifyIds)): ?>
                <button class="px-3 py-1 text-sm rounded-lg bg-orange-500 text-white hover:bg-orange-600">
                  <i class="fas fa-bell"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
    

<!-- Modal chi tiết -->
<div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white w-1/2 rounded-xl shadow-lg">
    <!-- Header modal cố định -->
    <div class="flex justify-between items-center px-6 py-3 border-b sticky top-0 bg-white z-10 rounded-t-xl">
      <h2 class="text-lg font-semibold text-gray-800">📋 Chi tiết lịch hẹn</h2>
      <button onclick="closeModal()" class="text-gray-500 hover:text-red-500">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <!-- Nội dung modal cuộn -->
    <div class="p-6 max-h-[400px] overflow-y-auto" id="modalContent">
      <!-- Nội dung sẽ được đổ bằng JS -->
    </div>
  </div>
</div>

<script>
  function openModal(data) {
    document.getElementById("modal").classList.remove("hidden");
    document.getElementById("modalContent").innerHTML = `
      <p><strong>Khách hàng:</strong> ${data.khachhang}</p>
      <p><strong>Môi giới:</strong> ${data.broker}</p>
      <p><strong>Bắt đầu:</strong> ${new Date(data.start * 1000).toLocaleString()}</p>
      <p><strong>Kết thúc:</strong> ${new Date(data.end * 1000).toLocaleString()}</p>
      <p><strong>Trạng thái:</strong> ${data.trangthai}</p>
    `;
  }

  function closeModal() {
    document.getElementById("modal").classList.add("hidden");
  }
</script>

</body>
</html>
