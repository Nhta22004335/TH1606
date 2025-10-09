<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đất Việt BĐS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

<!-- HEADER -->
<header class="sticky top-0 bg-white shadow-sm z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
    <!-- <div class="flex items-center space-x-2">
      <img src="https://cdn-icons-png.flaticon.com/512/684/684908.png" alt="Logo" class="w-8 h-8">
      <h1 class="text-xl font-bold text-gray-900">ĐẤT VIỆT BĐS</h1>
    </div> -->
<div class="flex items-center justify-center md:justify-between w-full md:w-auto cursor-pointer h-16">
                <div class="flex flex-col md:flex-row items-center md:items-center space-y-1 md:space-y-0 md:space-x-3">
                    <div class="relative h-16 w-16 flex items-center justify-center overflow-visible">
                    <img 
                        src="../../../public/assets/anhht/0/datviet.png" 
                        alt="Logo" 
                        class="h-14 transform scale-[2.6] object-contain pr-2">
                    </div>
                    <div class="flex flex-col justify-center leading-tight">
                        <span class="text-3xl pl-6 font-extrabold tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-sky-500 to-cyan-300 drop-shadow-[0_2px_6px_rgba(56,189,248,0.4)] font-[Poppins]">
                            Đất Việt
                        </span>
                        
                        <span class="text-xs sm:text-sm text-gray-500 italic md:pl-0 text-center md:text-left pl-0">
                            Không gian sống lý tưởng cho bạn
                        </span>

                    </div>
                    
                </div>
            </div>
             <!-- Thanh tìm kiếm -->
            <div class="w-full md:flex-1 md:mx-6">
                <div class="flex">
                    <!-- Ô nhập -->
                    <input id="searchInput" type="text" placeholder="Tìm kiếm" class="flex-1 h-10 border border-gray-300 px-3 text-sm rounded-l-md focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none">
                    <!-- Nút search -->
                    <button id="btnSearch" class="h-10 px-4 bg-red-500 text-white rounded-r-md border border-red-500 flex items-center justify-center hover:bg-red-600 transition">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
    <nav class="hidden md:flex space-x-6 font-medium">
      <a href="#" class="hover:text-blue-600 transition">Trang chủ</a>
      <a href="#" class="hover:text-blue-600 transition">Dự án</a>
      <a href="#" class="hover:text-blue-600 transition">Tin tức</a>
      <a href="#" class="hover:text-blue-600 transition">Liên hệ</a>
    </nav>

     <!-- Dropdown -->
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2" style="z-index: 20;">
                        <div class="px-4 py-2 flex items-center space-x-2 border-b">
                            <img src="../../../storage/pictures/avt/<?= $nd['avt'] ?>" alt="Avatar" class="w-10 h-10 rounded-full border">
                            <div>
                                <p class="text-sm font-medium text-gray-800"><?= $ind['ho_ten'] ?></p>
                                <p class="text-xs text-gray-500">Tài khoản cá nhân</p>
                            </div>
                        </div>
                        <a href="trangchu.php?page=../moi_gioi/ql_hoso_canhan" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Trang cá nhân</a>
                        <a href="../../models/auth/xuly_dangxuat.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Đăng xuất</a>
                    </div>
                </div>

    <!-- Nút mobile -->
    <button class="md:hidden p-2 rounded hover:bg-gray-100" id="menu-btn">
      <i class="fas fa-bars text-xl"></i>
    </button>
  </div>

  <!-- Menu mobile -->
  <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
    <nav class="flex flex-col p-4 space-y-2">
      <a href="#" class="py-2 hover:text-blue-600">Trang chủ</a>
      <a href="#" class="py-2 hover:text-blue-600">Dự án</a>
      <a href="#" class="py-2 hover:text-blue-600">Tin tức</a>
      <a href="#" class="py-2 hover:text-blue-600">Liên hệ</a>
    </nav>
  </div>
</header>
<!-- ✅ Banner Hero Carousel -->
<section id="heroCarousel" class="relative h-[480px] overflow-hidden">

    <!-- Slide 1 -->
    <div class="slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-100"
        style="background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.3)), 
        url('https://images.unsplash.com/photo-1501183638710-841dd1904471');">
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-4">
            <h2 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">Tìm ngôi nhà mơ ước của bạn</h2>
            <p class="mb-6 text-lg opacity-90">Khám phá hàng ngàn bất động sản uy tín, chất lượng cao trên toàn quốc.</p>
        </div>
    </div>

    <!-- Slide 2 -->
    <div class="slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-0"
        style="background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.3)), 
        url('https://images.unsplash.com/photo-1570129477492-45c003edd2be');">
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-4">
            <h2 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">Không gian sống xanh & hiện đại</h2>
            <p class="mb-6 text-lg opacity-90">Trải nghiệm cuộc sống tiện nghi trong các khu đô thị xanh.</p>
        </div>
    </div>

    <!-- Slide 3 -->
    <div class="slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-0"
        style="background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.3)), 
        url('https://images.unsplash.com/photo-1494526585095-c41746248156');">
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-4">
            <h2 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">Đầu tư thông minh, sinh lời bền vững</h2>
            <p class="mb-6 text-lg opacity-90">Chọn lựa bất động sản tiềm năng để gia tăng giá trị tương lai.</p>
        </div>
    </div>

    <!-- 🔘 Dots điều hướng -->
    <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-3">
        <button class="dot w-3 h-3 rounded-full bg-white opacity-70"></button>
        <button class="dot w-3 h-3 rounded-full bg-white opacity-40"></button>
        <button class="dot w-3 h-3 rounded-full bg-white opacity-40"></button>
    </div>
