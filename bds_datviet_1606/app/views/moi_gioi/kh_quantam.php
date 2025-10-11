<?php
// Giả lập môi trường và kết nối CSDL để có thể xem trước
// Trong môi trường thực tế, bạn sẽ sử dụng file kết nối thật
require_once "../../../config/database.php";
$pdo = ketnoicsdl();


// Lấy id BĐS từ GET
$khachhang_quantam = [];
$error_msg = null;

// Hàm kiểm tra UUID hợp lệ
function is_valid_uuid($uuid) {
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
}

try {
        // Câu truy vấn được cập nhật để nhóm theo khách hàng và đếm số lần quan tâm
        $sql = "
            SELECT
                nd.id,
                info.ho_ten,
                nd.email,
                COUNT(yc.id) AS so_lan_quan_tam,
                MAX(yc.ngay_tao) AS ngay_quan_tam_moi_nhat
            FROM
                yeu_cau yc
            JOIN
                nguoi_dung nd ON yc.id_nguoi_dung = nd.id
            LEFT JOIN
                info_nguoi_dung info ON nd.id = info.id_nguoi_dung
            GROUP BY
                nd.id, info.ho_ten, nd.email
            ORDER BY
                ngay_quan_tam_moi_nhat DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $khachhang_quantam = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    $error_msg = "Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage();
    $khachhang_quantam = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Khách hàng quan tâm BĐS</title>
<script src="https://cdn.tailwindcss.com"></script>
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
        <div class="max-w-6xl mx-auto">
            <header class="mb-8 pb-4 border-b border-slate-200">
                <h1 class="text-3xl font-bold text-slate-900">Khách Hàng Quan Tâm</h1>
            </header>

            <?php if ($error_msg): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6">
                    <p><?= htmlspecialchars($error_msg) ?></p>
                </div>
            <?php endif; ?>

            <div class="space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar pr-2">
                <?php if (!empty($khachhang_quantam)): ?>
                    <?php foreach($khachhang_quantam as $kh): ?>
                        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4 hover:shadow-md transition">
                            <div class="flex items-center gap-4 flex-grow">
                                <div class="relative flex-shrink-0">
                                    <div class="w-11 h-11 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-semibold text-lg">
                                        <span><?= htmlspecialchars(mb_substr($kh['ho_ten'] ?? '?', 0, 1)) ?></span>
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 bg-indigo-600 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center border-2 border-white" title="Số lần quan tâm">
                                        <?= htmlspecialchars($kh['so_lan_quan_tam']) ?>
                                    </div>
                                </div>
                                <div class="text-sm min-w-0">
                                    <h2 class="font-semibold text-slate-800 truncate"><?= htmlspecialchars($kh['ho_ten'] ?? '[Chưa có tên]') ?></h2>
                                    <p class="text-slate-500 text-xs truncate"><?= htmlspecialchars($kh['email']) ?></p>
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 text-left sm:text-right flex-shrink-0 w-full sm:w-auto border-t sm:border-0 pt-3 sm:pt-0">
                                <p class="font-medium text-slate-700">Lần cuối quan tâm</p>
                                <p class="text-xs mt-1"><?= date("d/m/Y H:i", strtotime($kh['ngay_quan_tam_moi_nhat'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (!$error_msg): ?>
                    <div class="text-center py-16 text-slate-500 flex flex-col items-center bg-white rounded-xl border border-slate-200">
                         <svg class="h-16 w-16 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p class="font-semibold mt-4">Chưa có khách hàng quan tâm</p>
                        <p class="text-sm mt-1">Bất động sản này hiện chưa có ai thể hiện sự quan tâm.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>