<?php
// ===================================================================
// PHẦN 1: XỬ LÝ LOGIC & DỮ LIỆU (BACKEND)
// ===================================================================

// Bắt đầu phiên làm việc an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối CSDL
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// --- Xác thực người dùng ---
$id_moigioi = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_moigioi) {
    http_response_code(403);
    exit("<p class='p-4 text-red-500 font-bold text-center'>⚠️ Vui lòng đăng nhập để xem sản phẩm cá nhân.</p>");
}

// --- Lấy và chuẩn hóa tham số tìm kiếm ---
$search_term = trim($_GET['search'] ?? '');

// --- Lưu lịch sử tìm kiếm ---
if (!empty($search_term)) {
    try {
        $stmt_history = $pdo->prepare("INSERT INTO lich_su_tim_kiem (id_nguoi_dung, tu_khoa_tim_kiem) VALUES (?, ?)");
        $stmt_history->execute([$id_moigioi, $search_term]);
    } catch (PDOException $e) {
        // bỏ qua lỗi lưu lịch sử
    }
}

$filter_status = trim($_GET['trang_thai'] ?? 'tat_ca');
$valid_statuses = ['chuaduyet', 'daduyet', 'daban', 'dathue'];
if (!in_array($filter_status, $valid_statuses) && $filter_status !== 'tat_ca') {
    $filter_status = 'tat_ca';
}

// --- Truy vấn sản phẩm ---
$params = [':id_moigioi' => $id_moigioi];

$sql = "
    SELECT 
        b.id, b.tieu_de, b.gia, b.dien_tich, b.khu_vuc, b.loai, 
        b.ngay_dang, b.trang_thai,
        COALESCE(d.diem, 0) AS rating,
        COALESCE(ha.url, 'chuacapnhat.jpg') AS anh_dai_dien
    FROM public.bat_dong_san b
    LEFT JOIN danh_gia_bds d ON d.id_bds = b.id
    LEFT JOIN LATERAL (
        SELECT url 
        FROM hinh_anh_bds 
        WHERE id_bds = b.id 
        ORDER BY ngay_tao DESC -- lấy ảnh mới nhất
        LIMIT 1
    ) ha ON TRUE
    WHERE b.id_nguoi_dung = :id_moigioi
";

if ($search_term !== '') {
    $sql .= " AND (b.tieu_de ILIKE :search OR b.khu_vuc ILIKE :search OR b.dia_chi ILIKE :search)";
    $params[':search'] = "%$search_term%";
}

if ($filter_status !== 'tat_ca') {
    $sql .= " AND b.trang_thai = :trang_thai";
    $params[':trang_thai'] = $filter_status;
}

$sql .= " ORDER BY b.ngay_dang DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Trạng thái & helper ---
$status_map = [
    'chuaduyet' => ['label' => 'Chờ duyệt', 'class' => 'bg-yellow-100 text-yellow-800'],
    'daduyet'   => ['label' => 'Đã duyệt',  'class' => 'bg-green-100 text-green-800'],
    'daban'     => ['label' => 'Đã bán',    'class' => 'bg-red-100 text-red-800'],
    'dathue'    => ['label' => 'Đã thuê',   'class' => 'bg-blue-100 text-blue-800'],
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
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-50">

<div class="container">
<header class="mb-6 border-b pb-4">
<h1 class="text-2xl font-bold text-gray-800">Sản phẩm cá nhân</h1>
<p class="text-sm mt-2 text-gray-500">Xem, tìm kiếm và quản lý sản phẩm.</p>
</header>

<!-- Filter -->
<div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
<form method="GET" class="flex flex-col sm:flex-row sm:items-center sm:flex-wrap gap-3">
<input type="hidden" name="page" value="<?= e($_GET['page'] ?? 'sp_canhan') ?>">
<div class="relative flex-grow">
<i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
<input type="text" name="search" placeholder="Tìm tiêu đề, khu vực..." value="<?= e($search_term) ?>" class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150">
</div>
<select name="trang_thai" class="w-full sm:w-48 text-sm border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150">
<option value="tat_ca" <?= $filter_status=='tat_ca'?'selected':'' ?>>Tất cả trạng thái</option>
<?php foreach($valid_statuses as $status): ?>
<option value="<?= e($status) ?>" <?= $filter_status==$status?'selected':'' ?>><?= e($status_map[$status]['label']) ?></option>
<?php endforeach; ?>
</select>
<button type="submit" class="flex items-center justify-center w-full sm:w-auto px-5 py-2 bg-gray-800 text-white rounded-md text-sm font-medium hover:bg-gray-900 transition duration-150">
<i class="fas fa-filter -ml-1 mr-2 h-4 w-4"></i>
<span>Lọc</span>
</button>
</form>
</div>

<!-- Table -->
<div class="bg-white shadow-lg rounded-xl overflow-hidden overflow-x-auto overflow-y-auto">
<table class="min-w-full table-auto">
<thead class="bg-gray-100 text-gray-700 text-sm font-semibold">
<tr>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Ảnh</th>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Tiêu đề</th>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Giá</th>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Khu vực</th>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Trạng thái</th>
<th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Đánh giá</th>
<th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase">Hành động</th>
</tr>
</thead>
<tbody class="divide-y divide-gray-100">
<?php if(empty($products)): ?>
<tr><td colspan="7" class="text-center text-gray-500 py-6">Không có sản phẩm nào phù hợp.</td></tr>
<?php else: ?>
<?php foreach($products as $product): ?>
<tr class="hover:bg-gray-50 transition">
<td class="px-4 py-3">
<img src="../../../storage/pictures/bds/<?= e($product['anh_dai_dien']) ?>?t=<?= time() ?>" alt="Ảnh <?= e($product['tieu_de']) ?>" class="w-16 h-16 object-cover rounded-lg border">
</td>
<td class="px-4 py-3 text-sm font-semibold text-gray-800">
<?= e($product['tieu_de']) ?>
<p class="text-xs text-gray-500 mt-1 font-normal">
<i class="fas fa-calendar-alt fa-fw"></i> <?= date("d/m/Y", strtotime($product['ngay_dang'])) ?>
</p>
<p class="text-xs text-gray-500 mt-1 font-normal">
<i class="fas fa-home fa-fw"></i> <?= e($product['loai']) ?>
</p>
</td>
<td class="px-4 py-3 text-sm text-gray-700 font-medium"><?= format_price_vietnamese($product['gia']) ?></td>
<td class="px-4 py-3 text-sm text-gray-700"><?= e($product['khu_vuc']) ?></td>
<td class="px-4 py-3">
<span class="px-2 py-1 rounded-full text-xs font-semibold <?= e($status_map[$product['trang_thai']]['class']) ?>">
<?= e($status_map[$product['trang_thai']]['label']) ?></span>
</td>
<td class="px-4 py-3 text-sm text-yellow-500 font-semibold">
<?= number_format((float)$product['rating'],1) ?> ⭐
</td>
<td class="px-4 py-3 text-center space-x-2">
    <!-- Nút Xem -->
    <a href="trangchu.php?page=../moi_gioi/xem_san_pham&id=<?= e($product['id']) ?>" 
       class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-eye"></i> Xem
    </a>

    <!-- Nút Sửa -->
    <a href="trangchu.php?page=../moi_gioi/sua_san_pham&id=<?= e($product['id']) ?>" 
       class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-edit"></i> Sửa
    </a>

    <!-- Nút Xóa -->
    <a href="xoa_san_pham.php?id=<?= e($product['id']) ?>" 
       class="text-red-600 hover:text-red-800"
       onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
        <i class="fas fa-trash"></i> Xóa
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
