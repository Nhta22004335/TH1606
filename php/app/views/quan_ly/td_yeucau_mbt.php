<?php

    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $sql = "
        SELECT yc.id, yc.loai, yc.trang_thai, yc.ngay_tao, yc.mo_ta_chi_tiet,
            nd.ten_dang_nhap AS nguoi_dung,
            bds.tieu_de, info.ho_ten
        FROM yeu_cau yc
        JOIN nguoi_dung nd ON yc.id_nguoi_dung = nd.id
        LEFT JOIN bat_dong_san bds ON yc.id_bds = bds.id
        LEFT JOIN info_nguoi_dung info ON info.id_nguoi_dung = nd.id
        ORDER BY yc.ngay_tao DESC
    ";

    $stmt = $pdo->query($sql);
    $yeucau = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
?>

<style>
    [x-cloak] { display: none !important; }
</style>

<div x-data="{ 
    openDetail: false, 
    currentRequest: {}, // Lưu dữ liệu chi tiết của yêu cầu đang xem
    
    // Hàm mở Modal và truyền dữ liệu
    viewDetails(request) {
        this.currentRequest = request;
        this.openDetail = true;
    }
}" class="p-6">
    
    <script>
        function updateStatus(id, newStatus) {
            if (!confirm(`Bạn có chắc chắn muốn chuyển yêu cầu ID ${id} sang trạng thái ${newStatus.toUpperCase()}?`)) {
                return;
            }
            
            // 1. Tạo dữ liệu form
            const formData = new URLSearchParams();
            formData.append('id', id);
            // Tên tham số này phải khớp với tên trong PHP: $trang_thai = trim($_POST['newStatus'] ?? '');
            formData.append('newStatus', newStatus); 
            
            // 2. Gọi AJAX/Fetch
            fetch("../../models/cn_trangthai_yc.php", { // Đảm bảo đường dẫn này chính xác
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
            .catch(err => console.error("Lỗi cập nhật:", err));
        }
    </script>
    
    <h1 class="flex text-2xl items-center font-bold mb-6 text-gray-600 border-l-4 border-indigo-500 pl-3 bg-white">
        <img src="../../../public/assets/anhht/0/rfp.gif" style="width: 40px; height: 40px; margin-right: 10px;">
        Quản lý Yêu cầu Khách hàng
    </h1>

    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700">Khách hàng</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700">Loại YC</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700">BĐS Quan tâm</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700">Trạng thái</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700">Ngày tạo</th>
                    <th class="py-3 px-4 text-center text-sm font-medium text-gray-700">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($yeucau as $row): ?>
                <tr class="hover:bg-indigo-50 transition duration-150">
                    <td class="py-3 px-4 text-gray-700"><?= htmlspecialchars($row["ho_ten"] ?? $row["nguoi_dung"]) ?></td>
                    <td class="py-3 px-4">
                        <?php 
                            $type_text = ""; $type_class = "";
                            switch($row["loai"]) {
                                case "mua": $type_text = "Mua"; $type_class = "bg-green-100 text-green-700"; break;
                                case "ban": $type_text = "Bán"; $type_class = "bg-blue-100 text-blue-700"; break;
                                default: $type_text = "Thuê"; $type_class = "bg-yellow-100 text-yellow-700";
                            }
                        ?>
                        <span class="px-3 py-1 <?= $type_class ?> rounded-full text-xs font-semibold"><?= $type_text ?></span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-600 truncate max-w-xs"><?= htmlspecialchars($row["tieu_de"] ?? "—") ?></td>
                    <td class="py-3 px-4">
                        <?php 
                            $status_text = ""; $status_class = "";
                            switch($row["trang_thai"]) {
                                case "choxuly": $status_text = "Chờ xử lý"; $status_class = "bg-orange-100 text-orange-700"; break;
                                case "daduyet": $status_text = "Đã duyệt"; $status_class = "bg-green-100 text-green-700"; break;
                                default: $status_text = "Đã hủy"; $status_class = "bg-red-100 text-red-700";
                            }
                        ?>
                        <span class="px-3 py-1 <?= $status_class ?> rounded-full text-xs font-semibold"><?= $status_text ?></span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-500"><?= date('d/m/Y', strtotime($row["ngay_tao"])) ?></td>
                    <td class="py-3 px-4 text-center flex gap-2 justify-center">
                        
                        <button @click='viewDetails(<?= json_encode($row); ?>)'
                            class="px-3 py-1 bg-gray-400 hover:bg-gray-500 text-white rounded-lg text-xs font-medium transition duration-150 flex items-center gap-1">
                            <i class="fa-solid fa-eye"></i> Chi tiết
                        </button>
                        
                        <?php if($row["trang_thai"] == "choxuly"): ?>
                            

                            <button @click="updateStatus('<?= $row['id'] ?>', 'daduyet')"
                                class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded-lg text-xs font-medium transition duration-150">
                                Xử lý
                            </button>
                        <?php endif; ?>
                        
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div x-show="openDetail" 
         x-transition x-cloak
         class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div @click.away="openDetail = false"
             class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 modal-content transform transition-all">
             
            <h2 class="text-xl font-bold mb-4 text-indigo-700 border-b pb-2 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice"></i> Chi tiết Yêu cầu ID: <span x-text="currentRequest.id"></span>
            </h2>

            <div class="space-y-4 text-gray-700">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-3 rounded-lg border">
                        <p class="text-xs uppercase font-semibold text-gray-500">Khách hàng</p>
                        <p class="font-medium text-gray-900" x-text="currentRequest.ho_ten || currentRequest.nguoi_dung"></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border">
                        <p class="text-xs uppercase font-semibold text-gray-500">Ngày tạo</p>
                        <p class="font-medium text-gray-900" x-text="currentRequest.ngay_tao"></p>
                    </div>
                </div>

                <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                    <p class="text-sm uppercase font-bold text-blue-700 mb-1 flex items-center gap-1"><i class="fa-solid fa-home"></i> Bất động sản quan tâm</p>
                    <p class="font-semibold text-gray-900" x-text="currentRequest.tieu_de || 'Không có BĐS cụ thể'"></p>
                </div>
                
                <div class="p-3 border rounded-lg">
                    <p class="text-sm uppercase font-bold text-gray-700 mb-1 flex items-center gap-1"><i class="fa-solid fa-info-circle"></i> Mô tả chi tiết yêu cầu</p>
                    <p class="text-gray-600 italic text-sm" x-text="currentRequest.mo_ta_chi_tiet || 'Không có mô tả chi tiết.'"></p>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button @click="openDetail = false" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg font-medium">
                    Đóng
                </button>
            </div>
        </div>
    </div>
    
    
</div>
