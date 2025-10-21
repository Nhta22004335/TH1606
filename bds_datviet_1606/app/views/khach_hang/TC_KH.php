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
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đất Việt BĐS | Sàn Giao Dịch Bất Động Sản Chuyên Nghiệp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Tùy chỉnh nhỏ để logo giữ tỷ lệ tốt hơn */
        .logo-container .logo-img {
            transform: scale(2.6) translate(-5%, 0%); /* Điều chỉnh vị trí sau khi scale */
            transform-origin: center right;
        }
        /* Style cho nút tìm kiếm chính trên banner */
        #main-search-button {
            transition: all 0.3s ease;
        }
        #main-search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
    </style>
</head>
<body class="bg-white text-gray-800 font-sans" x-data="{ mobileMenuOpen: false }">

    <header class="sticky top-0 bg-white shadow-md border-b border-gray-100 z-50" 
        x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex justify-between items-center h-20">
        
        <div class="flex items-center cursor-pointer min-w-[200px]">
            <div class="flex items-center space-x-2">
                <div class="relative h-12 w-12 flex items-center justify-center overflow-visible">
                    <img 
                        src="../../../public/assets/anhht/0/datviet.png" 
                        alt="Logo Đất Việt BĐS" 
                        class="h-10 transform scale-[2] translate-x-[-15%] object-contain"
                    >
                </div>
                <div class="flex flex-col justify-center leading-snug pl-4">
                    <span class="text-2xl font-extrabold tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-sky-500 to-cyan-400 font-[Poppins]">
                        Đất Việt
                    </span>
                    <span class="text-[10px] sm:text-xs text-gray-500 italic">
                        Không gian sống lý tưởng
                    </span>
                </div>
            </div>
        </div>

        <div class="hidden lg:flex flex-1 mx-8 max-w-lg">
            <div class="flex w-full">
                <input id="searchInput" type="text" placeholder="Tìm kiếm bất động sản, dự án..." 
                    class="flex-1 h-10 border border-gray-300 px-4 text-sm rounded-l-lg focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none transition"
                >
                <button id="btnSearch" class="h-10 px-4 bg-blue-600 text-white rounded-r-lg border border-blue-600 flex items-center justify-center hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <nav class="hidden xl:flex space-x-6 font-medium text-base">
                <a href="#" class="text-gray-700 hover:text-blue-600 transition duration-200">Trang chủ</a>
                <a href="#" class="text-gray-700 hover:text-blue-600 transition duration-200">Dự án</a>
                <a href="tintuc.php" class="text-gray-700 hover:text-blue-600 transition duration-200">Tin tức</a>
                <a href="#" class="text-gray-700 hover:text-blue-600 transition duration-200">Liên hệ</a>
            </nav>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" 
                    class="flex items-center space-x-2 p-2 rounded-full border border-gray-200 hover:bg-gray-50 transition">

                    <!-- Avatar -->
                    <img src="../../../storage/pictures/avt/<?= $nd['avt'] ?? 'avt.png' ?>" 
                        alt="Avatar" 
                        class="w-8 h-8 rounded-full border border-gray-300">

                    <!-- Tên người dùng -->
                    <span class="text-gray-700 font-medium"><?= htmlspecialchars($nd['ho_ten'] ?? 'Trương Quốc Đặng') ?></span>
                </button>


                <div x-show="open" x-cloak @click.outside="open = false" x-transition.origin.top-right class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden" style="z-index: 20;">
                     <div class="px-4 py-3 flex items-center space-x-3 border-b border-gray-100 bg-gray-50">
                        <img src="../../../storage/pictures/avt/<?= $nd['avt'] ?? 'default-avatar.png' ?>" alt="Avatar" class="w-10 h-10 rounded-full border border-blue-300 p-0.5">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 truncate"><?= $ind['ho_ten'] ?? 'Khách hàng' ?></p>
                            <p class="text-xs text-gray-500">Tài khoản cá nhân</p>
                        </div>
                    </div>
                    <div class="py-1">
                        <a href="trangchu.php?page=../moi_gioi/ql_hoso_canhan" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition">
                            <i class="fas fa-user-circle mr-3 w-4"></i> Trang cá nhân
                        </a>
                        <a href="../../models/auth/xuly_dangxuat.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                            <i class="fas fa-sign-out-alt mr-3 w-4"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded hover:bg-gray-100 transition">
                <i x-show="!mobileMenuOpen" class="fas fa-bars text-xl"></i>
                <i x-show="mobileMenuOpen" x-cloak class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>

    <div x-show="mobileMenuOpen" x-cloak x-transition.origin.top class="lg:hidden bg-white border-t border-gray-100">
        <div class="p-4">
            <div class="flex w-full">
                <input type="text" placeholder="Tìm kiếm nhanh..." class="flex-1 h-10 border border-gray-300 px-3 text-sm rounded-l-md focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none">
                <button class="h-10 px-4 bg-blue-600 text-white rounded-r-md flex items-center justify-center hover:bg-blue-700 transition">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <nav class="flex flex-col p-4 space-y-1 border-t border-gray-100">
            <a href="#" class="py-3 px-4 text-gray-800 hover:bg-blue-50 hover:text-blue-600 rounded-lg font-medium transition">Trang chủ</a>
            <a href="#" class="py-3 px-4 text-gray-800 hover:bg-blue-50 hover:text-blue-600 rounded-lg font-medium transition">Dự án</a>
            <a href="#" class="py-3 px-4 text-gray-800 hover:bg-blue-50 hover:text-blue-600 rounded-lg font-medium transition">Tin tức</a>
            <a href="#" class="py-3 px-4 text-gray-800 hover:bg-blue-50 hover:text-blue-600 rounded-lg font-medium transition">Liên hệ</a>
        </nav>
    </div>
