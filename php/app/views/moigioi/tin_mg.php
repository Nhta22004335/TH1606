<div>
    <h2 class="text-3xl font-bold mb-6 text-blue-600">Tin đăng của tôi</h2>

    <!-- Bảng danh sách tin đăng -->
    <div class="overflow-x-auto rounded shadow bg-white">
        <table class="min-w-full table-auto">
            <thead class="bg-blue-100 text-left text-gray-700">
                <tr>
                    <th class="py-3 px-6 font-semibold">Ảnh</th>
                    <th class="py-3 px-6 font-semibold">Tiêu đề</th>
                    <th class="py-3 px-6 font-semibold">Loại BĐS</th>
                    <th class="py-3 px-6 font-semibold">Giá</th>
                    <th class="py-3 px-6 font-semibold">Trạng thái</th>
                    <th class="py-3 px-6 font-semibold">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Dữ liệu giả lập có thêm đường dẫn ảnh
                $tin_dang = [
                    [
                        'img' => 'https://via.placeholder.com/100x75?text=CanHo', 
                        'title' => 'Căn hộ cao cấp quận 1', 
                        'type' => 'Căn hộ', 
                        'price' => '3 tỷ', 
                        'status' => 'Đang hoạt động'
                    ],
                    [
                        'img' => 'https://via.placeholder.com/100x75?text=NhaPho', 
                        'title' => 'Nhà phố Bình Thạnh', 
                        'type' => 'Nhà phố', 
                        'price' => '5.5 tỷ', 
                        'status' => 'Đã bán'
                    ],
                    [
                        'img' => 'https://via.placeholder.com/100x75?text=DatNen', 
                        'title' => 'Đất nền Thủ Đức', 
                        'type' => 'Đất nền', 
                        'price' => '1.2 tỷ', 
                        'status' => 'Đang hoạt động'
                    ],
                ];

                foreach ($tin_dang as $tin) {
                    echo "<tr class='border-t hover:bg-gray-50'>
                            <td class='py-3 px-6'>
                                <img src='{$tin['img']}' alt='Ảnh tin đăng' class='w-24 h-16 object-cover rounded' />
                            </td>
                            <td class='py-3 px-6'>{$tin['title']}</td>
                            <td class='py-3 px-6'>{$tin['type']}</td>
                            <td class='py-3 px-6'>{$tin['price']}</td>
                            <td class='py-3 px-6'>{$tin['status']}</td>
                            <td class='py-3 px-6'>
                                <a href='#' class='text-blue-600 hover:underline mr-4'>Sửa</a>
                                <a href='#' class='text-red-600 hover:underline'>Xóa</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <button class="mt-6 bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition">
        Thêm tin mới
    </button>
</div>
