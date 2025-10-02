<?php
$yeucau = [
    [
        "id" => 1,
        "khachhang" => "Nguyễn Văn A",
        "loai" => "mua",
        "batdongsan" => "Căn hộ Q1",
        "trangthai" => "choxuly",
        "ngay" => "2025-10-01"
    ],
    [
        "id" => 2,
        "khachhang" => "Trần Thị B",
        "loai" => "ban",
        "batdongsan" => "Nhà phố Gò Vấp",
        "trangthai" => "daxuly",
        "ngay" => "2025-09-28"
    ],
    [
        "id" => 3,
        "khachhang" => "Lê Văn C",
        "loai" => "thue",
        "batdongsan" => "Đất Bình Chánh",
        "trangthai" => "huy",
        "ngay" => "2025-09-30"
    ]
];
?>

<div x-data="{ open: false, current: null }" class="p-6">
    <h1 class="flex text-2xl items-center font-bold mb-4 text-gray-600">
        <img src="../../../public/assets/anhht/0/rfp.gif" style="width: 50px; height: 50px; margin-right: 10px;">
        Quản lý yêu cầu
    </h1>

    <!-- Bảng yêu cầu -->
    <table class="min-w-full bg-white shadow-lg rounded-lg overflow-hidden">
        <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="py-3 px-4 text-left">#</th>
                <th class="py-3 px-4 text-left">Khách hàng</th>
                <th class="py-3 px-4 text-left">Loại</th>
                <th class="py-3 px-4 text-left">BĐS</th>
                <th class="py-3 px-4 text-left">Trạng thái</th>
                <th class="py-3 px-4 text-left">Ngày</th>
                <th class="py-3 px-4 text-center">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($yeucau as $row): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4"><?= $row["id"] ?></td>
                <td class="py-3 px-4"><?= $row["khachhang"] ?></td>
                <td class="py-3 px-4 capitalize">
                    <?php if($row["loai"] == "mua"): ?>
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm">Mua</span>
                    <?php elseif($row["loai"] == "ban"): ?>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-sm">Bán</span>
                    <?php else: ?>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-sm">Thuê</span>
                    <?php endif; ?>
                </td>
                <td class="py-3 px-4"><?= $row["batdongsan"] ?></td>
                <td class="py-3 px-4">
                    <?php if($row["trangthai"] == "choxuly"): ?>
                        <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-sm">Chờ xử lý</span>
                    <?php elseif($row["trangthai"] == "daxuly"): ?>
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm">Đã xử lý</span>
                    <?php else: ?>
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-sm">Hủy</span>
                    <?php endif; ?>
                </td>
                <td class="py-3 px-4"><?= $row["ngay"] ?></td>
                <td class="py-3 px-4 text-center">
                    <button 
                        @click="open = true; current = <?= $row['id'] ?>" 
                        class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white rounded text-sm">
                        Phân công
                    </button>
                    <button class="px-3 py-1 bg-gray-500 hover:bg-gray-600 text-white rounded text-sm">Xem</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal phân công -->
    <div x-show="open" 
         x-transition 
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div @click.away="open = false"
             class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-lg font-bold mb-4 text-gray-700">
                Phân công yêu cầu #<span x-text="current"></span>
            </h2>
            
            <label class="block mb-2 text-sm font-medium text-gray-600">Chọn môi giới</label>
            <select class="w-full border rounded px-3 py-2 mb-4">
                <option value="">-- Chọn môi giới --</option>
                <option value="1">Nguyễn Văn Môi Giới</option>
                <option value="2">Trần Thị Tư Vấn</option>
            </select>

            <div class="flex justify-end gap-2">
                <button @click="open = false"
                        class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded">
                    Hủy
                </button>
                <button @click="open = false"
                        class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded">
                    Lưu
                </button>
            </div>
        </div>
    </div>
</div>
