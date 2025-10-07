<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trang Chủ | Bất Động Sản CCC</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Hiệu ứng hover cho menu */
    nav a {
      position: relative;
      transition: color 0.3s ease;
    }
    nav a::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -3px;
      width: 0;
      height: 2px;
      background-color: #2563EB;
      transition: width 0.3s ease;
    }
    nav a:hover::after {
      width: 100%;
    }
    nav a:hover {
      color: #2563EB;
    }

    /* Hover animation cho card */
    .hover-card {
      transition: all 0.3s ease;
    }
    .hover-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    /* Hover cho icon footer */
    .social-icon {
      transition: all 0.25s ease;
    }
    .social-icon:hover {
      color: #38bdf8;
      transform: scale(1.15);
    }
  </style>
</head>

<body class="bg-gray-100 text-gray-800 font-sans">

  <!-- ✅ HEADER -->
  <header class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">

      <!-- Logo + tên thương hiệu -->
      <div class="flex items-center cursor-pointer select-none">
        <div class="relative flex-shrink-0 mr-6">
          <img 
            src="../../../public/assets/anhht/0/datviet.png" 
            alt="Logo" 
            class="h-14 transform scale-[2.6] object-contain">
          <div class="absolute inset-0 rounded-full ring-2 ring-blue-500 opacity-20 blur-md"></div>
        </div>

        <div class="flex flex-col justify-center leading-tight">
          <span class="text-3xl font-extrabold tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-sky-500 to-cyan-300 drop-shadow-[0_2px_6px_rgba(56,189,248,0.4)] font-[Poppins]">
            Đất Việt
          </span>
          <span class="text-[13px] text-gray-500 font-medium -mt-1">
            Không gian sống lý tưởng cho bạn
          </span>
        </div>
      </div>

      <!-- Thanh tìm kiếm -->
      <div class="flex-1 mx-6">
        <div class="flex items-center bg-gray-50 rounded-full shadow-md hover:shadow-lg border border-gray-200 transition">
          <input type="text" placeholder="Tìm kiếm người dùng, dự án, sản phẩm..." class="flex-1 px-4 py-2 bg-transparent focus:outline-none text-gray-700">
          <button class="bg-gradient-to-r from-red-600 to-rose-500 hover:from-red-700 hover:to-rose-600 text-white px-5 py-2 rounded-r-full transition">
            🔍
          </button>
        </div>
      </div>

      <!-- Góc phải -->
      <div class="flex items-center space-x-4">
        <button class="text-blue-600 hover:text-blue-800 font-medium flex items-center space-x-1">
          🗺️ <span>Bản đồ</span>
        </button>
        <div class="flex items-center space-x-2">
          <img src="https://i.pravatar.cc/40" alt="avatar" class="w-9 h-9 rounded-full border">
          <span class="text-sm font-medium text-gray-700">Nguyễn Tuấn Anh</span>
        </div>
      </div>
    </div>

    <!-- Thanh chức năng -->
    <nav class="bg-blue-50 border-t border-b border-gray-200">
      <ul class="flex justify-center space-x-6 py-2 text-sm font-medium text-gray-700">
        <li><a href="#">Quản lý người dùng</a></li>
        <li><a href="#">Quản lý đơn hàng</a></li>
        <li><a href="#">Quản lý sản phẩm BĐS</a></li>
        <li><a href="#">Quản lý CMS</a></li>
        <li><a href="#">Thông báo & Chat</a></li>
        <li><a href="#">Quản lý đặt lịch</a></li>
        <li><a href="#">Quản lý vi phạm</a></li>
      </ul>
    </nav>
  </header>

  <!-- ✅ Banner Hero -->
  <section class="relative bg-cover bg-center h-[480px]" 
           style="background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1501183638710-841dd1904471');">
    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-4">
      <h2 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">Tìm ngôi nhà mơ ước của bạn</h2>
      <p class="mb-6 text-lg opacity-90">Khám phá hàng ngàn bất động sản uy tín, chất lượng cao trên toàn quốc.</p>
      <div class="flex bg-white rounded-lg overflow-hidden w-full max-w-2xl">
        <input type="text" placeholder="Nhập địa điểm, quận, dự án..." class="flex-1 px-4 py-3 text-gray-700 focus:outline-none">
        <button class="bg-gradient-to-r from-blue-600 to-sky-500 text-white px-6 hover:from-blue-700 hover:to-sky-600 transition">Tìm kiếm</button>
      </div>
    </div>
  </section>

  <!-- ✅ Danh mục nổi bật -->
  <section class="container mx-auto py-16">
    <h3 class="text-3xl font-bold text-center mb-10 text-gray-800">Danh mục nổi bật</h3>
    <div class="grid md:grid-cols-3 gap-8 px-4">
      <div class="bg-white rounded-2xl shadow hover-card p-6">
        <img src="https://images.unsplash.com/photo-1570129477492-45c003edd2be" class="rounded-xl mb-4 w-full h-56 object-cover" alt="Nhà ở">
        <h4 class="text-xl font-semibold mb-2">Nhà ở</h4>
        <p class="text-gray-600">Khám phá các căn nhà ở hiện đại, tiện nghi và giá hợp lý.</p>
      </div>
      <div class="bg-white rounded-2xl shadow hover-card p-6">
        <img src="https://images.unsplash.com/photo-1507089947368-19c1da9775ae" class="rounded-xl mb-4 w-full h-56 object-cover" alt="Căn hộ cao cấp">
        <h4 class="text-xl font-semibold mb-2">Căn hộ cao cấp</h4>
        <p class="text-gray-600">Lựa chọn căn hộ trung tâm với tiện ích đẳng cấp và vị trí thuận lợi.</p>
      </div>
      <div class="bg-white rounded-2xl shadow hover-card p-6">
        <img src="https://images.unsplash.com/photo-1493809842364-78817add7ffb" class="rounded-xl mb-4 w-full h-56 object-cover" alt="Đất nền">
        <h4 class="text-xl font-semibold mb-2">Đất nền</h4>
        <p class="text-gray-600">Đầu tư an toàn với các khu đất tiềm năng phát triển lâu dài.</p>
      </div>
    </div>
  </section>

  <!-- ✅ Dự án mới -->
  <section class="bg-blue-50 py-16">
    <div class="container mx-auto px-4">
      <h3 class="text-3xl font-bold text-center mb-10 text-gray-800">Dự án mới nhất</h3>
      <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-white shadow rounded-xl overflow-hidden hover-card">
          <img src="https://images.unsplash.com/photo-1598928506311-c55ded91a20c" class="h-56 w-full object-cover" alt="Dự án 1">
          <div class="p-6">
            <h4 class="text-xl font-semibold mb-2">Khu đô thị Vạn Phúc</h4>
            <p class="text-gray-600">Thủ Đức, TP.HCM</p>
            <p class="text-blue-600 font-bold mt-2">Giá từ 2.5 tỷ VNĐ</p>
          </div>
        </div>

        <div class="bg-white shadow rounded-xl overflow-hidden hover-card">
          <img src="https://images.unsplash.com/photo-1572120360610-d971b9d7767c" class="h-56 w-full object-cover" alt="Dự án 2">
          <div class="p-6">
            <h4 class="text-xl font-semibold mb-2">Sunshine City</h4>
            <p class="text-gray-600">Quận 7, TP.HCM</p>
            <p class="text-blue-600 font-bold mt-2">Giá từ 3.2 tỷ VNĐ</p>
          </div>
        </div>

        <div class="bg-white shadow rounded-xl overflow-hidden hover-card">
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

  <!-- ✅ Footer
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
          <a href="#" class="social-icon">🌐</a>
          <a href="#" class="social-icon">📘</a>
          <a href="#" class="social-icon">📸</a>
        </div>
      </div>
    </div>
    <div class="text-center mt-10 text-gray-500 text-sm border-t border-gray-700 pt-4">
      © 2025 CCC Real Estate. All rights reserved.
    </div>
  </footer> -->
  <!-- Footer chi tiết cho sàn BĐS - nền trắng -->
