<?php
    require_once "../../../config/database.php";
    session_start();
    $pdo = ketnoicsdl();

    $id = $_SESSION['id_nguoi_dung'] ?? null;

    $sql = "SELECT trang_thai FROM nguoi_dung WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $tt = $stmt->fetch();

    if ($tt) {
        $trang_thai = $tt['trang_thai'];
        if ($trang_thai === 'danghoatdong') {

        } else if ($trang_thai === 'chuakichhoat' || $trang_thai === 'khoa') {
            session_unset();
            session_destroy();

            // Chuyển hướng đến trang đăng nhập
            header("Location: ../../models/auth/xuly_dangxuat.php");
            exit();
        } 
    }

    $dsQuyen = [];
    $ind = ['ho_ten' => 'Khách', 'avt' => 'default_avatar.png'];
    $nd = ['avt' => 'default_avatar.png'];

    if ($id) {
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
        $ind_temp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ind_temp) $ind = $ind_temp;

        $sql = "SELECT * FROM nguoi_dung WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $nd_temp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($nd_temp) $nd = $nd_temp;
    }
    
    $is_quantri = in_array('quantri', $dsQuyen);
    $is_moigioi = in_array('moigioi', $dsQuyen);
    $is_logged_in = $id !== null;
    $current_page = isset($_GET['page']) ? $_GET['page'] : 'trangchu';
    
    // Mảng định nghĩa menu (GIỮ NGUYÊN)
    $menu_items = [
        [
            'title' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'link' => 'trangchu.php',
            'roles' => ['quantri', 'moigioi'],
        ],
        [
            'title' => 'Quản lý Người dùng',
            'icon' => 'fas fa-users',
            'roles' => ['quantri', 'moigioi'],
            'submenu' => [
                'Danh sách người dùng' => ['link' => 'ds_nguoidung', 'roles' => ['quantri']],
                'Lịch sử xác thực' => ['link' => 'ls_xacthuc', 'roles' => ['quantri']],
                'Quản lý biểu mẫu' => ['link' => 'ql_bieumau', 'roles' => ['quantri']],
                'Quản lý hồ sơ (MG)' => ['link' => '../moi_gioi/ql_hoso', 'roles' => ['moigioi']],
                'Tạo hồ sơ mới' => ['link' => '../moi_gioi/tao_hoso_moi', 'roles' => ['moigioi']],
            ]
        ],
        [
            'title' => 'Quản lý Bất động sản',
            'icon' => 'fas fa-building',
            'roles' => ['quantri', 'moigioi'],
            'submenu' => [
                'Danh sách BĐS' => ['link' => 'ds_sanpham_bds', 'roles' => ['quantri']],
                'QL Đánh giá' => ['link' => 'ql_danhgia', 'roles' => ['quantri']],
                'Sản phẩm cá nhân' => ['link' => '../moi_gioi/sp_canhan', 'roles' => ['moigioi']],
                'Đăng sản phẩm' => ['link' => '../moi_gioi/dang_sp', 'roles' => ['moigioi']],
            ]
        ],
        [
            'title' => 'Quản lý Giao dịch',
            'icon' => 'fas fa-file-invoice-dollar',
            'roles' => ['quantri', 'moigioi'],
            'submenu' => [
                'Theo dõi Y/C Mua/Bán/Thuê' => ['link' => 'td_yeucau_mbt', 'roles' => ['quantri']],
                'Quản lý thanh toán' => ['link' => 'ql_thanhtoan', 'roles' => ['quantri']],
                'Giao dịch cá nhân' => ['link' => '../moi_gioi/gd_canhan', 'roles' => ['moigioi']],
            ]
        ],
        [
            'title' => 'Khách hàng tiềm năng',
            'icon' => 'fas fa-handshake',
            'roles' => ['moigioi'],
            'submenu' => [
                'Khách hàng quan tâm' => ['link' => '../moi_gioi/kh_quantam', 'roles' => ['moigioi']],
                'Khách hàng đã mua' => ['link' => '../moi_gioi/kh_damua', 'roles' => ['moigioi']],
            ]
        ],
        [
            'title' => 'Quản lý Đặt lịch',
            'icon' => 'fas fa-calendar-alt',
            'roles' => ['quantri', 'moigioi'],
            'submenu' => [
                'Danh sách lịch đặt' => ['link' => 'ds_datlich', 'roles' => ['quantri']],
                'Lịch trình cá nhân' => ['link' => '../moi_gioi/lt_canhan', 'roles' => ['moigioi']],
            ]
        ],
        [
            'title' => 'CMS & Truyền thông',
            'icon' => 'fas fa-newspaper',
            'roles' => ['quantri', 'moigioi'],
            'submenu' => [
                'Quản lý tin tức (Admin)' => ['link' => 'ql_tintuc', 'roles' => ['quantri']],
                'Quản lý bài đăng (Admin)' => ['link' => 'ql_baidang', 'roles' => ['quantri']],
                'Quản lý tin tức (MG)' => ['link' => '../moi_gioi/ql_tintuc_mg', 'roles' => ['moigioi']],
            ]
        ],
        [
            'title' => 'Thông báo & Hỗ trợ',
            'icon' => 'fas fa-bell',
            'roles' => ['quantri', 'moigioi'],
            'submenu' => [
                'Gửi thông báo (Admin)' => ['link' => 'g_thongbao', 'roles' => ['quantri']],
                'Quản lý hộp thoại chat' => ['link' => 'ql_hopthoai', 'roles' => ['quantri']],
                'Gửi thông báo khách (MG)' => ['link' => '../moi_gioi/g_thongbao_kh', 'roles' => ['moigioi']],
                'Quản lý thông báo (MG)' => ['link' => '../moi_gioi/ql_thongbao', 'roles' => ['moigioi']],
                'Quản lý thông báo' => ['link' => 'ql_thongbao', 'roles' => ['quantri']]
            ]
        ],
        [
            'title' => 'Quản lý lịch sử',
            'icon' => 'fas fa-history',
            'link' => 'trangchu.php?page=ql_lichsu',
            'roles' => ['quantri'],
        ],
    ];

    // Hàm kiểm tra quyền (GIỮ NGUYÊN)
    function check_role($roles, $dsQuyen) {
        if (empty($roles)) return true;
        foreach ($roles as $role) {
            if (in_array($role, $dsQuyen)) return true;
        }
        return false;
    }
