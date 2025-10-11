<?php
// Demo dữ liệu
$datlich = [
    ["id" => 1, "khachhang" => "Phạm Minh Khoa", "broker" => "Nguyễn Văn A", "avatar" => "https://i.pravatar.cc/100?img=1", "thoigianbatdau" => "2025-10-10 14:00", "thoigiankt" => "2025-10-10 15:00", "trangthai" => "Đã xác nhận", "ghichu" => "Trao đổi về căn hộ Q7"],
    ["id" => 2, "khachhang" => "Ngô Thị Hoa", "broker" => "Nguyễn Văn A", "avatar" => "https://i.pravatar.cc/100?img=1", "thoigianbatdau" => "2025-10-10 14:30", "thoigiankt" => "2025-10-10 15:30", "trangthai" => "Chờ xác nhận", "ghichu" => "Tư vấn biệt thự Phú Mỹ Hưng"],
    ["id" => 3, "khachhang" => "Lê Quốc Huy", "broker" => "Trần Thị B", "avatar" => "https://i.pravatar.cc/100?img=2", "thoigianbatdau" => "2025-10-10 09:00", "thoigiankt" => "2025-10-10 10:00", "trangthai" => "Đã hủy", "ghichu" => "Khách hủy vì bận công tác"],
    ["id" => 4, "khachhang" => "Nguyễn Văn Dũng", "broker" => "Trần Thị B", "avatar" => "https://i.pravatar.cc/100?img=2", "thoigianbatdau" => "2025-10-10 09:30", "thoigiankt" => "2025-10-10 10:15", "trangthai" => "Đã xác nhận", "ghichu" => "Xem nhà phố Thủ Đức"],
    ["id" => 5, "khachhang" => "Võ Thị G", "broker" => "Nguyễn Văn A", "avatar" => "https://i.pravatar.cc/100?img=1", "thoigianbatdau" => "2025-10-10 10:00", "thoigiankt" => "2025-10-10 11:00", "trangthai" => "Đã xác nhận", "ghichu" => "Ký hợp đồng cọc"],
    ["id" => 6, "khachhang" => "Trần Văn H", "broker" => "Lê Sĩ C", "avatar" => "https://i.pravatar.cc/100?img=3", "thoigianbatdau" => "2025-10-10 11:00", "thoigiankt" => "2025-10-10 11:30", "trangthai" => "Đã xác nhận", "ghichu" => "Tư vấn nhanh qua điện thoại"],
];

// 1. XỬ LÝ DỮ LIỆU
foreach ($datlich as &$lich) {
    $lich['startObj'] = new DateTime($lich['thoigianbatdau']);
    $lich['endObj']   = new DateTime($lich['thoigiankt']);
    $lich['start']    = $lich['startObj']->getTimestamp();
    $lich['end']      = $lich['endObj']->getTimestamp();
}
unset($lich);

// Phát hiện trùng lặp
$duplicatedIds = [];
$needNotifyIds = [];
foreach ($datlich as $i => $lich1) {
    foreach ($datlich as $j => $lich2) {
        if ($i >= $j) continue;
        if ($lich1['broker'] === $lich2['broker'] && $lich1['start'] < $lich2['end'] && $lich2['start'] < $lich1['end']) {
            $duplicatedIds[] = $lich1['id'];
            $duplicatedIds[] = $lich2['id'];
            $needNotifyIds[] = ($lich1['start'] < $lich2['start']) ? $lich2['id'] : $lich1['id'];
        }
    }
}
$duplicatedIds = array_unique($duplicatedIds);
$needNotifyIds = array_unique($needNotifyIds);

// 2. NHÓM LỊCH THEO MÔI GIỚI
$lichTheoBroker = [];
foreach ($datlich as $lich) {
    $lichTheoBroker[$lich['broker']][] = $lich;
}
// Sắp xếp lịch trong mỗi broker theo thời gian bắt đầu
foreach ($lichTheoBroker as &$brokerLich) {
    usort($brokerLich, fn($a, $b) => $a['start'] <=> $b['start']);
}
unset($brokerLich);

