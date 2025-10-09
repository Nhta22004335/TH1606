<?php
// PHP MOCK DATA: Giả lập dữ liệu lịch trình/cuộc hẹn
// (PHẦN XỬ LÝ DỮ LIỆU CỦA BẠN ĐƯỢC GIỮ NGUYÊN)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$id_moigioi = $_SESSION['id_nguoi_dung'] ?? 'user_123'; 

$schedules_data = [
    [
        'id' => 1,
        'loai' => 'Tham quan BDS',
        'tieu_de' => 'Gặp khách hàng A xem căn hộ chung cư cao cấp',
        'thoi_gian' => '2025-10-10 10:00:00',
        'dia_diem' => 'Chung cư The Gold View',
        'trang_thai' => 'sap_toi', 
        'ghi_chu' => 'Chuẩn bị hồ sơ pháp lý và báo giá cuối cùng.'
    ],
    [
        'id' => 2,
        'loai' => 'Họp nội bộ',
        'tieu_de' => 'Báo cáo doanh số tuần và kế hoạch tháng mới',
        'thoi_gian' => '2025-10-09 14:30:00',
        'dia_diem' => 'Văn phòng chi nhánh Q1',
        'trang_thai' => 'hoan_thanh', 
        'ghi_chu' => 'Đã gửi file báo cáo qua email.'
    ],
    [
        'id' => 3,
        'loai' => 'Tham quan BDS',
        'tieu_de' => 'Xem đất nền với khách hàng B',
        'thoi_gian' => '2025-10-11 08:30:00',
        'dia_diem' => 'Khu đô thị mới Cát Lái',
        'trang_thai' => 'sap_toi',
        'ghi_chu' => 'Khách hàng quan tâm lô J3.'
    ],
    [
        'id' => 4,
        'loai' => 'Ký hợp đồng',
        'tieu_de' => 'Ký hợp đồng mua bán nhà phố liền kề',
        'thoi_gian' => '2025-10-12 16:00:00',
        'dia_diem' => 'Phòng công chứng số 7',
        'trang_thai' => 'sap_toi',
        'ghi_chu' => 'Cần mang theo 2 bản gốc hợp đồng.'
    ],
    [
        'id' => 5,
        'loai' => 'Tham quan BDS',
        'tieu_de' => 'Tham quan biệt thự biển',
        'thoi_gian' => '2025-10-07 09:00:00',
        'dia_diem' => 'Phú Quốc',
        'trang_thai' => 'da_huy', 
        'ghi_chu' => 'Khách hàng hoãn lịch đột xuất.'
    ]
];

// Lấy tham số tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$filter_status = $_GET['trang_thai'] ?? 'tat_ca';

$filtered_schedules = $schedules_data;

// Áp dụng tìm kiếm
if (!empty($search)) {
    $search_lower = strtolower($search);
    $filtered_schedules = array_filter($filtered_schedules, function($schedule) use ($search_lower) {
        return (
            str_contains(strtolower($schedule['tieu_de']), $search_lower) ||
            str_contains(strtolower($schedule['dia_diem']), $search_lower) ||
            str_contains(strtolower($schedule['ghi_chu']), $search_lower)
        );
    });
}

// Áp dụng lọc trạng thái
if ($filter_status !== 'tat_ca') {
    $filtered_schedules = array_filter($filtered_schedules, function($schedule) use ($filter_status) {
        return $schedule['trang_thai'] === $filter_status;
    });
}


// Sắp xếp theo thời gian (sắp tới lên trước)
usort($filtered_schedules, function($a, $b) {
    return strtotime($a['thoi_gian']) <=> strtotime($b['thoi_gian']);
});


// Các hàm chuyển đổi (Giữ nguyên)
function get_status_badge($status) {
    $classes = [
        'sap_toi' => 'bg-blue-100 text-blue-800',
        'hoan_thanh' => 'bg-green-100 text-green-800',
        'da_huy' => 'bg-red-100 text-red-800',
    ];
    $labels = [
        'sap_toi' => 'Sắp tới',
        'hoan_thanh' => 'Hoàn thành',
        'da_huy' => 'Đã hủy',
    ];
    $class = $classes[$status] ?? 'bg-gray-100 text-gray-800';
    $label = $labels[$status] ?? 'Không rõ';
    return "<span class='inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full $class'>$label</span>";
}

