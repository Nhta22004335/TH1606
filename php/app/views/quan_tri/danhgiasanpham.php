<?php
    // Dữ liệu demo
    $danhgiasanpham = [
        [
            'id' => 1,
            'ten_san_pham' => 'Căn hộ Sunshine',
            'loai' => 'Căn hộ',
            'trang_thai' => 'Đã bán',
            'so_luong_danh_gia' => 12,
            'so_luong_binh_luan' => 5
        ],
        [
            'id' => 2,
            'ten_san_pham' => 'Biệt thự Vinhomes',
            'loai' => 'Biệt thự',
            'trang_thai' => 'Đã bán',
            'so_luong_danh_gia' => 8,
            'so_luong_binh_luan' => 3
        ],
        [
            'id' => 3,
            'ten_san_pham' => 'Nhà phố Cityland',
            'loai' => 'Nhà phố',
            'trang_thai' => 'Đã bán',
            'so_luong_danh_gia' => 5,
            'so_luong_binh_luan' => 2
        ],
        [
            'id' => 4,
            'ten_san_pham' => 'Đất nền Phú Mỹ',
            'loai' => 'Đất nền',
            'trang_thai' => 'Đã bán',
            'so_luong_danh_gia' => 10,
            'so_luong_binh_luan' => 4
        ]
    ];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá BĐS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<div class="bg-white shadow rounded-lg p-6">
    <h2 class="flex items-center text-2xl font-bold text-gray-600">
        <img src="../../public/assets/anhht/0/list.gif" class="w-10 h-10 mr-3">
        Danh sách đánh giá
    </h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Tên sản phẩm</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Loại</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Trạng thái</th>
                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Số lượng đánh giá</th>
                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Số lượng bình luận</th>
                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($danhgiasanpham as $sp): ?>
                    <tr>
                        <td class="px-4 py-2"><?= $sp['ten_san_pham'] ?></td>
                        <td class="px-4 py-2"><?= $sp['loai'] ?></td>
                        <td class="px-4 py-2"><span class="text-white bg-gray-500 px-2 py-1 rounded text-sm">Đã bán</span></td>
                        <td class="px-4 py-2 text-center"><?= $sp['so_luong_danh_gia'] ?></td>
                        <td class="px-4 py-2 text-center"><?= $sp['so_luong_binh_luan'] ?></td>
                        <td class="px-4 py-2 text-center">
                            <a href="trangchu.php?page=danhgiasanphamct&id=<?= $sp['id'] ?>" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">
                                Xem chi tiết
                            </a>
                            <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm ml-2">Xóa</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
