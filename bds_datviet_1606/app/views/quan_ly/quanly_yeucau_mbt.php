<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../../../config/database.php";

try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("<div class='p-6 text-xl text-center text-red-700 bg-red-100 rounded-lg'>Lỗi kết nối CSDL: " . $e->getMessage() . "</div>");
}

// === SQL ĐÃ CẬP NHẬT: JOIN ĐỂ LẤY TÊN MÔI GIỚI ===
$sql_yeucau = "
    SELECT 
        yc.id, yc.loai, yc.trang_thai, yc.ngay_tao, yc.mo_ta_chi_tiet,
        yc.id_bds, 
        info_yc.ho_ten AS ten_nguoi_yeu_cau,
        bds.dia_chi_day_du AS bds_dia_chi,
        info_mg.ho_ten AS ten_moi_gioi -- Lấy tên môi giới (chủ sở hữu BĐS)
    FROM yeu_cau yc
    -- Lấy thông tin người gửi yêu cầu
    JOIN nguoi_dung nd_yc ON yc.id_nguoi_dung = nd_yc.id
    LEFT JOIN info_nguoi_dung info_yc ON info_yc.id_nguoi_dung = nd_yc.id
    
    -- Lấy thông tin BĐS (nếu có)
    LEFT JOIN bat_dong_san bds ON yc.id_bds = bds.id
    
    -- Lấy thông tin môi giới (chủ sở hữu BĐS)
    LEFT JOIN nguoi_dung nd_mg ON bds.id_chu_so_huu = nd_mg.id
    LEFT JOIN info_nguoi_dung info_mg ON info_mg.id_nguoi_dung = nd_mg.id

    ORDER BY yc.ngay_tao DESC
";
$stmt_yeucau = $pdo->query($sql_yeucau);
$yeucau_list = $stmt_yeucau->fetchAll(PDO::FETCH_ASSOC);

function getRequestBadgeInfo($type, $value) {
    $map = [
        'loai' => [
            'mua' => ['text' => 'Mua', 'class' => 'bg-green-100 text-green-800'],
            'ban' => ['text' => 'Bán', 'class' => 'bg-blue-100 text-blue-800'],
            'thue' => ['text' => 'Thuê', 'class' => 'bg-yellow-100 text-yellow-800'],
        ],
        'trang_thai' => [
            'choxuly' => ['text' => 'Chờ xử lý', 'class' => 'bg-orange-100 text-orange-800'],
            'daduyet' => ['text' => 'Đã duyệt', 'class' => 'bg-green-100 text-green-800'],
            'dahuy' => ['text' => 'Đã hủy', 'class' => 'bg-red-100 text-red-700'],
        ]
    ];
    return $map[$type][$value] ?? ['text' => ucfirst($value), 'class' => 'bg-gray-100 text-gray-700'];
}

$stats = [
    'pending' => count(array_filter($yeucau_list, fn($yc) => $yc['trang_thai'] === 'choxuly')),
    'approved' => count(array_filter($yeucau_list, fn($yc) => $yc['trang_thai'] === 'daduyet')),
    'total' => count($yeucau_list),
];
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Yêu cầu Khách hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> 
        .modal.hidden { display: none; }
    </style>