</header>


    
    <section id="heroCarousel" class="relative h-[550px] lg:h-[600px] overflow-hidden">
        
        <div class="slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-100"
            style="background-image: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.4)), 
            url('https://images.unsplash.com/photo-1501183638710-841dd1904471');">
        </div>

        <div class="slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-0"
            style="background-image: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.4)), 
            url('https://images.unsplash.com/photo-1570129477492-45c003edd2be');">
        </div>

        <div class="slide absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-0"
            style="background-image: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.4)), 
            url('https://images.unsplash.com/photo-1494526585095-c41746248156');">
        </div>

        <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-4">
            <h2 id="slide-title" class="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-4 drop-shadow-xl animate-fade-in-down">
                Tìm Ngôi Nhà Mơ Ước, Bắt Đầu Từ Đây.
            </h2>
            <p id="slide-subtitle" class="mb-10 text-lg sm:text-xl opacity-95 max-w-2xl drop-shadow-md animate-fade-in-up">
                Khám phá hơn 10.000 bất động sản uy tín, chất lượng cao trên toàn quốc với Đất Việt BĐS.
            </p>

            
        </div>

        <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-3">
            <button class="dot w-3 h-3 rounded-full bg-white opacity-90 transition duration-300 border-2 border-white focus:outline-none"></button>
            <button class="dot w-3 h-3 rounded-full bg-white opacity-50 transition duration-300 focus:outline-none"></button>
            <button class="dot w-3 h-3 rounded-full bg-white opacity-50 transition duration-300 focus:outline-none"></button>
        </div>
    </section>


    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <h3 class="text-3xl sm:text-4xl font-extrabold text-center mb-16 text-gray-900">
                Khám Phá Danh Mục Chính
                <div class="h-1 w-20 bg-blue-500 mx-auto mt-2 rounded"></div>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                
                <div class="group bg-white rounded-xl shadow-xl p-8 transform hover:scale-[1.03] transition duration-300 cursor-pointer border-t-4 border-blue-500">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition duration-300">
                            <i class="fas fa-home text-3xl"></i>
                        </div>
                    </div>
                    <h4 class="text-2xl font-bold text-center mb-2 text-gray-900 group-hover:text-blue-600 transition">Nhà ở & Căn hộ</h4>
                    <p class="text-gray-600 text-center text-sm">
                        Hàng ngàn lựa chọn nhà phố, biệt thự, chung cư chất lượng cao.
                    </p>
                    <a href="#" class="block text-center mt-4 text-sm font-semibold text-blue-500 group-hover:text-blue-700 transition">Xem chi tiết <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
                </div>

                <div class="group bg-white rounded-xl shadow-xl p-8 transform hover:scale-[1.03] transition duration-300 cursor-pointer border-t-4 border-yellow-500">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 rounded-full bg-yellow-100 text-yellow-600 group-hover:bg-yellow-600 group-hover:text-white transition duration-300">
                            <i class="fas fa-tree text-3xl"></i>
                        </div>
                    </div>
                    <h4 class="text-2xl font-bold text-center mb-2 text-gray-900 group-hover:text-yellow-600 transition">Đất nền Dự án</h4>
                    <p class="text-gray-600 text-center text-sm">
                        Cập nhật dự án đất nền mới nhất, vị trí tiềm năng, sinh lời cao.
                    </p>
                    <a href="#" class="block text-center mt-4 text-sm font-semibold text-yellow-500 group-hover:text-yellow-700 transition">Xem chi tiết <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
                </div>

                <div class="group bg-white rounded-xl shadow-xl p-8 transform hover:scale-[1.03] transition duration-300 cursor-pointer border-t-4 border-green-500">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 rounded-full bg-green-100 text-green-600 group-hover:bg-green-600 group-hover:text-white transition duration-300">
                            <i class="fas fa-building text-3xl"></i>
                        </div>
                    </div>
                    <h4 class="text-2xl font-bold text-center mb-2 text-gray-900 group-hover:text-green-600 transition">Văn phòng & Thuê</h4>
                    <p class="text-gray-600 text-center text-sm">
                        Tìm kiếm căn hộ, văn phòng cho thuê phù hợp với nhu cầu của bạn.
                    </p>
                    <a href="#" class="block text-center mt-4 text-sm font-semibold text-green-500 group-hover:text-green-700 transition">Xem chi tiết <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
                </div>

                <div class="group bg-white rounded-xl shadow-xl p-8 transform hover:scale-[1.03] transition duration-300 cursor-pointer border-t-4 border-purple-500">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 rounded-full bg-purple-100 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition duration-300">
                            <i class="fas fa-handshake text-3xl"></i>
                        </div>
                    </div>
                    <h4 class="text-2xl font-bold text-center mb-2 text-gray-900 group-hover:text-purple-600 transition">Dịch vụ Môi giới</h4>
                    <p class="text-gray-600 text-center text-sm">
                        Tìm kiếm chuyên viên tư vấn hoặc đăng tin chuyên nghiệp, hiệu quả cao.
                    </p>
                    <a href="#" class="block text-center mt-4 text-sm font-semibold text-purple-500 group-hover:text-purple-700 transition">Xem chi tiết <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
                </div>

            </div>
        </div>
    </section>


    <div class="bg-blue-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 items-center">
            
            <div class="text-center md:text-left md:col-span-1 lg:col-span-2">
                <p class="text-sm font-semibold text-blue-200 mb-2 uppercase tracking-widest">TÌM KIẾM - LỰA CHỌN BẤT ĐỘNG SẢN</p>
                <p class="text-4xl font-extrabold mb-4 drop-shadow-md">MỌI LÚC MỌI NƠI</p>
                <p class="text-base text-blue-100 leading-relaxed max-w-xl">
                    Cài đặt ứng dụng Đất Việt BĐS trên điện thoại để tìm kiếm nhà đất bán - cho thuê nhanh chóng, xem thông tin đầy đủ tất cả các dự án mới, tin tức mới nhất về thị trường nhà đất được cập nhật liên tục.
                </p>
                <div class="flex flex-wrap justify-center md:justify-start space-x-4 mt-6">
                    <a href="https://apps.apple.com/vn/app/b%E1%BA%A5t-%C4%91%E1%BB%99ng-s%E1%BA%A3n-homedy/id1438315559/?l=vi" title="App Store" target="_blank" class="hover:opacity-90 transition">
                        <img class="lazy w-36 h-auto" alt="app-store" src="https://static.homedy.com/src/images/social/app-store.png">
                    </a>
                    <a href="https://play.google.com/store/apps/details?id=com.homedyapp.android" title="Google Play" target="_blank" class="hover:opacity-90 transition">
                        <img class="lazy w-36 h-auto" alt="google-play" src="https://static.homedy.com/src/images/social/google-play.png">
                    </a>
                </div>
            </div>

            <div class="flex flex-col items-center lg:items-end md:col-span-1 lg:col-span-1">
                <img class="lazy w-52 h-auto rounded-lg shadow-2xl mb-6 transform hover:scale-[1.05] transition duration-300" 
                     alt="app demo" 
                     src="https://static.homedy.com/src/images/social/app.png">
                <div class="bg-white p-2 rounded-lg shadow-lg">
                    <img class="lazy w-24 h-auto" alt="qr code" src="https://static.homedy.com/src/images/social/qr.png">
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



        <style>
    .box-popup .popup-title {
        /*height: 40px;*/
        height:80px;
    }

    .box-list .title {
        height: 40px;
        box-sizing: content-box;
    }

    #ListMember .content {
        margin-top: 0px;
    }   
