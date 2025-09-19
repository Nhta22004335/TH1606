<!DOCTYPE html>
<html lang="vi" x-data="{ openFilter:false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bất động sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../public/assets/fontawesome/css/all.min.css">
</head>
<body>

<!-- Header -->
<header class="bg-white border-b shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between py-3 space-y-3 md:space-y-0">
            
            <!-- Logo + slogan -->
            <div class="flex items-center justify-between w-full md:w-auto">
                <div class="flex items-center space-x-3">
                <img src="../../public/assets/anhht/0/logo-homedy.png" alt="Logo" class="h-10">
                <span class="text-xs sm:text-sm text-gray-500 italic">
                    Không gian sống lý tưởng cho bạn
                </span>
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

            <!-- Bản đồ + avatar + đăng tin -->
            <div class="flex items-center justify-evenly space-x-3 w-full md:w-auto">
                <a href="#" class="px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm hover:bg-gray-100 flex items-center">
                    <i class="fas fa-map-marked-alt mr-2 text-blue-600"></i> Bản đồ
                </a>

                <div class="flex items-center space-x-2 cursor-pointer">
                    <img src="../../public/assets/anhht/0/avt.png" alt="Avatar" class="w-9 h-9 rounded-full border border-gray-300">
                    <span class="text-sm text-gray-700">Nguyễn Văn A</span>
                </div>

                <a href="#" class="px-3 py-1.5 border border-gray-400 text-gray-600 text-xs sm:text-sm rounded-md font-normal hover:bg-gray-200 transition">
                    Đăng tin
                </a>
            </div>
        </div>
    </div>

    <!-- Nút mở menu trên mobile -->
    <div class="flex items-center justify-between w-full md:hidden px-4 py-2 border-t">
        <button id="mobileMenuBtn" class="text-gray-700 text-xl">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Menu ngang -->
    <nav class="bg-gray-50 border-t hidden md:block">
        <ul class="flex space-x-6 py-2 text-sm font-normal text-gray-700 whitespace-nowrap justify-evenly">
            <li class="relative">
                <a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý Khách hàng <i class="fas fa-chevron-right ml-1 transition-transform duration-300"></i></a> 
                <ul class="hidden absolute left-0 top-full bg-white border shadow-md mt-4 sub-menu" style="z-index: 10;">
                    <li><a href="trangchu.php?page=khachhang" class="block px-4 py-2 hover:bg-blue-100">Danh sách khách hàng</a></li>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Lịch sử đăng nhập/đăng xuất</a></li>
                </ul>
            </li>
            <li class="relative ">
                <a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý môi giới<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 10;">
                    <li><a href="trangchu.php?page=moigioi" class="block px-4 py-2 hover:bg-blue-100">Danh sách môi giới</a></li>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Quản lý biểu mẫu</a></li>
                </ul>
            </li>
            <li class="relative">
                <a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý đơn hàng<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 10;">
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Theo dõi các y.c mua/bán/thuê</a>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Quản lý thanh toán</a></li>
                </ul>
            </li>
            <li class="relative"><a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý sản phẩm bds<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 10;">
                    <li><a href="trangchu.php?page=sanpham" class="block px-4 py-2 hover:bg-blue-100">Danh sách sản phẩm bds</a></li>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Quản lý hình ảnh/videos</a></li>
                    <li><a href="trangchu.php?page=danhgiasanpham" class="block px-4 py-2 hover:bg-blue-100">Quản lý đánh giá</a></li>
                </ul>
            </li>
            <li class="relative">
                <a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý CMS<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 10;">
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Quản lý tin tức</a></li>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Quản lý bài đăng</a></li>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Quản lý FAQ/hướng dẫn sử dụng web</a></li>
                </ul>
            </li>
            <li class="relative">
                <a class="hover:text-blue-600 menu-btn inline-flex items-center">Thông báo & chat<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 10;">
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Gửi thông báo</a></li>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Quản lý hộp thoại chat</a></li>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Quản lý thông báo</a></li>
                </ul>
            </li>
            <li class="relative">
                <a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý đặt lịch<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 10;">
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Danh sách lịch đặt</a></li>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Xử lý xung đột</a></li>
                    <li><a href="#" class="block px-4 py-2 hover:bg-blue-100">Nhắc nhở</a></li>
                </ul>
            </li>
            <li><a href="#" class="hover:text-blue-600">Quản lý vi phạm</a></li>
        </ul>
    </nav>


    <!-- Menu mobile dạng overlay -->
    <div id="mobileMenu" class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 z-50 md:hidden overflow-y-auto">
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <span class="font-semibold text-gray-700">Menu</span>
            <button id="closeMobileMenu" class="text-gray-700 text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="flex flex-col text-sm text-gray-700">
            <!-- Quản lý Khách hàng -->
            <li class="border-b">
                <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                    Quản lý Khách hàng <i class="fas fa-chevron-right"></i>
                </button>
                <ul class="hidden flex-col bg-gray-50">
                    <li><a href="trangchu.php?page=khachhang" class="block px-6 py-2 hover:bg-blue-100">Danh sách khách hàng</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Lịch sử đăng nhập/đăng xuất</a></li>
                </ul>
            </li>

            <!-- Quản lý môi giới -->
            <li class="border-b">
                <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                    Quản lý môi giới <i class="fas fa-chevron-right"></i>
                </button>
                <ul class="hidden flex-col bg-gray-50">
                    <li><a href="trangchu.php?page=moigioi" class="block px-6 py-2 hover:bg-blue-100">Danh sách môi giới</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Quản lý biểu mẫu</a></li>
                </ul>
            </li>

            <!-- Quản lý đơn hàng -->
            <li class="border-b">
                <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                    Quản lý đơn hàng <i class="fas fa-chevron-right"></i>
                </button>
                <ul class="hidden flex-col bg-gray-50">
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Theo dõi yêu cầu mua/bán/thuê</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Quản lý thanh toán</a></li>
                </ul>
            </li>

            <!-- Quản lý sản phẩm bds -->
            <li class="border-b">
                <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                    Quản lý sản phẩm BĐS <i class="fas fa-chevron-right"></i>
                </button>
                <ul class="hidden flex-col bg-gray-50">
                    <li><a href="trangchu.php?page=sanpham" class="block px-6 py-2 hover:bg-blue-100">Danh sách sản phẩm BĐS</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Quản lý hình ảnh/videos</a></li>
                    <li><a href="trangchu.php?page=danhgiasanpham" class="block px-6 py-2 hover:bg-blue-100">Quản lý đánh giá</a></li>
                </ul>
            </li>

            <!-- Quản lý CMS -->
            <li class="border-b">
                <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                    Quản lý CMS <i class="fas fa-chevron-right"></i>
                </button>
                <ul class="hidden flex-col bg-gray-50">
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Quản lý tin tức</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Quản lý bài đăng</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Quản lý FAQ/hướng dẫn sử dụng web</a></li>
                </ul>
            </li>

            <!-- Thông báo & chat -->
            <li class="border-b">
                <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                    Thông báo & chat <i class="fas fa-chevron-right"></i>
                </button>
                <ul class="hidden flex-col bg-gray-50">
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Gửi thông báo</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Quản lý hộp thoại chat</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Quản lý thông báo</a></li>
                </ul>
            </li>

            <!-- Quản lý đặt lịch -->
            <li class="border-b">
                <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                    Quản lý đặt lịch <i class="fas fa-chevron-right"></i>
                </button>
                <ul class="hidden flex-col bg-gray-50">
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Danh sách lịch đặt</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Xử lý xung đột</a></li>
                    <li><a href="#" class="block px-6 py-2 hover:bg-blue-100">Nhắc nhở</a></li>
                </ul>
            </li>

            <!-- Quản lý vi phạm (mục đơn, không có submenu) -->
            <li class="border-b">
                <a href="#" class="block px-4 py-2 hover:bg-blue-100">Quản lý vi phạm</a>
            </li>
        </ul>
    </div>
