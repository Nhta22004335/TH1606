<?php
// =================================================================================
// PHẦN 1: LOGIC & XỬ LÝ DỮ LIỆU
// =================================================================================

// Bật hiển thị lỗi để dễ dàng gỡ lỗi
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cài đặt múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once "../../../config/database.php";

// 1. Lấy tham số từ URL
$selectedDate = $_GET['date'] ?? null;
$brokerId = $_GET['broker_id'] ?? null;

if (!$selectedDate || !$brokerId) {
    die("Lỗi: Thiếu thông tin ngày hoặc ID môi giới để hiển thị timeline.");
}

// CÁC HÀM PHỤ TRỢ
function findMinColumns(array $intervals): int {
    if (empty($intervals)) return 0;
    usort($intervals, fn($a, $b) => $a['start'] <=> $b['start']);
    $columnsEndTime = [];
    foreach ($intervals as $interval) {
        $placed = false;
        for ($i = 0; $i < count($columnsEndTime); $i++) {
            if ($interval['start'] >= $columnsEndTime[$i]) {
                $columnsEndTime[$i] = $interval['end'];
                $placed = true;
                break;
            }
        }
        if (!$placed) $columnsEndTime[] = $interval['end'];
    }
    return count($columnsEndTime);
}

function getOverlappingColumns(array $currentApt, array $allAppointments): int {
    $overlappingIntervals = [];
    foreach ($allAppointments as $otherApt) {
        if ($currentApt['startObj'] < $otherApt['endObj'] && $otherApt['startObj'] < $currentApt['endObj']) {
            $overlappingIntervals[] = ['start' => $otherApt['startObj']->getTimestamp(), 'end' => $otherApt['endObj']->getTimestamp()];
        }
    }
    return findMinColumns($overlappingIntervals);
}

// HÀM CHÍNH LẤY DỮ LIỆU
function getBrokerTimelineData(string $brokerId, string $date): ?array
{
    $pdo = ketnoicsdl();
    $result = ['broker' => null, 'appointments' => [], 'conflict_ids' => []];

    // Lấy thông tin môi giới
    $sqlBroker = "SELECT info_mg.ho_ten, nd_mg.avt FROM nguoi_dung nd_mg LEFT JOIN info_nguoi_dung info_mg ON nd_mg.id = info_mg.id_nguoi_dung WHERE nd_mg.id = ?";
    $stmtBroker = $pdo->prepare($sqlBroker);
    $stmtBroker->execute([$brokerId]);
    $brokerInfo = $stmtBroker->fetch(PDO::FETCH_ASSOC);
    if (!$brokerInfo) return null;
    $result['broker'] = $brokerInfo;

    // Lấy danh sách lịch hẹn
    $startOfDay = $date . ' 00:00:00';
    $endOfDay = $date . ' 23:59:59';
    $sqlAppointments = "SELECT lt.id, lt.thoi_gian_bat_dau, lt.thoi_gian_ket_thuc, lt.trang_thai, lt.ghi_chu, info_kh.ho_ten AS ten_khach_hang FROM lich_trinh lt LEFT JOIN nguoi_dung nd_kh ON lt.id_khach_hang = nd_kh.id LEFT JOIN info_nguoi_dung info_kh ON nd_kh.id = info_kh.id_nguoi_dung WHERE lt.id_moi_gioi = ? AND lt.thoi_gian_bat_dau BETWEEN ? AND ? ORDER BY lt.thoi_gian_bat_dau ASC, lt.thoi_gian_ket_thuc DESC";
    $stmtAppointments = $pdo->prepare($sqlAppointments);
    $stmtAppointments->execute([$brokerId, $startOfDay, $endOfDay]);
    $appointments = $stmtAppointments->fetchAll(PDO::FETCH_ASSOC);

    // Xử lý dữ liệu hiển thị
    $pixelsPerHour = 80; // Thay đổi: Giảm chiều cao mỗi giờ
    $timelineStartHour = 7;
    $processedAppointments = [];
    foreach ($appointments as $apt) {
        $startObj = new DateTime($apt['thoi_gian_bat_dau']);
        $endObj = new DateTime($apt['thoi_gian_ket_thuc']);
        $top = (($startObj->format('H') - $timelineStartHour) * $pixelsPerHour) + ($startObj->format('i') / 60 * $pixelsPerHour);
        $durationMinutes = ($endObj->getTimestamp() - $startObj->getTimestamp()) / 60;
        $height = $durationMinutes / 60 * $pixelsPerHour;
        $apt['startObj'] = $startObj;
        $apt['endObj'] = $endObj;
        $apt['view_props'] = ['top' => max(0, $top), 'height' => max(20, $height), 'left' => 0, 'width' => 100, 'column' => 0];
        $processedAppointments[] = $apt;
    }

    // Phân bổ cột cho các lịch hẹn chồng chéo
    $columns = [];
    foreach ($processedAppointments as &$currentApt) {
        $colIndex = 0;
        while (true) {
            $canPlace = true;
            if (isset($columns[$colIndex])) {
                foreach ($columns[$colIndex] as $existingApt) {
                    if ($currentApt['startObj'] < $existingApt['endObj'] && $existingApt['startObj'] < $currentApt['endObj']) {
                        $canPlace = false;
                        $result['conflict_ids'][] = $currentApt['id'];
                        $result['conflict_ids'][] = $existingApt['id'];
                        break;
                    }
                }
            }
            if ($canPlace) {
                $columns[$colIndex][] = $currentApt;
                $currentApt['view_props']['column'] = $colIndex;
                break;
            }
            $colIndex++;
        }
    }
    unset($currentApt);

    // Cập nhật left và width
    foreach ($processedAppointments as &$apt) {
        $totalColumnsInGroup = getOverlappingColumns($apt, $processedAppointments);
        $totalColumnsInGroup = max(1, $totalColumnsInGroup);
        $apt['view_props']['width'] = (100 / $totalColumnsInGroup) - 0.5; // Giảm khoảng cách
        $apt['view_props']['left'] = ($apt['view_props']['column'] * (100 / $totalColumnsInGroup));
    }
    unset($apt);

    $result['appointments'] = $processedAppointments;
    $result['conflict_ids'] = array_unique($result['conflict_ids']);
    return $result;
}

