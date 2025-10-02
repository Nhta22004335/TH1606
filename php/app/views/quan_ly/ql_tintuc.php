<?php
$tintuc = [
  [
    "tieude"=>"Sốt đất nền vùng ven TP.HCM trở lại",
    "mota"=>"Giá đất nền tại các huyện ngoại thành TP.HCM tăng trở lại sau thời gian chững.",
    "chuyenmuc"=>"Đất nền","ngay"=>"01/10/2025","img"=>"https://picsum.photos/800/400?random=201",
    "moigioi"=>"Nguyễn Văn A","sdt"=>"0909123456","avt"=>"https://i.pravatar.cc/100?img=1","view"=>1250
  ],
  [
    "tieude"=>"Căn hộ cao cấp quận 7 hút khách hàng trẻ",
    "mota"=>"Các dự án căn hộ quận 7 với tiện ích hiện đại đang thu hút nhiều nhà đầu tư trẻ.",
    "chuyenmuc"=>"Căn hộ","ngay"=>"29/09/2025","img"=>"https://picsum.photos/400/250?random=202",
    "moigioi"=>"Trần Thị B","sdt"=>"0912345678","avt"=>"https://i.pravatar.cc/100?img=2","view"=>980
  ],
  [
    "tieude"=>"Biệt thự ven sông Sài Gòn được săn đón",
    "mota"=>"Biệt thự ven sông với không gian xanh và thoáng mát trở thành xu hướng mới.",
    "chuyenmuc"=>"Biệt thự","ngay"=>"28/09/2025","img"=>"https://picsum.photos/400/250?random=203",
    "moigioi"=>"Lê Văn C","sdt"=>"0988111222","avt"=>"https://i.pravatar.cc/100?img=3","view"=>1500
  ],
  [
    "tieude"=>"Nhà phố thương mại Bình Dương bùng nổ",
    "mota"=>"Các dự án nhà phố thương mại tại Bình Dương ghi nhận lượng giao dịch cao.",
    "chuyenmuc"=>"Nhà phố","ngay"=>"27/09/2025","img"=>"https://picsum.photos/400/250?random=204",
    "moigioi"=>"Phạm Thị D","sdt"=>"0933777888","avt"=>"https://i.pravatar.cc/100?img=4","view"=>745
  ],
  [
    "tieude"=>"Nhà phố thương mại Bình Dương bùng nổ",
    "mota"=>"Các dự án nhà phố thương mại tại Bình Dương ghi nhận lượng giao dịch cao.",
    "chuyenmuc"=>"Nhà phố","ngay"=>"27/09/2025","img"=>"https://picsum.photos/400/250?random=204",
    "moigioi"=>"Phạm Thị D","sdt"=>"0933777888","avt"=>"https://i.pravatar.cc/100?img=4","view"=>745
  ],
  [
    "tieude"=>"Nhà phố thương mại Bình Dương bùng nổ",
    "mota"=>"Các dự án nhà phố thương mại tại Bình Dương ghi nhận lượng giao dịch cao.",
    "chuyenmuc"=>"Nhà phố","ngay"=>"27/09/2025","img"=>"https://picsum.photos/400/250?random=204",
    "moigioi"=>"Phạm Thị D","sdt"=>"0933777888","avt"=>"https://i.pravatar.cc/100?img=4","view"=>745
  ],
];
$noibat = array_shift($tintuc);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tin tức Bất động sản</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<div class="flex justify-between items-center mb-6 mt-4 px-4 py-2 rounded-lg ">
    <!-- Tiêu đề -->
    <h1 class="flex items-center text-2xl font-bold text-gray-600">
        <img src="../../../public/assets/anhht/0/news.gif" 
             class="w-12 h-12 mr-3 ml-4 rounded-full border border-gray-300">
        Quản lý tin tức
    </h1>

    <!-- Nút mở bộ lọc (mobile) -->
    <button onclick="document.getElementById('filterPanel').classList.remove('hidden')" 
        class="md:hidden bg-blue-600 text-white px-3 py-2 rounded-lg shadow hover:bg-blue-700 transition">
        <i class="fas fa-filter"></i>
    </button>
</div>


<!-- Layout -->
<div class="max-w-7xl mx-auto mt-6 flex gap-6">