</section>


<!-- DANH MỤC -->
<section class="py-16 max-w-7xl mx-auto px-6">
  <h3 class="text-3xl font-bold text-center mb-12 text-gray-900">Danh mục nổi bật</h3>

  <div class="grid md:grid-cols-3 gap-8">
    <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition">
      <div class="flex justify-center mb-4">
        <img src="https://cdn-icons-png.flaticon.com/512/2356/2356784.png" alt="" class="w-12 h-12">
      </div>
      <h4 class="text-xl font-semibold text-center mb-2">Nhà ở</h4>
      <p class="text-gray-600 text-center">
        Hàng ngàn lựa chọn nhà phố, biệt thự, chung cư chất lượng cao.
      </p>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition">
      <div class="flex justify-center mb-4">
        <img src="https://cdn-icons-png.flaticon.com/512/1064/1064956.png" alt="" class="w-12 h-12">
      </div>
      <h4 class="text-xl font-semibold text-center mb-2">Đất nền</h4>
      <p class="text-gray-600 text-center">
        Cập nhật dự án đất nền mới nhất, vị trí tiềm năng, sinh lời cao.
      </p>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition">
      <div class="flex justify-center mb-4">
        <img src="https://cdn-icons-png.flaticon.com/512/889/889590.png" alt="" class="w-12 h-12">
      </div>
      <h4 class="text-xl font-semibold text-center mb-2">Cho thuê</h4>
      <p class="text-gray-600 text-center">
        Tìm kiếm căn hộ, văn phòng cho thuê phù hợp với nhu cầu của bạn.
      </p>
    </div>
  </div>
</section>
<!-- Section App Landing -->
<div class="bg-gray-50 text-gray-900 py-12 mt-4 border-t border-gray-300">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
        
        <!-- Hình ảnh App -->
        <div class="flex justify-center md:justify-start">
            <img class="lazy w-72 h-auto rounded-lg shadow-lg" alt="app demo" src="https://static.homedy.com/src/images/social/app.png">
        </div>

        <!-- Nội dung Text -->
        <div class="text-center md:text-left">
            <p class="text-sm font-semibold text-blue-700 mb-2">TÌM KIẾM - LỰA CHỌN BẤT ĐỘNG SẢN</p>
            <p class="text-lg font-bold text-blue-900 mb-4">MỌI LÚC MỌI NƠI</p>
            <p class="text-sm text-gray-700 leading-relaxed">
                Cài đặt ứng dụng Homedy trên điện thoại để tìm kiếm nhà đất bán - cho thuê nhanh chóng, xem thông tin đầy đủ tất cả các dự án mới, tin tức mới nhất về thị trường nhà đất được cập nhật liên tục.
            </p>
        </div>

        <!-- QR Code + Link Store -->
        <div class="flex flex-col items-center md:items-end space-y-4">
            <div class="mb-4">
                <img class="lazy w-32 h-auto" alt="qr" src="https://static.homedy.com/src/images/social/qr.png">
            </div>
            <div class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:space-x-2">
                <a href="https://apps.apple.com/vn/app/b%E1%BA%A5t-%C4%91%E1%BB%99ng-s%E1%BA%A3n-homedy/id1438315559/?l=vi" title="Homedy trên App Store">
                    <img class="lazy w-36 h-auto" alt="app-store" src="https://static.homedy.com/src/images/social/app-store.png">
                </a>
                <a href="https://play.google.com/store/apps/details?id=com.homedyapp.android" title="Homedy trên Google Play">
                    <img class="lazy w-36 h-auto" alt="google-play" src="https://static.homedy.com/src/images/social/google-play.png">
                </a>
            </div>
        </div>

    </div>
