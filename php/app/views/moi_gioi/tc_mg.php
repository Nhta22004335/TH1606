<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trang Chủ | Bất Động Sản CCC</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

  <!-- Header -->
  <header class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center p-4">
      <h1 class="text-2xl font-bold text-blue-700">🏠 CCC Real Estate</h1>
      <nav class="space-x-6 text-gray-700 font-medium">
        <a href="#" class="hover:text-blue-600">Trang chủ</a>
        <a href="#" class="hover:text-blue-600">Mua</a>
        <a href="#" class="hover:text-blue-600">Thuê</a>
        <a href="#" class="hover:text-blue-600">Dự án</a>
        <a href="#" class="hover:text-blue-600">Liên hệ</a>
        <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Đăng nhập</a>
      </nav>
    </div>
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
