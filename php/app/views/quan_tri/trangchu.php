<?php
    session_start();
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $id = $_SESSION['id_nguoi_dung'];

    $sql = "SELECT * FROM quan_tri WHERE id_nguoi_dung = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $tk = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" x-data="{ openFilter:false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bất động sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body>

<!-- Header -->
<header class="bg-white border-b shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between py-3 space-y-3 md:space-y-0">
            
            <!-- Logo + slogan -->
            <div class="flex items-center justify-between w-full md:w-auto">
                <div class="flex items-center space-x-3">
                <img src="../../../public/assets/anhht/0/logo-homedy.png" alt="Logo" class="h-10">
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

                <div x-data="{ open: false }" class="relative">
                    <!-- Nút avatar + tên -->
                    <div @click="open = !open" class="flex items-center space-x-2 cursor-pointer">
                        <img src="../../../public/assets/anhht/0/<?= $tk['avt'] ?>" alt="Avatar" class="w-9 h-9 rounded-full border border-gray-300">
                        <span class="text-sm text-gray-700"><?= $tk['ho_ten'] ?></span>
                    </div>

                    <!-- Dropdown -->
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2" style="z-index: 20;">
                    <div class="px-4 py-2 flex items-center space-x-2 border-b">
                        <img src="../../../public/assets/anhht/0/<?= $tk['avt'] ?>" alt="Avatar" class="w-10 h-10 rounded-full border">
                        <div>
                            <p class="text-sm font-medium text-gray-800"><?= $tk['ho_ten'] ?></p>
                            <p class="text-xs text-gray-500">Tài khoản cá nhân</p>
                        </div>
                    </div>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Trang cá nhân</a>
                        <a href="../../models/auth/xuly_dangxuat.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Đăng xuất</a>
                    </div>
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
                    <li><a href="trangchu.php?page=lichsuxacthuc" class="block px-4 py-2 hover:bg-blue-100">Lịch sử đăng nhập/đăng xuất</a></li>
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
                    <li><a href="trangchu.php?page=ql_anh_video_bds" class="block px-4 py-2 hover:bg-blue-100">Quản lý hình ảnh/videos</a></li>
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
                    <li><a href="trangchu.php?page=ql_hop_thoai_chat" class="block px-4 py-2 hover:bg-blue-100">Quản lý hộp thoại chat</a></li>
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
                    <li><a href="trangchu.php?page=ql_hop_thoai_chat" class="block px-6 py-2 hover:bg-blue-100">Quản lý hộp thoại chat</a></li>
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
    $allowed_pages = ['sanpham', 'danhgiasanpham', 'danhgiasanphamct', 'khachhang', 'moigioi', 'lichsuxacthuc', 'ql_anh_video_bds',
                     'ql_hop_thoai_chat'];
    $showHome = ($page === 'trangchu');
?>

<div id="main-content">
    <?php
        if(in_array($page, $allowed_pages) && $page != 'trangchu') include $page . '.php';
    ?>
</div>

<!-- Section App Landing -->
<div class="bg-blue-0 text-gray-900 py-12 mt-4 border-t border-gray-300">
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
    <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-5 gap-8">
        
        <!-- Logo + mô tả + liên hệ nhanh -->
        <div>
            <img src="../../../public/assets/anhht/0/logo-homedy.png" alt="Logo" class="h-12 mb-4">
            <p class="text-sm leading-relaxed text-gray-600 mb-4">
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
                <li><a href="trangchu.php?page=kinhnghiemdau tu" class="hover:text-blue-500">Kinh nghiệm đầu tư</a></li>
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

        <!-- Dự án nổi bật / Liên kết nhanh -->
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

            <!-- Giờ làm việc -->
            <p class="text-sm text-gray-600 leading-relaxed">
                ⏰ Thời gian làm việc: <br>
                <span class="text-gray-900">Thứ 2 - Thứ 6:</span> 8:00 - 18:00 <br>
                <span class="text-gray-900">Thứ 7:</span> 9:00 - 15:00 <br>
                <span class="text-gray-900">Chủ nhật:</span> Nghỉ
            </p>
        </div>

    </div>

    <!-- Bản quyền, điều khoản & thông tin liên hệ nhanh -->
    <div class="bg-gray-100 text-center text-sm py-6 border-t border-gray-300 space-y-2">
        <p class="text-gray-700">
            © 2025 Sàn BĐS 4335. Mọi quyền được bảo lưu. Trang web này cung cấp thông tin về các dự án bất động sản, dịch vụ mua bán, cho thuê nhà đất, và các tin tức liên quan đến thị trường bất động sản Việt Nam. 
            Vui lòng đọc <a href="trangchu.php?page=dieukhoan" class="hover:text-blue-500">Điều khoản & Điều kiện</a> trước khi sử dụng dịch vụ.
        </p>
        <p class="text-gray-700">
            📞 Hotline: <a href="tel:19001234" class="hover:text-blue-500">1900 1234</a> &nbsp;|&nbsp; 
            ✉ Email: <a href="mailto:support@homedy.com" class="hover:text-blue-500">hotro@bds.com</a> &nbsp;|&nbsp; 
            📍 Địa chỉ: 72, phường Long Châu, Nguyễn Huệ, Vĩnh Long
        </p>
    </div>
</footer>





</body>
</html>