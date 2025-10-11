<?php
// Tên file: ql_danhgia.php

require_once "../../../config/database.php";

try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 border border-red-200 rounded-lg'>Lỗi kết nối CSDL: " . $e->getMessage() . "</div>");
}

$sql = "
    SELECT 
        bds.id, bds.loai, bds.tieu_de AS ten_san_pham, bds.trang_thai,
        COUNT(dg.id) AS tong_so_danh_gia,
        ROUND(AVG(dg.diem), 2) AS diem_trung_binh,
        COUNT(dg.id) FILTER (WHERE dg.binh_luan IS NOT NULL AND dg.binh_luan <> '') AS so_binh_luan,
        COUNT(dg.id) FILTER (WHERE dg.trang_thai = 'an') AS can_kiem_duyet
    FROM bat_dong_san bds
    LEFT JOIN danh_gia_bds dg ON bds.id = dg.id_bds
    GROUP BY bds.id, bds.loai, bds.tieu_de, bds.trang_thai
    ORDER BY can_kiem_duyet DESC, tong_so_danh_gia DESC;
";

try {
    $stmt = $pdo->query($sql);
    $danhgiasanpham = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 border border-red-200 rounded-lg'>Lỗi truy vấn: " . $e->getMessage() . "</div>");
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

// Hàm helper để hiển thị badge trạng thái kiểm duyệt
function getStatusBadge($count) {
    if ($count > 0) {
        return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800'>{$count} cần duyệt</span>";
    }
    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'>Hợp lệ</span>";
}
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá BĐS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="h-full">
    
<header class="mb-6 border-b pb-4">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tổng quan Đánh giá</h1>
            <p class="text-sm mt-2 text-gray-500">
                Xem và quản lý tất cả đánh giá cho các bất động sản.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16">
                <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-search text-gray-400"></i>
                </div>
                <input type="text" placeholder="Tìm kiếm BĐS..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </div>
    </div>
</header>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Sản phẩm</th>
                    <th scope="col" class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Phân loại</th>
                    <th scope="col" class="py-3 px-4 text-center text-sm font-semibold text-slate-500 uppercase">Điểm TB</th>
                    <th scope="col" class="py-3 px-4 text-center text-sm font-semibold text-slate-500 uppercase">Bình luận</th>
                    <th scope="col" class="px-6 py-3py-3 px-4 text-center text-sm font-semibold text-slate-500 uppercase">Trạng thái duyệt</th>
                    <th scope="col" class="relative py-3 px-4"><span class="sr-only">Hành động</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($danhgiasanpham)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <i class="fa-solid fa-folder-open fa-2x mb-2"></i>
                            <p>Không có dữ liệu để hiển thị.</p>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach($danhgiasanpham as $sp): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-sm text-gray-900 truncate max-w-sm" title="<?= htmlspecialchars($sp['ten_san_pham']) ?>">
                                <?= htmlspecialchars($sp['ten_san_pham']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 capitalize">
                            <?= htmlspecialchars($sp['loai']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php $score = (float)$sp['diem_trung_binh']; ?>
                            <?php if ($score > 0): ?>
                                <div class="flex items-center justify-center gap-1">
                                    <?= renderStars($score) ?>
                                    <span class="text-xs font-semibold text-slate-500">(<?= $score ?>)</span>
                                </div>
                            <?php else: ?>
                                <span class="text-xs text-slate-400">Chưa có</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-slate-700">
                            <?= number_format($sp['so_binh_luan']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?= getStatusBadge((int)$sp['can_kiem_duyet']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="trangchu.php?page=ql_danhgia_ct&id=<?= $sp['id'] ?>" class="text-indigo-600 hover:text-indigo-800 transition-colors">
                                Xem chi tiết
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>