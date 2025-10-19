<?php
// ===================================================================
// PHẦN 1: XỬ LÝ LOGIC & DỮ LIỆU (BACKEND)
// ===================================================================

// Bắt đầu phiên làm việc an toàn
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Chuyển hướng nếu chưa đăng nhập
if (!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.html");
    exit;
}
// Kết nối CSDL
require_once "../../../config/database.php"; 
$pdo = ketnoicsdl();

// --- Xác thực người dùng và Vai trò ---
$id_moigioi = $_SESSION['id_nguoi_dung'] ?? null; 

if (!$id_moigioi) {
    http_response_code(403);
    exit("<p class='p-4 text-red-500 font-bold text-center'>⚠️ Vui lòng đăng nhập để xem sản phẩm cá nhân.</p>");
}

try {
    $stmt_role = $pdo->prepare("
        SELECT 1 
        FROM phan_quyen pq
        JOIN quyen q ON pq.id_quyen = q.id
        WHERE pq.id_nguoi_dung = ? AND q.vai_tro = 'moigioi'
    ");
    $stmt_role->execute([$id_moigioi]);
    if ($stmt_role->rowCount() === 0) {
        http_response_code(403);
        exit("<p class='p-4 text-red-500 font-bold text-center'>⚠️ Bạn không có quyền truy cập trang quản lý tin đăng.</p>");
    }
} catch (PDOException $e) {
    error_log("Role verification error: " . $e->getMessage());
    http_response_code(500);
    exit("Lỗi hệ thống: Không thể xác thực vai trò.");
}

// 2. BỔ SUNG: Kiểm tra xem ID người dùng này đã sở hữu BĐS nào chưa.
$total_products = 0;
try {
    // [SỬA ĐỔI] Đếm bat_dong_san thay vì bai_dang
    $stmt_count = $pdo->prepare("SELECT COUNT(id) FROM bat_dong_san WHERE id_chu_so_huu = ?");
    $stmt_count->execute([$id_moigioi]);
    $total_products = $stmt_count->fetchColumn();
} catch (PDOException $e) {
    error_log("Count products error: " . $e->getMessage());
}

// Nếu người dùng chưa có bất kỳ sản phẩm nào, ta sẽ hiển thị thông báo riêng biệt
$has_products = ($total_products > 0);

// --- Lấy và chuẩn hóa tham số tìm kiếm & lọc ---
$search_term = trim($_GET['search'] ?? '');
// [SỬA ĐỔI] Cập nhật các trạng thái hợp lệ cho bat_dong_san
$valid_statuses_for_query = ['chuaduyet', 'daduyet', 'huy']; 
$filter_status = trim($_GET['trang_thai'] ?? 'tat_ca');

if (!in_array($filter_status, $valid_statuses_for_query) && $filter_status !== 'tat_ca') {
    $filter_status = 'tat_ca';
}

// --- Lưu lịch sử tìm kiếm ---
if (!empty($search_term)) {
    try {
        $stmt_history = $pdo->prepare("INSERT INTO lich_su_tim_kiem (id_nguoi_dung, tu_khoa_tim_kiem) VALUES (?, ?)");
        $stmt_history->execute([$id_moigioi, $search_term]);
    } catch (PDOException $e) {
        // Bỏ qua lỗi lưu lịch sử
    }
}

// --- Truy vấn sản phẩm (CHỈ CHẠY KHI has_products = TRUE) ---
$products = [];
if ($has_products) {
    $params = [':id_moigioi' => $id_moigioi];

    // [SỬA ĐỔI] Truy vấn bắt đầu từ bat_dong_san (bds) và loại bỏ bai_dang (bd)
    $sql = "
    SELECT 
        bds.id AS id_bds,            -- ID chính là của BĐS
       
        bds.ngay_tao,             -- Thay thế cho ngay_dang
        bds.trang_thai,           -- Sử dụng trạng thái của BĐS
        bds.dien_tich_dat AS dien_tich, 
        bds.dia_chi_day_du, -- Sử dụng làm tiêu đề
        
        dm.ten_danh_muc AS loai, 
        
        (SELECT COALESCE(AVG(diem), 0) 
         FROM danh_gia_bds 
         WHERE id_bds = bds.id) AS rating, 
        
        -- [SỬA ĐỔI] Thay thế LATERAL JOIN bằng Correlated Subquery (MySQL)
        COALESCE(
            (SELECT ha.url 
             FROM hinh_anh_bds ha
             WHERE ha.id_bds = bds.id 
             ORDER BY ha.ngay_tao DESC 
             LIMIT 1),
            'chuacapnhat.jpg'
        ) AS anh_dai_dien 
    FROM bat_dong_san bds -- Bắt đầu từ bat_dong_san
    LEFT JOIN danh_muc dm ON bds.id_danh_muc = dm.id -- Bỏ 'public.'
    -- Đã xóa JOIN với bai_dang và LEFT JOIN LATERAL
    WHERE bds.id_chu_so_huu = :id_moigioi -- [SỬA ĐỔI] Điều kiện chính
    ";

    if ($search_term !== '') {
        // [SỬA ĐỔI] Chỉ tìm theo địa chỉ và thay ILIKE bằng LIKE
        $sql .= " AND (bds.dia_chi_day_du LIKE :search)";
        $params[':search'] = "%$search_term%";
    }

    if ($filter_status !== 'tat_ca') {
        // [SỬA ĐỔI] Lọc theo trạng thái của bds
        $sql .= " AND bds.trang_thai = :trang_thai";
        $params[':trang_thai'] = $filter_status;
    }

    // [SỬA ĐỔI] Sắp xếp theo ngày tạo BĐS
    $sql .= " ORDER BY bds.ngay_tao DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in sp_canhan: " . $e->getMessage());
        $products = [];
    }
}

