<?php
require_once "../../../config/database.php"; // file chứa hàm ketnoicsdl()
$pdo = ketnoicsdl(); // kết nối CSDL

$id_giao_dich_url = $_GET['id'] ?? null;
$giao_dich = null;
$cac_dot_thanh_toan = [];
$error_msg = null;

if (empty($id_giao_dich_url)) {
    $error_msg = "Không có ID giao dịch nào được cung cấp.";
} else {
    try {
        // Lấy thông tin chi tiết của giao dịch
        $sql_gd = "
            SELECT 
                gd.id AS id_giao_dich, gd.ngay_giao_dich,
                bds.tieu_de AS bds_tieu_de, bds.dia_chi as bds_dia_chi, bds.dien_tich as bds_dien_tich,
                mua_info.ho_ten AS nguoi_mua_ten, mua_nd.email AS nguoi_mua_email, mua_nd.so_dt AS nguoi_mua_sdt, mua_info.dia_chi AS nguoi_mua_dia_chi,
                ban_info.ho_ten AS nguoi_ban_ten, ban_nd.email AS nguoi_ban_email, ban_nd.so_dt AS nguoi_ban_sdt,
                khtt.tong_gia_tri, khtt.so_tien_da_tt, khtt.trang_thai_tt
            FROM giao_dich gd
            JOIN bat_dong_san bds ON gd.id_bds = bds.id
            JOIN nguoi_dung mua_nd ON gd.id_nguoi_dung = mua_nd.id
            LEFT JOIN info_nguoi_dung mua_info ON mua_nd.id = mua_info.id_nguoi_dung
            JOIN nguoi_dung ban_nd ON gd.id_nguoi_ban = ban_nd.id
            LEFT JOIN info_nguoi_dung ban_info ON ban_nd.id = ban_info.id_nguoi_dung
            LEFT JOIN ke_hoach_thanh_toan khtt ON gd.id = khtt.id_giao_dich
            WHERE gd.id = :id_giao_dich
        ";
        $stmt_gd = $pdo->prepare($sql_gd);
        $stmt_gd->execute([':id_giao_dich' => $id_giao_dich_url]);
        $giao_dich = $stmt_gd->fetch(PDO::FETCH_ASSOC);

        if (!$giao_dich) {
            $error_msg = "Không tìm thấy giao dịch với ID này.";
        } else {
            // Lấy chi tiết các đợt thanh toán
            $sql_dtt = "
                SELECT lan_tt, ngay_tt, so_tien_tt, phuong_thuc
                FROM dot_thanh_toan
                WHERE id_giao_dich = :id_giao_dich
                ORDER BY lan_tt ASC
            ";
            $stmt_dtt = $pdo->prepare($sql_dtt);
            $stmt_dtt->execute([':id_giao_dich' => $id_giao_dich_url]);
            $cac_dot_thanh_toan = $stmt_dtt->fetchAll(PDO::FETCH_ASSOC);
        }

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
<title>Hóa đơn Giao dịch</title>

<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root { font-family: 'Be Vietnam Pro', sans-serif; }
    @media print {
        body { -webkit-print-color-adjust: exact; }
        .no-print { display: none; }
    }
</style>
</head>
<body class="bg-gray-100">
<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">

    <div class="mb-6 flex justify-between items-center no-print">
        <a href="trangchu.php?page=../moi_gioi/kh_damua" 
   class="inline-flex items-center justify-center w-10 h-10 bg-gray-800 text-white rounded-full shadow hover:bg-gray-900 transition transform hover:-translate-y-0.5 hover:scale-105">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
</a>

        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm">
                🖨️ In hóa đơn
            </button>
        </div>
    </div>

    <?php if ($error_msg): ?>
        <div class="bg-red-100 text-red-800 p-4 rounded-lg">
            <p><?= htmlspecialchars($error_msg) ?></p>
        </div>
    <?php elseif ($giao_dich): ?>
        <?php
        // ÁP DỤNG NULL COALESCING CHO GIÁ TRỊ TIỀN TỆ
        $tong_gia_tri = $giao_dich['tong_gia_tri'] ?? 0;
        $so_tien_da_tt = $giao_dich['so_tien_da_tt'] ?? 0;
        $con_lai = $tong_gia_tri - $so_tien_da_tt;
        ?>
        <div class="bg-white rounded-lg shadow-lg p-8 sm:p-12">
            <header class="flex justify-between items-start pb-6 border-b-2 border-gray-200">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">HÓA ĐƠN GIAO DỊCH</h1>
                    <p class="text-gray-500 text-sm mt-2">Mã HĐ: <?= htmlspecialchars($giao_dich['id_giao_dich']) ?></p>
                </div>
                <div class="text-right">
                    <p class="text-gray-500 font-semibold">Ngày giao dịch</p>
                    <p class="text-gray-800 font-medium"><?= date("d/m/Y", strtotime($giao_dich['ngay_giao_dich'])) ?></p>
                </div>
            </header>

            <section class="grid grid-cols-1 sm:grid-cols-2 gap-8 my-8">
                <div>
                    <h2 class="font-bold text-gray-500 text-sm uppercase tracking-wider">BÊN BÁN (MÔI GIỚI)</h2>
                    <p class="font-semibold text-gray-800 text-lg mt-2"><?= htmlspecialchars($giao_dich['nguoi_ban_ten']) ?></p>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($giao_dich['nguoi_ban_email']) ?></p>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($giao_dich['nguoi_ban_sdt']) ?></p>
                </div>
                <div>
                    <h2 class="font-bold text-gray-500 text-sm uppercase tracking-wider">BÊN MUA</h2>
                    <p class="font-semibold text-gray-800 text-lg mt-2"><?= htmlspecialchars($giao_dich['nguoi_mua_ten']) ?></p>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($giao_dich['nguoi_mua_dia_chi']) ?></p>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($giao_dich['nguoi_mua_email']) ?></p>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($giao_dich['nguoi_mua_sdt']) ?></p>
                </div>
            </section>

            <section class="mt-10">
                <h3 class="font-semibold text-gray-800 text-lg mb-4">Chi tiết Bất động sản</h3>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <p class="font-bold text-gray-900"><?= htmlspecialchars($giao_dich['bds_tieu_de']) ?></p>
                    <p class="text-sm text-gray-600">Địa chỉ: <?= htmlspecialchars($giao_dich['bds_dia_chi']) ?></p>
                    <p class="text-sm text-gray-600">Diện tích: <?= htmlspecialchars($giao_dich['bds_dien_tich']) ?> m²</p>
                </div>
            </section>

            <section class="mt-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 text-sm">
                            <th class="font-semibold p-3">Mô tả</th>
                            <th class="font-semibold p-3 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="p-3">Giá trị Bất động sản</td>
                            <td class="p-3 text-right">
                                <?= number_format($tong_gia_tri, 0, ',', '.') ?> VNĐ
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="p-3 font-bold text-lg text-right text-gray-800">TỔNG CỘNG</td>
                            <td class="p-3 font-bold text-lg text-right text-blue-600"><?= number_format($tong_gia_tri, 0, ',', '.') ?> VNĐ</td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            <section class="mt-10">
                <h3 class="font-semibold text-gray-800 text-lg mb-4">Lịch sử thanh toán</h3>
                <?php if (!empty($cac_dot_thanh_toan)): ?>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="p-3 text-left font-semibold text-gray-600">Lần TT</th>
                                    <th class="p-3 text-left font-semibold text-gray-600">Ngày</th>
                                    <th class="p-3 text-left font-semibold text-gray-600">Phương thức</th>
                                    <th class="p-3 text-right font-semibold text-gray-600">Số tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($cac_dot_thanh_toan as $dot): ?>
                                    <tr>
                                        <td class="p-3">Đợt <?= htmlspecialchars($dot['lan_tt']) ?></td>
                                        <td class="p-3"><?= date("d/m/Y", strtotime($dot['ngay_tt'])) ?></td>
                                        <td class="p-3"><?= htmlspecialchars($dot['phuong_thuc']) ?></td>
                                        <td class="p-3 text-right font-medium"><?= number_format($dot['so_tien_tt'] ?? 0, 0, ',', '.') ?> VNĐ</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 grid sm:grid-cols-2 gap-4 text-right">
                        <div></div>
                        <div>
                            <div class="flex justify-between py-2 border-b">
                                <span class="text-gray-600">Đã thanh toán:</span>
                                <span class="font-semibold text-green-600"><?= number_format($so_tien_da_tt, 0, ',', '.') ?> VNĐ</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600">Còn lại:</span>
                                <span class="font-semibold text-red-600"><?= number_format($con_lai, 0, ',', '.') ?> VNĐ</span>
                            </div>
                            <div class="flex justify-between py-2 border-t mt-2">
                                <span class="text-gray-600 font-bold">Trạng thái:</span>
                                <span class="font-bold <?= ($con_lai == 0) ? 'text-green-700' : 'text-orange-600' ?>">
                                    <?= htmlspecialchars($giao_dich['trang_thai_tt'] ?? 'Chưa khởi tạo KHTT') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-sm italic">Chưa có lịch sử thanh toán cho giao dịch này.</p>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>
</div>
</body>
</html>