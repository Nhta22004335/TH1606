<?php

// BƯỚC 1: BẮT ĐẦU SESSION
session_start(); 

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();

// Hàm tiện ích để hiển thị HTML an toàn
function e($s){ 
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); 
}

// BƯỚC 2: XỬ LÝ KHI NGƯỜI DÙNG GỬI YÊU CẦU (ĐÃ CẬP NHẬT)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['gui_yeu_cau'])) {
    
    $id_nguoi_gui = $_SESSION['id_nguoi_dung'] ?? null;
    $id_moi_gioi = $_POST['id_moi_gioi'] ?? null;
    $id_bds = $_POST['id_bds'] ?? null;
    $hinh_thuc_bds = $_POST['hinh_thuc_bds'] ?? null;
    
    // THAY ĐỔI 1: Lấy thêm tiêu đề BĐS từ form
    $tieu_de_bds = $_POST['tieu_de_bds'] ?? 'Không rõ tiêu đề';

    if (!$id_nguoi_gui) {
        $_SESSION['request_message'] = ['type' => 'error', 'text' => 'Vui lòng đăng nhập để gửi yêu cầu!'];
    } elseif ($id_nguoi_gui == $id_moi_gioi) {
        $_SESSION['request_message'] = ['type' => 'error', 'text' => 'Bạn không thể gửi yêu cầu cho chính mình.'];
    } else {
        try {
            // Xác định loại yêu cầu
            $loai_yeu_cau = ($hinh_thuc_bds == 'ban') ? 'mua' : 'thue';

            // Tạo mô tả chi tiết tự động
            $mo_ta_chi_tiet = "Khách hàng quan tâm đến bất động sản: '" . $tieu_de_bds . "'. (ID BĐS: " . $id_bds . ")";

            // Kiểm tra xem yêu cầu đã tồn tại chưa
            $sqlCheck = "SELECT COUNT(*) FROM yeu_cau 
                         WHERE id_nguoi_dung = ? AND id_moi_gioi = ? AND id_bds = ? AND loai = ? AND trang_thai = 'choxuly'";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$id_nguoi_gui, $id_moi_gioi, $id_bds, $loai_yeu_cau]);
            
            if ($stmtCheck->fetchColumn() > 0) {
                $_SESSION['request_message'] = ['type' => 'info', 'text' => 'Bạn đã gửi yêu cầu cho BĐS này rồi.'];
            } else {
                // THAY ĐỔI 1.2: Cập nhật câu INSERT
                $sqlInsert = "INSERT INTO yeu_cau (id_nguoi_dung, id_moi_gioi, id_bds, loai, mo_ta_chi_tiet) 
                              VALUES (?, ?, ?, ?, ?)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                // Thêm $mo_ta_chi_tiet vào mảng execute
                $stmtInsert->execute([$id_nguoi_gui, $id_moi_gioi, $id_bds, $loai_yeu_cau, $mo_ta_chi_tiet]);
                
                $_SESSION['request_message'] = ['type' => 'success', 'text' => 'Gửi yêu cầu thành công! Môi giới sẽ liên hệ với bạn.'];
            }
        } catch (PDOException $e) {
            $_SESSION['request_message'] = ['type' => 'error', 'text' => 'Lỗi: ' . $e->getMessage()];
        }
    }
    
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}


// 1. LẤY THAM SỐ TỪ URL
$search  = trim($_GET['search'] ?? '');
$page_no = max(1, (int)($_GET['page_no'] ?? 1));
$perPage = 12; 
$offset = ($page_no - 1) * $perPage;

// 2. XÂY DỰNG CÂU TRUY VẤN
$baseJoins = "
    FROM bai_dang p
    JOIN bat_dong_san b ON p.id_bat_dong_san = b.id
    JOIN danh_muc dm ON b.id_danh_muc = dm.id
";

// Phần ĐIỀU KIỆN LỌC (WHERE)
$whereConditions = [];
$params = []; 
$whereConditions[] = "dm.ma_danh_muc IN ('nhapho', 'canho')";
$whereConditions[] = "p.trang_thai = 'daduyet'";
if ($search !== '') {
    $whereConditions[] = "(p.tieu_de ILIKE :search OR b.dia_chi_day_du ILIKE :search)";
    $params[':search'] = "%$search%";
}
$whereSql = ' WHERE ' . implode(' AND ', $whereConditions);

