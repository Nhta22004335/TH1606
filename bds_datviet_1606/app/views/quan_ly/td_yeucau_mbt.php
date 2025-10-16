<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

$sql_yeucau = "
    SELECT yc.id, yc.loai, yc.trang_thai, yc.ngay_tao, yc.mo_ta_chi_tiet,
           nd.ten_dang_nhap AS nguoi_dung,
           bds.tieu_de, info.ho_ten
    FROM yeu_cau yc
    JOIN nguoi_dung nd ON yc.id_nguoi_dung = nd.id
    LEFT JOIN bat_dong_san bds ON yc.id_bds = bds.id
    LEFT JOIN info_nguoi_dung info ON info.id_nguoi_dung = nd.id
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
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Yêu cầu Khách hàng</title>
</head>
<body>

<div class="space-y-8">
    
    <header class="border-b pb-4">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Quản lý Yêu cầu</h1>
                <p class="mt-1 text-sm text-gray-500">Xem và xử lý các yêu cầu từ khách hàng.</p>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-3 gap-2 sm:gap-4">
        <div class="bg-white p-3 sm:p-4 rounded-lg shadow-sm border flex items-center gap-3">
            <div class="flex-shrink-0 bg-orange-100 p-2 rounded-full"><i class="fas fa-hourglass-half text-orange-600"></i></div>
            <div><dt class="text-xs font-medium text-gray-500">Chờ xử lý</dt><dd class="mt-0.5 text-2xl font-semibold text-gray-900"><?= $stats['pending'] ?></dd></div>
        </div>
        <div class="bg-white p-3 sm:p-4 rounded-lg shadow-sm border flex items-center gap-3">
            <div class="flex-shrink-0 bg-green-100 p-2 rounded-full"><i class="fas fa-check-circle text-green-600"></i></div>
            <div><dt class="text-xs font-medium text-gray-500">Đã duyệt</dt><dd class="mt-0.5 text-2xl font-semibold text-gray-900"><?= $stats['approved'] ?></dd></div>
        </div>
        <div class="bg-white p-3 sm:p-4 rounded-lg shadow-sm border flex items-center gap-3">
            <div class="flex-shrink-0 bg-blue-100 p-2 rounded-full"><i class="fas fa-layer-group text-blue-600"></i></div>
            <div><dt class="text-xs font-medium text-gray-500">Tổng cộng</dt><dd class="mt-0.5 text-2xl font-semibold text-gray-900"><?= $stats['total'] ?></dd></div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    </thead>
                <tbody id="requests-table-body" class="divide-y divide-gray-200">
                    <?php foreach ($yeucau_list as $row): ?>
                        <tr id="request-row-<?= $row['id'] ?>" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row["ho_ten"] ?? $row["nguoi_dung"]) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php $badge = getRequestBadgeInfo('loai', $row['loai']); ?>
                                <span class="px-2.5 py-0.5 <?= $badge['class'] ?> rounded-full text-xs font-medium"><?= $badge['text'] ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs" title="<?= htmlspecialchars($row["tieu_de"] ?? '') ?>"><?= htmlspecialchars($row["tieu_de"] ?? "—") ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center status-cell">
                                <?php $badge = getRequestBadgeInfo('trang_thai', $row['trang_thai']); ?>
                                <span class="px-2.5 py-0.5 <?= $badge['class'] ?> rounded-full text-xs font-medium"><?= $badge['text'] ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d/m/Y', strtotime($row["ngay_tao"])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center actions-cell">
                                <div class="flex justify-center items-center gap-4">
                                    <button class="action-btn text-sm text-indigo-600 hover:text-indigo-800 transition" data-action="view" data-request='<?= htmlspecialchars(json_encode($row)) ?>'>
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <?php if($row["trang_thai"] == "choxuly"): ?>
                                        <button class="action-btn text-sm text-green-600 hover:text-green-800 transition" data-id="<?= $row['id'] ?>" data-action="daduyet">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="action-btn text-sm text-red-600 hover:text-red-800 transition" data-id="<?= $row['id'] ?>" data-action="dahuy">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    <?php elseif($row["trang_thai"] == "daduyet" || $row["trang_thai"] == "dahuy"): ?>
                                        <button class="action-btn text-sm text-yellow-400 hover:text-yellow-500 transition" data-id="<?= $row['id'] ?>" data-action="choxuly">
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

    <div id="detailModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 hidden">
        <div id="modalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Chi tiết Yêu cầu #<span id="modalRequestId"></span></h2>
                <button onclick="requestManager.closeModal()" class="p-2 text-gray-400 hover:text-gray-700 rounded-full"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6">
                <dl class="space-y-4 text-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><dt class="font-medium text-gray-500">Khách hàng</dt><dd id="modalCustomerName" class="mt-1 text-gray-800 font-semibold"></dd></div>
                        <div><dt class="font-medium text-gray-500">Ngày tạo</dt><dd id="modalRequestDate" class="mt-1 text-gray-800"></dd></div>
                    </div>
                    <div><dt class="font-medium text-gray-500">BĐS quan tâm</dt><dd id="modalPropertyName" class="mt-1 text-gray-800"></dd></div>
                    <div><dt class="font-medium text-gray-500">Mô tả chi tiết</dt><dd id="modalDescription" class="mt-1 text-gray-600 italic p-3 bg-gray-50 rounded-md border"></dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- QUẢN LÝ MODAL (Giữ nguyên) ---
        const modal = document.getElementById('detailModal');
        const modalContent = document.getElementById('modalContent');
        const closeModal = () => modal.classList.add('hidden');

        modal.addEventListener('click', (event) => {
            if (!modalContent.contains(event.target)) {
                closeModal();
            }
        });
        document.querySelector('#modalContent button').addEventListener('click', closeModal);

        const viewDetails = (request) => {
            document.getElementById('modalRequestId').textContent = request.id.substring(0, 8);
            document.getElementById('modalCustomerName').textContent = request.ho_ten || request.nguoi_dung;
            document.getElementById('modalRequestDate').textContent = new Date(request.ngay_tao).toLocaleDateString('vi-VN');
            document.getElementById('modalPropertyName').textContent = request.tieu_de || 'Chưa có BĐS cụ thể';
            document.getElementById('modalDescription').textContent = request.mo_ta_chi_tiet || 'Không có mô tả.';
            modal.classList.remove('hidden');
        };

        // --- XỬ LÝ CÁC HÀNH ĐỘNG VỚI FETCH VÀ EVENT DELEGATION ---
        const tableBody = document.getElementById('requests-table-body');
        const apiUrl = '../../models/cn_trangthai_yc.php';

        tableBody.addEventListener('click', async (event) => {
            const targetButton = event.target.closest('.action-btn');
            if (!targetButton) return;

            const action = targetButton.dataset.action;

            // Xử lý xem chi tiết
            if (action === 'view') {
                const requestData = JSON.parse(targetButton.dataset.request);
                viewDetails(requestData);
                return;
            }

            // Xử lý các hành động cập nhật (Duyệt, Hủy, Hoàn tác)
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
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error('Yêu cầu mạng thất bại.');
                const result = await response.json();

                if (result.status === 'success') {
                    const row = document.getElementById(`request-row-${id}`);
                    if (row) {
                        // Cập nhật giao diện với HTML mới từ server
                        row.querySelector('.status-cell').innerHTML = result.newStatusHtml;
                        row.querySelector('.actions-cell').innerHTML = result.newActionsHtml;
                    }
                } else {
                    alert('Lỗi: ' + result.message);
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