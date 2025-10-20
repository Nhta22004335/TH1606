<?php
require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();

// Hàm tiện ích để hiển thị HTML an toàn
function e($s){ 
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); 
}

// 1. LẤY THAM SỐ TỪ URL
$search  = trim($_GET['search'] ?? '');
$page_no = max(1, (int)($_GET['page_no'] ?? 1));
$perPage = 12; 
$offset = ($page_no - 1) * $perPage;

// 2. XÂY DỰNG CÂU TRUY VẤN
// Phần JOIN cơ bản giữa các bảng
$baseJoins = "
    FROM bai_dang p
    JOIN bat_dong_san b ON p.id_bat_dong_san = b.id
    JOIN danh_muc dm ON b.id_danh_muc = dm.id
";

// Phần ĐIỀU KIỆN LỌC (WHERE)
$whereConditions = [];
$params = []; // Mảng chứa các giá trị cho prepared statement

// Điều kiện bắt buộc: Lọc "biệt thự" và bài đã duyệt
$whereConditions[] = "dm.ma_danh_muc = 'bietthu'";
$whereConditions[] = "p.trang_thai = 'daduyet'";

// Điều kiện tìm kiếm (nếu có)
if ($search !== '') {
    // Tìm kiếm trong tiêu đề bài đăng HOẶC địa chỉ bất động sản
    $whereConditions[] = "(p.tieu_de ILIKE :search OR b.dia_chi_day_du ILIKE :search)";
    $params[':search'] = "%$search%";
}

// Ghép các điều kiện lại thành mệnh đề WHERE
$whereSql = ' WHERE ' . implode(' AND ', $whereConditions);

// 3. TRUY VẤN ĐẾM TỔNG SỐ KẾT QUẢ (ĐỂ PHÂN TRANG)
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
        
        b.id AS id_bds,
        b.dia_chi_day_du AS khu_vuc, 
        COALESCE(b.dien_tich_su_dung, b.dien_tich_dat) AS dien_tich,
        
        -- SỬA LỖI: Lấy ho_ten từ bảng info_nguoi_dung (bí danh info)
        COALESCE(info.ho_ten, 'N/A') AS ten_dang_nhap,
        u.so_dt,
        u.avt,
        
        -- Lấy ảnh đầu tiên của BĐS
        COALESCE(ha.url, 'chuacapnhat.jpg') AS anh_dai_dien
        
    $baseJoins
    
    -- JOIN để lấy thông tin người dùng (người đăng)
    LEFT JOIN nguoi_dung u ON u.id = p.id_nguoi_dung
    
    -- SỬA LỖI: THÊM JOIN ĐỂ LẤY THÔNG TIN CÁ NHÂN
    LEFT JOIN info_nguoi_dung info ON info.id_nguoi_dung = u.id
    
    -- JOIN để lấy hình ảnh đại diện (dùng LATERAL)
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

// Thêm tham số phân trang vào mảng $params
$params[':limit'] = $perPage;
$params[':offset'] = $offset;

// 5. THỰC THI TRUY VẤN VÀ LẤY KẾT QUẢ
$stmt = $pdo->prepare($sqlData);

// Bind các tham số
foreach ($params as $key => &$val) {
    // Xác định kiểu dữ liệu để bind chính xác
    if ($key == ':limit' || $key == ':offset') {
        $stmt->bindParam($key, $val, PDO::PARAM_INT);
    } else {
        $stmt->bindParam($key, $val, PDO::PARAM_STR);
    }
}

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Biệt thự</title>
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
    
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-3">Biệt thự</h1>
        <p class="text-lg text-gray-600">Khám phá những căn biệt thự sang trọng và đẳng cấp.</p>
    </div>

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
                <a href="#" class="group block overflow-hidden">
                    <img src="../../../storage/pictures/bds/<?=$p['anh_dai_dien'] ?>" alt="<?= e($p['tieu_de']) ?>" class="w-full h-52 object-cover transition-all duration-300 group-hover:scale-105">
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="text-xl font-bold text-gray-900 mb-2 truncate">
                        <a href="chitiet_bds.php?id=<?= e($p['id']) ?>" class="hover:text-blue-700"><?= e($p['tieu_de']) ?></a>
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
                       class="flex items-center justify-center px-4 h-10 leading-tight <?= $i===$page_no?'text-white bg-blue-700 border-blue-700 hover:bg-blue-800':'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700' ?> <?= $i==1?'rounded-l-lg':'' ?> <?= $i==$pages?'rounded-r-lg':'' ?>">
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
</body>
</html>