// 3. TRUY VẤN ĐẾM TỔNG SỐ KẾT QUẢ
$sqlCount = "SELECT COUNT(p.id) $baseJoins $whereSql";
$stmtC = $pdo->prepare($sqlCount);
$stmtC->execute($params);
$total = (int)$stmtC->fetchColumn();

// 4. TRUY VẤN LẤY DỮ LIỆU CHÍNH
$sqlData = "
    SELECT 
        p.id,
        p.tieu_de,
        p.gia,
        p.ngay_dang,
        
        p.hinh_thuc,
        p.id_nguoi_dung AS id_moi_gioi,
        
        b.id AS id_bds,
        b.dia_chi_day_du AS khu_vuc, 
        COALESCE(b.dien_tich_su_dung, b.dien_tich_dat) AS dien_tich,
        
        COALESCE(info.ho_ten, 'N/A') AS ten_dang_nhap,
        u.so_dt,
        u.avt,
        
        COALESCE(ha.url, 'chuacapnhat.jpg') AS anh_dai_dien
        
    $baseJoins
    
    LEFT JOIN nguoi_dung u ON u.id = p.id_nguoi_dung
    LEFT JOIN info_nguoi_dung info ON info.id_nguoi_dung = u.id
    LEFT JOIN LATERAL (
        SELECT url 
        FROM hinh_anh_bds 
        WHERE id_bds = b.id 
        ORDER BY ngay_tao ASC 
        LIMIT 1
    ) ha ON TRUE
    
    $whereSql
    
    ORDER BY p.ngay_dang DESC
    LIMIT :limit OFFSET :offset
";

$params[':limit'] = $perPage;
$params[':offset'] = $offset;