function format_datetime($datetime) {
    return date("H:i | d/m/Y", strtotime($datetime));
}

?>
<!DOCTYPE html>
<html lang="vi" x-data="{ openModal: false, selectedSchedule: null }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Trình Cá Nhân</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Đảm bảo bảng luôn có chiều rộng tối thiểu, nhưng vẫn căn chỉnh tốt */
        .schedule-table {
            min-width: 900px;
        }
        /* Căn chỉnh lại độ rộng các cột cụ thể */
        .col-time { width: 15%; }
        .col-type { width: 12%; }
        .col-title { width: 35%; } /* Tăng độ rộng cho Tiêu đề */
        .col-location { width: 20%; }
        .col-status { width: 10%; }
        .col-actions { width: 8%; } /* Giảm độ rộng cho Hành động */
    </style>
</head>
<body class="bg-gray-50">

<div class="max-w-7xl mx-auto p-6">
    <h1 class="flex items-center text-3xl font-bold text-gray-700 mb-6 border-b pb-3 text-indigo-700">
        <i class="fas fa-calendar-alt mr-3"></i> Quản Lý Lịch Trình Cá Nhân
    </h1>
    
    <div class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-lg mb-6 space-y-4 md:space-y-0 md:space-x-4">
        
        <form method="GET" class="w-full md:w-1/3">
            <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? 'lichtrinh') ?>"> 
            <div class="relative">
                <input type="text" name="search" placeholder="Tìm kiếm theo tiêu đề, địa điểm..." value="<?= htmlspecialchars($search) ?>"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       onchange="this.form.submit()">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </form>

        <form method="GET" class="w-full md:w-1/4">
             <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? 'lichtrinh') ?>">
             <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <select name="trang_thai" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                    onchange="this.form.submit()">
                <option value="tat_ca" <?= ($filter_status == 'tat_ca') ? 'selected' : '' ?>>-- Tất cả trạng thái --</option>
                <option value="sap_toi" <?= ($filter_status == 'sap_toi') ? 'selected' : '' ?>>Sắp tới</option>
                <option value="hoan_thanh" <?= ($filter_status == 'hoan_thanh') ? 'selected' : '' ?>>Hoàn thành</option>
                <option value="da_huy" <?= ($filter_status == 'da_huy') ? 'selected' : '' ?>>Đã hủy</option>
            </select>
        </form>

        <a href="them_lichtrinh.php" class="w-full md:w-auto flex items-center justify-center bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-indigo-700 transition font-medium">
            <i class="fas fa-plus mr-1"></i> Thêm Mới
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-x-auto border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 schedule-table">
            <thead class="bg-indigo-50">
                <tr>
                    <th class="col-time px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Thời gian</th>
                    <th class="col-type px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Loại</th>
                    <th class="col-title px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tiêu đề</th>
                    <th class="col-location px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Địa điểm</th>
                    <th class="col-status px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Trạng thái</th>
                    <th class="col-actions px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php if (empty($filtered_schedules)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-lg italic">
                            <i class="fas fa-box-open mr-2"></i> Không có lịch trình nào được tìm thấy.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filtered_schedules as $schedule): ?>
                    <tr class="hover:bg-indigo-50 transition duration-150">
                        <td class="col-time px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-700">
                            <?= format_datetime($schedule['thoi_gian']) ?>
                        </td>
                        <td class="col-type px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?= htmlspecialchars($schedule['loai']) ?>
                        </td>
                        <td class="col-title px-6 py-4 text-sm text-gray-800 font-medium truncate" title="<?= htmlspecialchars($schedule['tieu_de']) ?>">
                            <?= htmlspecialchars($schedule['tieu_de']) ?>
                        </td>
                        <td class="col-location px-6 py-4 text-sm text-gray-500 truncate">
                            <?= htmlspecialchars($schedule['dia_diem']) ?>
                        </td>
                        <td class="col-status px-6 py-4 whitespace-nowrap text-center">
                            <?= get_status_badge($schedule['trang_thai']) ?>
                        </td>
                        <td class="col-actions px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex items-center justify-center space-x-2">
                                <button @click="selectedSchedule = <?= htmlspecialchars(json_encode($schedule), ENT_QUOTES, 'UTF-8') ?>; openModal = true"
                                        title="Chi tiết"
                                        class="text-blue-600 hover:text-blue-900 p-1 transition duration-150">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="sua_lichtrinh.php?id=<?= $schedule['id'] ?>" title="Sửa"
                                   class="text-indigo-600 hover:text-indigo-900 p-1 transition duration-150">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="if(confirm('Bạn có chắc muốn xóa lịch trình này? Hành động này không thể hoàn tác.')) { /* Thêm logic xóa bằng AJAX/PHP */ }"
                                        title="Xóa"
                                        class="text-red-600 hover:text-red-900 p-1 transition duration-150">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-6 flex justify-between items-center text-sm text-gray-600">
        <p>Hiển thị <?= count($filtered_schedules) ?> lịch trình (trên tổng số <?= count($schedules_data) ?>)</p>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
            <a href="#" aria-current="page" class="z-10 bg-indigo-100 border-indigo-500 text-indigo-700 relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</a>
            <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">2</a>
            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <i class="fas fa-chevron-right"></i>
            </a>
        </nav>
    </div>

