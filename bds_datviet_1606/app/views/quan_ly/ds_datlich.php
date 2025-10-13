<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
// =================================================================================
// PHẦN 1: LOGIC & XỬ LÝ DỮ LIỆU
// =================================================================================

require_once "../../../config/database.php";

// 1. Lấy ngày được chọn từ URL, nếu không có thì lấy ngày hiện tại.
$selectedDate = $_GET['search'] ?? date('Y-m-d');

// 2. Hàm lấy và nhóm lịch hẹn theo môi giới cho một ngày cụ thể.
function getBrokerScheduleSummary(string $date): array {
    $pdo = ketnoicsdl();
    $sql = "
        SELECT
            lt.id_moi_gioi,
            info_mg.ho_ten AS ten_moi_gioi,
            nd_mg.avt AS avt_moi_gioi,
            COUNT(lt.id) AS so_luong_lich_hen
        FROM lich_trinh lt
        JOIN nguoi_dung nd_mg ON lt.id_moi_gioi = nd_mg.id
        LEFT JOIN info_nguoi_dung info_mg ON nd_mg.id = info_mg.id_nguoi_dung
        WHERE DATE(lt.thoi_gian_bat_dau) = ?
        GROUP BY lt.id_moi_gioi, ten_moi_gioi, avt_moi_gioi
        ORDER BY ten_moi_gioi ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 3. Thực thi lấy dữ liệu
$brokerSummary = getBrokerScheduleSummary($selectedDate);
$formattedDate = (new DateTime($selectedDate))->format('d/m/Y');

?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <title>Tổng quan Lịch trình Môi giới</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full p-4 md:p-6 lg:p-8">

<div class="max-w-screen-xl mx-auto">
    <header class="pb-5 border-b border-slate-200 mb-6">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Tổng quan Lịch trình Môi giới</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Báo cáo số lượng lịch hẹn của từng môi giới theo ngày.
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <form id="date-filter-form" method="GET" action="" class="flex items-center gap-2">
                    <label for="selected_date" class="text-sm font-medium text-slate-700">Chọn ngày:</label>
                    <input type="date" name="selected_date" id="selected_date"
                        value="<?= htmlspecialchars($selectedDate) ?>"
                        class="border-slate-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </form>
            </div>
        </div>
    </header>

    <script>
        // Đảm bảo mã chỉ chạy sau khi toàn bộ trang đã được tải
        document.addEventListener('DOMContentLoaded', function() {

            // 1. Lấy form và thẻ input ngày bằng ID của chúng
            const dateForm = document.getElementById('date-filter-form');
            const dateInput = document.getElementById('selected_date');

            dateInput.addEventListener('change', function() {
                const searchValue = dateInput.value;
                const encodedSearchValue = encodeURIComponent(searchValue.trim());
                const newUrl = `trangchu.php?page=ds_datlich&search=${encodedSearchValue}`;
                window.location.href = newUrl;
            });
        });
    </script>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-700">
                Lịch trình ngày: <span class="text-indigo-600"><?= $formattedDate ?></span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Môi giới</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Số lịch hẹn</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Ngày</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    <?php if (empty($brokerSummary)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">
                                <i class="fa-solid fa-calendar-xmark fa-2x mb-2"></i>
                                <p>Không có lịch hẹn nào trong ngày đã chọn.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($brokerSummary as $summary): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover" src="../../../storage/pictures/avt/<?= htmlspecialchars($summary['avt_moi_gioi']) ?>" alt="Avatar">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-slate-900"><?= htmlspecialchars($summary['ten_moi_gioi']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= $summary['so_luong_lich_hen'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                    <?= $formattedDate ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end items-center gap-2">
                                        <a href="trangchu.php?page=timeline-view&date=<?= $selectedDate ?>&broker_id=<?= $summary['id_moi_gioi'] ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-100 hover:bg-indigo-200 px-3 py-1 rounded-md transition-colors" title="Xem chi tiết lịch ngày">
                                            <i class="fa-solid fa-calendar-day mr-1"></i> Chi tiết ngày
                                        </a>
                                        <a href="trangchu.php?page=broker-history&date=<?= $selectedDate ?>&broker_id=<?= $summary['id_moi_gioi'] ?>" class="text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1 rounded-md transition-colors" title="Xem tất cả lịch trình">
                                            <i class="fa-solid fa-history mr-1"></i> Tất cả
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>