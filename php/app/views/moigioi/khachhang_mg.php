<div class="w-full px-2 sm:px-4">
    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-blue-600 text-center sm:text-left">
        Khách hàng quan tâm
    </h2>

    <div class="overflow-x-auto rounded-lg shadow bg-white w-full">
        <table class="w-full table-auto text-xs sm:text-sm md:text-base border-collapse">
            <thead class="bg-blue-100 text-left text-gray-700">
                <tr>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Tên khách hàng</th>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Số điện thoại</th>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Email</th>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Quan tâm đến</th>
                    <th class="py-2 px-2 sm:px-4 font-semibold">Ngày quan tâm</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $khach_hang = [
                    ['name' => 'Nguyễn Văn A', 'phone' => '0901234567', 'email' => 'a@example.com', 'interest' => 'Căn hộ quận 1', 'date' => '2025-09-10'],
                    ['name' => 'Trần Thị B', 'phone' => '0912345678', 'email' => 'b@example.com', 'interest' => 'Nhà phố Bình Thạnh', 'date' => '2025-09-12'],
                    ['name' => 'Lê Văn C', 'phone' => '0923456789', 'email' => 'c@example.com', 'interest' => 'Đất nền Thủ Đức', 'date' => '2025-09-15'],
                ];

                foreach ($khach_hang as $kh) {
                    echo "<tr class='border-t hover:bg-gray-50'>
                            <td class='py-2 px-2 sm:px-4 break-words'>{$kh['name']}</td>
                            <td class='py-2 px-2 sm:px-4 break-words'>{$kh['phone']}</td>
                            <td class='py-2 px-2 sm:px-4 break-words'>{$kh['email']}</td>
                            <td class='py-2 px-2 sm:px-4 break-words'>{$kh['interest']}</td>
                            <td class='py-2 px-2 sm:px-4 break-words'>{$kh['date']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
