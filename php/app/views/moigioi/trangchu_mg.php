<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>NhàĐẹp24h</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans flex flex-col min-h-screen">

  <!-- Header -->
  <header class="bg-white shadow-md px-4 sm:px-8 py-3 flex justify-between items-center">
  <!-- Logo + Tên thương hiệu -->
  <div class="flex items-center space-x-2">
    <img src="../moigioi/anh_mg/logo_tc_mg.png" alt="Logo" class="h-12 w-auto object-contain" />
    <div class="flex flex-col leading-tight">
      <span class="text-2xl font-bold text-blue-600 tracking-wide">Nhà Đất 24h</span>
     <span class="text-sm text-gray-500">Kiến tạo giá trị vàng - Nâng tầm cuộc sống</span>
    </div>
  </div>

  <!-- Mobile menu button -->
  <button id="menu-btn" class="sm:hidden bg-blue-500 text-white px-3 py-2 rounded-lg">
    ☰
  </button>

  <!-- Search + user desktop -->
  <div class="hidden sm:flex items-center space-x-4">
    <input type="text" placeholder="🔍 Tìm kiếm bất động sản..."
      class="px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300 w-64 shadow-sm" />
    <button class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 font-medium transition">
      Đăng tin
    </button>
    <img src="/public/avatar.png" alt="Avatar"
      class="h-10 w-10 rounded-full border-2 border-blue-500 shadow-sm" />
  </div>
</header>


  <!-- Sidebar + Content -->
  <div class="flex flex-1">
    <!-- Sidebar -->
    <aside id="sidebar"
      class="w-64 bg-white shadow-lg h-screen p-4 fixed sm:static inset-y-0 left-0 transform -translate-x-full sm:translate-x-0 transition-transform duration-300 z-50">
      <nav class="space-y-4 mt-16 sm:mt-0 font-medium">
        <a href="?page=tin_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600"><span>📋</span><span>Tin đăng của tôi</span></a>
        <a href="?page=khachhang_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600"><span>👤</span><span>Khách hàng quan tâm</span></a>
        <a href="?page=giaodich_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600"><span>📑</span><span>Hợp đồng / Giao dịch</span></a>
        <a href="?page=thongke_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600"><span>📊</span><span>Thống kê cá nhân</span></a>
        <a href="?page=chat_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600"><span>💬</span><span>Chat & Thông báo</span></a>
        <a href="?page=hoso_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600"><span>⚙️</span><span>Hồ sơ cá nhân</span></a>
        <a href="dangxuat_mg.php" class="flex items-center space-x-2 text-red-600 hover:text-red-700 font-semibold"><span>🚪</span><span>Đăng xuất</span></a>
      </nav>
    </aside>

    <!-- Overlay mobile -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40 sm:hidden"></div>

    <!-- Main content -->
    <main class="flex-1 p-6 ml-0 sm:ml-64 transition-all duration-300">
      <?php
      $allowed_pages = ['tin_mg', 'khachhang_mg', 'giaodich_mg', 'thongke_mg', 'chat_mg', 'hoso_mg'];
      if (isset($_GET['page']) && in_array($_GET['page'], $allowed_pages)) {
        $page = $_GET['page'];
        $file = __DIR__ . "/{$page}.php";
        if (file_exists($file)) {
          include $file;
        } else {
          echo "<p class='text-red-600'>Trang bạn yêu cầu không tồn tại.</p>";
        }
      } else {
        echo "<h2 class='text-2xl font-bold mb-4 text-blue-600'>Chào mừng bạn đến NhàĐẹp24h</h2>
              <p class='text-gray-600'>Hãy chọn chức năng từ menu bên trái để bắt đầu quản lý tin.</p>";
      }
      ?>
    </main>
  </div>

  <!-- Footer -->
  <footer class="bg-white border-t shadow-inner mt-auto">
    <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row justify-between items-center text-gray-600 text-sm">
      <p>© 2025 <span class="font-semibold text-blue-600">Nhà Đất 24h</span>. Bản quyền thuộc về chúng tôi.</p>
      <div class="space-x-4 mt-2 sm:mt-0">
        <a href="#" class="hover:text-blue-600">Về chúng tôi</a>
        <a href="#" class="hover:text-blue-600">Liên hệ</a>
        <a href="#" class="hover:text-blue-600">Điều khoản</a>
      </div>
    </div>
  </footer>

  <script>
    const btn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    btn.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    });
  </script>

</body>
</html>