<!-- Footer chi tiết cho sàn BĐS - nền xanh đậm -->
<footer class="bg-gradient-to-r from-[#0B1E3F] via-[#102B6A] to-[#1E3A8A] text-gray-100">
  <div class="max-w-7xl mx-auto px-6 py-10 grid grid-cols-2 md:grid-cols-5 gap-8">
    
    <!-- Logo + mô tả -->
    <div class="col-span-2 md:col-span-1">
      <img src="../../../public/assets/anhht/0/datviet.png" alt="Logo" class="h-12 transform scale-[2.8] ml-4 mt-0">
      <p class="text-sm leading-relaxed text-gray-300 mb-4 mt-8">
        Sàn giao dịch bất động sản uy tín, cung cấp thông tin chính xác, dịch vụ tư vấn chuyên nghiệp 
        và hỗ trợ khách hàng trong việc mua, bán, cho thuê bất động sản.
      </p>
    </div>

    <!-- Về chúng tôi -->
    <div>
      <h3 class="text-white font-semibold mb-4 uppercase tracking-wide">Về chúng tôi</h3>
      <ul class="space-y-2 text-sm">
        <li><a href="trangchu.php?page=gioithieuvesan" class="hover:text-[#60A5FA]">Giới thiệu sàn</a></li>
        <li><a href="trangchu.php?page=danhmucduan" class="hover:text-[#60A5FA]">Dự án nổi bật</a></li>
        <li><a href="trangchu.php?page=kinhnghiemdautu" class="hover:text-[#60A5FA]">Kinh nghiệm đầu tư</a></li>
        <li><a href="trangchu.php?page=blog" class="hover:text-[#60A5FA]">Blog & Tin tức</a></li>
      </ul>
    </div>

    <!-- Hỗ trợ khách hàng -->
    <div>
      <h3 class="text-white font-semibold mb-4 uppercase tracking-wide">Hỗ trợ khách hàng</h3>
      <ul class="space-y-2 text-sm">
        <li><a href="trangchu.php?page=lienhe" class="hover:text-[#60A5FA]">Liên hệ tư vấn</a></li>
        <li><a href="trangchu.php?page=huongdandaugia" class="hover:text-[#60A5FA]">Hướng dẫn mua/bán</a></li>
        <li><a href="trangchu.php?page=cauhoithuonggap" class="hover:text-[#60A5FA]">Câu hỏi thường gặp</a></li>
        <li><a href="trangchu.php?page=gopy" class="hover:text-[#60A5FA]">Góp ý - khiếu nại</a></li>
      </ul>
    </div>

    <!-- Dự án nổi bật -->
    <div>
      <h3 class="text-white font-semibold mb-4 uppercase tracking-wide">Dự án nổi bật</h3>
      <ul class="space-y-2 text-sm">
        <li><a href="#" class="hover:text-[#60A5FA]">VinHomes Central Park</a></li>
        <li><a href="#" class="hover:text-[#60A5FA]">Sunshine City</a></li>
        <li><a href="#" class="hover:text-[#60A5FA]">Masteri Thảo Điền</a></li>
        <li><a href="#" class="hover:text-[#60A5FA]">The Manor Central Park</a></li>
        <li><a href="#" class="hover:text-[#60A5FA]">Gem Riverside</a></li>
      </ul>
    </div>

    <!-- Kết nối -->
    <div>
      <h3 class="text-white font-semibold mb-4 uppercase tracking-wide">Kết nối với chúng tôi</h3>
      <div class="flex space-x-4 text-lg mb-3">
        <a href="#" class="hover:text-[#3B82F6]"><i class="fab fa-facebook"></i></a>
        <a href="#" class="hover:text-[#EC4899]"><i class="fab fa-instagram"></i></a>
        <a href="#" class="hover:text-[#0EA5E9]"><i class="fab fa-linkedin"></i></a>
        <a href="#" class="hover:text-[#F87171]"><i class="fab fa-youtube"></i></a>
      </div>
      <p class="text-sm text-gray-300 leading-relaxed">
        ⏰ Thời gian làm việc: <br>
        <span class="text-gray-100">Thứ 2 - Thứ 6:</span> 8:00 - 18:00 <br>
        <span class="text-gray-100">Thứ 7:</span> 9:00 - 15:00 <br>
        <span class="text-gray-100">Chủ nhật:</span> Nghỉ
      </p>
    </div>
  </div>

  <!-- Dòng bản quyền -->
  <div class="bg-[#081C36] text-center text-sm py-6 border-t border-[#1E3A8A] space-y-2">
    <p class="text-gray-400">
      © 2025 <span class="text-sky-400 font-semibold">Sàn BĐS Đất Việt</span>. Mọi quyền được bảo lưu. 
      <a href="trangchu.php?page=dieukhoan" class="hover:text-sky-400">Điều khoản & Điều kiện</a>.
    </p>
    <p class="text-gray-400">
      📞 <a href="tel:19001234" class="hover:text-sky-400">1900 1234</a> &nbsp;|&nbsp; 
      ✉ <a href="mailto:hotro@bds.com" class="hover:text-sky-400">hotro@bds.com</a> &nbsp;|&nbsp; 
      📍 72 Nguyễn Huệ, Vĩnh Long
    </p>
  </div>
</footer>



</body>
</html>
