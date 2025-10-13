<?php
// =================================================================================
// PHẦN 1: LOGIC & XỬ LÝ DỮ LIỆU
// =================================================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../../../config/database.php";

// 1. Lấy tham số từ URL
$brokerId = $_GET['broker_id'] ?? null;
$selectedDate = $_GET['date'] ?? null;

if (!$brokerId || !$selectedDate) {
    die("Lỗi: Thiếu thông tin môi giới hoặc ngày để xử lý.");
}

// 2. Xử lý yêu cầu CẬP NHẬT (khi form được gửi đi)
$updateStatus = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointment_id'] ?? null;
    $newStartTime = $_POST['new_start_time'] ?? null;
    $newEndTime = $_POST['new_end_time'] ?? null;

    if ($appointmentId && $newStartTime && $newEndTime) {
        $pdo = ketnoicsdl();
        $sql = "UPDATE lich_trinh SET thoi_gian_bat_dau = ?, thoi_gian_ket_thuc = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        // Chuyển đổi định dạng datetime-local (Y-m-d\TH:i) sang định dạng CSDL có thể hiểu
        $startTimeFormatted = (new DateTime($newStartTime))->format('Y-m-d H:i:s');
        $endTimeFormatted = (new DateTime($newEndTime))->format('Y-m-d H:i:s');

        if ($stmt->execute([$startTimeFormatted, $endTimeFormatted, $appointmentId])) {
            $updateStatus = 'success';
        } else {
            $updateStatus = 'error';
        }
    } else {
        $updateStatus = 'error';
    }
    // Tải lại trang để tránh gửi lại form, kèm theo trạng thái
    header("Location: doi_lich.php?broker_id=$brokerId&date=$selectedDate&status=$updateStatus");
    exit();
}
// Kiểm tra trạng thái từ URL sau khi tải lại
$statusMessage = $_GET['status'] ?? null;

// 3. Hàm lấy thông tin và các lịch hẹn bị xung đột trong ngày
function getConflictResolutionData(string $brokerId, string $date): ?array
{
    $pdo = ketnoicsdl();
    $result = ['broker' => null, 'conflicting_appointments' => []];

    // Lấy thông tin môi giới
    $sqlBroker = "SELECT info_mg.ho_ten, nd_mg.avt FROM nguoi_dung nd_mg LEFT JOIN info_nguoi_dung info_mg ON nd_mg.id = info_mg.id_nguoi_dung WHERE nd_mg.id = ?";
    $stmtBroker = $pdo->prepare($sqlBroker);
    $stmtBroker->execute([$brokerId]);
    $brokerInfo = $stmtBroker->fetch(PDO::FETCH_ASSOC);
    if (!$brokerInfo) return null;
    $result['broker'] = $brokerInfo;

    // Lấy các lịch hẹn bị xung đột của môi giới này TRONG ngày đã chọn
    $startOfDay = $date . ' 00:00:00';
    $endOfDay = $date . ' 23:59:59';
    $sqlConflicts = "
        SELECT DISTINCT lt1.*, info_kh.ho_ten AS ten_khach_hang
        FROM lich_trinh lt1
        JOIN lich_trinh lt2 ON lt1.id_moi_gioi = lt2.id_moi_gioi AND lt1.id <> lt2.id
            AND lt1.thoi_gian_bat_dau < lt2.thoi_gian_ket_thuc
            AND lt2.thoi_gian_bat_dau < lt1.thoi_gian_ket_thuc
        LEFT JOIN info_nguoi_dung info_kh ON lt1.id_khach_hang = info_kh.id_nguoi_dung
        WHERE lt1.id_moi_gioi = ?
        AND lt1.thoi_gian_bat_dau BETWEEN ? AND ?
        ORDER BY lt1.thoi_gian_bat_dau ASC
    ";
    $stmtConflicts = $pdo->prepare($sqlConflicts);
    $stmtConflicts->execute([$brokerId, $startOfDay, $endOfDay]);
    $result['conflicting_appointments'] = $stmtConflicts->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

$data = getConflictResolutionData($brokerId, $selectedDate);

?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <title>Dời lịch hẹn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="p-4 md:p-8">

<div class="max-w-4xl mx-auto">
    <header class="pb-4 border-b border-slate-300 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="../../../storage/pictures/avt/<?= htmlspecialchars($data['broker']['avt']) ?>" class="w-16 h-16 rounded-full object-cover shadow-md">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Giải quyết Lịch hẹn Trùng lặp</h1>
                    <p class="text-md text-slate-600">
                        Môi giới: <strong><?= htmlspecialchars($data['broker']['ho_ten']) ?></strong> | 
                        Ngày: <strong><?= (new DateTime($selectedDate))->format('d/m/Y') ?></strong>
                    </p>
                </div>
            </div>
            <a href="trangchu.php?page=broker-history&broker_id=<?= $brokerId ?>&date=<?= $selectedDate ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại Lịch sử
            </a>
        </div>
    </header>

    <div x-data="{ show: true }" x-show="show" x-transition class="mb-6">
        <?php if ($statusMessage === 'success'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Thành công!</strong>
                <span class="block sm:inline">Lịch hẹn đã được cập nhật.</span>
                <span @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">&times;</span>
            </div>
        <?php elseif ($statusMessage === 'error'): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Thất bại!</strong>
                <span class="block sm:inline">Không thể cập nhật lịch hẹn. Vui lòng thử lại.</span>
                <span @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">&times;</span>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($data['conflicting_appointments'])): ?>
        <div class="bg-white p-12 text-center rounded-lg shadow">
            <i class="fa-solid fa-circle-check text-green-500 fa-3x mb-4"></i>
            <p class="text-slate-600">Không còn xung đột nào trong ngày này hoặc đã được giải quyết.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php foreach ($data['conflicting_appointments'] as $index => $apt): ?>
                <div class="bg-white p-6 rounded-lg shadow-md border-t-4 
                    <?= $index % 2 == 0 ? 'border-blue-500' : 'border-orange-500' ?>">
                    
                    <form method="POST" action="">
                        <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($apt['id']) ?>">

                        <h3 class="text-lg font-bold text-slate-800 mb-4">
                            Lịch hẹn #<?= $index + 1 ?> (ID: <?= substr($apt['id'], 0, 8) ?>...)
                        </h3>
                        <dl class="space-y-3">
                            <div class="flex items-center">
                                <dt class="w-28 text-sm font-medium text-slate-500">Khách hàng</dt>
                                <dd class="text-sm text-slate-900 font-semibold"><?= htmlspecialchars($apt['ten_khach_hang'] ?: 'N/A') ?></dd>
                            </div>
                            <div>
                                <label for="start_time_<?= $apt['id'] ?>" class="block text-sm font-medium text-slate-500 mb-1">Thời gian bắt đầu</label>
                                <input type="datetime-local" id="start_time_<?= $apt['id'] ?>" name="new_start_time"
                                       value="<?= (new DateTime($apt['thoi_gian_bat_dau']))->format('Y-m-d\TH:i') ?>"
                                       class="w-full border-slate-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                             <div>
                                <label for="end_time_<?= $apt['id'] ?>" class="block text-sm font-medium text-slate-500 mb-1">Thời gian kết thúc</label>
                                <input type="datetime-local" id="end_time_<?= $apt['id'] ?>" name="new_end_time"
                                       value="<?= (new DateTime($apt['thoi_gian_ket_thuc']))->format('Y-m-d\TH:i') ?>"
                                       class="w-full border-slate-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </dl>
                        <div class="mt-6">
                             <button type="submit" class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                <i class="fa-solid fa-save mr-2"></i>Cập nhật Lịch
                            </button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>