// --- Thực thi và chuẩn bị dữ liệu cho View ---
$timelineData = getBrokerTimelineData($brokerId, $selectedDate);
$formattedDate = (new DateTime($selectedDate))->format('d/m/Y');
$timelineStartHour = 7;
$timelineEndHour = 19;
$pixelsPerHour = 80; // Thay đổi: Cần khớp với giá trị trong hàm

// =================================================================================
// PHẦN 2: CÁC HÀM HỖ TRỢ VIEW
// =================================================================================

function getAppointmentClasses(array $appointment, array $conflict_ids): array {
    $isConflict = in_array($appointment['id'], $conflict_ids);
    if ($isConflict) {
        return ['bg' => 'bg-red-50', 'border' => 'border-red-400', 'text' => 'text-red-900', 'icon' => 'text-red-500'];
    }
    $statusMap = [
        'daxacnhan' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-400', 'text' => 'text-blue-900', 'icon' => 'text-blue-500'],
        'choxacnhan' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-400', 'text' => 'text-yellow-900', 'icon' => 'text-yellow-500'],
        'dahuy' => ['bg' => 'bg-slate-50', 'border' => 'border-slate-400', 'text' => 'text-slate-600', 'icon' => 'text-slate-400'],
    ];
    return $statusMap[$appointment['trang_thai']] ?? ['bg' => 'bg-gray-100', 'border' => 'border-gray-400', 'text' => 'text-gray-800', 'icon' => 'text-gray-400'];
}