</head>
<body>
<div class="max-w-7xl mx-auto space-y-6">
    
    <header class="border-b pb-4">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-500">Quản lý Yêu cầu</h1>
                <p class="mt-1 text-sm text-gray-600">Xem và xử lý các yêu cầu từ khách hàng.</p>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg shadow-sm border flex items-center gap-3">
            <div class="flex-shrink-0 bg-orange-100 p-2.5 rounded-full"><i class="fas fa-hourglass-half text-orange-600 fa-fw"></i></div>
            <div><dt class="text-sm font-medium text-gray-500">Chờ xử lý</dt><dd class="mt-0.5 text-2xl font-semibold text-gray-900"><?= $stats['pending'] ?></dd></div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border flex items-center gap-3">
            <div class="flex-shrink-0 bg-green-100 p-2.5 rounded-full"><i class="fas fa-check-circle text-green-600 fa-fw"></i></div>
            <div><dt class="text-sm font-medium text-gray-500">Đã duyệt</dt><dd class="mt-0.5 text-2xl font-semibold text-gray-900"><?= $stats['approved'] ?></dd></div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border flex items-center gap-3">
            <div class="flex-shrink-0 bg-blue-100 p-2.5 rounded-full"><i class="fas fa-layer-group text-blue-600 fa-fw"></i></div>
            <div><dt class="text-sm font-medium text-gray-500">Tổng cộng</dt><dd class="mt-0.5 text-2xl font-semibold text-gray-900"><?= $stats['total'] ?></dd></div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Khách hàng</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Loại Yêu cầu</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Môi giới Phụ trách</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Trạng thái</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ngày tạo</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody id="requests-table-body" class="divide-y divide-gray-200">
                    <?php if (empty($yeucau_list)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">Chưa có yêu cầu nào.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($yeucau_list as $row): ?>
                        <tr id="request-row-<?= $row['id'] ?>" class="hover:bg-gray-50">
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row["ten_nguoi_yeu_cau"] ?? 'N/A') ?></td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <?php $badge = getRequestBadgeInfo('loai', $row['loai']); ?>
                                <span class="px-2.5 py-0.5 <?= $badge['class'] ?> rounded-full text-xs font-medium"><?= $badge['text'] ?></span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                <?= htmlspecialchars($row["ten_moi_gioi"] ?? "—") ?>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center status-cell">
                                <?php $badge = getRequestBadgeInfo('trang_thai', $row['trang_thai']); ?>
                                <span class="px-2.5 py-0.5 <?= $badge['class'] ?> rounded-full text-xs font-medium"><?= $badge['text'] ?></span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($row["ngay_tao"])) ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-center actions-cell">
                                <div class="flex justify-center items-center gap-4">
                                    <button class="action-btn text-sm text-indigo-600 hover:text-indigo-800 transition" data-action="view" data-request='<?= htmlspecialchars(json_encode($row)) ?>' title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($row["trang_thai"] == "choxuly"): ?>
                                        <button class="action-btn text-sm text-green-600 hover:text-green-800 transition" data-id="<?= $row['id'] ?>" data-action="daduyet" title="Duyệt">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="action-btn text-sm text-red-600 hover:text-red-800 transition" data-id="<?= $row['id'] ?>" data-action="dahuy" title="Hủy">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    <?php elseif($row["trang_thai"] == "daduyet" || $row["trang_thai"] == "dahuy"): ?>
                                        <button class="action-btn text-sm text-yellow-500 hover:text-yellow-600 transition" data-id="<?= $row['id'] ?>" data-action="choxuly" title="Hoàn tác">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="detailModal" class="modal fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 hidden transition-opacity duration-300 opacity-0">
        <div id="modalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-lg transition-transform duration-300 transform scale-95">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Chi tiết Yêu cầu #<span id="modalRequestId"></span></h2>
                <button id="modal-close-btn" class="p-2 text-gray-400 hover:text-gray-700 rounded-full"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6">
                <dl class="space-y-4 text-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><dt class="font-medium text-gray-500">Khách hàng</dt><dd id="modalCustomerName" class="mt-1 text-gray-800 font-semibold"></dd></div>
                        <div><dt class="font-medium text-gray-500">Ngày tạo</dt><dd id="modalRequestDate" class="mt-1 text-gray-800"></dd></div>
                    </div>
                    <div><dt class="font-medium text-gray-500">Môi giới Phụ trách</dt><dd id="modalAgentName" class="mt-1 text-gray-800 font-semibold"></dd></div>
                    <div><dt class="font-medium text-gray-500">BĐS Liên quan</dt><dd id="modalPropertyName" class="mt-1 text-gray-800"></dd></div>
                    <div><dt class="font-medium text-gray-500">Mô tả chi tiết</dt><dd id="modalDescription" class="mt-1 text-gray-600 italic p-3 bg-gray-50 rounded-md border max-h-40 overflow-y-auto"></dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- QUẢN LÝ MODAL ---
        const modal = document.getElementById('detailModal');
        const modalContent = document.getElementById('modalContent');
        const closeModalBtn = document.getElementById('modal-close-btn');

        const closeModal = () => {
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        };
        const openModal = () => {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
            }, 10);
        };

        closeModalBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (!modalContent.contains(event.target)) {
                closeModal();
            }
        });

        const viewDetails = (request) => {
            document.getElementById('modalRequestId').textContent = request.id.substring(0, 8);
            document.getElementById('modalCustomerName').textContent = request.ten_nguoi_yeu_cau || 'N/A';
            document.getElementById('modalRequestDate').textContent = new Date(request.ngay_tao).toLocaleString('vi-VN');
            document.getElementById('modalAgentName').textContent = request.ten_moi_gioi || 'Chưa rõ'; // Cập nhật tên môi giới
            document.getElementById('modalPropertyName').textContent = request.bds_dia_chi || 'Không liên kết BĐS cụ thể';
            document.getElementById('modalDescription').textContent = request.mo_ta_chi_tiet || 'Không có mô tả.';
            openModal();
        };

        // --- XỬ LÝ HÀNH ĐỘNG (FETCH) ---
        const tableBody = document.getElementById('requests-table-body');
        const apiUrl = '../../models/quanly_yeucau_mbt_qt/cn_trangthai_yeucau_qt.php'; // Đảm bảo đường dẫn này đúng

        tableBody.addEventListener('click', async (event) => {
            const targetButton = event.target.closest('.action-btn');
            if (!targetButton) return;

            const action = targetButton.dataset.action;

            if (action === 'view') {
                const requestData = JSON.parse(targetButton.dataset.request);
                viewDetails(requestData);
                return;
            }

            const id = targetButton.dataset.id;
            const newStatus = action;
            const messages = {
                daduyet: `Bạn có chắc muốn DUYỆT yêu cầu #${id.substring(0,8)}?`,
                dahuy: `Bạn có chắc muốn HỦY yêu cầu #${id.substring(0,8)}?`,
                choxuly: `Bạn có chắc muốn HOÀN TÁC yêu cầu #${id.substring(0,8)}?`
            };

            if (!confirm(messages[newStatus])) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('newStatus', newStatus);

            try {
                const response = await fetch(apiUrl, { method: 'POST', body: formData });
                if (!response.ok) throw new Error('Yêu cầu mạng thất bại.');
                
                const result = await response.json();

                if (result.status === 'success') {
                    const row = document.getElementById(`request-row-${id}`);
                    if (row) {
                        row.querySelector('.status-cell').innerHTML = result.newStatusHtml;
                        row.querySelector('.actions-cell').innerHTML = result.newActionsHtml;
                    }
                } else {
                    // Sửa lỗi cú pháp ở đây:
                    alert('Lỗi: ' + (result.message || 'Cập nhật thất bại'));
                }
            } catch (error) {
                console.error('Lỗi Fetch:', error);
                alert('Đã xảy ra lỗi khi gửi yêu cầu.');
            }
        });
    });
</script>

</body>
</html>