// 5. THỰC THI TRUY VẤN VÀ LẤY KẾT QUẢ
$stmt = $pdo->prepare($sqlData);
foreach ($params as $key => &$val) {
    if ($key == ':limit' || $key == ':offset') {
        $stmt->bindParam($key, $val, PDO::PARAM_INT);
    } else {
        $stmt->bindParam($key, $val, PDO::PARAM_STR);
    }
}
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$current_user_id = $_SESSION['id_nguoi_dung'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Nhà ở - căn hộ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <style>
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-4">
    <a href="/app/views/khach_hang/trangchu.php"
        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
        ← Về trang chủ
    </a>
    </div>

    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-3">Nhà ở - căn hộ</h1>
        <p class="text-lg text-gray-600">Khám phá những căn nhà phố và căn hộ tiện nghi.</p>
    </div>

    <?php if (isset($_SESSION['request_message'])): 
        $message = $_SESSION['request_message'];
        $type_class = 'bg-blue-100 border-blue-500 text-blue-700';
        if ($message['type'] == 'success') {
            $type_class = 'bg-green-100 border-green-500 text-green-700';
        } elseif ($message['type'] == 'error') {
            $type_class = 'bg-red-100 border-red-500 text-red-700';
        }
    ?>
    <div class="border-l-4 p-4 <?= $type_class ?> mb-6" role="alert">
        <p><?= e($message['text']) ?></p>
    </div>
    <?php unset($_SESSION['request_message']); ?>
    <?php endif; ?>

    <form method="GET" class="mb-8 p-4 bg-white rounded-lg shadow">
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
            </div>
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Tìm theo tiêu đề, địa chỉ..." class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" />
            <button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">Tìm kiếm</button>
        </div>
    </form>

    <?php if(!$rows): ?>
        <div class="text-center p-12 bg-white rounded-lg shadow">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">Không tìm thấy kết quả</h3>
            <p class="mt-1 text-sm text-gray-500">Không có sản phẩm nào phù hợp với tiêu chí của bạn.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($rows as $p): ?>
            
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 flex flex-col">
                <a href="chitietbaidang.php?id=<?= e($p['id']) ?>" class="group block overflow-hidden">
                    <img src="../../../storage/pictures/bds/<?=$p['anh_dai_dien'] ?>" alt="<?= e($p['tieu_de']) ?>" class="w-full h-52 object-cover transition-all duration-300 group-hover:scale-105">
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="text-xl font-bold text-gray-900 mb-2 truncate">
                        <a href="chitietbaidang.php?id=<?= e($p['id']) ?>" class="hover:text-blue-700"><?= e($p['tieu_de']) ?></a>
                    </h3>
                    
                    <div class="flex items-center text-sm text-gray-600 mb-3">
                        <svg class="w-4 h-4 mr-1.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                           <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 10a7 7 0 10-14 0c0 2.493 1.698 4.988 3.355 6.59C7.18 17.43 8.72 18.292 9.69 18.933zM10 11.75a1.75 1.75 0 100-3.5 1.75 1.75 0 000 3.5z" clip-rule="evenodd" />
                        </svg>
                        <span class="truncate"><?= e($p['khu_vuc']) ?></span>
                    </div>

                    <div class="flex items-center text-sm text-gray-600 mb-4">
                        <svg class="w-4 h-4 mr-1.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                           <path fill-rule="evenodd" d="M11.49 3.17c.125-.125.125-.328 0-.452s-.328-.125-.452 0l-6.5 6.5c-.125.125-.125.328 0 .452l6.5 6.5c.125.125.328.125.452 0s.125-.328 0-.452L5.702 10l5.788-6.83zM14.49 3.17c.125-.125.125-.328 0-.452s-.328-.125-.452 0l-6.5 6.5c-.125.125-.125.328 0 .452l6.5 6.5c.125.125.328.125.452 0s.125-.328 0-.452L8.702 10l5.788-6.83z" clip-rule="evenodd" />
                        </svg>
                        <span><?= e($p['dien_tich']) ?> m²</span>
                    </div>
                    
                    <div class="mt-auto">
                        <p class="text-2xl font-extrabold text-blue-700 mb-4"><?= e(number_format((float)$p['gia'],0,',','.')) ?> VNĐ</p>
                        
                        <?php if ($current_user_id && $current_user_id != $p['id_moi_gioi']): ?>
                        
                        <form method="POST" action="" class="mb-3">
                            <input type="hidden" name="id_moi_gioi" value="<?= e($p['id_moi_gioi']) ?>">
                            <input type="hidden" name="id_bds" value="<?= e($p['id_bds']) ?>">
                            <input type="hidden" name="hinh_thuc_bds" value="<?= e($p['hinh_thuc']) ?>">
                            <input type="hidden" name="tieu_de_bds" value="<?= e($p['tieu_de']) ?>">
                            
                            <button type="submit" name="gui_yeu_cau" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                Gửi yêu cầu quan tâm
                            </button>
                        </form>
                        
                        <?php elseif (!$current_user_id): ?>
                        <a href="/login.php" class="block w-full text-white bg-gray-400 hover:bg-gray-500 font-medium rounded-lg text-sm px-5 py-2.5 text-center mb-3">
                            Đăng nhập để gửi yêu cầu
                        </a>
                        <?php endif; ?>
                        
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                            <span><?= e($p['ten_dang_nhap']) ?></span>
                            <span><?= date('d/m/Y', strtotime($p['ngay_dang'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php $pages=max(1,(int)ceil($total/$perPage)); if($pages>1): ?>
        <nav class="mt-10 flex justify-center" aria-label="Pagination">
            <ul class="flex items-center -space-x-px h-10 text-base">
                <?php for($i=1;$i<=$pages;$i++): ?>
                <li>
                    <a href="?<?= http_build_query(['search'=>$search,'page_no'=>$i]) ?>" 
                       class="flex items-center justify-center px-4 h-10 leading-tight <?= $i===$page_no?'text-white bg-blue-700 border-blue-700 hover:bg-blue-800':'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700' ?> <?= $i==1?'rounded-l-lg':'' ?> <?= $i==$pages?'rounded-r-lg':'' ?>">
                       <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<footer class="bg-white rounded-lg shadow dark:bg-gray-900 m-4">
    <div class="w-full max-w-screen-xl mx-auto p-4 md:py-8">
        <span class="block text-sm text-gray-500 sm:text-center dark:text-gray-400">© 2025 <a href="/" class="hover:underline">BDS Portal™</a>. All Rights Reserved.</span>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
=======
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

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
<body>
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

>>>>>>> Stashed changes:php/app/views/khachhang/nhao.php
</body>
</html>