<?php
// app/views/khach_hang/nhao.php

// Kết nối DB
require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();

// Lấy tham số
$search = trim($_GET['search'] ?? '');
$page_no = max(1, (int)($_GET['page_no'] ?? 1));
$perPage = 12;
$offset = ($page_no - 1) * $perPage;

// Điều kiện loại: "Nhà ở & Căn hộ" — match nhiều dạng
$loai_patterns = ["%nhà%", "%nha%", "%căn hộ%", "%canho%"];

// Xây dựng điều kiện động cho loai trước
$loai_cond = "(" . implode(" OR ", array_fill(0, count($loai_patterns), "dm.ten_danh_muc ILIKE ?")) . ")";

// Build base SQL
$sql_base = "
    FROM bat_dong_san b
    JOIN bai_dang bd ON bd.id_bat_dong_san = b.id
    JOIN nguoi_dung u ON u.id = bd.id_nguoi_dung
    JOIN phan_quyen pq ON pq.id_nguoi_dung = u.id
    JOIN quyen q ON q.id = pq.id_quyen
    JOIN danh_muc dm ON dm.id = b.id_danh_muc
    LEFT JOIN LATERAL (
        SELECT url FROM hinh_anh_bds WHERE id_bds = b.id ORDER BY ngay_tao ASC LIMIT 1
    ) ha ON TRUE
    WHERE bd.trang_thai = 'daduyet'
      AND q.ten_quyen = 'Môi giới'  -- Thay vai_tro bằng ten_quyen (dựa trên dump)
      AND " . $loai_cond;  // Chèn $loai_cond một cách an toàn

$params = [];

// Full select
$sql_count = "SELECT COUNT(DISTINCT b.id) " . $sql_base . " LIMIT ? OFFSET ?";  // Thêm limit/offset
$sql = "SELECT b.id, bd.tieu_de, b.dia_chi_day_du AS khu_vuc, b.dien_tich_dat AS dien_tich, bd.gia, bd.ngay_dang, bd.trang_thai,
        COALESCE(ha.url, 'chuacapnhat.jpg') AS anh_dai_dien,
        u.ten_dang_nhap, u.so_dt, u.avt
        " . $sql_base . " ORDER BY bd.ngay_dang DESC LIMIT ? OFFSET ?";

// Add search
if ($search !== '') {
    $sql .= " AND (bd.tieu_de ILIKE ? OR b.dia_chi_day_du ILIKE ?)";
    $sql_count .= " AND (bd.tieu_de ILIKE ? OR b.dia_chi_day_du ILIKE ?)";
}

// Prepare params
foreach ($loai_patterns as $p) {
    $params[] = $p;
}

$params[] = $perPage;  // Thêm cho LIMIT
$params[] = $offset;   // Thêm cho OFFSET

if ($search !== '') {
    $sval = "%$search%";
    $params[] = $sval; $params[] = $sval;
}

$params[] = $perPage;  // Thêm lại cho LIMIT trong $sql
$params[] = $offset;   // Thêm lại cho OFFSET trong $sql

// Debug: In ra sql_count và params
echo "<pre>";
var_dump($sql_count);
var_dump($params);
echo "</pre>";
exit;  // Xóa dòng này sau khi kiểm tra

// Execute count
$stmtC = $pdo->prepare($sql_count);
$stmtC->execute($params);
$total = (int)$stmtC->fetchColumn();

// Execute main query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function
function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Nhà ở & Căn hộ - Đất Việt BĐS (Môi Giới)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .logo-container .logo-img { transform: scale(2.6) translate(-5%, 0%); transform-origin: center right; }
        #main-search-button { transition: all 0.3s ease; }
        #main-search-button:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4); }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-4">Nhà ở & Căn hộ (Môi Giới)</h1>

        <form method="GET" class="mb-6 flex gap-2">
            <input type="hidden" name="page" value="khach_hang/nhao">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Tìm theo tiêu đề, địa chỉ..." class="flex-1 border px-3 py-2 rounded" />
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Tìm</button>
        </form>

        <?php if (empty($rows)): ?>
            <p class="text-gray-500">Chưa có sản phẩm nhà ở nào từ môi giới.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rows as $p): 
                    $img = !empty($p['anh_dai_dien']) ? "../../../storage/pictures/bds/" . e($p['anh_dai_dien']) : "../../../storage/pictures/bds/chuacapnhat.jpg";
                ?>
                    <div class="bg-white rounded-lg shadow p-4">
                        <a href="chitiet_bds.php?id=<?= e($p['id']) ?>">
                            <img src="<?= $img ?>" alt="<?= e($p['tieu_de']) ?>" class="w-full h-44 object-cover rounded-md mb-3">
                        </a>
                        <h3 class="text-lg font-semibold"><?= e($p['tieu_de']) ?></h3>
                        <p class="text-sm text-gray-600"><?= e($p['khu_vuc']) ?> • <?= e($p['dien_tich']) ?> m²</p>
                        <p class="text-red-600 font-bold mt-2"><?= e(number_format($p['gia'], 0, ',', '.')) ?> VNĐ</p>
                        <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                            <span>Người đăng (Môi giới): <?= e($p['ten_dang_nhap']) ?></span>
                            <span><?= date('d/m/Y', strtotime($p['ngay_dang'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php 
            $pages = max(1, ceil($total / $perPage));
            if ($pages > 1): 
                $base = 'trangchu.php?page=khach_hang/nhao';
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