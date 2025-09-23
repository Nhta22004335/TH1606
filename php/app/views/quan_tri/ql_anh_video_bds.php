<?php
$media = [
  [
    "id" => 1,
    "id_bai_dang" => 1,
    "loai" => "image",
    "ten_file" => "cay_xanh.jpg",
    "duong_dan" => "https://picsum.photos/300?random=1",
    "kich_thuoc" => 245678,
    "trang_thai" => "Bình thường"
  ],
  [
    "id" => 2,
    "id_bai_dang" => 1,
    "loai" => "image",
    "ten_file" => "vuon_rau.png",
    "duong_dan" => "https://picsum.photos/300?random=2",
    "kich_thuoc" => 187532,
    "trang_thai" => "Cảnh báo nhẹ"
  ],
  [
    "id" => 3,
    "id_bai_dang" => 2,
    "loai" => "video",
    "ten_file" => "gioi_thieu_trang_trai.mp4",
    "duong_dan" => "https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4",
    "kich_thuoc" => 10485760,
    "trang_thai" => "Cảnh báo trung bình"
  ],
  [
    "id" => 4,
    "id_bai_dang" => 2,
    "loai" => "image",
    "ten_file" => "mo_hinh_trai.jpg",
    "duong_dan" => "https://picsum.photos/300?random=3",
    "kich_thuoc" => 356781,
    "trang_thai" => "Cảnh báo nghiêm trọng"
  ],
  [
    "id" => 5,
    "id_bai_dang" => 3,
    "loai" => "video",
    "ten_file" => "huong_dan_cham_soc.mp4",
    "duong_dan" => "https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4",
    "kich_thuoc" => 8054321,
    "trang_thai" => "Bình thường"
  ],
  [
    "id" => 6,
    "id_bai_dang" => 3,
    "loai" => "image",
    "ten_file" => "hat_giong.png",
    "duong_dan" => "https://picsum.photos/300?random=4",
    "kich_thuoc" => 123456,
    "trang_thai" => "Cảnh báo nhẹ"
  ],
  [
    "id" => 7,
    "id_bai_dang" => 3,
    "loai" => "image",
    "ten_file" => "hat_giong.png",
    "duong_dan" => "https://picsum.photos/300?random=5",
    "kich_thuoc" => 123456,
    "trang_thai" => "Bình thường"
  ]
];

// Hàm helper để lấy màu label theo trạng thái
function getLabelClass($status) {
  switch ($status) {
    case "Bình thường": return "bg-green-100 text-green-700";
    case "Cảnh báo nhẹ": return "bg-yellow-100 text-yellow-700";
    case "Cảnh báo trung bình": return "bg-orange-100 text-orange-700";
    case "Cảnh báo nghiêm trọng": return "bg-red-100 text-red-700";
    default: return "bg-gray-100 text-gray-700";
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý Ảnh & Video</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
  <div class="max-w-6xl mx-auto p-4">

    <h1 class="flex items-center justify-center text-2xl font-bold text-gray-600 mb-4">
      <img src="../../../public/assets/anhht/0/user.gif" alt="Users" 
           style="width: 50px; height: 50px; margin-right: 10px;">
      Quản lý Ảnh & Video
    </h1>

    <!-- Tabs -->
    <div class="flex justify-center mb-6">
      <button onclick="showTab('images')" id="tab-images" 
              class="px-4 py-2 bg-blue-600 text-white rounded-l">Ảnh</button>
      <button onclick="showTab('videos')" id="tab-videos" 
              class="px-4 py-2 bg-gray-200 rounded-r">Video</button>
    </div>

    <!-- Content Ảnh -->
    <div id="images" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
      <?php foreach ($media as $item): ?>
        <?php if ($item["loai"] === "image"): ?>
          <div class="bg-white border border-gray-100 p-2 rounded-xl shadow-md hover:shadow-xl transition">
            <img src="<?= $item['duong_dan'] ?>" alt="<?= $item['ten_file'] ?>" 
                 class="w-full h-48 object-cover rounded-lg mb-3">
            <h2 class="font-semibold text-lg truncate"><?= $item['ten_file'] ?></h2>
            <span class="inline-block px-2 py-1 text-xs font-semibold rounded 
                         <?= getLabelClass($item['trang_thai']) ?>">
              <?= $item['trang_thai'] ?>
            </span>
            <p class="text-gray-500 text-sm mt-1">
              Kích thước: <?= number_format($item['kich_thuoc']/1024, 2) ?> KB
            </p>
            <div class="flex gap-2 mt-3">
              <a href="<?= $item['duong_dan'] ?>" target="_blank" 
                 class="px-3 py-1 bg-green-500 text-white text-sm rounded">Xem</a>
              <a href="<?= $item['duong_dan'] ?>" download 
                 class="px-3 py-1 bg-blue-500 text-white text-sm rounded">Tải</a>
              <button class="px-3 py-1 bg-red-500 text-white text-sm rounded">Xóa</button>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Content Video -->
    <div id="videos" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 hidden">
      <?php foreach ($media as $item): ?>
        <?php if ($item["loai"] === "video"): ?>
          <div class="bg-white border border-gray-100 p-4 rounded-xl shadow-md hover:shadow-xl transition">
            <div class="w-full aspect-video mb-3">
              <video controls class="w-full h-full object-cover rounded-lg">
                <source src="<?= $item['duong_dan'] ?>" type="video/mp4">
              </video>
            </div>
            <h2 class="font-semibold text-lg truncate"><?= $item['ten_file'] ?></h2>
            <span class="inline-block px-2 py-1 text-xs font-semibold rounded 
                         <?= getLabelClass($item['trang_thai']) ?>">
              <?= $item['trang_thai'] ?>
            </span>
            <p class="text-gray-500 text-sm mt-1">
              Kích thước: <?= number_format($item['kich_thuoc']/1024/1024, 2) ?> MB
            </p>
            <div class="flex gap-2 mt-3">
              <a href="<?= $item['duong_dan'] ?>" target="_blank" 
                 class="px-3 py-1 bg-green-500 text-white text-sm rounded">Xem</a>
              <a href="<?= $item['duong_dan'] ?>" download 
                 class="px-3 py-1 bg-blue-500 text-white text-sm rounded">Tải</a>
              <button class="px-3 py-1 bg-red-500 text-white text-sm rounded">Xóa</button>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
    function showTab(tab) {
      document.getElementById('images').classList.add('hidden');
      document.getElementById('videos').classList.add('hidden');
      document.getElementById('tab-images').classList.remove('bg-blue-600','text-white');
      document.getElementById('tab-videos').classList.remove('bg-blue-600','text-white');
      document.getElementById('tab-images').classList.add('bg-gray-200');
      document.getElementById('tab-videos').classList.add('bg-gray-200');

      document.getElementById(tab).classList.remove('hidden');
      document.getElementById('tab-' + tab).classList.add('bg-blue-600','text-white');
      document.getElementById('tab-' + tab).classList.remove('bg-gray-200');
    }
  </script>
</body>
</html>