// 3. CÁC HÀM HELPER
function getStatusClasses($status) {
    switch ($status) {
        case 'Đã xác nhận': return 'bg-green-100 text-green-800 border-green-300';
        case 'Chờ xác nhận': return 'bg-yellow-100 text-yellow-800 border-yellow-300';
        case 'Đã hủy': return 'bg-slate-100 text-slate-500 border-slate-300';
        default: return 'bg-gray-100 text-gray-800';
    }
}
$pixelsPerHour = 60; // 60px cho mỗi giờ
$timelineStartHour = 8; // Timeline bắt đầu từ 8h sáng
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <title>Lịch trình Hẹn theo Ngày</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full" x-data="{ isModalOpen: false, modalData: {} }">


    <header class="pb-4 border-b mb-6">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Lịch trình trong ngày</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Tổng quan lịch hẹn của các môi giới. Hôm nay là: <strong><?= date('d/m/Y') ?></strong>.
                </p>
            </div>
        </div>
    </header>

    <div class="flex gap-6">
        <div class="w-16 text-right text-sm text-slate-500 flex-shrink-0">
            <?php for ($hour = $timelineStartHour; $hour <= 18; $hour++): ?>
                <div class="h-[<?= $pixelsPerHour ?>px] -translate-y-2"><?= sprintf('%02d:00', $hour) ?></div>
            <?php endfor; ?>
        </div>
        
        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
            <?php foreach ($lichTheoBroker as $brokerName => $appointments): ?>
                <div class="relative border-l border-slate-200 pl-2">
                    <div class="flex items-center gap-2 mb-2">
                        <img src="<?= htmlspecialchars($appointments[0]['avatar']) ?>" class="w-8 h-8 rounded-full">
                        <span class="font-semibold text-slate-700 text-sm"><?= htmlspecialchars($brokerName) ?></span>
                    </div>

                    <?php foreach ($appointments as $lich):
                        $top = (($lich['startObj']->format('H') - $timelineStartHour) * $pixelsPerHour) + ($lich['startObj']->format('i') / 60 * $pixelsPerHour);
                        $durationMinutes = ($lich['end'] - $lich['start']) / 60;
                        $height = $durationMinutes / 60 * $pixelsPerHour;
                        $isConflict = in_array($lich['id'], $duplicatedIds);
                    ?>
                        <div @click="isModalOpen = true; modalData = <?= htmlspecialchars(json_encode($lich)) ?>"
                             class="group absolute left-4 right-0 p-2 rounded-lg border cursor-pointer transition-all hover:shadow-lg hover:z-10 <?= getStatusClasses($lich['trangthai']) ?> <?= $isConflict ? 'border-red-500 border-2' : '' ?>"
                             style="top: <?= $top ?>px; height: <?= $height ?>px; min-height: 20px;">
                            
                            <p class="font-semibold text-xs truncate"><?= htmlspecialchars($lich['khachhang']) ?></p>
                            <p class="text-xs opacity-70">
                                <?= $lich['startObj']->format('H:i') ?> - <?= $lich['endObj']->format('H:i') ?>
                                <?php if ($isConflict): ?>
                                    <i class="fa-solid fa-triangle-exclamation text-red-600 ml-1" title="Lịch hẹn này bị trùng"></i>
                                <?php endif; ?>
                            </p>
                             <div class="absolute bottom-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="w-6 h-6 bg-red-500 text-white rounded text-xs hover:bg-red-600" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                                <?php if (in_array($lich['id'], $needNotifyIds)): ?>
                                    <button class="w-6 h-6 bg-orange-500 text-white rounded text-xs hover:bg-orange-600" title="Thông báo dời lịch"><i class="fa-solid fa-bell"></i></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<div x-show="isModalOpen" x-transition.opacity x-cloak class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div @click.away="isModalOpen = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all"
         x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-indigo-600"></i> Chi tiết Lịch hẹn #<span x-text="modalData.id"></span>
                    </h2>
                     <span class="mt-1 px-3 py-1 rounded-full text-xs font-semibold" :class="{
                         'bg-green-100 text-green-700': modalData.trangthai === 'Đã xác nhận',
                         'bg-yellow-100 text-yellow-700': modalData.trangthai === 'Chờ xác nhận',
                         'bg-slate-100 text-slate-700': modalData.trangthai === 'Đã hủy',
                     }" x-text="modalData.trangthai"></span>
                </div>
                <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            
            <dl class="mt-5 space-y-4 text-sm">
                <div><dt class="font-medium text-slate-500">Khách hàng</dt><dd class="mt-1 text-slate-800 font-semibold" x-text="modalData.khachhang"></dd></div>
                <div><dt class="font-medium text-slate-500">Môi giới</dt><dd class="mt-1 text-slate-800" x-text="modalData.broker"></dd></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><dt class="font-medium text-slate-500">Bắt đầu</dt><dd class="mt-1 text-slate-800" x-text="new Date(modalData.thoigianbatdau).toLocaleString('vi-VN')"></dd></div>
                    <div><dt class="font-medium text-slate-500">Kết thúc</dt><dd class="mt-1 text-slate-800" x-text="new Date(modalData.thoigiankt).toLocaleString('vi-VN')"></dd></div>
                </div>
                <div><dt class="font-medium text-slate-500">Ghi chú</dt><dd class="mt-1 text-slate-600 italic p-3 bg-slate-50 rounded-md" x-text="modalData.ghichu || 'Không có ghi chú.'"></dd></div>
            </dl>
        </div>
        <div class="bg-slate-50 px-6 py-4 rounded-b-xl flex justify-end gap-3">
            <button type="button" class="px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">Hủy lịch</button>
            <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">Xác nhận</button>
        </div>
    </div>
</div>

</body>
</html>