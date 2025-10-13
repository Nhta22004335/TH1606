<?php
// app/views/khach_hang/bietthu.php

// KẾT NỐI DB (an toàn)
require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl(); // hàm kết nối PDO trong config của bạn

// Helper
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Lấy tham số từ GET
$search = trim($_GET['search'] ?? '');
$page_no = max(1, (int)($_GET['page_no'] ?? 1));
$perPage = 12;
$offset = ($page_no - 1) * $perPage;

// Patterns để match "biệt thự", "biet thu", không phân biệt hoa thường
$patterns = ['%biệt thự%', '%biet thu%', '%bietthu%', '%biệtthự%'];

$loaiPlaceholders = [];
$loaiParams = [];
foreach ($patterns as $i => $pat) {
    $ph = ':loai' . $i;
    $loaiPlaceholders[] = "b.loai ILIKE $ph";
    $loaiParams[$ph] = $pat;
}
$loaiCondition = '(' . implode(' OR ', $loaiPlaceholders) . ')';

// Build count SQL
$sql_count = "SELECT COUNT(*) FROM bat_dong_san b JOIN nguoi_dung u ON u.id = b.id_nguoi_dung
              WHERE b.trang_thai = 'daduyet' AND ($loaiCondition)";

// Add search condition for count if present
if ($search !== '') {
    $sql_count .= " AND (b.tieu_de ILIKE :search OR b.khu_vuc ILIKE :search OR b.dia_chi ILIKE :search)";
}

// Prepare and execute count
$stmtC = $pdo->prepare($sql_count);
// bind loai params
foreach ($loaiParams as $k => $v) $stmtC->bindValue($k, $v, PDO::PARAM_STR);
// bind search if any
if ($search !== '') $stmtC->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmtC->execute();
$total = (int)$stmtC->fetchColumn();

// Build main SQL
$sql = "
    SELECT 
        b.id, b.tieu_de, b.gia, b.dien_tich, b.khu_vuc, b.loai, b.ngay_dang, b.trang_thai,
        COALESCE(ha.url, 'chuacapnhat.jpg') AS anh_dai_dien,
        u.ten_dang_nhap, u.so_dt, u.avt
    FROM bat_dong_san b
    JOIN nguoi_dung u ON u.id = b.id_nguoi_dung
    LEFT JOIN LATERAL (
        SELECT url FROM hinh_anh_bds WHERE id_bds = b.id ORDER BY ngay_tao ASC LIMIT 1
    ) ha ON TRUE
    WHERE b.trang_thai = 'daduyet' AND ($loaiCondition)
";

// Add search condition
if ($search !== '') {
    $sql .= " AND (b.tieu_de ILIKE :search OR b.khu_vuc ILIKE :search OR b.dia_chi ILIKE :search)";
}

$sql .= " ORDER BY b.ngay_dang DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

// Bind loai params
foreach ($loaiParams as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);

// Bind search
if ($search !== '') $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);

// Bind limit/offset as integers
$stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

// Execute
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Image & avatar prefixes (chỉnh theo dự án của bạn)
$imgPrefix = '/storage/bds/';
$avatarPrefix = '/storage/pictures/avt/';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Biệt thự - Đất Việt BĐS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-4">Biệt thự</h1>

        <form method="GET" class="mb-6 flex gap-2">
            <!-- Nếu site dùng trang trung tâm, giữ page param -->
            <input type="hidden" name="page" value="khach_hang/bietthu">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Tìm theo tiêu đề, khu vực..." class="flex-1 border px-3 py-2 rounded" />
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Tìm</button>
        </form>

        <?php if (empty($rows)): ?>
            <p class="text-gray-500">Chưa có sản phẩm biệt thự phù hợp.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rows as $p): 
                    $img = !empty($p['anh_dai_dien']) ? $imgPrefix . e($p['anh_dai_dien']) : '/public/assets/no-image.png';
                ?>
                    <div class="bg-white rounded-lg shadow p-4">
                        <a href="trangchu.php?page=khach_hang/chitiet_bds&id=<?= e($p['id']) ?>">
                            <img src="<?= e($img) ?>" alt="<?= e($p['tieu_de']) ?>" class="w-full h-44 object-cover rounded-md mb-3">
                        </a>
                        <h3 class="text-lg font-semibold"><?= e($p['tieu_de']) ?></h3>
                        <p class="text-sm text-gray-600"><?= e($p['khu_vuc']) ?> • <?= e($p['dien_tich']) ?> m²</p>
                        <p class="text-red-600 font-bold mt-2"><?= e(number_format($p['gia'], 0, ',', '.')) ?> VNĐ</p>
                        <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                            <span>Người đăng: <?= e($p['ten_dang_nhap']) ?></span>
                            <span><?= date('d/m/Y', strtotime($p['ngay_dang'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php 
            $pages = max(1, ceil($total / $perPage));
            if ($pages > 1): 
                $base = 'trangchu.php?page=khach_hang/bietthu';
                if ($search !== '') $base .= '&search=' . urlencode($search);
            ?>
                <div class="mt-6 flex justify-center items-center space-x-2">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="<?= $base . '&page_no=' . $i ?>" class="px-3 py-1 rounded <?= $i === $page_no ? 'bg-blue-600 text-white' : 'bg-white border' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
