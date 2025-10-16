<?php
// BƯỚC 1: KẾT NỐI CSDL VÀ KHỞI TẠO SESSION
// =================================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Giả sử bạn có một file kết nối CSDL trả về đối tượng $pdo
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// Lấy ID người môi giới đang đăng nhập từ session
// Thay 'user_UUID_placeholder' bằng một UUID hợp lệ để test nếu session chưa có
$id_moigioi = $_SESSION['id_nguoi_dung'] ?? 'user_UUID_placeholder'; 

// BƯỚC 2: XỬ LÝ ĐẦU VÀO (FILTER VÀ SEARCH)
// =================================================
$search = $_GET['search'] ?? '';
$filter_status = $_GET['trang_thai'] ?? 'tat_ca';

// BƯỚC 3: XÂY DỰNG VÀ THỰC THI TRUY VẤN SQL
// =================================================
$params = [':id_moi_gioi' => $id_moigioi];
$sql = "
    SELECT 
        lt.id, 
        lt.tieu_de, 
        lt.dia_diem, 
        lt.thoi_gian_bat_dau, 
        lt.thoi_gian_ket_thuc, 
        lt.trang_thai, 
        lt.ghi_chu,
        kh.ho_ten AS ten_khach_hang
    FROM 
        lich_trinh lt
    JOIN 
        info_nguoi_dung kh ON lt.id_khach_hang = kh.id_nguoi_dung
    WHERE 
        lt.id_moi_gioi = :id_moi_gioi
";