</div>

<div x-show="openModal" class="fixed z-10 inset-0 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="openModal = false">
            <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
             role="dialog" aria-modal="true" aria-labelledby="modal-headline"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                        <i class="fas fa-calendar-check text-indigo-600 text-xl"></i>
                    </div>
                    <div class="mt-0 ml-4 w-full">
                        <h3 class="text-xl leading-6 font-bold text-gray-900 border-b pb-2" id="modal-headline">
                            Chi Tiết Lịch Trình
                        </h3>
                        <div class="mt-4 text-sm text-gray-700 space-y-3">
                            <p class="font-bold text-lg text-indigo-700" x-text="selectedSchedule ? selectedSchedule.tieu_de : ''"></p>
                            
                            <div class="grid grid-cols-2 gap-y-1">
                                <p><span class="font-semibold text-gray-600">Thời gian:</span></p>
                                <p class="font-medium text-right" x-text="selectedSchedule ? format_datetime(selectedSchedule.thoi_gian) : ''"></p>
                                
                                <p><span class="font-semibold text-gray-600">Loại:</span></p>
                                <p class="text-right" x-text="selectedSchedule ? selectedSchedule.loai : ''"></p>
                                
                                <p><span class="font-semibold text-gray-600">Trạng thái:</span></p>
                                <p class="text-right" x-html="selectedSchedule ? get_status_badge(selectedSchedule.trang_thai) : ''"></p>
                            </div>

                            <div class="pt-3 border-t">
                                <p><span class="font-semibold text-gray-600">Địa điểm:</span> <span x-text="selectedSchedule ? selectedSchedule.dia_diem : ''"></span></p>
                            </div>
                            
                            <div class="p-3 bg-gray-100 rounded-lg border border-gray-200">
                                <p class="font-semibold text-gray-600 mb-1">Ghi chú:</p>
                                <p class="text-gray-800 italic" x-text="selectedSchedule ? selectedSchedule.ghi_chu : ''"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" @click="openModal = false"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Hàm format_datetime (được sử dụng lại trong Alpine.js)
    function format_datetime(datetime) {
        if (!datetime) return '';
        const date = new Date(datetime);
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();
        return `${hours}:${minutes} | ${day}/${month}/${year}`;
    }

    // Hàm get_status_badge (được sử dụng lại trong Alpine.js)
    function get_status_badge(status) {
        const classes = {
            'sap_toi': 'bg-blue-100 text-blue-800',
            'hoan_thanh': 'bg-green-100 text-green-800',
            'da_huy': 'bg-red-100 text-red-800',
        };
        const labels = {
            'sap_toi': 'Sắp tới',
            'hoan_thanh': 'Hoàn thành',
            'da_huy': 'Đã hủy',
        };
        const classStr = classes[status] || 'bg-gray-100 text-gray-800';
        const label = labels[status] || 'Không rõ';
        // Thêm inline-flex và items-center để badge căn giữa tốt hơn trong Modal
        return `<span class='inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full ${classStr}'>${label}</span>`;
    }
    
    // Gán lại hàm vào Alpine.data để Modal sử dụng
    Alpine.data('scheduleManager', () => ({
        format_datetime: format_datetime,
        get_status_badge: get_status_badge
    }));

</script>

</body>
</html>