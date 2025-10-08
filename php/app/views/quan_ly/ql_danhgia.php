<?php
// Tên file: ql_danhgia.php

require_once "../../../config/database.php";
// Thêm khối try-catch để đảm bảo kết nối CSDL
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 border border-red-200 rounded-lg'>Lỗi kết nối CSDL: " . $e->getMessage() . "</div>");
}

$sql = "
    SELECT 
        bds.id,
        bds.loai,
        bds.tieu_de AS ten_san_pham,
        bds.trang_thai,
        
        -- Tính toán thống kê đánh giá
        COUNT(dg.id) AS tong_so_danh_gia,
        ROUND(AVG(dg.diem), 2) AS diem_trung_binh,
        COUNT(dg.id) FILTER (WHERE dg.binh_luan IS NOT NULL AND dg.binh_luan <> '') AS so_binh_luan,
        COUNT(dg.id) FILTER (WHERE dg.trang_thai = 'an') AS can_kiem_duyet
        
    FROM 
        bat_dong_san bds
    LEFT JOIN 
        danh_gia_bds dg ON bds.id = dg.id_bds
    GROUP BY 
        bds.id, bds.loai, bds.tieu_de, bds.trang_thai
    ORDER BY
        tong_so_danh_gia DESC;
";

try {
    $stmt = $pdo->query($sql);
    $danhgiasanpham = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 border border-red-200 rounded-lg'>Lỗi truy vấn: " . $e->getMessage() . "</div>");
}

// Hàm helper để định dạng trạng thái BĐS


// Hàm helper để hiển thị sao
function renderStars($score) {
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá BĐS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Đảm bảo bảng có thể cuộn */
        .sticky-header {
            position: sticky;
            top: 0;
            background-color: #f9fafb; /* gray-50 */
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-50">

<div class="p-6">
    <div class="bg-white rounded-xl shadow-2xl p-6 border border-gray-100">
        
        <h2 class="flex items-center text-3xl font-extrabold text-blue-700 mb-6 border-b pb-3">
            <i class="fa-solid fa-star-half-stroke text-2xl mr-3 text-yellow-500"></i>
            Quản lý Đánh giá Bất động sản
        </h2>

        <div class="mb-4 text-sm text-gray-600">
            Tổng cộng: **<?= count($danhgiasanpham) ?>** BĐS có đánh giá.
        </div>

        <div class="overflow-x-auto max-h-[70vh]">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="sticky-header">
                    <tr class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <th class="w-2/6 px-4 py-3 text-left font-bold">Tên sản phẩm</th>
                        <th class="w-1/12 px-4 py-3 text-left font-bold">Loại</th>
                       
                        <th class="w-1/12 px-4 py-3 text-center font-bold">Đ.Giá TB</th>
                        <th class="w-1/12 px-4 py-3 text-center font-bold">Bình luận</th>
                        <th class="w-1/12 px-4 py-3 text-center font-bold">Cần duyệt</th>
                        <th class="w-1/6 px-4 py-3 text-center font-bold">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($danhgiasanpham)): ?>
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-500 italic">Không có bất động sản nào có đánh giá.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach($danhgiasanpham as $sp): 
                       
                    ?>
                        <tr class="hover:bg-blue-50 transition duration-150">
                            <td class="px-4 py-3 font-medium text-gray-900 truncate max-w-xs"><?= htmlspecialchars($sp['ten_san_pham']) ?></td>
                            <td class="px-4 py-3 text-gray-600 capitalize"><?= htmlspecialchars($sp['loai']) ?></td>
                            
                            <td class="px-4 py-3 text-center text-sm font-semibold">
                                <?php 
                                    $score = (float)$sp['diem_trung_binh'];
                                    echo $score > 0 ? renderStars($score) . " (" . $score . ")" : '---';
                                ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold text-gray-700"><?= number_format($sp['so_binh_luan']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($sp['can_kiem_duyet'] > 0): ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-500 text-white shadow-md">
                                        <?= $sp['can_kiem_duyet'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-green-500">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="trangchu.php?page=ql_danhgia_ct&id=<?= $sp['id'] ?>" 
                                    class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium transition duration-150 flex items-center justify-center gap-1 mx-auto max-w-[120px]">
                                    <i class="fa-solid fa-eye"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>