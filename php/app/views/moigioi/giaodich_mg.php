<div>
    <h2 class="text-3xl font-bold mb-6 text-blue-600">Hợp đồng / Giao dịch</h2>

    <div class="overflow-x-auto rounded shadow bg-white">
        <table class="min-w-full table-auto">
            <thead class="bg-blue-100 text-left text-gray-700">
                <tr>
                    <th class="py-3 px-6 font-semibold">Mã hợp đồng</th>
                    <th class="py-3 px-6 font-semibold">Khách hàng</th>
                    <th class="py-3 px-6 font-semibold">BĐS</th>
                    <th class="py-3 px-6 font-semibold">Giá trị</th>
                    <th class="py-3 px-6 font-semibold">Ngày ký</th>
                    <th class="py-3 px-6 font-semibold">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $giaodich = [
                    ['id' => 'HD001', 'customer' => 'Nguyễn Văn A', 'property' => 'Căn hộ quận 1', 'value' => '3 tỷ', 'date' => '2025-08-20', 'status' => 'Đã hoàn thành'],
                    ['id' => 'HD002', 'customer' => 'Trần Thị B', 'property' => 'Nhà phố Bình Thạnh', 'value' => '5.5 tỷ', 'date' => '2025-08-22', 'status' => 'Đang xử lý'],
                    ['id' => 'HD003', 'customer' => 'Lê Văn C', 'property' => 'Đất nền Thủ Đức', 'value' => '1.2 tỷ', 'date' => '2025-08-25', 'status' => 'Đã hủy'],
                ];

                foreach ($giaodich as $gd) {
                    echo "<tr class='border-t hover:bg-gray-50'>
                            <td class='py-3 px-6'>{$gd['id']}</td>
                            <td class='py-3 px-6'>{$gd['customer']}</td>
                            <td class='py-3 px-6'>{$gd['property']}</td>
                            <td class='py-3 px-6'>{$gd['value']}</td>
                            <td class='py-3 px-6'>{$gd['date']}</td>
                            <td class='py-3 px-6'>{$gd['status']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
