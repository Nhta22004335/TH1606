<?php
// PHẦN LOGIC PHP CỦA BẠN - GIỮ NGUYÊN HOÀN TOÀN
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
    ORDER BY
        CASE yc.trang_thai WHEN 'choxuly' THEN 1 WHEN 'daduyet' THEN 2 WHEN 'dahuy' THEN 3 ELSE 4 END ASC,
        yc.ngay_tao DESC
";
$stmt_yeucau = $pdo->query($sql_yeucau);
$yeucau_list = $stmt_yeucau->fetchAll(PDO::FETCH_ASSOC);

$sql_agents = "
    SELECT nd.id, info.ho_ten
    FROM nguoi_dung nd
    JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
    JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung
    JOIN quyen q ON pq.id_quyen = q.id
    WHERE q.vai_tro = 'moigioi' ORDER BY info.ho_ten ASC
";
$stmt_agents = $pdo->query($sql_agents);
$agents = $stmt_agents->fetchAll(PDO::FETCH_ASSOC);

function getRequestBadgeInfo($type, $value) {
    $map = [
        'loai' => [
            'mua' => ['text' => 'Mua', 'class' => 'bg-green-100 text-green-800'],
            'ban' => ['text' => 'Bán', 'class' => 'bg-blue-100 text-blue-800'],
            'thue' => ['text' => 'Thuê', 'class' => 'bg-yellow-100 text-yellow-800'],
        ],
        'trang_thai' => [
            'choxuly' => ['text' => 'Chờ xử lý', 'class' => 'bg-orange-100 text-orange-800 animate-pulse'],
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<div class="space-y-8">
    
    <header>
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Quản lý Yêu cầu</h1>
                <p class="mt-1 text-sm text-gray-500">Xem, xử lý và phân công các yêu cầu từ khách hàng.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button type="button" class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md shadow-sm hover:bg-gray-900 transition">
                    <i class="fas fa-plus fa-sm"></i> Thêm yêu cầu
                </button>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-3 gap-2 sm:gap-4">
    
        <div class="bg-white p-3 sm:p-4 rounded-lg shadow-sm border flex items-center gap-3">
            <div class="flex-shrink-0 bg-orange-100 p-2 rounded-full">
                <i class="fas fa-hourglass-half text-orange-600"></i>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Chờ xử lý</dt>
                <dd class="mt-0.5 text-2xl font-semibold text-gray-900"><?= $stats['pending'] ?></dd>
            </div>
        </div>
        
        <div class="bg-white p-3 sm:p-4 rounded-lg shadow-sm border flex items-center gap-3">
            <div class="flex-shrink-0 bg-green-100 p-2 rounded-full">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Đã duyệt</dt>
                <dd class="mt-0.5 text-2xl font-semibold text-gray-900"><?= $stats['approved'] ?></dd>
            </div>
        </div>
        
        <div class="bg-white p-3 sm:p-4 rounded-lg shadow-sm border flex items-center gap-3">
            <div class="flex-shrink-0 bg-blue-100 p-2 rounded-full">
                <i class="fas fa-layer-group text-blue-600"></i>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Tổng cộng</dt>
                <dd class="mt-0.5 text-2xl font-semibold text-gray-900"><?= $stats['total'] ?></dd>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Khách hàng</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Loại YC</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">BĐS Quan tâm</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Ngày tạo</th>
                        <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($yeucau_list as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row["ho_ten"] ?? $row["nguoi_dung"]) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php $badge = getRequestBadgeInfo('loai', $row['loai']); ?>
                                <span class="px-2.5 py-0.5 <?= $badge['class'] ?> rounded-full text-xs font-medium"><?= $badge['text'] ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs" title="<?= htmlspecialchars($row["tieu_de"] ?? '') ?>"><?= htmlspecialchars($row["tieu_de"] ?? "—") ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php $badge = getRequestBadgeInfo('trang_thai', $row['trang_thai']); ?>
                                <span class="px-2.5 py-0.5 <?= $badge['class'] ?> rounded-full text-xs font-medium"><?= $badge['text'] ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d/m/Y', strtotime($row["ngay_tao"])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center items-center gap-4">
                                    <button onclick='requestManager.viewDetails(<?= htmlspecialchars(json_encode($row)) ?>)' class="text-gray-400 hover:text-indigo-600 transition" title="Xem & Phân công">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($row["trang_thai"] == "choxuly"): ?>
                                        <button onclick="requestManager.updateStatus('<?= $row['id'] ?>', 'daduyet')" class="text-gray-400 hover:text-green-600 transition" title="Đánh dấu đã duyệt">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="requestManager.updateStatus('<?= $row['id'] ?>', 'dahuy')" class="text-gray-400 hover:text-red-600 transition" title="Hủy yêu cầu">
                                            <i class="fas fa-times-circle"></i>
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
            <div id="modalFooter" class="bg-gray-50 px-6 py-4 rounded-b-xl">
               <label for="agent_id" class="block text-sm font-medium text-gray-800">Phân công cho Môi giới</label>
               <div class="mt-2 flex gap-3">
                   <select id="agentSelect" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                       <option value="">-- Chọn môi giới --</option>
                       <?php foreach($agents as $agent): ?>
                           <option value="<?= $agent['id'] ?>"><?= htmlspecialchars($agent['ho_ten']) ?></option>
                       <?php endforeach; ?>
                   </select>
                   <button id="assignButton" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed">Lưu</button>
               </div>
            </div>
        </div>
    </div>
</div>

<script>
    const requestManager = {
        agents: <?= json_encode($agents) ?>,
        currentRequest: {},
        
        viewDetails(request) {
            this.currentRequest = request;
            
            // Lấy các element trong modal
            const modal = document.getElementById('detailModal');
            const modalFooter = document.getElementById('modalFooter');
            const assignButton = document.getElementById('assignButton');
            const agentSelect = document.getElementById('agentSelect');

            // Đổ dữ liệu vào modal
            document.getElementById('modalRequestId').textContent = request.id;
            document.getElementById('modalCustomerName').textContent = request.ho_ten || request.nguoi_dung;
            document.getElementById('modalRequestDate').textContent = new Date(request.ngay_tao).toLocaleDateString('vi-VN');
            document.getElementById('modalPropertyName').textContent = request.tieu_de || 'Chưa có BĐS cụ thể';
            document.getElementById('modalDescription').textContent = request.mo_ta_chi_tiet || 'Không có mô tả.';
            
            // Xử lý hiển thị phần phân công
            if (request.trang_thai === 'choxuly') {
                modalFooter.classList.remove('hidden');
                agentSelect.value = ''; // Reset select
                assignButton.disabled = true; // Vô hiệu hóa nút lưu
            } else {
                modalFooter.classList.add('hidden');
            }

            // Mở modal
            modal.classList.remove('hidden');
        },
        
        closeModal() {
            document.getElementById('detailModal').classList.add('hidden');
        },
        
        handleAction(url, formData, confirmMsg) {
            if (confirmMsg && !confirm(confirmMsg)) return;
            
            fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams(formData).toString()
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success" || data.status === "warning") {
                    location.reload();
                }
            })
            .catch(err => console.error("Lỗi:", err));
        },
        
        updateStatus(id, newStatus) {
            const formData = { id, newStatus };
            const confirmMsg = `Bạn có chắc muốn chuyển yêu cầu #${id} sang trạng thái '${newStatus.toUpperCase()}'?`;
            this.handleAction("../../models/cn_trangthai_yc.php", formData, confirmMsg);
        },
        
        assignAgent() {
            const agentSelect = document.getElementById('agentSelect');
            const assignedAgentId = agentSelect.value;
            
            if (!assignedAgentId) {
                alert('Vui lòng chọn một môi giới để phân công.');
                return;
            }
            
            const formData = {
                request_id: this.currentRequest.id,
                agent_id: assignedAgentId
            };
            const confirmMsg = `Bạn có chắc muốn phân công yêu cầu #${this.currentRequest.id} cho môi giới đã chọn?`;
            this.handleAction("../../models/cn_phancong_yc.php", formData, confirmMsg); 
        }
    };

    // Thêm event listeners sau khi DOM đã tải
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('detailModal');
        const modalContent = document.getElementById('modalContent');
        const agentSelect = document.getElementById('agentSelect');
        const assignButton = document.getElementById('assignButton');

        // Đóng modal khi click ra ngoài
        modal.addEventListener('click', (event) => {
            if (!modalContent.contains(event.target)) {
                requestManager.closeModal();
            }
        });

        // Kích hoạt/Vô hiệu hóa nút Lưu khi chọn môi giới
        agentSelect.addEventListener('change', () => {
            assignButton.disabled = !agentSelect.value;
        });

        // Gán sự kiện cho nút Lưu
        assignButton.addEventListener('click', () => {
            requestManager.assignAgent();
        });
    });
</script>

</body>
</html>