</style>

<!-- Chat Widget -->
<button style="border: none" class="btn-show" id="ShowMember" onclick="if (!window.__cfRLUnblockHandlers) return false; ButtonToggle(this)" aria-label="chat" tooltip="Chat trực tuyến" flow="left" data-cf-modified-adf9c6615a41c37c997e0d31-="">
    <div class="unread-badge" id="az_widget_chat_total_unread_badge" style="display: none;"><p>0</p></div>
</button>

<div class="box-list" id="ListMember" style="z-index:99999;">
    <form id="form1">
        <div class="title">
            <div class="name"></div>
            <div class="sub"></div>
            <div class="minimize" onclick="if (!window.__cfRLUnblockHandlers) return false; Minimizebutton(this)" data-cf-modified-adf9c6615a41c37c997e0d31-=""><span class="ic ic-arrow-down"></span></div>
        </div>

        <!-- List member -->
        <div class="content" id="chat_box">
            <ul class="scroll-style-2"></ul>
        </div>

        <div id="chat_div"></div>
        <input id="hdUserName" type="hidden" />
        <input id="hdnCurrentUserName" type="hidden" value="" />
        <input id="hdnCurrentAvatar" type="hidden" value="" />
        <input id="hdnCurrentMobile" type="hidden" value="" />
        <input id="hdnCurrentEmail" type="hidden" value="" />
        <input id="hdnCurrentFullName" type="hidden" value="" />
        <input id="hdnCurrentManager" type="hidden" value="" />
        <input id="hdnCurrentType" type="hidden" value="" />
        <input id="hdnCurrentSystem" type="hidden" value="" />
        <input id="TypeProduct" type="hidden" value="" />
        <input id="CheckLogout" type="hidden" value="" />
    </form>
