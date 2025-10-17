<?php
// =================================================================================
// PHẦN 1: LOGIC & XỬ LÝ DỮ LIỆU
// =================================================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../../../config/database.php";

// 1. Lấy tham số và thiết lập phân trang
$brokerId = $_GET['broker_id'] ?? null;
$selectedDate = $_GET['date'] ?? null;
if (!$brokerId) {
    die("Lỗi: Không có ID môi giới được cung cấp.");
}

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// HÀM 1: Lấy ID của tất cả lịch hẹn bị xung đột
function getBrokerConflictIds(string $brokerId): array
{
    $pdo = ketnoicsdl();
    $sql = "
        SELECT DISTINCT lt1.id
        FROM lich_trinh lt1
        JOIN lich_trinh lt2
          ON lt1.id_moi_gioi = lt2.id_moi_gioi
          AND lt1.id <> lt2.id
          AND lt1.thoi_gian_bat_dau < lt2.thoi_gian_ket_thuc
          AND lt2.thoi_gian_bat_dau < lt1.thoi_gian_ket_thuc
        WHERE lt1.id_moi_gioi = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$brokerId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

// HÀM 2: Lấy dữ liệu lịch sử
function getBrokerHistoryData(string $brokerId, int $limit, int $offset): ?array
{
    $pdo = ketnoicsdl();
    $result = [
        'broker' => null,
        'appointments' => [],
        'total_records' => 0,
        'total_pages' => 0,
        'conflict_ids' => []
    ];

    $sqlBroker = "SELECT info_mg.ho_ten, nd_mg.avt FROM nguoi_dung nd_mg
                  LEFT JOIN info_nguoi_dung info_mg ON nd_mg.id = info_mg.id_nguoi_dung
                  WHERE nd_mg.id = ?";
    $stmtBroker = $pdo->prepare($sqlBroker);
    $stmtBroker->execute([$brokerId]);
    $brokerInfo = $stmtBroker->fetch(PDO::FETCH_ASSOC);

    if (!$brokerInfo) return null;
    $result['broker'] = $brokerInfo;
    $result['conflict_ids'] = getBrokerConflictIds($brokerId);

    $stmtTotal = $pdo->prepare("SELECT COUNT(id) FROM lich_trinh WHERE id_moi_gioi = ?");
    $stmtTotal->execute([$brokerId]);
    $totalRecords = (int)$stmtTotal->fetchColumn();
    
    $result['total_records'] = $totalRecords;
    $result['total_pages'] = ceil($totalRecords / $limit);

    $sqlAppointments = "
        SELECT lt.id, lt.thoi_gian_bat_dau, lt.thoi_gian_ket_thuc, lt.trang_thai, info_kh.ho_ten AS ten_khach_hang
        FROM lich_trinh lt
        LEFT JOIN nguoi_dung nd_kh ON lt.id_khach_hang = nd_kh.id
        LEFT JOIN info_nguoi_dung info_kh ON nd_kh.id = info_kh.id_nguoi_dung
        WHERE lt.id_moi_gioi = ?
        ORDER BY lt.thoi_gian_bat_dau DESC
        LIMIT ? OFFSET ?
    ";
    $stmtAppointments = $pdo->prepare($sqlAppointments);
    $stmtAppointments->execute([$brokerId, $limit, $offset]);
    $result['appointments'] = $stmtAppointments->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

$historyData = getBrokerHistoryData($brokerId, $limit, $offset);

// =================================================================================
// PHẦN 2: CÁC HÀM HỖ TRỢ VIEW
// =================================================================================

function getStatusBadge(string $status): string
{
    $text = ucfirst(str_replace('_', ' ', $status));
    $classes = '';
    switch ($status) {
        case 'daxacnhan': $classes = 'bg-green-100 text-green-800'; break;
        case 'choxacnhan': $classes = 'bg-yellow-100 text-yellow-800'; break;
        case 'dahuy': $classes = 'bg-red-100 text-red-800'; break;
        default: $classes = 'bg-slate-100 text-slate-800'; break;
    }
    return "<span class='px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {$classes}'>{$text}</span>";
}
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử Lịch hẹn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full p-4 md:p-6 lg:p-8">

<div class="max-w-5xl mx-auto">
    <?php if (!$historyData): ?>
        <div class="text-center p-12 bg-white rounded-lg shadow-md">
            <h1 class="text-xl font-bold text-slate-800">Không tìm thấy môi giới</h1>
            <p class="text-slate-600 mt-2">ID môi giới không hợp lệ hoặc không tồn tại.</p>
        </div>
    <?php else: ?>
        <header class="pb-4 border-b border-slate-200 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <img src="../../../storage/pictures/avt/<?= htmlspecialchars($historyData['broker']['avt']) ?>" class="w-16 h-16 rounded-full object-cover shadow-md">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Lịch sử Lịch hẹn</h1>
                        <p class="text-md text-slate-600 font-semibold"><?= htmlspecialchars($historyData['broker']['ho_ten']) ?></p>
                    </div>
                </div>
                <a href="trangchu.php?page=quanly_lichtrinh&search=<?= $selectedDate ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại trang Tổng quan
                </a>
            </div>
        </header>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Thời gian</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Khách hàng</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php if (empty($historyData['appointments'])): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-500">
                                    <p>Môi giới này chưa có lịch hẹn nào.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                            $appointmentsByDate = [];
                            foreach ($historyData['appointments'] as $apt) {
                                $date = (new DateTime($apt['thoi_gian_bat_dau']))->format('Y-m-d');
                                $appointmentsByDate[$date][] = $apt;
                            }
                            ?>

                            <?php foreach ($appointmentsByDate as $date => $appointmentsOnThisDay): ?>
                                <?php
                                $conflictingAptsOnThisDay = array_filter($appointmentsOnThisDay, fn($apt) => in_array($apt['id'], $historyData['conflict_ids']));
                                $hasConflictOnThisDay = count($conflictingAptsOnThisDay) > 1;
                                ?>
                                <tr class="bg-slate-100 border-t-2 border-slate-200">
                                    <td colspan="3" class="px-6 py-2 text-sm font-bold text-slate-700 flex justify-between items-center">
                                        <span>Ngày: <?= (new DateTime($date))->format('d/m/Y') ?></span>
                                        <?php if ($hasConflictOnThisDay): ?>
                                            <div x-data="{ isMenuOpen: false }" class="relative">
                                                <button @click="isMenuOpen = !isMenuOpen" 
                                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-full hover:bg-red-600 transition-colors flex items-center gap-1">
                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                    <span>Giải quyết</span>
                                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                                </button>
                                                
                                                <div x-show="isMenuOpen"
                                                     @click.outside="isMenuOpen = false"
                                                     x-transition
                                                     x-cloak
                                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                                    <div class="py-1">
                                                        <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            <i class="fa-solid fa-bell text-yellow-500"></i>
                                                            <span>Cảnh báo Môi giới</span>
                                                        </a>
                                                        <a href="timeline-view.php?broker_id=<?= $brokerId ?>&date=<?= $date ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            <i class="fa-solid fa-calendar-days text-blue-500"></i>
                                                            <span>(Xem Timeline)</span>
                                                        </a>
                                                        <a href="trangchu.php?page=doi_lich&broker_id=<?= $brokerId ?>&date=<?= $date ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            <i class="fa-solid fa-calendar-days text-blue-500"></i>
                                                            <span>Dời lịch</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <?php foreach ($appointmentsOnThisDay as $apt): ?>
                                    <tr class="<?= in_array($apt['id'], $historyData['conflict_ids']) ? 'bg-red-50' : '' ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                                            <?= (new DateTime($apt['thoi_gian_bat_dau']))->format('H:i') ?> - <?= (new DateTime($apt['thoi_gian_ket_thuc']))->format('H:i') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                            <?= htmlspecialchars($apt['ten_khach_hang'] ?: 'N/A') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <?= getStatusBadge($apt['trang_thai']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($historyData['total_pages'] > 1): ?>
                <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                    <div class="text-sm text-slate-600">
                        Hiển thị từ <strong><?= $offset + 1 ?></strong> đến <strong><?= min($offset + $limit, $historyData['total_records']) ?></strong> trên <strong><?= $historyData['total_records'] ?></strong> kết quả
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="?broker_id=<?= $brokerId ?>&page=<?= max(1, $page - 1) ?>" class="<?= $page <= 1 ? 'pointer-events-none text-slate-300' : 'text-slate-600 hover:bg-slate-100' ?> px-3 py-1 rounded-md transition-colors">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                        <span class="text-sm text-slate-700">Trang <?= $page ?> / <?= $historyData['total_pages'] ?></span>
                        <a href="?broker_id=<?= $brokerId ?>&page=<?= min($historyData['total_pages'], $page + 1) ?>" class="<?= $page >= $historyData['total_pages'] ? 'pointer-events-none text-slate-300' : 'text-slate-600 hover:bg-slate-100' ?> px-3 py-1 rounded-md transition-colors">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>