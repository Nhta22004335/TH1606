<?php
$tintuc = [
  [
    "tieude"=>"Sốt đất nền vùng ven TP.HCM trở lại",
    "mota"=>"Giá đất nền tại các huyện ngoại thành TP.HCM tăng trở lại sau thời gian chững.",
    "chuyenmuc"=>"Đất nền","ngay"=>"01/10/2025","img"=>"https://picsum.photos/800/400?random=201"
  ],
  [
    "tieude"=>"Căn hộ cao cấp quận 7 hút khách hàng trẻ",
    "mota"=>"Các dự án căn hộ quận 7 với tiện ích hiện đại đang thu hút nhiều nhà đầu tư trẻ.",
    "chuyenmuc"=>"Căn hộ","ngay"=>"29/09/2025","img"=>"https://picsum.photos/400/250?random=202"
  ],
  [
    "tieude"=>"Biệt thự ven sông Sài Gòn được săn đón",
    "mota"=>"Biệt thự ven sông với không gian xanh và thoáng mát trở thành xu hướng mới.",
    "chuyenmuc"=>"Biệt thự","ngay"=>"28/09/2025","img"=>"https://picsum.photos/400/250?random=203"
  ],
  [
    "tieude"=>"Nhà phố thương mại Bình Dương bùng nổ",
    "mota"=>"Các dự án nhà phố thương mại tại Bình Dương ghi nhận lượng giao dịch cao.",
    "chuyenmuc"=>"Nhà phố","ngay"=>"27/09/2025","img"=>"https://picsum.photos/400/250?random=204"
  ],
  [
    "tieude"=>"Dòng căn hộ trung cấp tại Hà Nội tăng giá",
    "mota"=>"Căn hộ trung cấp tại các quận nội đô Hà Nội ghi nhận mức tăng giá 8% trong quý 3.",
    "chuyenmuc"=>"Căn hộ","ngay"=>"26/09/2025","img"=>"https://picsum.photos/400/250?random=205"
  ],
  [
    "tieude"=>"Đất nền Long An: nhiều nhà đầu tư quay lại",
    "mota"=>"Hạ tầng giao thông kết nối TP.HCM - Long An giúp thị trường đất nền khu vực này nóng trở lại.",
    "chuyenmuc"=>"Đất nền","ngay"=>"25/09/2025","img"=>"https://picsum.photos/400/250?random=206"
  ],
  [
    "tieude"=>"Biệt thự nghỉ dưỡng ven biển Nha Trang hút khách",
    "mota"=>"Các dự án biệt thự nghỉ dưỡng ven biển có pháp lý rõ ràng đang thu hút dòng vốn lớn.",
    "chuyenmuc"=>"Biệt thự","ngay"=>"24/09/2025","img"=>"https://picsum.photos/400/250?random=207"
  ],
  [
    "tieude"=>"Nhà phố liền kề khu Đông TP.HCM sôi động",
    "mota"=>"Khu vực Thủ Đức, Quận 9 đang là tâm điểm phát triển loại hình nhà phố liền kề.",
    "chuyenmuc"=>"Nhà phố","ngay"=>"23/09/2025","img"=>"https://picsum.photos/400/250?random=208"
  ],
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tin tức Bất động sản</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">
  <div class="max-w-7xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="mb-8 text-center">
      <h1 class="text-3xl font-bold text-gray-800">Tin tức Bất động sản</h1>
      <p class="text-gray-500">Cập nhật xu hướng thị trường, dự án và chính sách mới nhất</p>
    </div>

    <!-- Tin nổi bật -->
    <?php $noibat = array_shift($tintuc); ?>
    <div class="bg-white rounded-xl shadow overflow-hidden mb-10">
      <img src="<?= $noibat['img'] ?>" alt="<?= $noibat['tieude'] ?>" class="w-full h-96 object-cover">
      <div class="p-6">
        <span class="text-sm font-medium text-blue-600 uppercase"><?= $noibat['chuyenmuc'] ?></span>
        <h2 class="text-2xl font-bold mt-2 mb-3 hover:text-blue-600 cursor-pointer">
          <?= $noibat['tieude'] ?>
        </h2>
        <p class="text-gray-600 mb-3"><?= $noibat['mota'] ?></p>
        <div class="flex gap-3">
          <a href="#" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Chi tiết</a>
          <button class="px-4 py-2 bg-yellow-500 text-white text-sm rounded-lg hover:bg-yellow-600">Đánh dấu</button>
          <button class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Xóa</button>
        </div>
        <span class="text-sm text-gray-400">Ngày đăng: <?= $noibat['ngay'] ?></span>
      </div>
    </div>

    <!-- Danh sách tin khác dạng slider -->
    <div class="relative">
      <!-- Nút prev -->
      <button onclick="slideLeft()" 
        class="absolute left-0 top-1/2 -translate-y-1/2 bg-white shadow p-2 rounded-full hover:bg-gray-100 z-10">
        ←
      </button>
      <!-- Nút next -->
      <button onclick="slideRight()" 
        class="absolute right-0 top-1/2 -translate-y-1/2 bg-white shadow p-2 rounded-full hover:bg-gray-100 z-10">
        →
      </button>

      <div id="newsSlider" class="flex gap-6 overflow-x-auto scroll-smooth no-scrollbar py-2">
        <?php foreach ($tintuc as $tin): ?>
          <div class="bg-white rounded-xl shadow hover:shadow-lg transition w-72 flex-shrink-0">
            <img src="<?= $tin['img'] ?>" alt="<?= $tin['tieude'] ?>" class="w-full h-40 object-cover rounded-t-xl">
            <div class="p-4">
              <span class="text-xs font-medium text-indigo-600 uppercase"><?= $tin['chuyenmuc'] ?></span>
              <h3 class="font-semibold text-lg mt-1 mb-2 hover:text-blue-600 cursor-pointer">
                <?= $tin['tieude'] ?>
              </h3>
              <p class="text-sm text-gray-600 line-clamp-2"><?= $tin['mota'] ?></p>
              <div class="flex gap-2 mt-3">
                <a href="#" class="flex-1 text-center px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Chi tiết</a>
                <button class="flex-1 px-2 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600">Đánh dấu</button>
                <button class="flex-1 px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Xóa</button>
              </div>
              <span class="block mt-2 text-xs text-gray-400">Ngày đăng: <?= $tin['ngay'] ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <script>
    const slider = document.getElementById('newsSlider');
    function slideLeft() { slider.scrollBy({ left: -300, behavior: 'smooth' }); }
    function slideRight() { slider.scrollBy({ left: 300, behavior: 'smooth' }); }
  </script>

  <style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</body>
</html>
