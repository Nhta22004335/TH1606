<?php
// (PHẦN XỬ LÝ DỮ LIỆU CỦA BẠN ĐƯỢC GIỮ NGUYÊN)
if (session_status() == PHP_SESSION_NONE) { session_start(); }
$id_moigioi = $_SESSION['id_nguoi_dung'] ?? 'user_123';
$schedules_data = [
    ['id' => 1, 'loai' => 'Tham quan BDS', 'tieu_de' => 'Gặp khách hàng A xem căn hộ chung cư cao cấp', 'thoi_gian' => '2025-10-10 10:00:00', 'dia_diem' => 'Chung cư The Gold View', 'trang_thai' => 'sap_toi', 'ghi_chu' => 'Chuẩn bị hồ sơ pháp lý và báo giá cuối cùng.'],
    ['id' => 2, 'loai' => 'Họp nội bộ', 'tieu_de' => 'Báo cáo doanh số tuần và kế hoạch tháng mới', 'thoi_gian' => '2025-10-09 14:30:00', 'dia_diem' => 'Văn phòng chi nhánh Q1', 'trang_thai' => 'hoan_thanh', 'ghi_chu' => 'Đã gửi file báo cáo qua email.'],
    ['id' => 3, 'loai' => 'Tham quan BDS', 'tieu_de' => 'Xem đất nền với khách hàng B', 'thoi_gian' => '2025-10-11 08:30:00', 'dia_diem' => 'Khu đô thị mới Cát Lái', 'trang_thai' => 'sap_toi', 'ghi_chu' => 'Khách hàng quan tâm lô J3.'],
    ['id' => 4, 'loai' => 'Ký hợp đồng', 'tieu_de' => 'Ký hợp đồng mua bán nhà phố liền kề', 'thoi_gian' => '2025-10-12 16:00:00', 'dia_diem' => 'Phòng công chứng số 7', 'trang_thai' => 'sap_toi', 'ghi_chu' => 'Cần mang theo 2 bản gốc hợp đồng.'],
    ['id' => 5, 'loai' => 'Tham quan BDS', 'tieu_de' => 'Tham quan biệt thự biển', 'thoi_gian' => '2025-10-07 09:00:00', 'dia_diem' => 'Phú Quốc', 'trang_thai' => 'da_huy', 'ghi_chu' => 'Khách hàng hoãn lịch đột xuất.'],
    ['id' => 6, 'loai' => 'Họp nội bộ', 'tieu_de' => 'Team building tháng 10', 'thoi_gian' => '2025-10-10 14:00:00', 'dia_diem' => 'Nhà hàng The Deck Saigon', 'trang_thai' => 'sap_toi', 'ghi_chu' => 'Team gathering.'],
];
$search = $_GET['search'] ?? '';
$filter_status = $_GET['trang_thai'] ?? 'tat_ca';
$filtered_schedules = $schedules_data;
if (!empty($search)) {
    $search_lower = strtolower($search);
    $filtered_schedules = array_filter($filtered_schedules, fn($s) => str_contains(strtolower($s['tieu_de']), $search_lower) || str_contains(strtolower($s['dia_diem']), $search_lower) || str_contains(strtolower($s['ghi_chu']), $search_lower));
}
if ($filter_status !== 'tat_ca') {
    $filtered_schedules = array_filter($filtered_schedules, fn($s) => $s['trang_thai'] === $filter_status);
}
usort($filtered_schedules, fn($a, $b) => strtotime($a['thoi_gian']) <=> strtotime($b['thoi_gian']));

// ====[ LOGIC MỚI: Nhóm lịch trình theo ngày ]====
$schedules_by_day = [];
foreach ($filtered_schedules as $schedule) {
    $day = date('Y-m-d', strtotime($schedule['thoi_gian']));
    if (!isset($schedules_by_day[$day])) {
        $schedules_by_day[$day] = [];
    }
    $schedules_by_day[$day][] = $schedule;
}
// ====[ KẾT THÚC LOGIC MỚI ]====

