<?php
// Bắt đầu phiên
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// --- KHAI BÁO BIẾN ---
$giao_dich_hoan_tat = [];
$error_msg = null;
$stats = ['total_transactions' => 0, 'total_revenue' => 0];

// --- HELPER FUNCTION: Định dạng giá tiền ---
function format_price_vietnamese(float $price): string {
    if ($price >= 1000000000) {
        return rtrim(rtrim(number_format($price / 1000000000, 2, ',', ''), '0'), ',') . ' tỷ';
    } elseif ($price >= 1000000) {
        return number_format($price / 1000000, 0, ',', '.') . ' triệu';
    }
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// --- LẤY DỮ LIỆU ---
try {
    // 1. Lấy dữ liệu thống kê tổng quan (không bị ảnh hưởng bởi bộ lọc)
    $stat_sql = "SELECT COUNT(gd.id) AS total_transactions, SUM(bds.gia) AS total_revenue
                 FROM giao_dich gd
                 JOIN bat_dong_san bds ON gd.id_bds = bds.id
                 WHERE gd.trang_thai = 'hoantat'";
    $stat_stmt = $pdo->query($stat_sql);
    $stats = $stat_stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Xử lý bộ lọc
    $search_term = trim($_GET['search'] ?? '');
    $filter_date = trim($_GET['date'] ?? '');
    
    // =============================================
    // === CHÈN LOGIC LƯU LỊCH SỬ TÌM KIẾM TẠI ĐÂY ===
    // =============================================
    $id = $_SESSION['id_nguoi_dung'] ?? null; // Lấy ID người dùng từ session

    if (!empty(trim($search_term)) && !empty($id)) {
        try {
            $sql_insert = "INSERT INTO lich_su_tim_kiem (id_nguoi_dung, tu_khoa_tim_kiem, thoi_gian_tim) VALUES (?, ?, NOW())";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$id, $search_term]);
        } catch (PDOException $e) {
            // Có thể ghi log lỗi vào file
            // error_log("Lỗi khi lưu lịch sử tìm kiếm: " . $e->getMessage());
        }
    }
    // =============================================
    // === KẾT THÚC CHÈN ===
    // =============================================

    $where_conditions = ["gd.trang_thai = 'hoantat'"];
    $params = [];

    if (!empty($search_term)) {
        $where_conditions[] = "(bds.tieu_de ILIKE :search OR info_mua.ho_ten ILIKE :search OR info_ban.ho_ten ILIKE :search)";
        $params[':search'] = "%{$search_term}%";
    }

    if (!empty($filter_date)) {
        $where_conditions[] = "gd.ngay_giao_dich::date = :filter_date";
        $params[':filter_date'] = $filter_date;
    }

    // 3. Câu truy vấn chính
    $sql = "
        SELECT 
            gd.id AS id_giao_dich, gd.ngay_giao_dich,
            bds.id AS id_bds, bds.tieu_de AS bds_tieu_de, bds.gia AS bds_gia,
            info_mua.ho_ten AS nguoi_mua_ten, nd_mua.email AS nguoi_mua_email,
            info_ban.ho_ten AS nguoi_ban_ten, nd_ban.email AS nguoi_ban_email
        FROM giao_dich gd
        JOIN nguoi_dung nd_mua ON gd.id_nguoi_dung = nd_mua.id
        LEFT JOIN info_nguoi_dung info_mua ON nd_mua.id = info_mua.id_nguoi_dung
        JOIN nguoi_dung nd_ban ON gd.id_nguoi_ban = nd_ban.id
        LEFT JOIN info_nguoi_dung info_ban ON nd_ban.id = info_ban.id_nguoi_dung
        JOIN bat_dong_san bds ON gd.id_bds = bds.id
    ";

    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(' AND ', $where_conditions);
    }

    $sql .= " ORDER BY gd.ngay_giao_dich DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $giao_dich_hoan_tat = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_msg = "Lỗi truy vấn: " . $e->getMessage();
}

function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khách hàng đã mua</title>
</head>

<body class="bg-gray-50">