if (!empty($search)) {
    $sql .= " AND (lt.tieu_de ILIKE :search OR lt.dia_diem ILIKE :search OR lt.ghi_chu ILIKE :search OR kh.ho_ten ILIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$sql .= " ORDER BY lt.thoi_gian_bat_dau ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $all_schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// BƯỚC 4: XỬ LÝ DỮ LIỆU SAU KHI TRUY VẤN
// =================================================

$processed_schedules = [];
foreach ($all_schedules as $schedule) {
    $now = new DateTime();
    $start_time = new DateTime($schedule['thoi_gian_bat_dau']);
    $end_time = new DateTime($schedule['thoi_gian_ket_thuc']);
    
    // Tạo ra "trạng thái hiển thị" (display_status) cho giao diện
    $display_status = 'khong_ro';
    if ($schedule['trang_thai'] === 'dahuy') {
        $display_status = 'da_huy';
    } elseif ($schedule['trang_thai'] === 'choxacnhan') {
        $display_status = 'cho_xac_nhan'; // Trạng thái mới
    } elseif ($schedule['trang_thai'] === 'daxacnhan') {
        if ($now > $end_time) {
            $display_status = 'hoan_thanh';
        } else {
            $display_status = 'sap_toi';
        }
    }

    // Tạo một mảng mới với cấu trúc mà giao diện cũ đang dùng
    $processed_schedules[] = [
        'id' => $schedule['id'],
        'tieu_de' => htmlspecialchars($schedule['tieu_de']),
        'dia_diem' => htmlspecialchars($schedule['dia_diem'] ?? 'Chưa cập nhật'),
        'thoi_gian' => $schedule['thoi_gian_bat_dau'], // Dùng thời gian bắt đầu để nhóm và sắp xếp
        'thoi_gian_ket_thuc' => $schedule['thoi_gian_ket_thuc'],
        'trang_thai' => $display_status, // Sử dụng trạng thái đã xử lý
        'ghi_chu' => htmlspecialchars($schedule['ghi_chu'] ?? ''),
        'ten_khach_hang' => htmlspecialchars($schedule['ten_khach_hang']),
        'loai' => 'Lịch hẹn khách hàng' 
    ];
}

// Áp dụng bộ lọc trạng thái trên dữ liệu đã được xử lý
$filtered_schedules = $processed_schedules;
if ($filter_status !== 'tat_ca') {
    $filtered_schedules = array_filter($processed_schedules, fn($s) => $s['trang_thai'] === $filter_status);
}

// Nhóm lịch trình theo ngày
$schedules_by_day = [];
foreach ($filtered_schedules as $schedule) {
    $day = date('Y-m-d', strtotime($schedule['thoi_gian']));
    if (!isset($schedules_by_day[$day])) {
        $schedules_by_day[$day] = [];
    }
    $schedules_by_day[$day][] = $schedule;
}

// BƯỚC 5: CẬP NHẬT CÁC HÀM HELPER
// =================================================
function get_status_badge($status) {
    $map = [
        'sap_toi'       => ['bg-blue-100', 'text-blue-800', 'Sắp tới'],
        'hoan_thanh'    => ['bg-green-100', 'text-green-800', 'Hoàn thành'],
        'da_huy'        => ['bg-red-100', 'text-red-800', 'Đã hủy'],
        'cho_xac_nhan'  => ['bg-yellow-100', 'text-yellow-800', 'Chờ xác nhận']
    ];
    [$bg, $text, $label] = $map[$status] ?? ['bg-gray-100', 'text-gray-800', 'Không rõ'];
    return "<span class='inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full $bg $text'>$label</span>";
}

// Vì không có cột 'loai', ta có thể thay đổi màu viền theo trạng thái
function get_type_color_by_status($status) {
    switch ($status) {
        case 'sap_toi': return 'border-blue-500';
        case 'hoan_thanh': return 'border-green-500';
        case 'da_huy': return 'border-red-500';
        case 'cho_xac_nhan': return 'border-yellow-500';
        default: return 'border-gray-400';
    }
}

function format_day_of_week($date_string) {
    $days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    return $days[date('w', strtotime($date_string))];
}

// Lưu lịch sử tìm kiếm (giữ nguyên)
if (!empty(trim($search))) {
    try {
        $sql_history = "INSERT INTO lich_su_tim_kiem (id_nguoi_dung, tu_khoa_tim_kiem) VALUES (?, ?)";
        $stmt_history = $pdo->prepare($sql_history);
        $stmt_history->execute([$id_moigioi, $search]);
    } catch (PDOException $e) {
        // error_log("Lỗi khi lưu lịch sử tìm kiếm: " . $e->getMessage());
    }
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
<body class="h-full p-8">

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
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" placeholder="Tìm theo tiêu đề, địa điểm, ghi chú, tên khách hàng..." value="<?= htmlspecialchars($search) ?>"
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
                <option value="cho_xac_nhan" <?= ($filter_status == 'cho_xac_nhan') ? 'selected' : '' ?>>Chờ xác nhận</option>
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
                            <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-xl transition-shadow border-l-4 <?= get_type_color_by_status($schedule['trang_thai']) ?>">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="text-center w-24 flex-shrink-0">
                                            <p class="font-bold text-indigo-700">
                                                <?= date('H:i', strtotime($schedule['thoi_gian'])) ?>
                                                -
                                                <?= date('H:i', strtotime($schedule['thoi_gian_ket_thuc'])) ?>
                                            </p>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-semibold text-slate-900"><?= $schedule['tieu_de'] ?></p>
                                            <p class="text-sm text-slate-500 flex items-center gap-2 mt-1">
                                                <i class="fa-solid fa-location-dot fa-fw"></i>
                                                <span><?= $schedule['dia_diem'] ?></span>
                                            </p>
                                             <p class="text-sm text-slate-500 flex items-center gap-2 mt-1">
                                                <i class="fa-solid fa-user fa-fw"></i>
                                                <span><?= $schedule['ten_khach_hang'] ?></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 mt-3 sm:mt-0 flex-shrink-0">
                                        <?= get_status_badge($schedule['trang_thai']) ?>
                                        <div class="flex items-center gap-2 text-slate-400">
                                            <button class="view-details-btn hover:text-blue-600" title="Xem chi tiết" 
                                                    data-schedule='<?= htmlspecialchars(json_encode($schedule), ENT_QUOTES, 'UTF-8') ?>'>
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <a href="trangchu.php?page=../moi_gioi/sua_lichtrinh_mg&id=<?= $schedule['id'] ?>" class="hover:text-indigo-600" title="Chỉnh sửa"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <button onclick="confirmDelete('<?= $schedule['id'] ?>')" class="hover:text-red-600" title="Xóa"><i class="fa-solid fa-trash-can"></i></button>
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
                    <div><dt class="font-medium text-slate-500">Khách hàng</dt><dd id="modal-khach_hang" class="mt-1 text-slate-800"></dd></div>
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
        const modal = document.getElementById('details-modal');
        const modalContent = document.getElementById('modal-content');
        const viewButtons = document.querySelectorAll('.view-details-btn');
        const closeButtons = document.querySelectorAll('.close-modal-btn');
        
        // --- Các phần tử để hiển thị dữ liệu trong modal ---
        const modalTitle = document.getElementById('modal-tieu_de');
        const modalStatus = document.getElementById('modal-status');
        const modalTime = document.getElementById('modal-thoi_gian');
        const modalCustomer = document.getElementById('modal-khach_hang');
        const modalLocation = document.getElementById('modal-dia_diem');
        const modalNotes = document.getElementById('modal-ghi_chu');
        const modalEditLink = document.getElementById('modal-edit-link');

        // Hàm helper để tạo status badge, giống hàm PHP
        function getStatusBadgeHTML(status) {
            const map = {
                'sap_toi': { class: 'bg-blue-100 text-blue-800', label: 'Sắp tới' },
                'hoan_thanh': { class: 'bg-green-100 text-green-800', label: 'Hoàn thành' },
                'da_huy': { class: 'bg-red-100 text-red-800', label: 'Đã hủy' },
                'cho_xac_nhan': { class: 'bg-yellow-100 text-yellow-800', label: 'Chờ xác nhận' }
            };
            const details = map[status] || { class: 'bg-gray-100 text-gray-800', label: 'Không rõ' };
            return `<span class='inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full ${details.class}'>${details.label}</span>`;
        }
        
        function formatDateTimeRange(start, end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = startDate.toLocaleDateString('vi-VN', options);
            const startTimeString = startDate.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            const endTimeString = endDate.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

            return `${startTimeString} - ${endTimeString}, ${dateString}`;
        }

        // --- Hàm mở và điền dữ liệu vào modal ---
        function openModal(schedule) {
            modalTitle.textContent = schedule.tieu_de;
            modalStatus.innerHTML = getStatusBadgeHTML(schedule.trang_thai);
            modalTime.textContent = formatDateTimeRange(schedule.thoi_gian, schedule.thoi_gian_ket_thuc);
            modalCustomer.textContent = schedule.ten_khach_hang;
            modalLocation.textContent = schedule.dia_diem;
            modalNotes.textContent = schedule.ghi_chu || 'Không có ghi chú.';
            modalEditLink.href = `../moi_gioi/trangchu.php?page=../moi_gioi/sua_lichtrinh_mg&id=${schedule.id}`;

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
            }, 10);
        }

        // --- Hàm đóng modal ---
        function closeModal() {
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        viewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const scheduleData = JSON.parse(button.dataset.schedule);
                openModal(scheduleData);
            });
        });

        closeButtons.forEach(button => button.addEventListener('click', closeModal));
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
    });

    // Hàm xác nhận xóa
    function confirmDelete(id) {
        if (confirm('Bạn có chắc chắn muốn xóa lịch trình này không?')) {
            // Thay đổi action của form hoặc chuyển hướng để xử lý xóa
            window.location.href = 'xoa_lichtrinh.php?id=' + id;
        }
    }
    </script>
</body>
</html>