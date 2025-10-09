<?php
    session_start();
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $id = $_SESSION['id_nguoi_dung'];

    $sql = "
        SELECT q.vai_tro
        FROM phan_quyen pq
        JOIN quyen q ON pq.id_quyen = q.id
        WHERE pq.id_nguoi_dung = :id_nguoi_dung
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_nguoi_dung', $id, PDO::PARAM_STR); 
    $stmt->execute();
    $dsQuyen = $stmt->fetchAll(PDO::FETCH_COLUMN); 

    $sql = "SELECT * FROM info_nguoi_dung WHERE id_nguoi_dung = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $ind = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM nguoi_dung WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $nd = $stmt->fetch(PDO::FETCH_ASSOC);
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
                    <button id="btnSearch" class="h-10 px-4 bg-blue-500 text-white rounded-r-md border border-blue-500 flex items-center justify-center hover:bg-blue-700 transition">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Bản đồ + avatar (khách hàng không có đăng tin) -->
            <div class="flex items-center justify-evenly space-x-3 w-full md:w-auto">
                <!-- 🏠 Nút Home -->
                <a href="trangchu.php" class="px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm hover:bg-gray-100 flex items-center">
                    <i class="fas fa-home mr-2 text-blue-600"></i> Trang chủ
                </a>
                <a href="#" class="px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm hover:bg-gray-100 flex items-center">
                    <i class="fas fa-map-marked-alt mr-2 text-blue-600"></i> Bản đồ
                </a>
                <div x-data="{ open: false }" class="relative">
                    <!-- Nút avatar + tên -->
                    <div @click="open = !open" class="flex items-center space-x-2 cursor-pointer">
                        <img src="../../../storage/pictures/avt/<?= $nd['avt'] ?>" alt="Avatar" class="w-9 h-9 rounded-full border border-gray-300">
                        <span class="text-sm text-gray-700"><?= $ind['ho_ten'] ?></span>
                    </div>
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

                <?php if (in_array('moigioi', $dsQuyen)): ?>
                    <a href="#" class="px-3 py-1.5 border border-gray-400 text-gray-600 text-xs sm:text-sm rounded-md font-normal hover:bg-gray-200 transition">
                        Đăng tin
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Nút mở menu trên mobile -->
    <div class="flex items-center justify-between w-full md:hidden px-4 py-2 border-t">
        <button id="mobileMenuBtn" class="text-gray-700 text-xl">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Menu Desktop-->
    <nav class="bg-gray-50 border-t hidden md:block cursor-pointer">
        <ul class="flex space-x-6 py-2 text-sm font-normal text-gray-700 whitespace-nowrap justify-evenly">
            <?php if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)): ?>
                <li class="relative">
                    <a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý người dùng <i class="fas fa-chevron-right ml-1 transition-transform duration-300"></i></a> 
                    <ul class="hidden absolute left-0 top-full bg-white border shadow-md mt-4 sub-menu" style="z-index: 99999;">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=ds_nguoidung" class="block px-4 py-2 hover:bg-blue-100">Danh sách người dùng</a></li>
                            <li><a href="trangchu.php?page=ls_xacthuc" class="block px-4 py-2 hover:bg-blue-100">Lịch sử xác thực</a></li>
                            <li><a href="trangchu.php?page=ql_bieumau" class="block px-4 py-2 hover:bg-blue-100">Quản lý biểu mẫu</a></li>
                        <?php endif; ?>
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/ql_hoso" class="block px-4 py-2 hover:bg-blue-100">Quản lý đơn từ</a></li>
                            <li><a href="trangchu.php?page=../moi_gioi/cn_hoso" class="block px-4 py-2 hover:bg-blue-100">Tạo hồ sơ mới</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (in_array('moigioi', $dsQuyen)): ?>
                <li class="relative">
                    <a class="hover:text-blue-600 menu-btn inline-flex items-center">Khách hàng tiềm năng<i class="fas fa-chevron-right ml-1 transition-transform duration-300"></i></a> 
                    <ul class="hidden absolute left-0 top-full bg-white border shadow-md mt-4 sub-menu" style="z-index: 99999;">
                        <li><a href="trangchu.php?page=../moi_gioi/kh_quantam" class="block px-4 py-2 hover:bg-blue-100">Khách hàng quan tâm</a></li>
                        <li><a href="trangchu.php?page=../moi_gioi/kh_damua" class="block px-4 py-2 hover:bg-blue-100">Khách hàng đã mua</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)): ?>
                <li class="relative">
                    <a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý đơn hàng<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                    <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 99999;">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=td_yeucau_mbt" class="block px-4 py-2 hover:bg-blue-100">Theo dõi các y.c mua/bán/thuê</a>
                            <li><a href="trangchu.php?page=ql_thanhtoan" class="block px-4 py-2 hover:bg-blue-100">Quản lý thanh toán</a></li>
                        <?php endif; ?>
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/gd_canhan" class="block px-4 py-2 hover:bg-blue-100">Giao dịch cá nhân</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (!in_array('khachhang', $dsQuyen)): ?>
                <li class="relative"><a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý sản phẩm bds<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                    <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 99999;">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=ds_sanpham_bds" class="block px-4 py-2 hover:bg-blue-100">Danh sách sản phẩm bds</a></li>
                            <li><a href="trangchu.php?page=ql_anh_video_bds" class="block px-4 py-2 hover:bg-blue-100">Quản lý hình ảnh/videos</a></li>
                            <li><a href="trangchu.php?page=ql_danhgia" class="block px-4 py-2 hover:bg-blue-100">Quản lý đánh giá</a></li>
                        <?php endif; ?>
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/sp_canhan" class="block px-4 py-2 hover:bg-blue-100">Sản phẩm cá nhân</a></li>
                            <li><a href="trangchu.php?page=../moi_gioi/dang_sp" class="block px-4 py-2 hover:bg-blue-100">Đăng sản phẩm</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (in_array('quantri', $dsQuyen)|| in_array('moigioi', $dsQuyen)): ?>
                <li class="relative">
                    <a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý CMS<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                    <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 99999;">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=ql_tintuc" class="block px-4 py-2 hover:bg-blue-100">Quản lý tin tức</a></li>
                            <li><a href="trangchu.php?page=ql_baidang" class="block px-4 py-2 hover:bg-blue-100">Quản lý bài đăng</a></li>
                        <?php endif; ?>
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/ql_tintuc_mg" class="block px-4 py-2 hover:bg-blue-100">Quản lý tin tức </a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)): ?>
                <li class="relative">
                    <a class="hover:text-blue-600 menu-btn inline-flex items-center">Thông báo & chat<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                    <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 99999;">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=g_thongbao" class="block px-4 py-2 hover:bg-blue-100">Gửi thông báo</a></li>
                            <li><a href="trangchu.php?page=ql_hopthoai" class="block px-4 py-2 hover:bg-blue-100">Quản lý hộp thoại</a></li>
                            <li><a href="trangchu.php?page=ql_thongbao" class="block px-4 py-2 hover:bg-blue-100">Quản lý thông báo</a></li>
                        <?php endif; ?>
                        
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/ql_thongbao_mg" class="block px-4 py-2 hover:bg-blue-100">Quản lý thông báo</a></li>
                            <li><a href="trangchu.php?page=../moi_gioi/g_thongbao_kh" class="block px-4 py-2 hover:bg-blue-100">Gửi thông báo khách hàng</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)): ?>
                <li class="relative">
                    <a class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý đặt lịch<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                    <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 99999;">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=ds_datlich" class="block px-4 py-2 hover:bg-blue-100">Danh sách lịch đặt</a></li>
                        <?php endif; ?>
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/lt_canhan" class="block px-4 py-2 hover:bg-blue-100">Lịch trình cá nhân</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (in_array('quantri', $dsQuyen)): ?>
                <li class="relative">
                    <a href="#" class="hover:text-blue-600 menu-btn inline-flex items-center">Quản lý vi phạm<i class="fas fa-chevron-right ml-2 transition-transform duration-300"></i></a>
                    <ul class="hidden absolute bg-white border shadow-md mt-4 sub-menu" style="z-index: 99999;">
                        <li><a href="trangchu.php?page=ds_vipham" class="block px-4 py-2 hover:bg-blue-100">Danh sách vi phạm</a></li>
                    </ul>
                </li>
            <?php endif; ?>

        </ul>
    </nav>

    <!-- Menu Mobile -->
    <div id="mobileMenu" class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 z-50 md:hidden overflow-y-auto">
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <span class="font-semibold text-gray-700">Menu</span>
            <button id="closeMobileMenu" class="text-gray-700 text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="flex flex-col text-sm text-gray-700">

            <!-- Quản lý người dùng -->
            <?php if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)): ?>
                <li class="border-b">
                    <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                        Quản lý người dùng <i class="fas fa-chevron-right"></i>
                    </button>
                    <ul class="hidden flex-col bg-gray-50">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=ds_nguoidung" class="block px-6 py-2 hover:bg-blue-100">Danh sách người dùng</a></li>
                            <li><a href="trangchu.php?page=ls_xacthuc" class="block px-6 py-2 hover:bg-blue-100">Lịch sử xác thực</a></li>
                            <li><a href="trangchu.php?page=ql_bieumau" class="block px-6 py-2 hover:bg-blue-100">Quản lý biểu mẫu</a></li>
                        <?php endif; ?>
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/ql_hoso" class="block px-6 py-2 hover:bg-blue-100">Quản lý đơn từ</a></li>
                            <li><a href="trangchu.php?page=../moi_gioi/cn_hoso" class="block px-6 py-2 hover:bg-blue-100">Tạo hồ sơ mới</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Khách hàng tiềm năng -->
            <?php if (in_array('moigioi', $dsQuyen)): ?>
                <li class="border-b">
                    <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                        Khách hàng tiềm năng <i class="fas fa-chevron-right"></i>
                    </button>
                    <ul class="hidden flex-col bg-gray-50">
                        <li><a href="trangchu.php?page=../moi_gioi/kh_quantam" class="block px-6 py-2 hover:bg-blue-100">Khách hàng quan tâm</a></li>
                        <li><a href="trangchu.php?page=../moi_gioi/kh_damua" class="block px-6 py-2 hover:bg-blue-100">Khách hàng đã mua</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Quản lý đơn hàng -->
            <?php if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)): ?>
                <li class="border-b">
                    <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                        Quản lý đơn hàng <i class="fas fa-chevron-right"></i>
                    </button>
                    <ul class="hidden flex-col bg-gray-50">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=td_yeucau_mbt" class="block px-6 py-2 hover:bg-blue-100">Theo dõi các y.c mua/bán/thuê</a></li>
                            <li><a href="trangchu.php?page=ql_thanhtoan" class="block px-6 py-2 hover:bg-blue-100">Quản lý thanh toán</a></li>
                        <?php endif; ?> 
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/gd_canhan" class="block px-6 py-2 hover:bg-blue-100">Giao dịch cá nhân</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Quản lý sản phẩm bds -->
            <?php if (!in_array('khachhang', $dsQuyen)): ?>
                <li class="border-b">
                    <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                        Quản lý sản phẩm BĐS <i class="fas fa-chevron-right"></i>
                    </button>
                    <ul class="hidden flex-col bg-gray-50">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=ds_sanpham_bds" class="block px-6 py-2 hover:bg-blue-100">Danh sách sản phẩm BĐS</a></li>
                            <li><a href="trangchu.php?page=ql_anh_video_bds" class="block px-6 py-2 hover:bg-blue-100">Quản lý hình ảnh/videos</a></li>
                            <li><a href="trangchu.php?page=ql_danhgia" class="block px-6 py-2 hover:bg-blue-100">Quản lý đánh giá</a></li>
                        <?php endif; ?>
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/sp_canhan" class="block px-6 py-2 hover:bg-blue-100">Sản phẩm cá nhân</a></li>
                            <li><a href="trangchu.php?page=../moi_gioi/dang_sp" class="block px-6 py-2 hover:bg-blue-100">Đăng sản phẩm</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Quản lý CMS -->
            <?php if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)): ?>
                <li class="border-b">
                    <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                        Quản lý CMS <i class="fas fa-chevron-right"></i>
                    </button>
                    <ul class="hidden flex-col bg-gray-50">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=ql_tintuc" class="block px-6 py-2 hover:bg-blue-100">Quản lý tin tức</a></li>
                            <li><a href="trangchu.php?page=ql_baidang" class="block px-6 py-2 hover:bg-blue-100">Quản lý bài đăng</a></li>
                        <?php endif; ?>
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/ql_tintuc_mg" class="block px-6 py-2 hover:bg-blue-100">Quản lý tin tức </a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Thông báo & chat -->
            <?php if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)): ?>
                <li class="border-b">
                    <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                        Thông báo & chat <i class="fas fa-chevron-right"></i>
                    </button>
                    <ul class="hidden flex-col bg-gray-50">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=g_thongbao" class="block px-6 py-2 hover:bg-blue-100">Gửi thông báo</a></li>
                            <li><a href="trangchu.php?page=ql_hopthoai" class="block px-6 py-2 hover:bg-blue-100">Quản lý hộp thoại chat</a></li>
                            <li><a href="trangchu.php?page=ql_thongbao" class="block px-6 py-2 hover:bg-blue-100">Quản lý thông báo</a></li>
                        <?php endif; ?>
                        
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/g_thongbao_kh" class="block px-6 py-2 hover:bg-blue-100">Gửi thông báo khách hàng</a></li>
                            <li><a href="trangchu.php?page=../moi_gioi/ql_thongbao_mg" class="block px-6 py-2 hover:bg-blue-100">Quản lý thông báo</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Quản lý đặt lịch -->
            <?php if (in_array('quantri', $dsQuyen) || in_array('moigioi', $dsQuyen)): ?>
                <li class="border-b">
                    <button class="w-full flex justify-between items-center px-4 py-2 menu-mobile-btn">
                        Quản lý đặt lịch <i class="fas fa-chevron-right"></i>
                    </button>
                    <ul class="hidden flex-col bg-gray-50">
                        <?php if (in_array('quantri', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=ds_datlich" class="block px-6 py-2 hover:bg-blue-100">Danh sách lịch đặt</a></li>
                        <?php endif; ?>
                        <?php if (in_array('moigioi', $dsQuyen)): ?>
                            <li><a href="trangchu.php?page=../moi_gioi/lt_canhan" class="block px-6 py-2 hover:bg-blue-100">Lịch trình cá nhân</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Quản lý vi phạm (mục đơn, không có submenu) -->
            <?php if (in_array('quantri', $dsQuyen)): ?>
                <li class="border-b">
                    <a href="trangchu.php?page=ds_vipham" class="block px-4 py-2 hover:bg-blue-100">Quản lý vi phạm</a>
                </li>
            <?php endif; ?>
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
            const url = new URL(window.location.href);
            url.searchParams.set("search", query); 
            window.location.href = url.toString();
        }
    });

</script>

<?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'trangchu';
    $showHome = ($page === 'trangchu');
?>

<div id="main-content">
    <?php
        if($page != 'trangchu') include $page . '.php';
    ?>
</div>

<script>
    window.addEventListener("DOMContentLoaded", () => {
        const hero = document.getElementById("heroCarousel");
        const params = new URLSearchParams(window.location.search);

        // Nếu URL có ?page=... thì ẩn banner
        if (params.has("page") && hero) {
            hero.style.display = "none";
        } 
        // Nếu không có ?page=... thì hiện banner (trang chủ)
        else if (!params.has("page") && hero) {
            hero.style.display = "block";
        }
    });
</script>

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


<!-- Footer chi tiết cho sàn BĐS - nền trắng -->
<footer class="bg-white text-gray-800 border-t border-gray-300">        

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


</body>
</html>