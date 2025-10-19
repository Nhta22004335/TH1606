<?php
    require_once "../../../config/database.php"; 

    try {
        $pdo = ketnoicsdl();
    } catch (PDOException $e) { die("Lỗi kết nối CSDL: " . $e->getMessage()); }

    // --- LOGIC LẤY DỮ LIỆU ---
    $search = $_GET['search'] ?? '';
    $params = [];
    $sql = "
        SELECT 
            bds.id, bds.dia_chi_day_du,
            COALESCE(bds.dien_tich_su_dung, bds.dien_tich_dat) AS dien_tich,
            bds.so_phong_ngu, bds.so_phong_tam, bds.ngay_tao,
            dm.ten_danh_muc, info.ho_ten AS ten_chu_so_huu
        FROM bat_dong_san AS bds
        LEFT JOIN danh_muc AS dm ON bds.id_danh_muc = dm.id
        LEFT JOIN info_nguoi_dung AS info ON bds.id_chu_so_huu = info.id_nguoi_dung
    ";
    if (!empty($search)) {
        $searchable_columns = "bds.dia_chi_day_du || ' ' || dm.ten_danh_muc || ' ' || info.ho_ten";
        $sql .= " WHERE REPLACE(unaccent({$searchable_columns}), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
        $params[':search'] = '%' . $search . '%';
    }
    $sql .= " ORDER BY bds.ngay_tao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bất động sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        [x-cloak] { display: none !important; } 
    </style>
</head>
<body class="p-4 md:p-6" 
      x-data="{ show: false, message: '', type: 'success', 
                 showToast(detail) { 
                     this.message = detail.message; 
                     this.type = detail.type || 'success'; 
                     this.show = true; 
                     setTimeout(() => this.show = false, 3000); 
                 } 
             }">
    <div class="max-w-7xl mx-auto">
        <header class="mb-4 border-b pb-2">
            <h1 class="text-2xl font-bold text-gray-500">Quản lý Bất động sản</h1>
            <p class="mt-1 text-sm text-slate-600">Xem, tìm kiếm và quản lý tất cả các tài sản trong hệ thống.</p>
        </header>

        <form id="search-form" method="GET" action="trangchu.php" class="flex items-center mb-6">
             <input type="hidden" name="page" value="ds_sanpham_bds"> 
             <div class="relative w-full md:w-72">
                 <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                 <input type="search" name="search" id="search-input" class="bg-white outline-none border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 transition" 
                        placeholder="Tìm địa chỉ, chủ sở hữu..." value="<?= htmlspecialchars($search) ?>">
             </div>
             <button type="submit" class="ml-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Tìm</button>
        </form>

        <main class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200/80">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Bất động sản</th>
                            <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Chủ sở hữu</th>
                            <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Thông số</th>
                            <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Ngày tạo</th>
                            <th class="p-4 text-center text-xs font-bold text-gray-500 uppercase">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="property-table-body" class="divide-y divide-gray-200">
                        <?php if (empty($properties)): ?>
                            <tr><td colspan="5" class="p-8 text-center text-gray-500">Không tìm thấy tài sản nào.</td></tr>
                        <?php else: ?>
                            <?php foreach($properties as $prop): ?>
                            <tr id="property-row-<?= $prop['id'] ?>" class="hover:bg-slate-50 transition duration-150"> 
                                <td class="p-4 max-w-sm">
                                    <p class="font-medium text-sm text-gray-900 truncate" title="<?= htmlspecialchars($prop['dia_chi_day_du']) ?>"><?= htmlspecialchars($prop['dia_chi_day_du']) ?></p>
                                    <p class="text-xs text-indigo-600 font-semibold"><?= htmlspecialchars($prop['ten_danh_muc'] ?? 'Chưa phân loại') ?></p>
                                </td>
                                <td class="p-4 text-sm text-gray-700 whitespace-nowrap">
                                    <?= htmlspecialchars($prop['ten_chu_so_huu'] ?? 'Chưa rõ') ?>
                                </td>
                                <td class="p-4 text-sm text-gray-500 whitespace-nowrap">
                                    <span title="Diện tích"><i class="fa-solid fa-ruler-combined mr-1"></i><?= htmlspecialchars($prop['dien_tich']) ?> m²</span>
                                    <span class="ml-3" title="Phòng ngủ"><i class="fa-solid fa-bed mr-1"></i><?= htmlspecialchars($prop['so_phong_ngu'] ?? '0') ?></span>
                                    <span class="ml-3" title="Phòng tắm"><i class="fa-solid fa-bath mr-1"></i><?= htmlspecialchars($prop['so_phong_tam'] ?? '0') ?></span>
                                </td>
                                <td class="p-4 text-sm text-gray-500 whitespace-nowrap">
                                    <?= date("d/m/Y", strtotime($prop['ngay_tao'])) ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-center items-center gap-4 text-sm">
                                        <a href="trangchu.php?page=quanly_chitiet_sanpham_bds&id=<?= $prop['id'] ?>" class="font-medium text-blue-600 hover:text-blue-800" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></a>
                                        <button type="button" class="delete-btn font-medium text-red-600 hover:text-red-800" data-id="<?= $prop['id'] ?>" title="Xóa"><i class="fa-solid fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div x-show="show" x-cloak
         @show-toast.window="showToast($event.detail)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="fixed bottom-5 right-5 w-full max-w-sm p-4 rounded-xl shadow-2xl text-white font-semibold z-50" 
         :class="{ 'bg-gradient-to-r from-green-500 to-green-600': type === 'success', 'bg-gradient-to-r from-red-500 to-red-600': type === 'error' }">
        <div class="flex items-center">
            <i class="fas fa-2x mr-4" :class="{ 'fa-check-circle': type === 'success', 'fa-exclamation-triangle': type === 'error' }"></i>
            <span x-text="message"></span>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.getElementById('property-table-body');
        const apiUrl = '../../models/quanly_sanpham_bds/xoa_sanpham_bds_qt.php'; // <<<=== Đảm bảo đường dẫn này chính xác

        if (tableBody) {
            tableBody.addEventListener('click', async function(event) {
                const targetButton = event.target.closest('.delete-btn'); 
                if (!targetButton) return; 

                const propertyId = targetButton.dataset.id;

                if (confirm('Bạn có chắc chắn muốn xóa tài sản này không? Hành động này không thể hoàn tác.')) {
                    const formData = new FormData();
                    formData.append('id', propertyId);

                    try {
                        const response = await fetch(apiUrl, { method: 'POST', body: formData });
                        const result = await response.json(); 

                        if (!response.ok) {
                             throw new Error(result.message || `Lỗi mạng: ${response.statusText}`);
                        }

                        if (result.success) {
                            const row = document.getElementById(`property-row-${propertyId}`);
                            if (row) {
                                row.style.transition = 'opacity 0.3s ease-out';
                                row.style.opacity = '0';
                                setTimeout(() => {
                                    row.remove();
                                    // Dispatch sự kiện thành công
                                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Xóa thành công!', type: 'success' } }));
                                }, 300); 
                            }
                        } else {
                            // Dispatch sự kiện lỗi
                            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Xóa không thành công.', type: 'error' } }));
                        }

                    } catch (error) {
                        console.error('Lỗi Fetch:', error);
                        // Dispatch sự kiện lỗi kết nối
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Lỗi kết nối: ' + error.message, type: 'error' } }));
                    }
                }
            });
        }
    });
</script>
</body>
</html>