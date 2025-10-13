<?php
    // ===== PHẦN LOGIC PHP - ĐƯỢC TỐI ƯU HÓA =====
    require_once "../../../config/database.php";

    // Các hàm helper để code sạch sẽ
    function formatPrice($price) {
        if ($price >= 1000000000) {
            return round($price / 1000000000, 2) . ' tỷ';
        } elseif ($price >= 1000000) {
            return round($price / 1000000, 2) . ' triệu';
        }
        return number_format($price) . ' đ';
    }

    function getStatusInfo($status) {
        $map = [
            'chuaduyet' => ['text' => 'Chờ duyệt', 'classes' => 'bg-yellow-100 text-yellow-800'],
            'daduyet'   => ['text' => 'Đã duyệt', 'classes' => 'bg-green-100 text-green-800'],
        ];
        return $map[$status] ?? ['text' => 'Không rõ', 'classes' => 'bg-gray-100 text-gray-800'];
    }

    $pdo = ketnoicsdl();
    $search = $_GET['search'] ?? '';

    // ===== TỐI ƯU: Chỉ dùng MỘT câu lệnh SQL duy nhất =====
    $sql = "
        SELECT b.id, b.tieu_de, b.mo_ta, b.gia, b.dien_tich, b.dia_chi, b.loai, 
            b.khu_vuc, b.ngay_dang, b.trang_thai
        FROM bat_dong_san b
    ";

    $params = [];

    // ===== Thêm điều kiện tìm kiếm nếu có =====
    if (!empty($search)) {
        $sql .= " WHERE b.tieu_de ILIKE :search OR b.mo_ta ILIKE :search OR b.dia_chi ILIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    // ===== Thứ tự sắp xếp để DISTINCT ON hoạt động đúng =====
    $sql .= " ORDER BY b.id, b.ngay_dang DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bất động sản</title>
</head>
<body>

<div class="container">

    <header class="mb-6 border-b pb-4">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý Bất động sản</h1>
        <p class="text-sm mt-2 text-gray-500">Xem, tìm kiếm và quản lý các tin đăng bất động sản.</p>
    </header>

    
    <form id="search-form" method="GET" class="flex items-center mb-6">
        <div class="relative w-72">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fas fa-search text-gray-400"></i></div>
            <input type="search" id="search-input" name="search" class="bg-white outline-none border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2" 
                placeholder="Tìm theo tiêu đề, mô tả, địa chỉ..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" id="search-button" class="ml-2 px-4 py-2 text-sm font-medium text-white bg-gray-400 rounded-lg hover:bg-gray-500">Tìm</button>
    </form>

    <script>
        // 1. Lấy các phần tử HTML cần thiết qua ID
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const searchButton = document.getElementById('search-button');

        // 2. Hàm để thực hiện submit
        function submitSearch() {
            console.log('Đang chuẩn bị chuyển hướng bằng window.location...');

            // 1. Lấy giá trị từ ô input
            const searchValue = searchInput.value;

            // 2. (Quan trọng) Mã hóa giá trị để đảm bảo URL hợp lệ
            //    Ví dụ: "áo thun" -> "ao%20thun"
            const encodedSearchValue = encodeURIComponent(searchValue.trim());

            // 3. Xây dựng URL mới một cách thủ công
            //    Hãy chắc chắn rằng đường dẫn cơ sở '/app/trangchu.php' là đúng với cấu trúc dự án của bạn
            const newUrl = `trangchu.php?page=ql_bieumau&search=${encodedSearchValue}`;

            // 4. Dùng window.location.href để chuyển hướng trình duyệt đến URL mới
            window.location.href = newUrl;
        }

        // 3. Gán sự kiện cho nút bấm
        searchButton.addEventListener('click', function(event) {
            event.preventDefault(); // Ngăn hành vi mặc định của nút
            submitSearch();
        });

        // 4. Gán sự kiện cho ô input (submit khi nhấn Enter)
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Ngăn form bị gửi đi 2 lần
                submitSearch();
            }
        });
    </script>

    <main class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Tên Bất động sản</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Giá</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Diện tích</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Ngày đăng</th>
                        <th class="py-3 px-4 text-center text-xs font-bold text-gray-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-gray-500 text-lg">Không tìm thấy bất động sản nào.</td></tr>
                    <?php else: ?>
                        <?php foreach($products as $p): 
                            $status_info = getStatusInfo($p["trang_thai"]);
                        ?>
                            <tr class="hover:bg-slate-50 transition duration-150">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <p class="font-medium text-sm text-gray-900 line-clamp-1" title="<?= htmlspecialchars($p['tieu_de']) ?>"><?= htmlspecialchars($p['tieu_de']) ?></p>
                                            <p class="text-xs text-gray-500 line-clamp-1" title="<?= htmlspecialchars($p['dia_chi']) ?>"><?= htmlspecialchars($p['dia_chi']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 font-semibold text-red-600 text-sm"><?= formatPrice($p['gia']) ?></td>
                                <td class="p-4 text-gray-700 text-sm"><?= htmlspecialchars($p['dien_tich']) ?> m²</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full <?= $status_info['classes'] ?>">
                                        <?= $status_info['text'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-gray-500 text-sm"><?= date("d/m/Y", strtotime($p['ngay_dang'])) ?></td>
                                <td class="p-4">
                                    <div class="flex justify-center items-center">
                                        
                                        <a href="trangchu.php?page=ct_sanpham&id=<?= htmlspecialchars($p['id']) ?>" 
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium" 
                                        title="Xem chi tiết">
                                        Xem chi tiết
                                        </a>
                                        
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>