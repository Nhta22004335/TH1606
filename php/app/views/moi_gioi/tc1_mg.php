<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trang Chủ | Bất Động Sản CCC</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

  <!-- ✅ Header mới theo ảnh -->
  <header class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">
      <!-- Logo
      <div class="flex items-center space-x-2">
        <img src="https://cdn-icons-png.flaticon.com/512/619/619153.png" alt="logo" class="w-8 h-8">
        <span class="text-xl font-bold text-blue-600">ĐẤT VIỆT</span>
      </div> -->

   <div class="flex items-center cursor-pointer select-none">
  <!-- Logo -->
  <div class="relative flex-shrink-0 mr-6">
    <img 
      src="../../../public/assets/anhht/0/datviet.png" 
      alt="Logo" 
      class="h-14 transform scale-[2.6] object-contain">
    <div class="absolute inset-0 rounded-full ring-2 ring-blue-500 opacity-20 blur-md"></div>
  </div>

  <!-- Tên thương hiệu -->
  <div class="flex flex-col justify-center leading-tight">
    <span class="text-3xl font-extrabold tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-sky-400 to-cyan-300 drop-shadow-[0_0_8px_rgba(56,189,248,0.4)] font-[Poppins]">
  Đất Việt
</span>

    <span class="text-[13px] text-gray-500 font-medium -mt-1">
      Không gian sống lý tưởng cho bạn
    </span>
  </div>
