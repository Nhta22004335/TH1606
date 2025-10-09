<?php
// Bắt đầu phiên
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Lấy ID môi giới đang đăng nhập
$id_moigioi = $_SESSION['id_nguoi_dung'] ?? null;

if (!$id_moigioi) {
    echo "<p class='p-4 text-red-500 font-bold text-center'>⚠️ Vui lòng đăng nhập để xem sản phẩm cá nhân.</p>";
    exit;
}

// Nhận từ khóa tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Câu truy vấn
$sql = "
    SELECT 
        b.id,
        b.tieu_de,
        b.mo_ta,
        b.gia,
        b.dien_tich,
        b.dia_chi,
        b.loai,
        b.khu_vuc,
        b.ngay_dang,
        b.trang_thai,
        b.hinh_thuc,
        COALESCE(d.diem, 0) AS rating,
        COALESCE(ha.url, 'chuacapnhat.jpg') AS anh_dai_dien
    FROM public.bat_dong_san b
    LEFT JOIN danh_gia_bds d ON d.id_bds = b.id
    LEFT JOIN LATERAL (
        SELECT url FROM hinh_anh_bds WHERE id_bds = b.id ORDER BY ngay_tao ASC LIMIT 1
    ) ha ON TRUE
    WHERE b.id_nguoi_dung = :id_moigioi
";

if ($keyword !== '') {
    $sql .= " AND (b.tieu_de ILIKE :kw OR b.khu_vuc ILIKE :kw OR b.dia_chi ILIKE :kw)";
}

$sql .= " ORDER BY b.ngay_dang DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_moigioi', $id_moigioi, PDO::PARAM_STR);

if ($keyword !== '') {
    $stmt->bindValue(':kw', "%$keyword%", PDO::PARAM_STR);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bất động sản cá nhân</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../../public/assets/fontawesome/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        <i class="fas fa-building text-red-500"></i> Quản lý Bất động sản
    </h1>

<!-- Thanh tìm kiếm sản phẩm -->
<div class="bg-white shadow-md rounded-xl p-4 mb-6 flex flex-col md:flex-row items-center justify-between gap-3">

    <!-- Ô tìm kiếm -->
    <form method="GET" class="w-full md:w-auto flex items-center space-x-2">
        <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? 'sanpham_canhan') ?>"> 
        <div class="relative">
            <input type="text" name="search" placeholder="Nhập từ khóa tìm kiếm..." value="<?= htmlspecialchars($search ?? '') ?>"
                   class="w-64 pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-full 
                          focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                   onchange="this.form.submit()">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
        </div>
    </form>

    <!-- Bộ lọc trạng thái -->
    <form method="GET" class="w-full md:w-auto">
        <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? 'sanpham_canhan') ?>">
        <input type="hidden" name="search" value="<?= htmlspecialchars($search ?? '') ?>">
        <select name="trang_thai"
                class="w-48 text-sm border border-gray-300 rounded-full px-3 py-2 bg-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                onchange="this.form.submit()">
            <option value="tat_ca" <?= ($filter_status ?? '') == 'tat_ca' ? 'selected' : '' ?>>-- Tất cả trạng thái --</option>
            <option value="chuaduyet" <?= ($filter_status ?? '') == 'chuaduyet' ? 'selected' : '' ?>>Chờ duyệt</option>
            <option value="daduyet" <?= ($filter_status ?? '') == 'daduyet' ? 'selected' : '' ?>>Đã duyệt</option>
            <option value="daban" <?= ($filter_status ?? '') == 'daban' ? 'selected' : '' ?>>Đã bán</option>
            <option value="dathue" <?= ($filter_status ?? '') == 'dathue' ? 'selected' : '' ?>>Đã thuê</option>
        </select>
    </form>

</div>


    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <table class="min-w-full table-auto border-collapse">
            <thead class="bg-gray-100 text-gray-700 uppercase text-sm font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left">Ảnh</th>
                    <th class="px-4 py-3 text-left">Tiêu đề</th>
                    <th class="px-4 py-3 text-left">Giá (VNĐ)</th>
                    <th class="px-4 py-3 text-left">Diện tích (m²)</th>
                    <th class="px-4 py-3 text-left">Khu vực</th>
                    <th class="px-4 py-3 text-left">Loại</th>
                    <th class="px-4 py-3 text-left">Trạng thái</th>
                    <th class="px-4 py-3 text-left">Đánh giá</th>
                    <th class="px-4 py-3 text-center">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-gray-500 py-6">
                            Không có bất động sản nào <?= $keyword ? "phù hợp với từ khóa “" . htmlspecialchars($keyword) . "”" : "để hiển thị" ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <img src="../../../storage/bds/<?= htmlspecialchars($p['anh_dai_dien']) ?>" 
                                     alt="Ảnh" class="w-16 h-16 object-cover rounded-lg border">
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-800">
                                <?= htmlspecialchars($p['tieu_de']) ?>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= date("d/m/Y", strtotime($p['ngay_dang'])) ?>
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm text-red-600 font-bold">
                                <?= number_format($p['gia'], 0, ',', '.') ?> đ
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?= $p['dien_tich'] ?> m²</td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($p['khu_vuc']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700 capitalize"><?= htmlspecialchars($p['loai']) ?></td>
                            <td class="px-4 py-3 text-sm">
                                <?php
                                    $statusColors = [
                                        'chuaduyet' => 'bg-yellow-100 text-yellow-800',
                                        'daduyet'   => 'bg-green-100 text-green-800',
                                        'daban'     => 'bg-red-100 text-red-800',
                                        'dathue'    => 'bg-blue-100 text-blue-800'
                                    ];
                                    $statusText = [
                                        'chuaduyet' => 'Chờ duyệt',
                                        'daduyet'   => 'Đã duyệt',
                                        'daban'     => 'Đã bán',
                                        'dathue'    => 'Đã thuê'
                                    ];
                                    $class = $statusColors[$p['trang_thai']] ?? 'bg-gray-100 text-gray-700';
                                    $label = $statusText[$p['trang_thai']] ?? 'Không rõ';
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $class ?>">
                                    <?= $label ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-yellow-500">
                                <?php $rating = (int)round($p['rating']); ?>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $rating): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="text-gray-500 text-xs ml-1">(<?= number_format($p['rating'], 1) ?>/5)</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="#" class="text-blue-600 hover:text-blue-800 mr-3 text-sm" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="text-red-600 hover:text-red-800 text-sm" title="Xóa">
                                    <i class="fas fa-trash-alt"></i>
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