// --- Data Mapping & Helper Functions ---
// [SỬA ĐỔI] Cập nhật map trạng thái cho bat_dong_san
$display_statuses = ['chuaduyet', 'daduyet', 'huy'];
$status_map = [
    'chuaduyet' => ['label' => 'Chờ duyệt', 'class' => 'bg-yellow-100 text-yellow-800'],
    'daduyet' => ['label' => 'Đã duyệt', 'class' => 'bg-green-100 text-green-800'],
    'huy' => ['label' => 'Đã hủy', 'class' => 'bg-red-100 text-red-800'],
    'chuacapnhat' => ['label' => 'Chưa cập nhật', 'class' => 'bg-gray-100 text-gray-700']
];

function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function format_price_vietnamese(float $price): string {
    if ($price >= 1000000000) return rtrim(rtrim(number_format($price / 1000000000, 2, ',', ''), '0'), ',') . ' tỷ';
    elseif ($price >= 1000000) return number_format($price / 1000000, 0, ',', '.') . ' triệu';
    elseif ($price > 0) return number_format($price, 0, ',', '.') . ' VNĐ';
    else return 'Thỏa thuận';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sản phẩm cá nhân</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

<div class="container mx-auto p-6"> 
<header class="mb-6 border-b pb-4">
<h1 class="text-2xl font-bold text-gray-800">Quản lý Tài sản cá nhân</h1>
<p class="text-sm mt-2 text-gray-500">Xem, tìm kiếm và quản lý các bất động sản bạn sở hữu.</p>
</header>

<div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
<form method="GET" class="flex flex-col sm:flex-row sm:items-center sm:flex-wrap gap-3">
<input type="hidden" name="page" value="<?= e($_GET['page'] ?? 'sp_canhan') ?>">
<div class="relative flex-grow">
<i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
<input type="text" name="search" placeholder="Tìm địa chỉ..." value="<?= e($search_term) ?>" class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150">
</div>
<select name="trang_thai" class="w-full sm:w-48 text-sm border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150">
<option value="tat_ca" <?= $filter_status=='tat_ca'?'selected':'' ?>>Tất cả trạng thái</option>
<?php 
// [SỬA ĐỔI] Dùng $display_statuses (đã cập nhật)
foreach($display_statuses as $status): 
?>
<option value="<?= e($status) ?>" <?= $filter_status==$status?'selected':'' ?>><?= e($status_map[$status]['label'] ?? $status) ?></option>
<?php endforeach; ?>
</select>
<button type="submit" class="flex items-center justify-center w-full sm:w-auto px-5 py-2 bg-gray-800 text-white rounded-md text-sm font-medium hover:bg-gray-900 transition duration-150">
<i class="fas fa-filter -ml-1 mr-2 h-4 w-4"></i>
<span>Lọc</span>
</button>
</form>
</div>

<div class="bg-white shadow-lg rounded-xl overflow-hidden overflow-x-auto">
<table class="min-w-full table-auto">
<thead class="bg-gray-100 text-gray-700 text-sm font-semibold">
<tr>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase w-20">Ảnh</th>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase w-auto">Thông tin chi tiết</th>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase w-32">Giá</th>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase w-32">Trạng thái</th>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase w-32">Đánh giá</th>
<th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase w-56">Hành động</th>
</tr>
</thead>
<tbody class="divide-y divide-gray-100">
<?php 
// Hiển thị thông báo nếu người dùng chưa có BĐS nào
if (!$has_products): 
?>
<tr><td colspan="7" class="text-center text-gray-500 py-8">
    <i class="fas fa-exclamation-circle fa-2x mb-2 text-indigo-500"></i><br>
    <p class="font-bold text-gray-700">Bạn chưa sở hữu bất động sản nào.</p>
    <p class="text-sm mt-1">Vui lòng tạo bất động sản mới để bắt đầu quản lý.</p>
</td></tr>
<?php 
// Hiển thị thông báo khi không tìm thấy kết quả sau khi lọc/tìm kiếm
elseif(empty($products)): 
?>
<tr><td colspan="7" class="text-center text-gray-500 py-8">
    <i class="fas fa-box-open fa-2x mb-2"></i><br>
    Không tìm thấy bất động sản nào phù hợp với điều kiện tìm kiếm/lọc.
</td></tr>
<?php 
// Hiển thị danh sách sản phẩm
else: 
?>
<?php foreach($products as $product): ?>
<tr class="hover:bg-gray-50 transition">
<td class="px-4 py-3">
    <img src="../../../storage/pictures/bds/<?= e($product['anh_dai_dien']) ?>?t=<?= time() ?>" 
         onerror="this.onerror=null;this.src='../../../storage/pictures/bds/chuacapnhat.jpg';"
         alt="Ảnh <?= e($product['dia_chi_day_du']) ?>" class="w-16 h-16 object-cover rounded-lg border shadow-md">
</td>
<td class="px-4 py-3 text-sm font-semibold text-gray-800">
    <a href="trangchu.php?page=../moi_gioi/chi_tiet_bds&id=<?= e($product['id_bds']) ?>" class="text-indigo-600 hover:text-indigo-800 hover:underline">
        <?= e($product['dia_chi_day_du']) ?>
    </a>
    <p class="text-xs text-gray-500 mt-1 font-normal">
        <i class="fas fa-home fa-fw mr-1"></i> Loại: <?= e($product['loai']) ?>
        <span class="ml-3"><i class="fas fa-th-large fa-fw mr-1"></i> DT: <?= number_format((float)$product['dien_tich'], 0, '.', ',') ?> m²</span>
    </p>
    <p class="text-xs text-gray-500 mt-1 font-normal">
        <i class="fas fa-calendar-alt fa-fw"></i> Tạo ngày: <?= date("d/m/Y", strtotime($product['ngay_tao'])) ?>
    </p>
</td>
<td class="px-4 py-3 text-sm text-green-600 font-bold">
    <?= format_price_vietnamese($product['gia'] ?? 0) ?>
</td>
<td class="px-4 py-3">
    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= e($status_map[$product['trang_thai']]['class'] ?? 'bg-gray-100 text-gray-700') ?>">
    <?= e($status_map[$product['trang_thai']]['label'] ?? $product['trang_thai']) ?>
    </span>
</td>
<td class="px-4 py-3 text-sm text-yellow-500 font-semibold">
    <?= number_format((float)$product['rating'],1) ?> ⭐
</td>
<td class="px-4 py-3 text-center space-x-2 flex flex-col sm:flex-row items-center justify-center gap-1">
    <a href="trangchu.php?page=../moi_gioi/chi_tiet_bds&id=<?= e($product['id_bds']) ?>" 
       class="text-indigo-600 hover:text-indigo-800 px-2 py-1 text-sm bg-indigo-50 hover:bg-indigo-100 rounded-md transition w-full sm:w-auto">
        <i class="fas fa-eye mr-1"></i> Xem
    </a>
    
    <a href="trangchu.php?page=../moi_gioi/sua_san_pham&id=<?= e($product['id_bds']) ?>" 
       class="text-blue-600 hover:text-blue-800 px-2 py-1 text-sm bg-blue-50 hover:bg-blue-100 rounded-md transition w-full sm:w-auto">
        <i class="fas fa-edit mr-1"></i> Sửa
    </a>
    <a href="../../models/xoa_san_pham.php?id=<?= e($product['id_bds']) ?>" 
       class="text-red-600 hover:text-red-800 px-2 py-1 text-sm bg-red-50 hover:bg-red-100 rounded-md transition w-full sm:w-auto"
       onclick="return confirm('Bạn có chắc muốn xóa BẤT ĐỘNG SẢN này (ID: <?= e($product['id_bds']) ?>)?');">
        <i class="fas fa-trash-alt mr-1"></i> Xóa
    </a>
</td>

</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

</body>
</html>