<div class="max-w-7xl mx-auto p-2 sm:p-2 lg:p-2">
    <header class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Khách hàng đã mua</h1>
        <p class="text-slate-500 mt-1 text-sm">Tổng quan các giao dịch đã hoàn tất trên hệ thống.</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 flex items-center gap-4">
            <div class="bg-sky-100 text-sky-600 rounded-full h-12 w-12 flex items-center justify-center">
                <i class="fa-solid fa-receipt fa-xl"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Tổng giao dịch</p>
                <p class="text-xl font-bold text-slate-900"><?= number_format($stats['total_transactions']) ?></p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center gap-4">
            <div class="bg-emerald-100 text-emerald-600 rounded-full h-10 w-10 flex items-center justify-center">
                <i class="fa-solid fa-sack-dollar fa-xl"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Tổng doanh thu</p>
                <p class="text-xl font-bold text-slate-900"><?= format_price_vietnamese((float)$stats['total_revenue']) ?></p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 flex items-center gap-4">
            <div class="bg-amber-100 text-amber-600 rounded-full h-12 w-12 flex items-center justify-center">
                <i class="fa-solid fa-chart-pie fa-xl"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Trung bình/Giao dịch</p>
                <p class="text-xl font-bold text-slate-900">
                    <?= $stats['total_transactions'] > 0 ? format_price_vietnamese((float)$stats['total_revenue'] / $stats['total_transactions']) : 'N/A' ?>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-200">
            <form id="search-form" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:flex-grow">
                    <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="search-input" name="search" value="<?= e($search_term) ?>" placeholder="Tìm BĐS, người mua, người bán..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                </div>
                <input type="date" name="date" value="<?= e($filter_date) ?>" class="w-full sm:w-auto border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                <button type="submit" id="search-button" class="w-full sm:w-auto bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-sky-700 transition shadow-sm">Lọc</button>
            </form>
        </div>

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
                const newUrl = `trangchu.php?page=../moi_gioi/kh_damua&search=${encodedSearchValue}`;

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
        
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="py-2 px-4 text-left font-semibold text-slate-600 uppercase">Bất động sản</th>
                        <th scope="col" class="py-2 px-4 text-left font-semibold text-slate-600 uppercase">Người Mua</th>
                        <th scope="col" class="py-2 px-4 text-left font-semibold text-slate-600 uppercase">Người Bán</th>
                        <th scope="col" class="py-2 px-4 text-right font-semibold text-slate-600 uppercase">Giá trị</th>
                        <th scope="col" class="py-2 px-4 text-center font-semibold text-slate-600 uppercase">Ngày hoàn tất</th>
                        <th scope="col" class="py-2 px-4 text-center font-semibold text-slate-600 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (!empty($giao_dich_hoan_tat)): ?>
                        <?php foreach($giao_dich_hoan_tat as $gd): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-2 px-3 align-top">
                                    <p class="font-bold text-slate-800"><?= e($gd['bds_tieu_de']) ?></p>
                                    <p class="text-xs text-slate-500">ID: <?= e($gd['id_giao_dich']) ?></p>
                                </td>
                                <td class="py-2 px-3 align-top">
                                    <p class="font-semibold text-slate-800"><?= e($gd['nguoi_mua_ten']) ?></p>
                                    <p class="text-xs text-slate-500"><?= e($gd['nguoi_mua_email']) ?></p>
                                </td>
                                <td class="py-2 px-3 align-top">
                                    <p class="font-semibold text-slate-800"><?= e($gd['nguoi_ban_ten']) ?></p>
                                    <p class="text-xs text-slate-500"><?= e($gd['nguoi_ban_email']) ?></p>
                                </td>
                                <td class="py-2 px-3 text-right font-bold text-emerald-600 align-top">
                                    <?= format_price_vietnamese((float)$gd['bds_gia']) ?>
                                </td>
                                <td class="py-2 px-3 text-center text-slate-600 align-top">
                                    <?= date("d/m/Y", strtotime($gd['ngay_giao_dich'])) ?>
                                </td>
                                <td class="py-2 px-3 text-center align-top">
                                    <a href="trangchu.php?page=../moi_gioi/hoa_don&id=<?= e($gd['id_giao_dich']) ?>" class="text-sky-600 hover:text-sky-800 font-semibold">Xem</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-500">
                                <i class="fa-solid fa-folder-open fa-2x text-slate-300"></i>
                                <p class="font-semibold mt-3 text-lg">Không tìm thấy giao dịch</p>
                                <p class="text-xs mt-1">Không có dữ liệu phù hợp với bộ lọc của bạn.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-3 border-t border-slate-200 flex justify-between items-center text-xs">
            <p class="text-slate-600">Hiển thị <span class="font-bold"><?= count($giao_dich_hoan_tat) ?></span> kết quả</p>
        </div>
    </div>
</div>

</body>
</html>