?>
<!DOCTYPE html>
<html lang="vi" x-data="{ sidebarOpen: window.innerWidth >= 768 }" :class="{'overflow-hidden': sidebarOpen}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Đất Việt Bất Động Sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- <script defer src="https://unpkg.com/alpinejs"></script> -->
    <link rel="stylesheet" href="../../../public/css/style.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="../../../public/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
        
        .sidebar { 
            transition: transform 0.3s ease-in-out, width 0.3s ease-in-out; 
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
        }

        /* Ẩn thanh cuộn trên các trình duyệt */
        .scrollbar-hidden::-webkit-scrollbar { display: none; }
        .scrollbar-hidden { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50">

<div class="relative min-h-screen md:flex" @keydown.escape.window="sidebarOpen = false">

    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-transition:enter="transition-opacity ease-in-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition-opacity ease-in-out duration-300" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden" 
         x-cloak>
    </div>

    <div x-cloak :class="{'translate-x-0 ease-out': sidebarOpen, '-translate-x-full ease-in': !sidebarOpen}" 
        class="sidebar fixed inset-y-0 left-0 text-gray-700 w-64 z-40 h-screen flex flex-col bg-white">
       
        <div class="flex-shrink-0 flex items-center justify-center h-16 bg-white border-b shadow-sm">
            <a href="trangchu.php" class="flex items-center space-x-2 cursor-pointer p-2">
                <div class="relative h-14 w-14 flex items-center justify-center">
                    <img src="../../../public/images/datviet.png" alt="Logo" class="transform scale-150">
                </div>
                <div class="flex flex-col justify-center leading-none">
                    <span class="text-2xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-teal-500 drop-shadow-sm">
                        Đất Việt
                    </span>
                    <span class="text-xs text-gray-500 italic mt-0.5">
                        Quản trị BĐS
                    </span>
                </div>
            </a>
        </div>

        <nav class="flex-1 min-h-0 py-4 space-y-1 px-3 overflow-y-auto scrollbar-hidden">
            <?php foreach ($menu_items as $item): ?>
                <?php if (check_role($item['roles'], $dsQuyen)): ?>
                    <?php 
                        $is_active = $current_page === basename($item['link'] ?? '');
                        if (isset($item['submenu'])) {
                            foreach ($item['submenu'] as $sub_title => $sub_item) {
                                if (str_replace('trangchu.php?page=', '', $sub_item['link']) === $current_page) {
                                    $is_active = true;
                                    break;
                                }
                            }
                        }
                    ?>
                    <?php if (isset($item['submenu'])): ?>
                        <div x-data="{ open: <?= $is_active ? 'true' : 'false' ?> }" class="relative">
                            <button @click="open = !open" 
                                    class="w-full flex items-center justify-between px-3 py-2 text-left text-sm font-semibold rounded-lg transition duration-150 <?= $is_active ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' ?>">
                                <div class="flex items-center">
                                    <i class="<?= $item['icon'] ?> w-5 mr-3 <?= $is_active ? 'text-indigo-600' : 'text-gray-500' ?>"></i> 
                                    <span><?= $item['title'] ?></span>
                                </div>
                                <i :class="{'rotate-90': open, 'rotate-0': !open}" class="fas fa-chevron-right text-xs transition-transform duration-200"></i>
                            </button>
                            <div x-show="open" x-cloak x-transition
                                 class="bg-gray-50 py-1 space-y-1 origin-top border-l-2 border-indigo-400 ml-3 mt-1">
                                <?php foreach ($item['submenu'] as $sub_title => $sub_item): ?>
                                    <?php if (check_role($sub_item['roles'], $dsQuyen)): ?>
                                        <?php $sub_active = str_replace('trangchu.php?page=', '', $sub_item['link']) === $current_page; ?>
                                        <a href="trangchu.php?page=<?= $sub_item['link'] ?>" 
                                           class="block pl-8 pr-3 py-1.5 text-sm font-medium transition duration-150 <?= $sub_active ? 'bg-indigo-100 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-indigo-50' ?>">
                                            <?= $sub_title ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= $item['link'] ?>" 
                           class="flex items-center px-3 py-2 text-sm font-semibold rounded-lg transition duration-150 <?= $is_active ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' ?>">
                            <i class="<?= $item['icon'] ?> w-5 mr-3 <?= $is_active ? 'text-indigo-600' : 'text-gray-500' ?>"></i> 
                            <span><?= $item['title'] ?></span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <footer class="flex-shrink-0 p-3 border-t flex flex-col justify-center">
            <?php if ($is_moigioi): ?>
                <a href="trangchu.php?page=../moi_gioi/dang_tin" class="flex items-center justify-center p-2 text-sm font-semibold bg-transparent text-gray-700 rounded-lg hover:bg-gray-100 transition-colors md:hidden border border-gray-200">
                    <span>Đăng tin</span>
                </a>
            <?php endif; ?>

            <a href="../../models/auth/xuly_dangxuat.php" class="flex items-center justify-center p-2 text-sm font-semibold bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                <i class="fas fa-sign-out-alt w-5 mr-3" aria-hidden="true"></i>
                <span>Đăng xuất</span>
            </a>
        </footer>
        
    </div>

    <div class="flex-1 flex flex-col md:ml-64 h-screen">

        <header class="flex items-center justify-between h-16 bg-white border-b shadow-sm px-4 md:px-6 z-20">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 md:hidden p-2 rounded-full hover:bg-gray-100 transition">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <div class="hidden md:block"></div>

            <div x-show="!sidebarOpen" class="md:hidden">
                <a href="trangchu.php" class="flex items-center space-x-2 cursor-pointer p-2">
                    <div class="relative h-14 w-14 flex items-center justify-center">
                        <img src="../../../public/images/datviet.png" alt="Logo" class="transform scale-150">
                    </div>
                    <div class="flex flex-col justify-center leading-none">
                        <span class="text-2xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-teal-500 drop-shadow-sm">
                            Đất Việt
                        </span>
                        <span class="text-xs text-gray-500 italic mt-0.5">
                            Quản trị BĐS
                        </span>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-4">
                <?php if ($is_moigioi): ?>
                    <a href="trangchu.php?page=../moi_gioi/dang_tin" 
                       class="hidden sm:flex items-center px-4 py-2 border border-gray-200 bg-transparent text-gray-700 text-sm rounded-lg hover:bg-gray-100 transition duration-300">
                        Đăng tin
                    </a>
                <?php endif; ?>

                <?php if ($is_logged_in): ?>
                    <div x-data="{ open: false }" class="relative">
                        <div @click="open = !open" class="flex items-center space-x-3 cursor-pointer p-1 rounded-lg hover:bg-gray-100 transition-colors duration-200 ease-in-out">
                            <img src="../../../storage/pictures/avt/<?= htmlspecialchars($nd['avt']) ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                            <div class="hidden lg:block">
                                <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($ind['ho_ten']) ?></span>
                            </div>
                            <i class="fas fa-chevron-down text-gray-500 text-xs hidden lg:block transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
                        </div>
                        
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition 
                             class="absolute right-0 mt-3 w-64 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 origin-top-right z-50">
                            <div class="px-4 py-3 flex items-center space-x-3 border-b border-gray-100">
                                <div>
                                    <p class="text-base font-semibold text-gray-800 truncate"><?= htmlspecialchars($ind['ho_ten']) ?></p>
                                    <p class="text-xs text-indigo-600 font-medium"><?= $is_quantri ? 'Quản Trị Viên' : ($is_moigioi ? 'Môi Giới' : 'Khách Hàng') ?></p>
                                </div>
                            </div>
                            <a href="trangchu.php?page=../moi_gioi/ql_hoso_canhan" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                <i class="fas fa-user-circle w-5 mr-2"></i> Trang cá nhân
                            </a>
                            <a href="../../models/auth/xuly_quen_matkhau.php?email=<?= urlencode($nd['email']) ?>" class="block w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-yellow-50 transition">
                                <i class="fas fa-key w-5 mr-2"></i> Quên mật khẩu
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-6 min-h-0">
            <?php
                $page_to_include = str_replace('trangchu.php?page=', '', $current_page);
                
                if ($page_to_include !== 'trangchu' && file_exists($page_to_include . '.php')) {
                    include $page_to_include . '.php';
                } else if ($current_page === 'trangchu') {
                    echo '<div class="p-6 border border-indigo-200 bg-indigo-50 rounded-lg space-y-3">';
                    echo '<p class="text-xl font-semibold text-indigo-700">Chào mừng, ' . htmlspecialchars($ind['ho_ten']) . '!</p>';
                    echo '<p class="text-gray-700">Bạn đang ở Dashboard của hệ thống quản trị Bất Động Sản Đất Việt. Vui lòng sử dụng thanh Menu bên trái để truy cập các chức năng quản lý chi tiết.</p>';
                    echo '</div>';
                } else {
                    echo '<div class="p-6 bg-red-50 border border-red-300 text-red-700 rounded-lg">Không tìm thấy nội dung trang. Vui lòng kiểm tra lại đường dẫn: ' . htmlspecialchars($page_to_include) . '.php</div>';
                }
            ?>
        </main>

        <footer class="flex-shrink-0 h-12 bg-white border-t border-gray-200 flex items-center justify-center text-xs text-gray-500 shadow-inner">
            <span>© 2025 Đất Việt BDS. Quản trị BĐS: Minh bạch, Hiệu quả, Tăng trưởng.</span>
        </footer>
    </div>
</div>


</body>
</html>