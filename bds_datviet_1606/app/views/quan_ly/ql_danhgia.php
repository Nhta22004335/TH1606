<?php
    require_once "../../../config/database.php";

    // --- 1. THIẾT LẬP KẾT NỐI VÀ CÁC HÀM HELPER ---
    try {
        $pdo = ketnoicsdl();
    } catch (PDOException $e) {
        die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 rounded-lg'>Lỗi kết nối CSDL: " . $e->getMessage() . "</div>");
    }

    // Hàm helper để hiển thị sao
    function renderStars($score) {
        $score = $score ?? 0;
        $full = floor($score);
        $half = ceil($score - $full);
        $empty = 5 - $full - $half;
        $html = '<span class="text-yellow-400">';
        $html .= str_repeat('<i class="fa-solid fa-star"></i>', $full);
        $html .= str_repeat('<i class="fa-solid fa-star-half-stroke"></i>', $half);
        $html .= '</span>';
        $html .= str_repeat('<i class="fa-regular fa-star text-gray-300"></i>', $empty);
        return $html;
    }

    // --- 2. XỬ LÝ ĐẦU VÀO (PAGINATION & SEARCH) ---
    $limit = 10; // Số sản phẩm mỗi trang
    $page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';

    // --- 3. XÂY DỰNG VÀ THỰC THI CÂU TRUY VẤN ---
    $baseSql = "
        FROM bat_dong_san bds
        LEFT JOIN danh_gia_bds dg ON bds.id = dg.id_bds
    ";
    $whereClauses = [];
    $params = [];

    if (!empty($search)) {
        $whereClauses[] = "bds.tieu_de ILIKE :search";
        $params[':search'] = '%' . $search . '%';
    }
    
    $whereSql = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";
    $groupBySql = " GROUP BY bds.id, bds.loai, bds.tieu_de, bds.trang_thai";

    // Câu truy vấn đếm tổng số dòng để phân trang
    $countSql = "SELECT COUNT(DISTINCT bds.id) " . $baseSql . $whereSql;
    $totalStmt = $pdo->prepare($countSql);
    $totalStmt->execute($params);
    $totalRows = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    // Câu truy vấn lấy dữ liệu cho trang hiện tại
    $dataSql = "
        SELECT 
            bds.id, bds.loai, bds.tieu_de AS ten_san_pham,
            COUNT(dg.id) AS tong_so_danh_gia,
            ROUND(AVG(dg.diem), 1) AS diem_trung_binh,
            COUNT(dg.id) FILTER (WHERE dg.binh_luan IS NOT NULL AND dg.binh_luan <> '') AS so_binh_luan
        " . $baseSql . $whereSql . $groupBySql . "
        ORDER BY tong_so_danh_gia DESC, diem_trung_binh DESC
        LIMIT :limit OFFSET :offset;
    ";
    
    try {
        $stmt = $pdo->prepare($dataSql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        if (!empty($search)) {
            $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
        }
        $stmt->execute();
        $danhgiasanpham = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 rounded-lg'>Lỗi truy vấn: " . $e->getMessage() . "</div>");
    }
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tổng quan Đánh giá Bất động sản</title>
</head>
<body>

    <header class="mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tổng quan đánh giá</h1>
            <p class="text-sm mt-1 text-gray-500">Xem và quản lý tất cả đánh giá cho các bất động sản.</p>
        </div>
    </header>

    <!-- Khối tìm kiếm và thông tin -->
    <div class="mb-5">
        <form method="GET">
            <input type="hidden" name="page" value="ql_danhgia">
            <div class="flex items-center">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-search text-gray-400"></i>
                    </div>
                    <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm theo tên bất động sản..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <button type="submit" class="ml-3 px-5 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">Tìm</button>
            </div>
        </form>
        <p class="text-xs text-gray-500 mt-2">
            Hiển thị <?= count($danhgiasanpham) ?> trên tổng số <strong><?= $totalRows ?></strong> kết quả.
        </p>
    </div>

    <!-- Bảng dữ liệu -->
    <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200/80">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sản phẩm</th>
                        <th scope="col" class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Điểm TB / Lượt</th>
                        <th scope="col" class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Số bình luận</th>
                        <th scope="col" class="relative py-3 px-4"><span class="sr-only">Hành động</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($danhgiasanpham)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="inline-block bg-gray-100 p-5 rounded-full text-gray-400">
                                    <i class="fa-solid fa-folder-open fa-2x"></i>
                                </div>
                                <p class="mt-4 font-semibold text-gray-700">Không có dữ liệu</p>
                                <p class="text-sm text-gray-500">Không tìm thấy đánh giá nào phù hợp với tìm kiếm của bạn.</p>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach($danhgiasanpham as $sp): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <p class="font-medium text-sm text-gray-900 truncate max-w-sm" title="<?= htmlspecialchars($sp['ten_san_pham']) ?>">
                                    <?= htmlspecialchars($sp['ten_san_pham']) ?>
                                </p>
                                <p class="text-xs text-gray-500 capitalize">
                                    ID: #<?= $sp['id'] ?> &middot; <?= htmlspecialchars($sp['loai']) ?>
                                </p>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <?php $score = (float)$sp['diem_trung_binh']; ?>
                                <?php if ($score > 0): ?>
                                    <div class="flex items-center justify-center gap-2">
                                        <?= renderStars($score) ?>
                                        <span class="text-xs font-bold text-gray-600">(<?= $score ?>/5)</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1"><?= $sp['tong_so_danh_gia'] ?> lượt</p>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">Chưa có</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-700">
                                <?= number_format($sp['so_binh_luan']) ?>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="trangchu.php?page=ql_danhgia_ct&id=<?= urlencode($sp['id']) ?>" 
                                   class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-900 disabled:opacity-50 disabled:pointer-events-none transition-colors"
                                   <?= ($sp['tong_so_danh_gia'] == 0) ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                                    Xem chi tiết
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
        <nav class="flex items-center justify-between mt-6" aria-label="Pagination">
            <div class="text-sm text-gray-600">
                Trang <span class="font-bold"><?= $page ?></span> trên <span class="font-bold"><?= $totalPages ?></span>
            </div>
            <div class="flex items-center gap-2">
                <a href="?page=ql_danhgia&p=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" 
                   class="px-3 py-1.5 text-sm font-semibold bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 <?= ($page <= 1) ? 'pointer-events-none opacity-50' : '' ?>">
                    <i class="fa-solid fa-chevron-left mr-1"></i> Trước
                </a>
                <a href="?page=ql_danhgia&p=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" 
                   class="px-3 py-1.5 text-sm font-semibold bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 <?= ($page >= $totalPages) ? 'pointer-events-none opacity-50' : '' ?>">
                    Sau <i class="fa-solid fa-chevron-right ml-1"></i>
                </a>
            </div>
        </nav>
    <?php endif; ?>

</body>
</html>