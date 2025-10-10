<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    // Lấy danh sách yêu cầu
$sql_yeucau = "
    SELECT yc.id, yc.loai, yc.trang_thai, yc.ngay_tao, yc.mo_ta_chi_tiet,
           nd.ten_dang_nhap AS nguoi_dung,
           bds.tieu_de, info.ho_ten
    FROM yeu_cau yc
    JOIN nguoi_dung nd ON yc.id_nguoi_dung = nd.id
    LEFT JOIN bat_dong_san bds ON yc.id_bds = bds.id
    LEFT JOIN info_nguoi_dung info ON info.id_nguoi_dung = nd.id
    ORDER BY
        CASE yc.trang_thai
            WHEN 'choxuly' THEN 1
            WHEN 'daduyet' THEN 2
            WHEN 'dahuy'   THEN 3
            ELSE 4
        END ASC, -- Sắp xếp theo thứ tự ưu tiên
        yc.ngay_tao DESC -- Sau đó sắp xếp theo ngày tạo mới nhất
";
    $stmt_yeucau = $pdo->query($sql_yeucau);
    $yeucau_list = $stmt_yeucau->fetchAll(PDO::FETCH_ASSOC);

    // Lấy danh sách môi giới để phân công
    $sql_agents = "
        SELECT nd.id, info.ho_ten
        FROM nguoi_dung nd
        JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
        JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung
        JOIN quyen q ON pq.id_quyen = q.id
        WHERE q.vai_tro = 'moigioi'
        ORDER BY info.ho_ten ASC
    ";
    $stmt_agents = $pdo->query($sql_agents);
    $agents = $stmt_agents->fetchAll(PDO::FETCH_ASSOC);

    // --- TỐI ƯU CODE: Hàm Helper để lấy thông tin badge ---
    function getRequestBadgeInfo($type, $value) {
        $map = [
            'loai' => [
                'mua' => ['text' => 'Mua', 'class' => 'bg-green-100 text-green-700'],
                'ban' => ['text' => 'Bán', 'class' => 'bg-blue-100 text-blue-700'],
                'thue' => ['text' => 'Thuê', 'class' => 'bg-yellow-100 text-yellow-700'],
            ],
            'trang_thai' => [
                'choxuly' => ['text' => 'Chờ xử lý', 'class' => 'bg-orange-100 text-orange-800 animate-pulse'],
                'daduyet' => ['text' => 'Đã duyệt', 'class' => 'bg-green-100 text-green-800'],
                'dahuy' => ['text' => 'Đã hủy', 'class' => 'bg-red-100 text-red-700'],
            ]
        ];
        return $map[$type][$value] ?? ['text' => ucfirst($value), 'class' => 'bg-gray-100 text-gray-700'];
    }

    // --- TÍNH TOÁN THỐNG KÊ ---
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        summary::marker { content: ''; }
    </style>
