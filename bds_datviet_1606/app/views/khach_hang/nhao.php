<?php
// app/views/khach_hang/nhao.php

// Kết nối DB (an toàn)
require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl(); // hàm trong config của bạn (PostgreSQL PDO)

// Lấy tham số
$search = trim($_GET['search'] ?? '');
$page_no = max(1, (int)($_GET['page_no'] ?? 1));
$perPage = 12;
$offset = ($page_no - 1) * $perPage;

// Điều kiện loại: "Nhà ở & Căn hộ" — match nhiều dạng (không phụ thuộc chữ hoa/ thường)
$loai_patterns = ["%nhà%", "%nha%", "%căn hộ%", "%canho%"];

// Build base SQL (Postgres - ILIKE)
$sql_base = "
    FROM bat_dong_san b
    JOIN nguoi_dung u ON u.id = b.id_chu_so_huu
    LEFT JOIN LATERAL (
        SELECT url FROM hinh_anh_bds WHERE id_bds = b.id ORDER BY ngay_tao ASC LIMIT 1
    ) ha ON TRUE
    WHERE b.trang_thai = 'daduyet'
      AND (
        LOWER(b.loai) ILIKE ANY (ARRAY['nhà', 'nha', 'căn hộ', 'canho']::text[])
      )
";

// We'll build a safer condition for loai using ILIKE ORs
$loai_cond = "(" . implode(" OR ", array_fill(0, count($loai_patterns), "b.loai ILIKE ?")) . ")";
$params = [];

// Start full select
$sql_count = "SELECT COUNT(*) FROM bat_dong_san b JOIN nguoi_dung u ON u.id = b.id_chu_so_huu WHERE b.trang_thai = 'daduyet' AND ($loai_cond)";

// Full select query
$sql = "SELECT b.id, b.tieu_de, b.gia, b.dien_tich, b.khu_vuc, b.loai, b.ngay_dang, b.trang_thai,
        COALESCE(ha.url, 'chuacapnhat.jpg') AS anh_dai_dien,
        u.ten_dang_nhap, u.so_dt, u.avt
        FROM bat_dong_san b
        JOIN nguoi_dung u ON u.id = b.id_chu_so_huu
        LEFT JOIN LATERAL (
            SELECT url FROM hinh_anh_bds WHERE id_bds = b.id ORDER BY ngay_tao ASC LIMIT 1
        ) ha ON TRUE
        WHERE b.trang_thai = 'daduyet' AND ($loai_cond)";

// Add search condition if present
if ($search !== '') {
    $sql .= " AND (b.tieu_de ILIKE ? OR b.khu_vuc ILIKE ? OR b.dia_chi ILIKE ?)";
    $sql_count .= " AND (b.tieu_de ILIKE ? OR b.khu_vuc ILIKE ? OR b.dia_chi ILIKE ?)";
}

// Ordering and limit
$sql .= " ORDER BY b.ngay_dang DESC LIMIT ? OFFSET ?";

// Prepare params array in same order as placeholders (Postgres PDO supports positional params)
foreach ($loai_patterns as $p) {
    $params[] = $p;
}

// If search given, add three search params
if ($search !== '') {
    $sval = "%$search%";
    $params[] = $sval; $params[] = $sval; $params[] = $sval;
}

// Count query params (same initial loai and optional search)
$countParams = $params;

// Execute count
$stmtC = $pdo->prepare($sql_count);
$stmtC->execute($countParams);
$total = (int)$stmtC->fetchColumn();

// Now add limit/offset params for main query
$params[] = $perPage;
$params[] = $offset;

// Prepare and execute main query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

//echo print_r($rows); // debug

// helper
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Nhà ở & Căn hộ - Đất Việt BĐS</title>
    <script src="https://cdn.tailwindcss.com"></script>

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
<body class="bg-gray-50 text-gray-800">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-4">Nhà ở & Căn hộ</h1>

        <form method="GET" class="mb-6 flex gap-2">
            <input type="hidden" name="page" value="khach_hang/nhao">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Tìm theo tiêu đề, khu vực..." class="flex-1 border px-3 py-2 rounded" />
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Tìm</button>
        </form>

        <?php if (empty($rows)): ?>
            <p class="text-gray-500">Chưa có sản phẩm nhà ở nào phù hợp.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rows as $p): 
                    $img = !empty($p['anh_dai_dien']) ? "../../../storage/pictures/bds/" . e($p['anh_dai_dien']) : "../../../storage/pictures/bds/";
                ?>
                    <div class="bg-white rounded-lg shadow p-4">
                   <a href="chitiet_bds.php?id=<?= htmlspecialchars($p['id']) ?>">
                            <img src="../../../storage/pictures/bds/<?= $p['anh_dai_dien'] ?>" alt="<?= e($p['tieu_de']) ?>" class="w-full h-44 object-cover rounded-md mb-3">
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

            <!-- pagination -->
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
