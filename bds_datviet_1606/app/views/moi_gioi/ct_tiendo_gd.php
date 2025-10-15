<?php
// Tên file: ct_tiendo_gd.php

// Đảm bảo SESSION và kết nối CSDL
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$pdo = ketnoicsdl();
$id_giao_dich = $_GET['id'] ?? null;

if (!$id_giao_dich) {
    echo "<div class='p-4 text-red-600 bg-red-100 rounded-lg'>Thiếu ID giao dịch để xem tiến độ!</div>";
    exit;
}

// Lấy thông tin Giao dịch, Bất động sản và Thanh toán
try {
    // 1. Lấy thông tin Giao dịch và BĐS liên quan
    $sql_gd = "
        SELECT 
            gd.id, gd.loai, gd.ngay_giao_dich, gd.trang_thai,
            bds.tieu_de AS ten_bds, bds.dia_chi
        FROM 
            giao_dich gd
        LEFT JOIN 
            bat_dong_san bds ON gd.id_bds = bds.id
        WHERE 
            gd.id = :id_gd
    ";
    $stmt_gd = $pdo->prepare($sql_gd);
    $stmt_gd->execute([':id_gd' => $id_giao_dich]);
    $giao_dich = $stmt_gd->fetch(PDO::FETCH_ASSOC);

    if (!$giao_dich) {
        echo "<div class='p-4 text-orange-600 bg-orange-100 rounded-lg'>Không tìm thấy giao dịch ID: " . htmlspecialchars($id_giao_dich) . "</div>";
        exit;
    }
    
    // 2. LẤY KẾ HOẠCH THANH TOÁN (Tổng tiền và Trạng thái TT tổng thể)
    $sql_khtt = "
        SELECT 
            tong_gia_tri, so_tien_da_tt, trang_thai_tt
        FROM 
            ke_hoach_thanh_toan
        WHERE 
            id_giao_dich = :id_gd
    ";
    $stmt_khtt = $pdo->prepare($sql_khtt);
    $stmt_khtt->execute([':id_gd' => $id_giao_dich]);
    $ke_hoach = $stmt_khtt->fetch(PDO::FETCH_ASSOC);
    
    // 3. LẤY LỊCH SỬ CÁC ĐỢT THANH TOÁN ĐÃ THỰC HIỆN
    $sql_dtt = "
        SELECT 
            lan_tt, so_tien_tt, ngay_tt, phuong_thuc
        FROM 
            dot_thanh_toan
        WHERE 
            id_giao_dich = :id_gd
        ORDER BY lan_tt ASC
    ";
    $stmt_dtt = $pdo->prepare($sql_dtt);
    $stmt_dtt->execute([':id_gd' => $id_giao_dich]);
    $dot_thanh_toan_history = $stmt_dtt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    error_log("Lỗi CSDL Tiến độ GD: " . $e->getMessage());
    echo "<div class='p-4 text-red-600 bg-red-100 rounded-lg'>Lỗi hệ thống khi tải dữ liệu tiến độ.</div>";
    exit;
}

