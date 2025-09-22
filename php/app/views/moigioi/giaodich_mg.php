<div class="w-full px-2 sm:px-4">
    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-blue-600 text-center sm:text-left">
        Hợp đồng / Giao dịch
    </h2>

    <div class="overflow-x-auto rounded-lg shadow bg-white w-full">
        <table class="w-full table-auto text-sm sm:text-base border-collapse">
            <thead class="bg-blue-100 text-left text-gray-700">
                <tr>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Mã hợp đồng</th>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Khách hàng</th>
                    <th class="py-2 px-2 sm:px-4 font-semibold">BĐS</th>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Giá trị</th>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Ngày ký</th>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $giaodich = [
                    ['id'=>'HD001','customer'=>'Nguyễn Văn A','property'=>'Căn hộ quận 1','value'=>'3 tỷ','date'=>'2025-08-20','status'=>'Đã hoàn thành'],
                    ['id'=>'HD002','customer'=>'Trần Thị B','property'=>'Nhà phố Bình Thạnh','value'=>'5.5 tỷ','date'=>'2025-08-22','status'=>'Đang xử lý'],
                    ['id'=>'HD003','customer'=>'Lê Văn C','property'=>'Đất nền Thủ Đức','value'=>'1.2 tỷ','date'=>'2025-08-25','status'=>'Đã hủy'],
                ];

                foreach($giaodich as $gd){
                    $statusColor = match($gd['status']){
                        'Đã hoàn thành'=>'text-green-600 font-semibold',
                        'Đang xử lý'=>'text-yellow-600 font-semibold',
                        'Đã hủy'=>'text-red-600 font-semibold',
                        default=>'text-gray-600'
                    };
                    echo "<tr class='border-t hover:bg-gray-50'>
                        <td class='py-2 px-2 sm:px-4 break-words'>{$gd['id']}</td>
                        <td class='py-2 px-2 sm:px-4 break-words'>{$gd['customer']}</td>
                        <td class='py-2 px-2 sm:px-4 break-words'>{$gd['property']}</td>
                        <td class='py-2 px-2 sm:px-4 break-words'>{$gd['value']}</td>
                        <td class='py-2 px-2 sm:px-4 break-words'>{$gd['date']}</td>
                        <td class='py-2 px-2 sm:px-4 {$statusColor} break-words'>{$gd['status']}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