</head>
<body class="h-full">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="requestManager(<?= htmlspecialchars(json_encode($agents)) ?>)">
    
    <header>
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold leading-6 text-slate-900">Quản lý Yêu cầu</h1>
                <p class="mt-2 text-sm text-slate-600">Xem, xử lý và phân công các yêu cầu từ khách hàng.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16">
                <button type="button" class="inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fa-solid fa-plus"></i> Thêm yêu cầu mới
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Chờ xử lý</dt><dd class="mt-1 text-3xl font-semibold text-orange-500"><?= $stats['pending'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Đã duyệt</dt><dd class="mt-1 text-3xl font-semibold text-green-600"><?= $stats['approved'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Tổng cộng</dt><dd class="mt-1 text-3xl font-semibold text-slate-900"><?= $stats['total'] ?></dd></dl></div></div>
        </div>
    </header>

    <div class="mt-8 bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Khách hàng</th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Loại YC</th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">BĐS Quan tâm</th>
                        <th scope="col" class="px-6 py-3 text-center text-sm font-semibold text-slate-600">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Ngày tạo</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Hành động</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($yeucau_list as $row): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap"><div class="font-medium text-slate-800"><?= htmlspecialchars($row["ho_ten"] ?? $row["nguoi_dung"]) ?></div></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php $badge = getRequestBadgeInfo('loai', $row['loai']); ?>
                                <span class="px-3 py-1 <?= $badge['class'] ?> rounded-full text-xs font-semibold"><?= $badge['text'] ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 truncate max-w-xs" title="<?= htmlspecialchars($row["tieu_de"] ?? '') ?>"><?= htmlspecialchars($row["tieu_de"] ?? "—") ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php $badge = getRequestBadgeInfo('trang_thai', $row['trang_thai']); ?>
                                <span class="px-3 py-1 <?= $badge['class'] ?> rounded-full text-xs font-semibold"><?= $badge['text'] ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500"><?= date('d/m/Y', strtotime($row["ngay_tao"])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <details class="relative inline-block text-left">
                                    <summary class="list-none cursor-pointer p-2 text-slate-500 hover:text-slate-800"><i class="fa-solid fa-ellipsis-vertical"></i></summary>
                                    <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                        <div class="py-1" role="menu">
                                            <a href="#" @click.prevent="viewDetails(<?= htmlspecialchars(json_encode($row)) ?>)" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" role="menuitem">Xem & Phân công</a>
                                            <?php if($row["trang_thai"] == "choxuly"): ?>
                                                <a href="#" @click.prevent="updateStatus(<?= $row['id'] ?>, 'daduyet')" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" role="menuitem">Đánh dấu đã duyệt</a>
                                                <a href="#" @click.prevent="updateStatus(<?= $row['id'] ?>, 'dahuy')" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem">Hủy yêu cầu</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="openDetail" x-transition.opacity x-cloak class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div @click.away="closeModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all"
             x-show="openDetail" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            <div class="p-6">
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-indigo-600"></i> Chi tiết Yêu cầu #<span x-text="currentRequest.id"></span>
                </h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div><dt class="font-medium text-slate-500">Khách hàng</dt><dd class="mt-1 text-slate-800 font-semibold" x-text="currentRequest.ho_ten || currentRequest.nguoi_dung"></dd></div>
                        <div><dt class="font-medium text-slate-500">Ngày tạo</dt><dd class="mt-1 text-slate-800" x-text="new Date(currentRequest.ngay_tao).toLocaleDateString('vi-VN')"></dd></div>
                    </div>
                    <div><dt class="font-medium text-slate-500">BĐS quan tâm</dt><dd class="mt-1 text-slate-800" x-text="currentRequest.tieu_de || 'Chưa có BĐS cụ thể'"></dd></div>
                    <div><dt class="font-medium text-slate-500">Mô tả chi tiết</dt><dd class="mt-1 text-slate-600 italic p-3 bg-slate-50 rounded-md" x-text="currentRequest.mo_ta_chi_tiet || 'Không có mô tả.'"></dd></div>
                </dl>
            </div>
            <div class="bg-slate-50 px-6 py-4 rounded-b-xl" x-show="currentRequest.trang_thai === 'choxuly'">
                 <label for="agent_id" class="block text-sm font-medium text-slate-800">Phân công cho Môi giới</label>
                 <div class="mt-2 flex gap-3">
                     <select x-model="assignedAgentId" id="agent_id" class="block w-full px-3 py-2 bg-white border border-slate-300 rounded-md shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                         <option value="">-- Chọn môi giới --</option>
                         <template x-for="agent in agents" :key="agent.id">
                             <option :value="agent.id" x-text="agent.ho_ten"></option>
                         </template>
                     </select>
                     <button @click="assignAgent()" :disabled="!assignedAgentId" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-400 disabled:cursor-not-allowed">Lưu</button>
                 </div>
            </div>
        </div>
    </div>
</div>

<script>
    function requestManager(agentsData) {
        return {
            openDetail: false,
            currentRequest: {},
            agents: agentsData,
            assignedAgentId: '',

            viewDetails(request) {
                this.currentRequest = request;
                this.assignedAgentId = ''; // Reset khi mở modal mới
                this.openDetail = true;
            },
            
            closeModal() {
                this.openDetail = false;
            },

            // Hàm xử lý chung, có thể mở rộng
            handleAction(url, formData, confirmMsg) {
                if (confirmMsg && !confirm(confirmMsg)) return;
                
                fetch(url, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: formData.toString()
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

            // Cập nhật trạng thái đơn giản
            updateStatus(id, newStatus) {
                const formData = new URLSearchParams();
                formData.append('id', id);
                formData.append('newStatus', newStatus);
                const confirmMsg = `Bạn có chắc muốn chuyển yêu cầu #${id} sang trạng thái '${newStatus.toUpperCase()}'?`;
                this.handleAction("../../models/cn_trangthai_yc.php", formData, confirmMsg);
            },

            // Phân công cho môi giới
            assignAgent() {
                if (!this.assignedAgentId) {
                    alert('Vui lòng chọn một môi giới để phân công.');
                    return;
                }
                const formData = new URLSearchParams();
                formData.append('request_id', this.currentRequest.id);
                formData.append('agent_id', this.assignedAgentId);
                const confirmMsg = `Bạn có chắc muốn phân công yêu cầu #${this.currentRequest.id} cho môi giới đã chọn?`;
                // Lưu ý: đường dẫn này cần được tạo ở phía backend
                this.handleAction("../../models/cn_phancong_yc.php", formData, confirmMsg); 
            }
        };
    }
</script>

</body>
</html>