<?php
// =================================================================================
// PHẦN 1: LOGIC & XỬ LÝ DỮ LIỆU
// =================================================================================

// Bật hiển thị lỗi để dễ dàng gỡ lỗi
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../../../config/database.php";

// 1. Lấy tham số từ URL
$selectedDate = $_GET['date'] ?? null;
$brokerId = $_GET['broker_id'] ?? null;

// Nếu thiếu tham số, dừng thực thi và báo lỗi
if (!$selectedDate || !$brokerId) {
    die("Lỗi: Thiếu thông tin ngày hoặc ID môi giới để hiển thị timeline.");
}

/**
 * Lấy thông tin chi tiết và danh sách lịch hẹn của một môi giới trong một ngày cụ thể.
 * @param string $brokerId UUID của môi giới.
 * @param string $date Ngày cần xem (định dạng 'Y-m-d').
 * @return array|null Trả về một mảng chứa thông tin môi giới và lịch hẹn, hoặc null nếu không tìm thấy môi giới.
 */
function getBrokerTimelineData(string $brokerId, string $date): ?array
{
    $pdo = ketnoicsdl();
    $result = [
        'broker' => null,
        'appointments' => [],
        'conflict_ids' => []
    ];

    // --- Lấy thông tin môi giới ---
    $sqlBroker = "
        SELECT info_mg.ho_ten, nd_mg.avt 
        FROM nguoi_dung nd_mg
        LEFT JOIN info_nguoi_dung info_mg ON nd_mg.id = info_mg.id_nguoi_dung
        WHERE nd_mg.id = ?
    ";
    $stmtBroker = $pdo->prepare($sqlBroker);
    $stmtBroker->execute([$brokerId]);
    $brokerInfo = $stmtBroker->fetch(PDO::FETCH_ASSOC);

    if (!$brokerInfo) {
        return null; // Không tìm thấy môi giới
    }
    $result['broker'] = $brokerInfo;

    // --- Lấy danh sách lịch hẹn ---
    $startOfDay = $date . ' 00:00:00';
    $endOfDay = $date . ' 23:59:59';

    $sqlAppointments = "
        SELECT
            lt.id, lt.thoi_gian_bat_dau, lt.thoi_gian_ket_thuc, lt.trang_thai, lt.ghi_chu,
            info_kh.ho_ten AS ten_khach_hang
        FROM lich_trinh lt
        LEFT JOIN nguoi_dung nd_kh ON lt.id_khach_hang = nd_kh.id
        LEFT JOIN info_nguoi_dung info_kh ON nd_kh.id = info_kh.id_nguoi_dung
        WHERE lt.id_moi_gioi = ? AND lt.thoi_gian_bat_dau BETWEEN ? AND ?
        ORDER BY lt.thoi_gian_bat_dau ASC
    ";
    $stmtAppointments = $pdo->prepare($sqlAppointments);
    $stmtAppointments->execute([$brokerId, $startOfDay, $endOfDay]);
    $appointments = $stmtAppointments->fetchAll(PDO::FETCH_ASSOC);

    // --- Xử lý dữ liệu để hiển thị ---
    $pixelsPerHour = 60;
    $timelineStartHour = 8;
    $processedAppointments = [];

    foreach ($appointments as $apt) {
        $startObj = new DateTime($apt['thoi_gian_bat_dau']);
        $endObj = new DateTime($apt['thoi_gian_ket_thuc']);
        
        $top = (($startObj->format('H') - $timelineStartHour) * $pixelsPerHour) + ($startObj->format('i') / 60 * $pixelsPerHour);
        $durationMinutes = ($endObj->getTimestamp() - $startObj->getTimestamp()) / 60;
        $height = $durationMinutes / 60 * $pixelsPerHour;

        $apt['startObj'] = $startObj;
        $apt['endObj'] = $endObj;
        $apt['view_props'] = [
            'top' => max(0, $top),
            'height' => max(20, $height),
        ];
        $processedAppointments[] = $apt;
    }
    $result['appointments'] = $processedAppointments;

    // --- Phát hiện trùng lặp (tối ưu O(N)) ---
    for ($i = 0; $i < count($processedAppointments) - 1; $i++) {
        $currentApt = $processedAppointments[$i];
        $nextApt = $processedAppointments[$i+1];
        if ($nextApt['startObj']->getTimestamp() < $currentApt['endObj']->getTimestamp()) {
            $result['conflict_ids'][] = $currentApt['id'];
            $result['conflict_ids'][] = $nextApt['id'];
        }
    }
    $result['conflict_ids'] = array_unique($result['conflict_ids']);

    return $result;
}

// Thực thi lấy dữ liệu
$timelineData = getBrokerTimelineData($brokerId, $selectedDate);
$formattedDate = (new DateTime($selectedDate))->format('d/m/Y');

// =================================================================================
// PHẦN 2: CÁC HÀM HỖ TRỢ VIEW
// =================================================================================
function getStatusClasses(string $status): string {
    $map = [
        'daxacnhan' => 'bg-green-100 text-green-800 border-green-300',
        'choxacnhan' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'dahuy' => 'bg-slate-100 text-slate-500 border-slate-300',
    ];
    return $map[$status] ?? 'bg-gray-100 text-gray-800';
}