// Hàm helper để định dạng trạng thái (Bổ sung trạng thái TT tổng thể)
function formatStatus($status) {
    $map = [
        'choxuly' => ['text' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-800'],
        'dangxuly' => ['text' => 'Đang xử lý', 'class' => 'bg-blue-100 text-blue-800'],
        'hoantat' => ['text' => 'HOÀN TẤT', 'class' => 'bg-green-100 text-green-800'],
        'dahuy' => ['text' => 'Đã HỦY', 'class' => 'bg-red-100 text-red-800'],
        
        // Trạng thái Thanh toán tổng thể
        'chuathanhtoan' => ['text' => 'Chưa TT', 'class' => 'bg-red-400 text-white'],
        'dangthanhtoan' => ['text' => 'Đang TT', 'class' => 'bg-orange-400 text-white'],
        'hoantat' => ['text' => 'Đã TT Xong', 'class' => 'bg-green-600 text-white'],
    ];
    return $map[$status] ?? ['text' => $status, 'class' => 'bg-gray-200 text-gray-700'];
}

// Tính phần trăm đã thanh toán
$tong_gia_tri = (float)($ke_hoach['tong_gia_tri'] ?? 0);
$so_tien_da_tt = (float)($ke_hoach['so_tien_da_tt'] ?? 0);
$phan_tram_tt = ($tong_gia_tri > 0) ? round(($so_tien_da_tt / $tong_gia_tri) * 100) : 0;

$status_tt_tong = formatStatus($ke_hoach['trang_thai_tt'] ?? 'chuathanhtoan');
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<div class="max-w-3xl mx-auto p-4 md:p-6 ">
    <div class="bg-white rounded-xl shadow-2xl p-6 border border-gray-200">
        
        <h1 class="text-2xl font-extrabold text-blue-700 mb-2 flex items-center gap-2">
            <i class="fa-solid fa-gauge-high"></i> TIẾN ĐỘ GIAO DỊCH
        </h1>
        <p class="text-sm text-gray-500 mb-6 border-b pb-3">Mã GD: <span class="font-mono text-gray-800 font-semibold"><?= htmlspecialchars($giao_dich['id']) ?></span></p>

        <div class="grid md:grid-cols-2 gap-4 mb-8">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 shadow-sm">
                <p class="text-xs uppercase font-semibold text-blue-600 mb-1"><i class="fa-solid fa-list"></i> Loại & Ngày GD</p>
                <p class="text-lg font-bold text-gray-800 capitalize"><?= htmlspecialchars($giao_dich['loai']) ?></p>
                <p class="text-xs text-gray-600">Bắt đầu: <?= date('d/m/Y H:i', strtotime($giao_dich['ngay_giao_dich'])) ?></p>
            </div>

            <?php $status_gd = formatStatus($giao_dich['trang_thai']); ?>
            <div class="p-4 rounded-lg border <?= $status_gd['class'] ?> shadow-sm flex flex-col justify-center">
                <p class="text-xs uppercase font-semibold mb-1"><i class="fa-solid fa-star"></i> Trạng thái Hợp đồng</p>
                <span class="text-xl font-extrabold block"><?= htmlspecialchars($status_gd['text']) ?></span>
            </div>
        </div>
        
        <div class="mb-8 p-4 bg-white rounded-lg border border-gray-300 shadow-md">
            <h3 class="text-base font-bold text-gray-800 mb-3 flex justify-between items-center">
                <span><i class="fa-solid fa-credit-card text-blue-600 mr-2"></i> Tiến độ Thanh toán Tổng thể</span>
                <span class="text-sm font-semibold <?= $status_tt_tong['class'] ?> px-2 py-1 rounded"><?= $status_tt_tong['text'] ?></span>
            </h3>
            
            <div class="space-y-1 text-sm">
                <p>Tổng giá trị hợp đồng: <span class="font-bold text-red-600"><?= number_format($tong_gia_tri, 0, ',', '.') ?> VND</span></p>
                <p>Đã thanh toán: <span class="font-bold text-green-600"><?= number_format($so_tien_da_tt, 0, ',', '.') ?> VND</span></p>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-3">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $phan_tram_tt ?>%"></div>
            </div>
            <div class="text-right text-xs font-bold text-blue-600 mt-1"><?= $phan_tram_tt ?>% Hoàn thành</div>
        </div>

        <div class="mb-8 p-4 bg-gray-100 rounded-lg border border-gray-300">
            <h3 class="text-base font-bold text-gray-800 mb-1"><i class="fa-solid fa-building text-gray-600 mr-2"></i> Bất Động Sản</h3>
            <p class="text-lg font-extrabold text-gray-900"><?= htmlspecialchars($giao_dich['ten_bds'] ?? '--- Không có BĐS ---') ?></p>
            <p class="text-sm text-gray-600 mt-0"><?= htmlspecialchars($giao_dich['dia_chi'] ?? 'Địa chỉ chưa cập nhật') ?></p>
        </div>


        <h2 class="text-xl font-bold text-gray-700 mb-4"><i class="fa-solid fa-clock-rotate-left text-gray-500 mr-2"></i> Lịch sử các Đợt Thanh toán</h2>
        
        <div class="relative pl-6">
            <div class="absolute left-1 top-0 bottom-0 w-0.5 bg-gray-300"></div>

            <?php $i = 0; ?>
            
            <div class="mb-6 relative">
                <div class="absolute -left-5 top-0 w-3 h-3 bg-blue-600 rounded-full border-2 border-white shadow-lg"></div>
                <div class="ml-4 bg-blue-50 p-3 rounded-lg shadow-sm border border-blue-200">
                    <p class="font-bold text-sm text-blue-700">Khởi tạo Giao dịch</p>
                    <p class="text-xs text-gray-700">Giao dịch được tạo: <?= date('d/m/Y H:i', strtotime($giao_dich['ngay_giao_dich'])) ?></p>
                </div>
            </div>

            <?php 
            // Lặp qua Lịch sử Thanh toán (dot_thanh_toan)
            if (!empty($dot_thanh_toan_history)):
                foreach ($dot_thanh_toan_history as $tt): 
                    $i++;
            ?>
                    <div class="mb-6 relative">
                        <div class="absolute -left-5 top-0 w-3 h-3 bg-green-600 rounded-full border-2 border-white shadow-lg"></div>
                        <div class="ml-4 p-3 rounded-lg shadow-sm border-l-4 border-green-500 bg-green-50">
                            <p class="font-bold text-base text-green-700">Thanh toán Đợt <?= htmlspecialchars($tt['lan_tt']) ?></p>
                            <p class="text-xl font-extrabold text-gray-900 mt-0"><?= number_format($tt['so_tien_tt'], 0, ',', '.') ?> VND</p>
                            <p class="text-xs text-gray-700">Hình thức: <?= htmlspecialchars($tt['phuong_thuc']) ?></p>
                            <p class="text-xs mt-0 text-gray-500">Thời gian: <?= date('d/m/Y H:i', strtotime($tt['ngay_tt'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                 <div class="ml-4 p-4 text-orange-600 bg-orange-100 rounded-lg">Chưa có đợt thanh toán nào được ghi nhận.</div>
            <?php endif; ?>
            
            <?php 
            // Sự kiện Kết thúc Giao dịch (Nếu đã Hoàn tất)
            if ($giao_dich['trang_thai'] === 'hoantat'): 
            ?>
                <div class="relative">
                    <div class="absolute -left-5 top-0 w-3 h-3 bg-green-600 rounded-full border-2 border-white shadow-lg"></div>
                    <div class="ml-4 p-3 rounded-lg shadow-md bg-green-100 text-green-800">
                        <p class="font-bold text-base text-green-800">GIAO DỊCH HOÀN TẤT</p>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <div class="mt-8 text-center border-t pt-4">
             <a href="javascript:history.back()" class="text-blue-600 hover:text-blue-800 hover:underline font-medium flex items-center justify-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>

    </div>
</div>