<!-- Sidebar bộ lọc -->
<aside id="filterPanel" 
    class="hidden md:block fixed md:static top-0 right-0 w-72 h-full md:h-fit bg-white shadow rounded-xl p-4 z-50 transition-transform">
    <div class="flex items-center justify-between md:block mb-4">
        <h2 class="text-lg font-semibold flex items-center gap-2">
            <span>🔍</span> Bộ lọc
        </h2>
        <!-- Nút đóng (chỉ hiện mobile) -->
        <button onclick="document.getElementById('filterPanel').classList.add('hidden')" 
            class="md:hidden text-gray-500 text-2xl">&times;</button>
    </div>

    <label class="block text-sm mb-2">Loại tin</label>
    <select class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring-0 focus:border-blue-500">
        <option>Tất cả</option>
        <option>Đất nền</option>
        <option>Căn hộ</option>
        <option>Biệt thự</option>
        <option>Nhà phố</option>
    </select>

    <label class="block text-sm mb-2">Môi giới</label>
    <input type="text" placeholder="Tên môi giới"
        class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring-0 focus:border-blue-500">

    <label class="block text-sm mb-2">Ngày đăng</label>
    <input type="date"
        class="w-full border rounded-lg p-2 mb-4 outline-none focus:ring-0 focus:border-blue-500">

    <label class="block text-sm mb-2">Sắp xếp theo</label>
    <select class="w-full border rounded-lg p-2 outline-none focus:ring-0 focus:border-blue-500">
        <option>Mới nhất</option>
        <option>Lượt xem cao</option>
    </select>

    <button class="mt-4 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Áp dụng</button>
</aside>

<!-- Nội dung -->
<main class="flex-1">
    <!-- Tin nổi bật -->
    <div class="bg-white rounded-xl shadow overflow-hidden mb-6">
        <img src="<?= $noibat['img'] ?>" class="w-full h-80 object-cover">
        <div class="p-6">
        <span class="text-sm text-blue-600 uppercase font-medium"><?= $noibat['chuyenmuc'] ?></span>
        <h2 class="text-2xl font-bold mt-2 mb-2"><?= $noibat['tieude'] ?></h2>
        <p class="text-gray-600 mb-3"><?= $noibat['mota'] ?></p>

        <div class="flex items-center gap-3 mb-3">
            <img src="<?= $noibat['avt'] ?>" class="w-10 h-10 rounded-full border">
            <div>
            <p class="font-semibold"><?= $noibat['moigioi'] ?></p>
            <p class="text-xs text-gray-500"><?= $noibat['sdt'] ?></p>
            </div>
        </div>

        <div class="flex gap-2 mt-3 mb-3"> 
            <a href="#" class="flex-1 text-center px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Chi tiết</a> 
            <button class="flex-1 px-2 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600">Đánh dấu</button> 
            <button class="flex-1 px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Xóa</button> 
        </div>

        <div class="flex justify-between text-sm text-gray-500">
            <span>📅 <?= $noibat['ngay'] ?></span>
            <span>👁 <?= number_format($noibat['view']) ?> lượt xem</span>
        </div>
        </div>
    </div>

    
</main>
</div>
<div class="relative">
<!-- Nút trái -->
<button onclick="slideLeft()" 
    class="absolute left-4 top-1/2 -translate-y-1/2
           bg-blue-500/80 backdrop-blur-md shadow-lg
           p-5 rounded-full text-white text-2xl
           hover:bg-blue-600/90 hover:scale-110
           transition transform duration-300 ease-in-out z-10">
    <i class="fas fa-chevron-left"></i>
</button>

<!-- Nút phải -->
<button onclick="slideRight()" 
    class="absolute right-4 top-1/2 -translate-y-1/2
           bg-blue-500/80 backdrop-blur-md shadow-lg
           p-5 rounded-full text-white text-2xl
           hover:bg-blue-600/90 hover:scale-110
           transition transform duration-300 ease-in-out z-10">
    <i class="fas fa-chevron-right"></i>
</button>





    <!-- Danh sách tin tức dạng slider ngang -->
    <div id="newsSlider" class="flex flex-nowrap gap-6 overflow-x-auto scroll-smooth no-scrollbar py-2">
        <?php foreach ($tintuc as $tin): ?>
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition w-72 flex-shrink-0">
                <img src="<?= $tin['img'] ?>" class="w-full h-40 object-cover rounded-t-xl">
                <div class="p-4">
                    <h3 class="font-semibold mt-1 mb-2 hover:text-blue-600 cursor-pointer"><?= $tin['tieude'] ?></h3>
                    <p class="text-sm text-gray-600 line-clamp-2"><?= $tin['mota'] ?></p>
                    
                    <div class="flex items-center gap-2 mt-3">
                        <img src="<?= $tin['avt'] ?>" class="w-8 h-8 rounded-full border">
                        <div>
                            <p class="text-xs font-semibold"><?= $tin['moigioi'] ?></p>
                            <p class="text-[11px] text-gray-500"><?= $tin['sdt'] ?></p>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-3 mb-3"> 
                        <a href="#" class="flex-1 text-center px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Chi tiết</a> 
                        <button class="flex-1 px-2 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600">Đánh dấu</button> 
                        <button class="flex-1 px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Xóa</button> 
                    </div>

                    <div class="flex justify-between mt-3 text-xs text-gray-500">
                        <span>📅 <?= $tin['ngay'] ?></span>
                        <span>👁 <?= number_format($tin['view']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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
