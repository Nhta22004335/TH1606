<?php

require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();

// Hàm tiện ích
function e($s){ 
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); 
}

// 1. KIỂM TRA XÁC THỰC (MÔI GIỚI)
$current_broker_id = $_SESSION['id_nguoi_dung'] ?? null;

// 3. LẤY DỮ LIỆU CÁC YÊU CẦU
$filter_trang_thai = $_GET['trang_thai'] ?? 'choxuly';

$params = [':broker_id' => $current_broker_id];
$whereSql = "yc.id_moi_gioi = :broker_id";

if ($filter_trang_thai != 'all') {
    $whereSql .= " AND yc.trang_thai = :trang_thai";
    $params[':trang_thai'] = $filter_trang_thai;
}

// ĐÃ TỐI ƯU HÓA SQL: Bỏ JOIN bat_dong_san và bai_dang
// ĐÃ THÊM: kh.id AS id_khachhang
$sqlData = "
    SELECT 
        yc.id, yc.loai, yc.trang_thai, yc.ngay_tao, yc.mo_ta_chi_tiet,
        info_kh.ho_ten AS ten_khach_hang,
        kh.so_dt AS sdt_khach_hang,
        kh.id AS id_khachhang -- <<< ĐÃ THÊM ĐỂ CÓ ID GỬI ĐI
    FROM yeu_cau yc
    JOIN nguoi_dung kh ON yc.id_nguoi_dung = kh.id
    JOIN info_nguoi_dung info_kh ON yc.id_nguoi_dung = info_kh.id_nguoi_dung
    WHERE $whereSql
    ORDER BY yc.ngay_tao DESC
";

$stmt = $pdo->prepare($sqlData);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusClass($status) {
    switch ($status) {
        case 'daduyet':
            return 'bg-green-100 text-green-800';
        case 'dahuy':
            return 'bg-red-100 text-red-800';
        case 'choxuly':
        default:
            return 'bg-blue-100 text-blue-800';
    }
}

// Tên file này là ql_yeucau_khachhang
$base_url = "trangchu.php?page=../moi_gioi/ql_yeucau_khachhang"; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Yêu Cầu Khách Hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <style>
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <div class="mb-6">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-3">Quản lý Yêu Cầu 📑</h1>
        <p class="text-lg text-gray-600">Nơi xử lý các yêu cầu quan tâm từ khách hàng.</p>
    </div>

    <?php if (isset($_SESSION['action_message'])): 
        $message = $_SESSION['action_message'];
         $type_class = 'bg-blue-100 border-blue-500 text-blue-700'; // Mặc định là info
        if ($message['type'] == 'success') {
            $type_class = 'bg-green-100 border-green-500 text-green-700';
        } elseif ($message['type'] == 'error') {
            $type_class = 'bg-red-100 border-red-500 text-red-700';
        }
    ?>
    <div class="border-l-4 p-4 <?= $type_class ?> mb-6" role="alert">
        <p><?= e($message['text']) ?></p>
    </div>
    <?php unset($_SESSION['action_message']); ?>
    <?php endif; ?>

    <div class="mb-6 bg-white rounded-lg shadow-sm">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <?php 
                $tabs = [
                    'choxuly' => 'Chờ xử lý',
                    'daduyet' => 'Đã duyệt',
                    'dahuy'   => 'Đã hủy',
                    'all'     => 'Tất cả'
                ];
                ?>
                <?php foreach ($tabs as $key => $value): ?>
                    <?php
                    $is_active = ($key == $filter_trang_thai);
                    $active_class = $is_active 
                        ? 'border-blue-500 text-blue-600' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                    ?>
                    <a href="<?= $base_url ?>&trang_thai=<?= $key ?>" 
                       class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm <?= $active_class ?>"
                       <?= $is_active ? 'aria-current="page"' : '' ?>>
                        <?= e($value) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
        <?php if(empty($requests)): ?>
            <p class="text-center text-gray-500 p-10">Không có yêu cầu nào phù hợp với bộ lọc này.</p>
        <?php else: ?>

        <div class="overflow-x-auto hidden md:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Liên hệ</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi tiết yêu cầu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày gửi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= e($req['ten_khach_hang']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-700"><?= e($req['sdt_khach_hang']) ?></div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700 max-w-xs truncate" title="<?= e($req['mo_ta_chi_tiet']) ?>">
                                <?= e($req['mo_ta_chi_tiet']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('d/m/Y H:i', strtotime($req['ngay_tao'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= getStatusClass(e($req['trang_thai'])) ?>">
                                <?= e($req['trang_thai']) ?>
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="trangchu.php?page=../../models/xl_yeucau_khachhang&id=<?= e($req['id_khachhang']) ?>" 
                               class="text-blue-600 hover:text-blue-700">
                                Nhắn tin
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="block md:hidden border-t border-gray-200">
            <?php foreach ($requests as $req): ?>
            <div class="border-b border-gray-200 p-4">
                <div class="space-y-3">
                    <div>
                        <div class="text-base font-semibold text-gray-900"><?= e($req['ten_khach_hang']) ?></div>
                        <div class="text-sm text-gray-600"><?= e($req['sdt_khach_hang']) ?></div>
                    </div>
                    
                    <p class="text-sm text-gray-700 italic">
                        "<?= e($req['mo_ta_chi_tiet']) ?>"
                    </p>

                    <div class="flex justify-between items-center pt-2">
                        <span class="text-sm text-gray-500">
                            <?= date('d/m/Y H:i', strtotime($req['ngay_tao'])) ?>
                        </span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= getStatusClass(e($req['trang_thai'])) ?>">
                            <?= e($req['trang_thai']) ?>
                        </span>
                    </div>

                    <div class="pt-3">
                        <a href="trangchu.php?page=../moi_gioi/xl_yeucau_khachhang&id=<?= e($req['id_khachhang']) ?>" 
                           class="w-full block text-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Nhắn tin
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>