<?php
// Giả lập môi trường và kết nối CSDL để có thể xem trước
// Trong môi trường thực tế, bạn sẽ sử dụng file kết nối thật
// require_once "../../../config/database.php";
// $pdo = ketnoicsdl();

if (!isset($pdo)) {
    class MockPDO {
        public function prepare($sql) { return $this; }
        public function execute($params = []) {}
        public function fetchAll($fetch_style = 0) {
            // Dữ liệu giả lập khớp với cấu trúc truy vấn của bạn
            return [
                [
                    'id_giao_dich' => 'gd001',
                    'ngay_giao_dich' => '2025-10-10 14:00:00',
                    'bds_tieu_de' => 'Bán nhà vườn tại Vĩnh Long',
                    'bds_gia' => 2800000000,
                    'nguoi_mua_ten' => 'Trương Quốc Đăng',
                    'nguoi_mua_email' => 'dang.tq@example.com',
                    'nguoi_ban_ten' => 'Lê Ngọc Quỳnh',
                    'nguoi_ban_email' => 'quynh.ln@example.com',
                ],
                [
                    'id_giao_dich' => 'gd002',
                    'ngay_giao_dich' => '2025-09-25 11:20:00',
                    'bds_tieu_de' => 'Dự án đất nền ven sông Vĩnh Long',
                    'bds_gia' => 950000000,
                    'nguoi_mua_ten' => 'Nguyễn Văn An (ví dụ)',
                    'nguoi_mua_email' => 'an.nv@example.com',
                    'nguoi_ban_ten' => 'Lê Ngọc Quỳnh',
                    'nguoi_ban_email' => 'quynh.ln@example.com',
                ],
            ];
        }
    }
    $pdo = new MockPDO();
}

$giao_dich_hoan_tat = [];
$error_msg = null;

try {
    // Truy vấn các giao dịch đã hoàn tất
    $sql = "
        SELECT 
            gd.id AS id_giao_dich,
            gd.ngay_giao_dich,
            bds.tieu_de AS bds_tieu_de,
            bds.gia AS bds_gia,
            info_mua.ho_ten AS nguoi_mua_ten,
            nd_mua.email AS nguoi_mua_email,
            info_ban.ho_ten AS nguoi_ban_ten,
            nd_ban.email AS nguoi_ban_email
        FROM 
            giao_dich gd
        JOIN 
            nguoi_dung nd_mua ON gd.id_nguoi_dung = nd_mua.id
        LEFT JOIN 
            info_nguoi_dung info_mua ON nd_mua.id = info_mua.id_nguoi_dung
        JOIN 
            nguoi_dung nd_ban ON gd.id_nguoi_ban = nd_ban.id
        LEFT JOIN 
            info_nguoi_dung info_ban ON nd_ban.id = info_ban.id_nguoi_dung
        JOIN 
            bat_dong_san bds ON gd.id_bds = bds.id
        WHERE 
            gd.trang_thai = 'hoantat'
        ORDER BY 
            gd.ngay_giao_dich DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $giao_dich_hoan_tat = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Khách hàng đã mua</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #a5b4fc; border-radius: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #e0e7ff; }
</style>
</head>

<body class="bg-slate-100 text-slate-800">

<div class="flex min-h-screen">
  

    <!-- Main Content -->
    <main class="flex-grow p-4 sm:p-6 lg:p-8 overflow-y-auto">
        <div class="max-w-7xl mx-auto">
            <header class="mb-8 pb-4 border-b border-slate-200">
                <h1 class="text-3xl font-bold text-slate-900">Lịch Sử Giao Dịch Hoàn Tất</h1>
                <p class="text-slate-500 mt-1">Danh sách các bất động sản đã được giao dịch thành công.</p>
            </header>

            <?php if ($error_msg): ?>
                 <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6" role="alert">
                    <p><?= htmlspecialchars($error_msg) ?></p>
                </div>
            <?php endif; ?>

            <div class="space-y-5 max-h-[75vh] overflow-y-auto custom-scrollbar pr-2">
                <?php if (!empty($giao_dich_hoan_tat)): ?>
                    <?php foreach($giao_dich_hoan_tat as $gd): ?>
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm transition hover:shadow-md hover:border-indigo-200">
                            <div class="p-5">
                                <div class="flex justify-between items-start">
                                    <h2 class="font-bold text-lg text-slate-800 mb-1"><?= htmlspecialchars($gd['bds_tieu_de']) ?></h2>
                                    <span class="text-xs font-bold bg-green-100 text-green-800 px-2.5 py-1 rounded-full">Đã hoàn tất</span>
                                </div>
                                <p class="text-indigo-600 font-semibold text-base"><?= number_format($gd['bds_gia'], 0, ',', '.') ?> VNĐ</p>
                            </div>
                            <div class="bg-slate-50 px-5 py-4 border-t border-slate-200 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm rounded-b-xl">
                                <!-- Người mua -->
                                <div>
                                    <p class="text-xs text-slate-500 font-semibold mb-2">BÊN MUA</p>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-slate-200 rounded-full flex items-center justify-center font-bold text-slate-600">
                                            <?= htmlspecialchars(mb_substr($gd['nguoi_mua_ten'] ?? '?', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-700"><?= htmlspecialchars($gd['nguoi_mua_ten']) ?></p>
                                            <p class="text-xs text-slate-500"><?= htmlspecialchars($gd['nguoi_mua_email']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Người bán -->
                                <div>
                                    <p class="text-xs text-slate-500 font-semibold mb-2">BÊN BÁN (MÔI GIỚI)</p>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-slate-200 rounded-full flex items-center justify-center font-bold text-slate-600">
                                            <?= htmlspecialchars(mb_substr($gd['nguoi_ban_ten'] ?? '?', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-700"><?= htmlspecialchars($gd['nguoi_ban_ten']) ?></p>
                                            <p class="text-xs text-slate-500"><?= htmlspecialchars($gd['nguoi_ban_email']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Ngày giao dịch -->
                                <div>
                                     <p class="text-xs text-slate-500 font-semibold mb-2">NGÀY HOÀN TẤT</p>
                                     <p class="font-semibold text-slate-700"><?= date("d/m/Y", strtotime($gd['ngay_giao_dich'])) ?></p>
                                     <p class="text-xs text-slate-500"><?= date("H:i", strtotime($gd['ngay_giao_dich'])) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (!$error_msg): ?>
                    <div class="text-center py-16 text-slate-500 flex flex-col items-center bg-white rounded-xl border border-slate-200">
                        <svg class="h-16 w-16 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                        <p class="font-semibold mt-4">Chưa có giao dịch nào</p>
                        <p class="text-sm mt-1">Hiện tại chưa có giao dịch nào được hoàn tất trong hệ thống.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

</body>
</html>