</div>


      <!-- Thanh tìm kiếm -->
      <div class="flex-1 mx-6">
        <div class="flex items-center bg-gray-100 rounded-full overflow-hidden shadow-inner">
          <input type="text" placeholder="Tìm kiếm người dùng, dự án, sản phẩm..." class="flex-1 px-4 py-2 bg-transparent focus:outline-none text-gray-700">
          <button class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-r-full">🔍</button>
        </div>
      </div>

      <!-- Phần bên phải -->
      <div class="flex items-center space-x-4">
        <button class="text-blue-600 hover:text-blue-800 font-medium">🗺️ Bản đồ</button>
        <div class="flex items-center space-x-2">
          <img src="https://i.pravatar.cc/40" alt="avatar" class="w-9 h-9 rounded-full border">
          <span class="text-sm font-medium text-gray-700">Nguyễn Tuấn Anh</span>
        </div>
      </div>
    </div>

    <!-- Thanh chức năng -->
    <nav class="bg-blue-50 border-t border-b border-gray-200">
      <ul class="flex justify-center space-x-6 py-2 text-sm font-medium text-gray-700">
        <li><a href="#" class="hover:text-blue-600">Quản lý người dùng</a></li>
        <li><a href="#" class="hover:text-blue-600">Quản lý đơn hàng</a></li>
        <li><a href="#" class="hover:text-blue-600">Quản lý sản phẩm BĐS</a></li>
        <li><a href="#" class="hover:text-blue-600">Quản lý CMS</a></li>
        <li><a href="#" class="hover:text-blue-600">Thông báo & Chat</a></li>
        <li><a href="#" class="hover:text-blue-600">Quản lý đặt lịch</a></li>
        <li><a href="#" class="hover:text-blue-600">Quản lý vi phạm</a></li>
      </ul>
    </nav>
  </header>

  <!-- Banner -->
  <section class="relative bg-cover bg-center h-[480px]" style="background-image: url('https://images.unsplash.com/photo-1501183638710-841dd1904471');">
    <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col items-center justify-center text-white text-center px-4">
      <h2 class="text-4xl md:text-5xl font-bold mb-4">Tìm ngôi nhà mơ ước của bạn</h2>
      <p class="mb-6 text-lg">Khám phá hàng ngàn bất động sản uy tín, chất lượng cao trên toàn quốc.</p>
      <div class="flex bg-white rounded-lg overflow-hidden w-full max-w-2xl">
        <input type="text" placeholder="Nhập địa điểm, quận, dự án..." class="flex-1 px-4 py-3 text-gray-700 focus:outline-none">
        <button class="bg-blue-600 text-white px-6 hover:bg-blue-700">Tìm kiếm</button>
      </div>
    </div>
  </section>

  <!-- Danh mục nổi bật -->
  <section class="container mx-auto py-16">
    <h3 class="text-3xl font-bold text-center mb-10 text-gray-800">Danh mục nổi bật</h3>
    <div class="grid md:grid-cols-3 gap-8 px-4">
      <div class="bg-white rounded-2xl shadow hover:shadow-xl transition p-6">
        <img src="https://images.unsplash.com/photo-1570129477492-45c003edd2be" class="rounded-xl mb-4 w-full h-56 object-cover" alt="Nhà ở">
        <h4 class="text-xl font-semibold mb-2">Nhà ở</h4>
        <p class="text-gray-600">Khám phá các căn nhà ở hiện đại, tiện nghi và giá hợp lý.</p>
      </div>
      <div class="bg-white rounded-2xl shadow hover:shadow-xl transition p-6">
        <img src="https://images.unsplash.com/photo-1507089947368-19c1da9775ae" class="rounded-xl mb-4 w-full h-56 object-cover" alt="Căn hộ cao cấp">
        <h4 class="text-xl font-semibold mb-2">Căn hộ cao cấp</h4>
        <p class="text-gray-600">Lựa chọn căn hộ trung tâm với tiện ích đẳng cấp và vị trí thuận lợi.</p>
      </div>
      <div class="bg-white rounded-2xl shadow hover:shadow-xl transition p-6">
        <img src="https://images.unsplash.com/photo-1493809842364-78817add7ffb" class="rounded-xl mb-4 w-full h-56 object-cover" alt="Đất nền">
        <h4 class="text-xl font-semibold mb-2">Đất nền</h4>
        <p class="text-gray-600">Đầu tư an toàn với các khu đất tiềm năng phát triển lâu dài.</p>
      </div>
    </div>
  </section>

  <!-- Dự án mới -->
  <section class="bg-blue-50 py-16">
    <div class="container mx-auto px-4">
      <h3 class="text-3xl font-bold text-center mb-10 text-gray-800">Dự án mới nhất</h3>
      <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-xl transition">
          <img src="https://images.unsplash.com/photo-1598928506311-c55ded91a20c" class="h-56 w-full object-cover" alt="Dự án 1">
          <div class="p-6">
            <h4 class="text-xl font-semibold mb-2">Khu đô thị Vạn Phúc</h4>
            <p class="text-gray-600">Thủ Đức, TP.HCM</p>
            <p class="text-blue-600 font-bold mt-2">Giá từ 2.5 tỷ VNĐ</p>
          </div>
        </div>
        <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-xl transition">
          <img src="https://images.unsplash.com/photo-1572120360610-d971b9d7767c" class="h-56 w-full object-cover" alt="Dự án 2">
          <div class="p-6">
            <h4 class="text-xl font-semibold mb-2">Sunshine City</h4>
            <p class="text-gray-600">Quận 7, TP.HCM</p>
            <p class="text-blue-600 font-bold mt-2">Giá từ 3.2 tỷ VNĐ</p>
          </div>
        </div>
        <div class="bg-white shadow rounded-xl overflow-hidden hover:shadow-xl transition">
          <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688" class="h-56 w-full object-cover" alt="Dự án 3">
          <div class="p-6">
            <h4 class="text-xl font-semibold mb-2">The Manor Central Park</h4>
            <p class="text-gray-600">Hà Nội</p>
            <p class="text-blue-600 font-bold mt-2">Giá từ 4 tỷ VNĐ</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-300 py-10 mt-16">
    <div class="container mx-auto grid md:grid-cols-3 gap-8 px-4">
      <div>
        <h4 class="text-lg font-bold text-white mb-4">CCC Real Estate</h4>
        <p>Cung cấp giải pháp bất động sản toàn diện, giúp bạn dễ dàng mua, bán và thuê nhà.</p>
      </div>
      <div>
        <h4 class="text-lg font-bold text-white mb-4">Liên hệ</h4>
        <p>📍 Quận 2, TP.HCM</p>
        <p>📞 0909 999 999</p>
        <p>📧 contact@ccc.com</p>
      </div>
      <div>
        <h4 class="text-lg font-bold text-white mb-4">Theo dõi chúng tôi</h4>
        <div class="flex space-x-4 text-xl">
          <a href="#" class="hover:text-blue-400">🌐</a>
          <a href="#" class="hover:text-blue-400">📘</a>
          <a href="#" class="hover:text-blue-400">📸</a>
        </div>
      </div>
    </div>
    <div class="text-center mt-10 text-gray-500 text-sm border-t border-gray-700 pt-4">
      © 2025 CCC Real Estate. All rights reserved.
    </div>
  </footer>

</body>
</html>
