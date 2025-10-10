<?php

require_once "../../../config/database.php";
$pdo = ketnoicsdl();
$giao_dich_hoan_tat = [];
$error_msg = null;

try {
    // Câu truy vấn vẫn giữ nguyên
    $sql = "
        SELECT 
            gd.id AS id_giao_dich, gd.ngay_giao_dich,
            bds.tieu_de AS bds_tieu_de, bds.gia AS bds_gia,
            info_mua.ho_ten AS nguoi_mua_ten, nd_mua.email AS nguoi_mua_email,
            info_ban.ho_ten AS nguoi_ban_ten, nd_ban.email AS nguoi_ban_email
        FROM giao_dich gd
        JOIN nguoi_dung nd_mua ON gd.id_nguoi_dung = nd_mua.id
        LEFT JOIN info_nguoi_dung info_mua ON nd_mua.id = info_mua.id_nguoi_dung
        JOIN nguoi_dung nd_ban ON gd.id_nguoi_ban = nd_ban.id
        LEFT JOIN info_nguoi_dung info_ban ON nd_ban.id = info_ban.id_nguoi_dung
        JOIN bat_dong_san bds ON gd.id_bds = bds.id
        WHERE gd.trang_thai = 'hoantat'
        ORDER BY gd.ngay_giao_dich DESC
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
<title>Lịch sử Giao dịch</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root { font-family: 'Be Vietnam Pro', sans-serif; }
</style>
</head>

<body class="bg-gray-50 text-gray-800">

<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Khách hàng đã mua</h1>
        <p class="text-gray-500 mt-1">Xem lại tất cả các giao dịch đã hoàn tất trên hệ thống.</p>
    </header>

    <div class="mb-6 p-4 bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col sm:flex-row items-center gap-4">
        <div class="relative w-full sm:w-1/2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" placeholder="Tìm kiếm theo tên BĐS, người mua..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
        </div>
        <input type="date" class="w-full sm:w-auto border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
        <button class="w-full sm:w-auto bg-blue-600 text-white font-semibold px-4 py-2 rounded-md hover:bg-blue-700 transition shadow">Lọc</button>
    </div>

    <?php if ($error_msg): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg" role="alert">
            <p><?= htmlspecialchars($error_msg) ?></p>
        </div>
    <?php endif; ?>

    <div class="space-y-4">
        <?php if (!empty($giao_dich_hoan_tat)): ?>
            <?php foreach($giao_dich_hoan_tat as $gd): ?>
                <a href="/hoa-don.php?id=<?= htmlspecialchars($gd['id_giao_dich']) ?>" 
                   class="block bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden transition hover:shadow-lg hover:border-blue-400 group">
                    
                    <div class="p-5">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-2">
                            <h2 class="font-bold text-lg text-gray-800 leading-tight group-hover:text-blue-600 transition-colors">
                                <?= htmlspecialchars($gd['bds_tieu_de']) ?>
                            </h2>
                            <span class="text-xs font-bold bg-green-100 text-green-800 px-3 py-1 rounded-full flex-shrink-0">
                                ✔️ Hoàn tất
                            </span>
                        </div>
                        </div>

                    <div class="px-5 pb-5 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-5 text-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold">BÊN MUA</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($gd['nguoi_mua_ten']) ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold">BÊN BÁN (MÔI GIỚI)</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($gd['nguoi_ban_ten']) ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                           <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                           </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold">NGÀY HOÀN TẤT</p>
                                <p class="font-semibold text-gray-800"><?= date("d/m/Y", strtotime($gd['ngay_giao_dich'])) ?></p>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php elseif (!$error_msg): ?>
            <div class="text-center py-16 text-gray-500 flex flex-col items-center bg-white rounded-lg border border-gray-200">
                <svg class="h-16 w-16 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                <p class="font-semibold mt-4 text-lg">Chưa có giao dịch nào</p>
                <p class="text-sm mt-1">Hiện tại chưa có giao dịch nào được hoàn tất trong hệ thống.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>