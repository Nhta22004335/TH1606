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
            // Dữ liệu giả lập đã được nhóm lại theo yêu cầu
            return [
                [
                    'id' => '7a6fa374-5628-4870-be48-a4ea18aef621',
                    'ho_ten' => 'Trương Quốc Đặng',
                    'email' => 'dang.tq@example.com',
                    'so_lan_quan_tam' => 5,
                    'ngay_quan_tam_moi_nhat' => '2025-10-09 11:00:00'
                ],
                [
                    'id' => 'ea5c0d77-9ce2-4309-b0e7-cbe579f9209d',
                    'ho_ten' => 'Lê Ngọc Quỳnh',
                    'email' => 'quynh.ln@example.com',
                    'so_lan_quan_tam' => 3,
                    'ngay_quan_tam_moi_nhat' => '2025-10-09 10:30:00'

                ],
                [
                    'id' => 'ab76fa3c-893e-487d-983f-d8429ee95436',
                    'ho_ten' => 'Nguyễn Tuấn Anh',
                    'email' => 'anh.nt@example.com',
                    'so_lan_quan_tam' => 1,
                    'ngay_quan_tam_moi_nhat' => '2025-10-08 15:00:00'
                ],
            ];
        }
    }
    $pdo = new MockPDO();
}

// Lấy id BĐS từ GET
$id_bds = $_GET['id_bds'] ?? '9b17fb30-8c6e-4494-920a-cbdd1621ee20'; // UUID BĐS mẫu để xem trước
$khachhang_quantam = [];
$error_msg = null;

// Hàm kiểm tra UUID hợp lệ
function is_valid_uuid($uuid) {
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
}

if (!is_valid_uuid($id_bds)) {
    $error_msg = "Mã bất động sản không hợp lệ. Vui lòng kiểm tra lại URL.";
} else {
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
            WHERE
                yc.id_bds = :id_bds
            GROUP BY
                nd.id, info.ho_ten, nd.email
            ORDER BY
                ngay_quan_tam_moi_nhat DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_bds' => $id_bds]);
        $khachhang_quantam = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_msg = "Lỗi truy vấn CSDL: " . $e->getMessage();
    }
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