</div>
<!-- Footer chi tiết cho sàn BĐS - nền trắng -->
<footer class="bg-white text-gray-800 border-t border-gray-300">
    <div class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-2 md:grid-cols-5 gap-8">
        
        <!-- Logo + mô tả + liên hệ nhanh -->
        <div class="col-span-2 md:col-span-1">
            <img src="../../../public/assets/anhht/0/datviet.png" alt="Logo" class="h-12 transform scale-[2.8] ml-4 mt-0">
            <p class="text-sm leading-relaxed text-gray-600 mb-4 mt-8">
                Sàn giao dịch bất động sản uy tín, cung cấp thông tin chính xác, dịch vụ tư vấn chuyên nghiệp 
                và hỗ trợ khách hàng trong việc mua, bán, cho thuê bất động sản.
            </p>
        </div>

        <!-- Về chúng tôi -->
        <div>
            <h3 class="text-gray-900 font-semibold mb-4">Về chúng tôi</h3>
            <ul class="space-y-2 text-sm">
                <li><a href="trangchu.php?page=gioithieuvesan" class="hover:text-blue-500">Giới thiệu sàn</a></li>
                <li><a href="trangchu.php?page=danhmucduan" class="hover:text-blue-500">Dự án nổi bật</a></li>
                <li><a href="trangchu.php?page=kinhnghiemdautu" class="hover:text-blue-500">Kinh nghiệm đầu tư</a></li>
                <li><a href="trangchu.php?page=blog" class="hover:text-blue-500">Blog & Tin tức</a></li>
            </ul>
        </div>

        <!-- Hỗ trợ khách hàng -->
        <div>
            <h3 class="text-gray-900 font-semibold mb-4">Hỗ trợ khách hàng</h3>
            <ul class="space-y-2 text-sm">
                <li><a href="trangchu.php?page=lienhe" class="hover:text-blue-500">Liên hệ tư vấn</a></li>
                <li><a href="trangchu.php?page=huongdandaugia" class="hover:text-blue-500">Hướng dẫn mua/bán</a></li>
                <li><a href="trangchu.php?page=cauhoithuonggap" class="hover:text-blue-500">Câu hỏi thường gặp</a></li>
                <li><a href="trangchu.php?page=gopy" class="hover:text-blue-500">Góp ý - khiếu nại</a></li>
            </ul>
        </div>

        <!-- Dự án nổi bật -->
        <div>
            <h3 class="text-gray-900 font-semibold mb-4">Dự án nổi bật</h3>
            <ul class="space-y-2 text-sm">
                <li><a href="#" class="hover:text-blue-500">VinHomes Central Park</a></li>
                <li><a href="#" class="hover:text-blue-500">Sunshine City</a></li>
                <li><a href="#" class="hover:text-blue-500">Masteri Thảo Điền</a></li>
                <li><a href="#" class="hover:text-blue-500">The Manor Central Park</a></li>
                <li><a href="#" class="hover:text-blue-500">Gem Riverside</a></li>
            </ul>
        </div>

        <!-- Mạng xã hội + giờ làm việc -->
        <div>
            <h3 class="text-gray-900 font-semibold mb-4">Kết nối với chúng tôi</h3>
            <div class="flex space-x-4 text-lg mb-3">
                <a href="#" class="hover:text-blue-500"><i class="fab fa-facebook"></i></a>
                <a href="#" class="hover:text-blue-400"><i class="fab fa-instagram"></i></a>
                <a href="#" class="hover:text-blue-300"><i class="fab fa-linkedin"></i></a>
                <a href="#" class="hover:text-red-500"><i class="fab fa-youtube"></i></a>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">
                ⏰ Thời gian làm việc: <br>
                <span class="text-gray-900">Thứ 2 - Thứ 6:</span> 8:00 - 18:00 <br>
                <span class="text-gray-900">Thứ 7:</span> 9:00 - 15:00 <br>
                <span class="text-gray-900">Chủ nhật:</span> Nghỉ
            </p>
        </div>
    </div>

    <div class="bg-gray-100 text-center text-sm py-6 border-t border-gray-300 space-y-2">
        <p class="text-gray-700">
            © 2025 Sàn BĐS 4335. Mọi quyền được bảo lưu. Vui lòng đọc 
            <a href="trangchu.php?page=dieukhoan" class="hover:text-blue-500">Điều khoản & Điều kiện</a>.
        </p>
        <p class="text-gray-700">
            📞 <a href="tel:19001234" class="hover:text-blue-500">1900 1234</a> &nbsp;|&nbsp; 
            ✉ <a href="mailto:hotro@bds.com" class="hover:text-blue-500">hotro@bds.com</a> &nbsp;|&nbsp; 
            📍 72 Nguyễn Huệ, Vĩnh Long
        </p>
    </div>
</footer>


<script>
  const menuBtn = document.getElementById('menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  menuBtn.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
  });
</script>
<script>
    const slides = document.querySelectorAll("#heroCarousel .slide");
    const dots = document.querySelectorAll("#heroCarousel .dot");
    let current = 0;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.style.opacity = i === index ? "1" : "0";
            dots[i].classList.toggle("opacity-70", i === index);
            dots[i].classList.toggle("opacity-40", i !== index);
        });
    }

    function nextSlide() {
        current = (current + 1) % slides.length;
        showSlide(current);
    }

    // Tự động chuyển slide
    setInterval(nextSlide, 5000);

    // Cho phép click chọn slide
    dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {
            current = index;
            showSlide(index);
        });
    });
</script>
</body>
</html>