function get_status_badge($status) { /* giữ nguyên */ 
    $map = ['sap_toi' => ['bg-blue-100', 'text-blue-800', 'Sắp tới'], 'hoan_thanh' => ['bg-green-100', 'text-green-800', 'Hoàn thành'], 'da_huy' => ['bg-red-100', 'text-red-800', 'Đã hủy']];
    [$bg, $text, $label] = $map[$status] ?? ['bg-gray-100', 'text-gray-800', 'Không rõ'];
    return "<span class='inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full $bg $text'>$label</span>";
}
function get_type_color($type) {
    switch ($type) {
        case 'Tham quan BDS': return 'border-blue-500';
        case 'Họp nội bộ': return 'border-green-500';
        case 'Ký hợp đồng': return 'border-purple-500';
        default: return 'border-gray-400';
    }
}
function format_day_of_week($date_string) {
    $days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    return $days[date('w', strtotime($date_string))];
}
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Trình Cá Nhân</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="h-full">

    <header class="mb-6 pb-4 border-b">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Lịch trình của tôi</h1>
                <p class="mt-2 text-sm text-slate-600">Tổng quan các công việc và cuộc hẹn sắp tới.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="them_lichtrinh.php" class="inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fa-solid fa-plus"></i> Thêm lịch trình mới
                </a>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <form method="GET" class="md:col-span-3">
            <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? 'lichtrinh') ?>">
            <input type="hidden" name="trang_thai" value="<?= htmlspecialchars($filter_status) ?>">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" placeholder="Tìm kiếm lịch trình..." value="<?= htmlspecialchars($search) ?>"
                       class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                       onchange="this.form.submit()">
            </div>
        </form>
        <form method="GET" class="md:col-span-2">
            <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? 'lichtrinh') ?>">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <select name="trang_thai" class="w-full border py-2 border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" onchange="this.form.submit()">
                <option value="tat_ca" <?= ($filter_status == 'tat_ca') ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="sap_toi" <?= ($filter_status == 'sap_toi') ? 'selected' : '' ?>>Sắp tới</option>
                <option value="hoan_thanh" <?= ($filter_status == 'hoan_thanh') ? 'selected' : '' ?>>Hoàn thành</option>
                <option value="da_huy" <?= ($filter_status == 'da_huy') ? 'selected' : '' ?>>Đã hủy</option>
            </select>
        </form>
    </div>

    <div class="space-y-8">
        <?php if (empty($schedules_by_day)): ?>
            <div class="text-center py-16 text-slate-500 bg-white rounded-lg shadow">
                <i class="fa-solid fa-calendar-xmark fa-3x mb-4"></i>
                <p class="text-lg font-semibold">Không có lịch trình nào</p>
                <p>Không tìm thấy lịch trình nào phù hợp với bộ lọc của bạn.</p>
            </div>
        <?php else: ?>
            <?php foreach ($schedules_by_day as $day => $schedules_on_this_day): ?>
                <section>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-indigo-600"><?= date('d', strtotime($day)) ?></p>
                            <p class="text-xs font-semibold text-slate-500 uppercase">Thg <?= date('m', strtotime($day)) ?></p>
                        </div>
                        <div class="border-l pl-3">
                            <h2 class="font-bold text-lg text-slate-800"><?= format_day_of_week($day) ?></h2>
                            <p class="text-sm text-slate-500"><?= count($schedules_on_this_day) ?> lịch trình</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($schedules_on_this_day as $schedule): ?>
                            <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-xl transition-shadow border-l-4 <?= get_type_color($schedule['loai']) ?>">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="text-center w-16 flex-shrink-0">
                                            <p class="font-bold text-indigo-700"><?= date('H:i', strtotime($schedule['thoi_gian'])) ?></p>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-semibold text-slate-900"><?= htmlspecialchars($schedule['tieu_de']) ?></p>
                                            <p class="text-sm text-slate-500 flex items-center gap-2 mt-1">
                                                <i class="fa-solid fa-location-dot fa-fw"></i>
                                                <span><?= htmlspecialchars($schedule['dia_diem']) ?></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 mt-3 sm:mt-0 flex-shrink-0">
                                        <?= get_status_badge($schedule['trang_thai']) ?>
                                        <div class="flex items-center gap-2 text-slate-400">
                                            <button class="view-details-btn hover:text-blue-600" title="Xem chi tiết" 
                                                    data-schedule='<?= htmlspecialchars(json_encode($schedule)) ?>'>
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <a href="sua_lichtrinh.php?id=<?= $schedule['id'] ?>" class="hover:text-indigo-600" title="Chỉnh sửa"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <button class="hover:text-red-600" title="Xóa"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<div id="details-modal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 transition-opacity duration-300 opacity-0">
    <div id="modal-content" class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all scale-95">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 id="modal-tieu_de" class="text-xl font-bold text-slate-900"></h2>
                    <div id="modal-status" class="mt-2"></div>
                </div>
                <button class="close-modal-btn text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
            </div>
            <dl class="mt-5 space-y-4 text-sm">
                <div><dt class="font-medium text-slate-500">Thời gian</dt><dd id="modal-thoi_gian" class="mt-1 text-slate-800 font-semibold text-base"></dd></div>
                <div><dt class="font-medium text-slate-500">Loại lịch trình</dt><dd id="modal-loai" class="mt-1 text-slate-800"></dd></div>
                <div><dt class="font-medium text-slate-500">Địa điểm</dt><dd id="modal-dia_diem" class="mt-1 text-slate-800"></dd></div>
                <div><dt class="font-medium text-slate-500">Ghi chú</dt><dd id="modal-ghi_chu" class="mt-1 text-slate-600 italic p-3 bg-slate-50 rounded-md"></dd></div>
            </dl>
        </div>
        <div class="bg-slate-50 px-6 py-4 rounded-b-xl flex justify-end gap-3">
            <button type="button" class="close-modal-btn px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">Đóng</button>
            <a id="modal-edit-link" href="#" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Chỉnh sửa</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Lấy các phần tử DOM ---
    const modal = document.getElementById('details-modal');
    const modalContent = document.getElementById('modal-content');
    const viewButtons = document.querySelectorAll('.view-details-btn');
    const closeButtons = document.querySelectorAll('.close-modal-btn');
    
    // --- Các phần tử để hiển thị dữ liệu trong modal ---
    const modalTitle = document.getElementById('modal-tieu_de');
    const modalStatus = document.getElementById('modal-status');
    const modalTime = document.getElementById('modal-thoi_gian');
    const modalType = document.getElementById('modal-loai');
    const modalLocation = document.getElementById('modal-dia_diem');
    const modalNotes = document.getElementById('modal-ghi_chu');
    const modalEditLink = document.getElementById('modal-edit-link');

    // Hàm helper để tạo status badge, giống hàm PHP
    function getStatusBadgeHTML(status) {
        const map = {
            'sap_toi': { class: 'bg-blue-100 text-blue-800', label: 'Sắp tới' },
            'hoan_thanh': { class: 'bg-green-100 text-green-800', label: 'Hoàn thành' },
            'da_huy': { class: 'bg-red-100 text-red-800', label: 'Đã hủy' }
        };
        const details = map[status] || { class: 'bg-gray-100 text-gray-800', label: 'Không rõ' };
        return `<span class='inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full ${details.class}'>${details.label}</span>`;
    }

    // --- Hàm mở và điền dữ liệu vào modal ---
    function openModal(schedule) {
        // Điền dữ liệu
        modalTitle.textContent = schedule.tieu_de;
        modalStatus.innerHTML = getStatusBadgeHTML(schedule.trang_thai);
        modalTime.textContent = new Date(schedule.thoi_gian).toLocaleString('vi-VN', { dateStyle: 'full', timeStyle: 'short' });
        modalType.textContent = schedule.loai;
        modalLocation.textContent = schedule.dia_diem;
        modalNotes.textContent = schedule.ghi_chu || 'Không có ghi chú.';
        modalEditLink.href = `sua_lichtrinh.php?id=${schedule.id}`;

        // Hiển thị modal với hiệu ứng
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 10); // Một khoảng trễ nhỏ để transition hoạt động
    }

    // --- Hàm đóng modal ---
    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); // Đợi transition hoàn thành rồi mới ẩn hẳn
    }

    // --- Gán sự kiện cho các nút ---

    // Gán sự kiện click cho tất cả các nút "Xem chi tiết"
    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            const scheduleData = JSON.parse(button.dataset.schedule);
            openModal(scheduleData);
        });
    });

    // Gán sự kiện click cho tất cả các nút đóng modal
    closeButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });

    // Gán sự kiện click vào nền mờ để đóng modal
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Gán sự kiện nhấn phím Esc để đóng modal
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
});
</script>

</body>
</html>