</div>
        

 <!-- User info -->
    <script type="adf9c6615a41c37c997e0d31-text/javascript">
        var _type = 4;
        if (location.href.indexOf('trang-ca-nhan') >= 0)
            _type = 3;

        window.PageData = window.PageData || {};
        PageData.user = {
            logged_in: 0,
            username: '',
            mobile: '',
            email: '',
            full_name: '',
            avatar: '',
            typeId: '',
            companyUserName: ''
        };
        PageData.chat = {
            type: _type,
            system: 'HDY'
        }

        var elements = document.getElementsByClassName('loged_in_name');
        if (elements !== null && elements !== undefined) {
            for (var i = 0; i < elements.length; i++) {
                elements[i].innerHTML = '';
            }
        }

        var element = document.getElementById('filter_loged_in_name');
        if (element !== null && element !== undefined){
            element.innerHTML = '';
        }

        var element = document.getElementsByClassName('filter_loged_in_avatar');
        if (element !== null && element !== undefined) {
            for (var i = 0; i < element.length; i++) {
                element[i].style.background = 'url(https://static.homedy.com/src/images/broker.jpg)';
            }
        }

</script>
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
        const slides = document.querySelectorAll("#heroCarousel .slide");
        const dots = document.querySelectorAll("#heroCarousel .dot");
        const slideTitles = [
            "Tìm Ngôi Nhà Mơ Ước, Bắt Đầu Từ Đây.",
            "Không Gian Sống Xanh, Cuộc Sống Hiện Đại.",
            "Đầu Tư Thông Minh, Sinh Lời Bền Vững.",
        ];
        const slideSubtitles = [
            "Khám phá hơn 10.000 bất động sản uy tín, chất lượng cao trên toàn quốc với Đất Việt BĐS.",
            "Tận hưởng tiện nghi và không gian xanh mát tại các khu đô thị mới nhất.",
            "Chọn lựa bất động sản tiềm năng để gia tăng giá trị tài sản trong tương lai.",
        ];
        const titleElement = document.getElementById('slide-title');
        const subtitleElement = document.getElementById('slide-subtitle');
        let current = 0;

        function updateContent(index) {
            titleElement.classList.remove('animate-fade-in-down');
            subtitleElement.classList.remove('animate-fade-in-up');
            // Force reflow to restart animation
            void titleElement.offsetWidth;
            void subtitleElement.offsetWidth;
            
            titleElement.textContent = slideTitles[index];
            subtitleElement.textContent = slideSubtitles[index];

            titleElement.classList.add('animate-fade-in-down');
            subtitleElement.classList.add('animate-fade-in-up');
        }

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.style.opacity = i === index ? "1" : "0";
                dots[i].classList.toggle("opacity-90", i === index);
                dots[i].classList.toggle("opacity-50", i !== index);
                dots[i].classList.toggle("border-2", i === index);
            });
            updateContent(index);
        }

        function nextSlide() {
            current = (current + 1) % slides.length;
            showSlide(current);
        }

        // Tự động chuyển slide sau 6 giây
        setInterval(nextSlide, 6000);

        // Cho phép click chọn slide
        dots.forEach((dot, index) => {
            dot.addEventListener("click", () => {
                current = index;
                showSlide(index);
            });
        });

        // Khởi tạo trạng thái đầu tiên
        showSlide(current);
    </script>

</body>
</html>