function renderAppointmentCard(array $appointment, array $conflict_ids): void {
    $isConflict = in_array($appointment['id'], $conflict_ids);
    $statusClasses = getStatusClasses($appointment['trang_thai']);
    $conflictClasses = $isConflict ? 'border-red-500 border-2 shadow-md' : 'border';
    $jsonAppointment = htmlspecialchars(json_encode($appointment), ENT_QUOTES, 'UTF-8');
?>
    <div @click="isModalOpen = true; modalData = <?= $jsonAppointment ?>"
         class="group absolute left-4 right-2 p-2 rounded-lg cursor-pointer transition-all hover:shadow-lg hover:z-10 <?= $statusClasses ?> <?= $conflictClasses ?>"
         style="top: <?= $appointment['view_props']['top'] ?>px; height: <?= $appointment['view_props']['height'] ?>px;">
        <p class="font-semibold text-xs truncate" title="<?= htmlspecialchars($appointment['ten_khach_hang']) ?>">
            <?= htmlspecialchars($appointment['ten_khach_hang'] ?: 'N/A') ?>
        </p>
        <p class="text-xs opacity-70 flex items-center">
            <?= $appointment['startObj']->format('H:i') ?> - <?= $appointment['endObj']->format('H:i') ?>
            <?php if ($isConflict): ?>
                <i class="fa-solid fa-triangle-exclamation text-red-500 ml-1" title="Lịch hẹn này bị trùng"></i>
            <?php endif; ?>
        </p>
    </div>
<?php
}
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <title>Timeline chi tiết</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full p-4 md:p-6 lg:p-8" x-data="{ isModalOpen: false, modalData: {} }">

<div class="max-w-4xl mx-auto">
    <?php if (!$timelineData): ?>
        <div class="text-center p-12 bg-white rounded-lg shadow-md">
            <i class="fa-solid fa-circle-exclamation text-red-500 fa-3x mb-4"></i>
            <h1 class="text-xl font-bold text-slate-800">Không tìm thấy môi giới</h1>
            <p class="text-slate-600 mt-2">ID môi giới được cung cấp không hợp lệ hoặc không tồn tại.</p>
            <a href="summary-view.php" class="mt-6 inline-block bg-indigo-600 text-white px-5 py-2 rounded-md hover:bg-indigo-700">
                <i class="fa-solid fa-arrow-left mr-2"></i>Quay lại trang tổng quan
            </a>
        </div>
    <?php else: ?>
        <header class="pb-4 border-b border-slate-200 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3">
                         <img src="../../../storage/pictures/avt/<?= htmlspecialchars($timelineData['broker']['avt']) ?>" class="w-12 h-12 rounded-full object-cover">
                         <div>
                            <h1 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($timelineData['broker']['ho_ten']) ?></h1>
                            <p class="text-sm text-slate-600">Lịch trình chi tiết ngày: <strong><?= $formattedDate ?></strong></p>
                         </div>
                    </div>
                </div>
                <a href="trangchu.php?page=ds_datlich&search=<?= $selectedDate ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
                </a>
            </div>
        </header>

        <div class="flex gap-4">
            <div class="w-14 text-right text-xs text-slate-500 flex-shrink-0 pt-2">
                <?php for ($hour = 8; $hour <= 18; $hour++): ?>
                    <div class="h-[60px] relative">
                        <span class="absolute -top-2"><?= sprintf('%02d:00', $hour) ?></span>
                    </div>
                <?php endfor; ?>
            </div>
            
            <div class="flex-1 relative">
                 <div class="absolute inset-0 -z-10">
                    <?php for ($hour = 8; $hour <= 18; $hour++): ?>
                        <div class="h-[60px] border-t border-slate-200"></div>
                    <?php endfor; ?>
                </div>

                <?php if (empty($timelineData['appointments'])): ?>
                     <div class="flex items-center justify-center h-full text-slate-500 text-center p-8 bg-slate-50/50 rounded-md">
                        <div>
                            <i class="fa-solid fa-calendar-check fa-2x mb-2"></i>
                            <p>Không có lịch hẹn nào trong ngày này.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($timelineData['appointments'] as $appointment): ?>
                        <?php renderAppointmentCard($appointment, $timelineData['conflict_ids']); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div x-show="isModalOpen" x-transition.opacity x-cloak class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div @click.away="isModalOpen = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all"
            x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-indigo-600"></i> Chi tiết Lịch hẹn
                </h2>
                <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
            </div>
            <dl class="mt-5 space-y-4 text-sm">
                <div><dt class="font-medium text-slate-500">Khách hàng</dt><dd class="mt-1 text-slate-800 font-semibold" x-text="modalData.ten_khach_hang || 'N/A'"></dd></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="font-medium text-slate-500">Bắt đầu</dt><dd class="mt-1 text-slate-800" x-text="new Date(modalData.thoi_gian_bat_dau).toLocaleString('vi-VN')"></dd></div>
                    <div><dt class="font-medium text-slate-500">Kết thúc</dt><dd class="mt-1 text-slate-800" x-text="new Date(modalData.thoi_gian_ket_thuc).toLocaleString('vi-VN')"></dd></div>
                </div>
                <div><dt class="font-medium text-slate-500">Trạng thái</dt><dd class="mt-1"><span class="px-3 py-1 rounded-full text-xs font-semibold capitalize" :class="{
                    'bg-green-100 text-green-700': modalData.trang_thai === 'daxacnhan',
                    'bg-yellow-100 text-yellow-700': modalData.trang_thai === 'choxacnhan',
                    'bg-slate-100 text-slate-700': modalData.trang_thai === 'dahuy',
                }" x-text="modalData.trang_thai.replace('_', ' ')"></span></dd></div>
                <div><dt class="font-medium text-slate-500">Ghi chú</dt><dd class="mt-1 text-slate-600 italic p-3 bg-slate-50 rounded-md whitespace-pre-wrap" x-text="modalData.ghi_chu || 'Không có ghi chú.'"></dd></div>
            </dl>
        </div>
    </div>
</div>

</body>
</html>