</header>

<script>
    // Mở/đóng menu mobile
    const mobileMenuBtn = document.getElementById("mobileMenuBtn");
    const mobileMenu = document.getElementById("mobileMenu");
    const closeMobileMenu = document.getElementById("closeMobileMenu");

    mobileMenuBtn.addEventListener("click", () => {
        mobileMenu.classList.remove("-translate-x-full");
    });

    closeMobileMenu.addEventListener("click", () => {
        mobileMenu.classList.add("-translate-x-full");
    });

    // Toggle submenu trong mobile
    document.querySelectorAll(".menu-mobile-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const submenu = btn.nextElementSibling;
            submenu.classList.toggle("hidden");

            // Đổi icon
            const icon = btn.querySelector("i");
            icon.classList.toggle("fa-chevron-down");
            icon.classList.toggle("fa-chevron-right");
        });
    });

    const btnmenu = document.querySelectorAll(".menu-btn");
    btnmenu.forEach(btn => {
        const submenu = btn.nextElementSibling;
        const icon = btn.querySelector("i");

        btn.addEventListener("click", (e) => {
            e.stopPropagation();

            // Ẩn các menu con khác + reset icon
            document.querySelectorAll(".sub-menu").forEach(sm => {
                if (sm !== submenu) sm.classList.add("hidden");
            });

            document.querySelectorAll(".menu-btn i").forEach(ic => {
                if (ic !== icon) {
                    ic.classList.remove("fa-chevron-down");
                    ic.classList.add("fa-chevron-right");
                }
            });

            // Toggle menu hiện tại
            submenu.classList.toggle("hidden");

            // Đổi icon theo trạng thái submenu
            if (submenu.classList.contains("hidden")) {
                icon.classList.remove("fa-chevron-down");
                icon.classList.add("fa-chevron-right");
            } else {
                icon.classList.remove("fa-chevron-right");
                icon.classList.add("fa-chevron-down");
            }
        });
    });

    // Click ra ngoài đóng hết menu và reset icon
    document.addEventListener("click", () => {
        document.querySelectorAll(".sub-menu").forEach(sm => sm.classList.add("hidden"));
        document.querySelectorAll(".menu-btn i").forEach(ic => {
            ic.classList.remove("fa-chevron-down");
            ic.classList.add("fa-chevron-right");
        });
    });

    document.getElementById("btnSearch").addEventListener("click", function() {
        const query = document.getElementById("searchInput").value.trim();
        if(query) {
            // Lấy URL hiện tại
            const url = new URL(window.location.href);
            url.searchParams.set("search", query); 
            window.location.href = url.toString();
        }
    });

</script>

<?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'trangchu';
    $allowed_pages = ['sanpham', 'danhgiasanpham', 'danhgiasanphamct', 'khachhang', 'moigioi'];
    $showHome = ($page === 'trangchu');
?>

<div id="main-content">
    <?php
        if(in_array($page, $allowed_pages) && $page != 'trangchu') include $page . '.php';
    ?>
</div>

</body>
</html>