function renderAppointmentCard(array $appointment, array $conflict_ids): void {
    $classes = getAppointmentClasses($appointment, $conflict_ids);
    $isConflict = in_array($appointment['id'], $conflict_ids);
    $jsonAppointment = htmlspecialchars(json_encode($appointment), ENT_QUOTES, 'UTF-8');
?>
    <div @click="isModalOpen = true; modalData = <?= $jsonAppointment ?>"
         class="absolute rounded-md cursor-pointer transition-all duration-200 ease-in-out group hover:!z-20 hover:scale-[1.01] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex overflow-hidden border-l-[3px] border <?= $classes['bg'] ?> <?= $classes['border'] ?> <?= $classes['text'] ?>"
         style="top: <?= $appointment['view_props']['top'] ?>px; height: <?= $appointment['view_props']['height'] ?>px; left: <?= $appointment['view_props']['left'] ?>%; width: <?= $appointment['view_props']['width'] ?>%;">
        <div class="p-1.5 flex-grow overflow-hidden">
            <p class="font-semibold text-sm truncate" title="<?= htmlspecialchars($appointment['ten_khach_hang']) ?>">
                <i class="fa-solid fa-user-tag text-xs mr-1 opacity-80 <?= $classes['icon'] ?>"></i><?= htmlspecialchars($appointment['ten_khach_hang'] ?: 'N/A') ?>
            </p>
            <p class="text-xs opacity-80 flex items-center mt-0.5">
                <i class="fa-solid fa-clock text-xs mr-1 opacity-80 <?= $classes['icon'] ?>"></i>
                <?= $appointment['startObj']->format('H:i') ?> - <?= $appointment['endObj']->format('H:i') ?>
                <?php if ($isConflict): ?>
                    <i class="fa-solid fa-triangle-exclamation text-red-500 ml-1.5 animate-pulse" title="Lịch hẹn này bị trùng"></i>
                <?php endif; ?>
            </p>
        </div>
    </div>
<?php
}
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <title>Timeline Chi Tiết Lịch Hẹn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        body::-webkit-scrollbar { width: 6px; }
        body::-webkit-scrollbar-track { background: #f1f1f1; }
        body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="h-full p-4 sm:p-5 font-sans antialiased" x-data="{ isModalOpen: false, modalData: {} }">

<div class="max-w-5xl mx-auto">
    <?php if (!$timelineData): ?>
        <div class="text-center p-12 bg-white rounded-xl shadow-lg border">
            <i class="fa-solid fa-circle-exclamation text-red-500 fa-3x mb-4"></i>
            <h1 class="text-xl font-bold text-slate-800">Không tìm thấy môi giới</h1>
            <p class="text-slate-600 mt-2">ID môi giới không hợp lệ hoặc không tồn tại.</p>
            <a href="trangchu.php?page=ds_datlich" class="mt-6 inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition-colors shadow">
                <i class="fa-solid fa-arrow-left"></i>Quay lại
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
            <header class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                         <img src="../../../storage/pictures/avt/<?= htmlspecialchars($timelineData['broker']['avt']) ?>" class="w-14 h-14 rounded-full object-cover ring-2 ring-offset-2 ring-indigo-300">
                         <div>
                             <h1 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($timelineData['broker']['ho_ten']) ?></h1>
                             <p class="text-sm text-slate-500">Lịch trình ngày: <strong class="text-indigo-600"><?= $formattedDate ?></strong></p>
                         </div>
                    </div>
                    <a href="trangchu.php?page=ds_datlich&search=<?= $selectedDate ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors bg-indigo-100 hover:bg-indigo-200 px-3 py-1.5 rounded-md">
                        <i class="fa-solid fa-chevron-left"></i> Quay lại
                    </a>
                </div>
            </header>

            <div class="p-4 sm:p-6" x-data="timeline(<?= $timelineStartHour ?>, <?= $timelineEndHour ?>, <?= $pixelsPerHour ?>)">
                <div class="flex">
                    <div class="w-14 text-right text-xs text-slate-400 font-semibold flex-shrink-0 pt-2 border-r border-slate-200 pr-2 mr-2">
                        <?php for ($hour = $timelineStartHour; $hour < $timelineEndHour; $hour++): ?>
                            <div class="h-[<?= $pixelsPerHour ?>px] relative -top-2">
                                <span><?= sprintf('%02d:00', $hour) ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="flex-1 relative">
                        <div class="absolute inset-0">
                            <?php for ($hour = $timelineStartHour; $hour < $timelineEndHour; $hour++): ?>
                                <div class="h-[<?= $pixelsPerHour ?>px] border-t border-slate-200/80 <?= $hour % 2 == 0 ? 'bg-slate-50/60' : '' ?>"></div>
                            <?php endfor; ?>
                        </div>
                        
                        <div x-show="isVisible" class="absolute left-0 w-full h-0.5 bg-red-500 z-20" :style="{ top: currentTimeTop + 'px' }">
                            <div class="absolute -left-1.5 -top-1.5 w-3 h-3 bg-red-500 rounded-full"></div>
                        </div>
                        
                        <div class="relative h-full min-h-[300px]">
                            <?php if (empty($timelineData['appointments'])): ?>
                                 <div class="absolute inset-0 flex items-center justify-center text-slate-500 text-center p-8">
                                     <div>
                                         <i class="fa-solid fa-calendar-alt fa-2x mb-3 text-slate-400"></i>
                                         <p class="font-medium">Không có lịch hẹn trong ngày này.</p>
                                     </div>
                                 </div>
                            <?php else: ?>
                                <?php foreach ($timelineData['appointments'] as $appointment): ?>
                                    <?php renderAppointmentCard($appointment, $timelineData['conflict_ids']); ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div x-show="isModalOpen" x-transition.opacity x-cloak class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div @click.away="isModalOpen = false" class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all border"
         x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="p-5">
            <div class="flex items-start justify-between mb-5">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2.5">
                    <i class="fa-solid fa-calendar-check text-indigo-500"></i> Chi tiết Lịch hẹn
                </h2>
                <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 text-2xl w-7 h-7 rounded-full hover:bg-slate-100 flex items-center justify-center transition-colors">&times;</button>
            </div>

            <div class="space-y-4 text-sm">
                <div class="flex items-start gap-3"><i class="fa-solid fa-user text-slate-400 w-4 text-center mt-1"></i>
                    <div>
                        <p class="font-medium text-slate-500 text-xs">Khách hàng</p>
                        <p class="mt-0.5 text-slate-800 font-semibold" x-text="modalData.ten_khach_hang || 'N/A'"></p>
                    </div>
                </div>
                <div class="flex items-start gap-3"><i class="fa-solid fa-clock text-slate-400 w-4 text-center mt-1"></i>
                    <div>
                        <p class="font-medium text-slate-500 text-xs">Thời gian</p>
                        <p class="mt-0.5 text-slate-800">
                            <span class="font-mono" x-text="new Date(modalData.thoi_gian_bat_dau).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})"></span> -
                            <span class="font-mono" x-text="new Date(modalData.thoi_gian_ket_thuc).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})"></span>,
                            <span class="font-medium" x-text="new Date(modalData.thoi_gian_bat_dau).toLocaleDateString('vi-VN')"></span>
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3"><i class="fa-solid fa-info-circle text-slate-400 w-4 text-center mt-1"></i>
                    <div>
                        <p class="font-medium text-slate-500 text-xs">Trạng thái</p>
                        <p class="mt-1"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize" :class="{
                            'bg-green-100 text-green-700': modalData.trang_thai === 'daxacnhan',
                            'bg-yellow-100 text-yellow-700': modalData.trang_thai === 'choxacnhan',
                            'bg-slate-100 text-slate-600': modalData.trang_thai === 'dahuy',
                        }" x-text="(modalData.trang_thai || '').replace('_', ' ')"></span></p>
                    </div>
                </div>
                <div class="flex items-start gap-3"><i class="fa-solid fa-sticky-note text-slate-400 w-4 text-center mt-1"></i>
                    <div>
                        <p class="font-medium text-slate-500 text-xs">Ghi chú</p>
                        <div class="mt-1 text-slate-700 p-2 bg-slate-50 rounded-md whitespace-pre-wrap border max-h-28 overflow-auto">
                            <p x-text="modalData.ghi_chu || 'Không có ghi chú.'"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('timeline', (startHour, endHour, pixelsPerHour) => ({
        currentTimeTop: 0,
        isVisible: false,
        updateLine() {
            const now = new Date();
            const currentDay = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
            const selectedDay = '<?= $selectedDate ?>';
            if (currentDay !== selectedDay) {
                this.isVisible = false;
                return;
            }
            const hours = now.getHours();
            const minutes = now.getMinutes();
            if (hours >= startHour && hours < endHour) {
                this.isVisible = true;
                this.currentTimeTop = ((hours - startHour) * pixelsPerHour) + (minutes / 60 * pixelsPerHour);
            } else {
                this.isVisible = false;
            }
        },
        init() {
            this.updateLine();
            setInterval(() => this.updateLine(), 30000); // Cập nhật mỗi 30 giây
        }
    }))
})
</script>

</body>
</html>