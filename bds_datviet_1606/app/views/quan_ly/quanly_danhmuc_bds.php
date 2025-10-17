<?php
    require_once "../../../config/database.php"; 

    // Các hàm helper (giữ nguyên)
    function formatPrice($price) {
        if ($price >= 1000000000) return round($price / 1000000000, 2) . ' tỷ';
        if ($price >= 1000000) return round($price / 1000000, 2) . ' triệu';
        return number_format($price) . ' đ';
    }
    
    function getStatusInfo($status) {
        $map = [
            'chuaduyet' => ['text' => 'Chờ duyệt', 'classes' => 'bg-yellow-100 text-yellow-800'],
            'daduyet'   => ['text' => 'Đã duyệt', 'classes' => 'bg-green-100 text-green-800'],
        ];
        return $map[$status] ?? ['text' => ucfirst($status), 'classes' => 'bg-gray-100 text-gray-800'];
    }

    try {
        $pdo = ketnoicsdl();
    } catch (PDOException $e) {
        die("Lỗi kết nối CSDL: " . $e->getMessage());
    }

    // ==========================================================
    // == THAY ĐỔI LỚN: CÂU LỆNH SQL ĐÃ BỎ TRUY VẤN HÌNH ẢNH ==
    // ==========================================================
    $search = $_GET['search'] ?? '';
    $params = [];

    $sql = "
        SELECT 
            bd.id AS bai_dang_id,
            bd.tieu_de,
            bd.gia,
            bd.ngay_dang,
            bd.trang_thai,
            bds.dia_chi_day_du,
            COALESCE(bds.dien_tich_su_dung, bds.dien_tich_dat) AS dien_tich,
            info.ho_ten AS ten_nguoi_dang,
            dm.ten_danh_muc
        FROM 
            bai_dang AS bd
        JOIN 
            bat_dong_san AS bds ON bd.id_bat_dong_san = bds.id
        LEFT JOIN 
            danh_muc AS dm ON bds.id_danh_muc = dm.id
        LEFT JOIN 
            nguoi_dung AS nd ON bd.id_nguoi_dung = nd.id
        LEFT JOIN
            info_nguoi_dung AS info ON nd.id = info.id_nguoi_dung
    ";

    if (!empty($search)) {
        $searchable_columns = "bd.tieu_de || ' ' || bds.dia_chi_day_du || ' ' || info.ho_ten || ' ' || dm.ten_danh_muc";
        $sql .= " WHERE REPLACE(unaccent({$searchable_columns}), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
        $params[':search'] = '%' . $search . '%';
    }
    
    $sql .= " ORDER BY bd.ngay_dang DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bai_dang_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tin đăng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <header class="mb-6 border-b pb-4">
            <h1 class="text-2xl font-bold text-gray-800">Quản lý Tin đăng Bất động sản</h1>
            <p class="text-sm mt-2 text-gray-500">Xem, tìm kiếm và duyệt các tin đăng trên hệ thống.</p>
        </header>

        <form id="search-form" method="GET" class="flex items-center mb-6">
            <input type="hidden" name="page" value="ds_sanpham_bds">
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="search" id="search-input" name="search" class="bg-white outline-none border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 transition" 
                       placeholder="Tìm theo tiêu đề, địa chỉ, người đăng..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="ml-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Tìm</button>
        </form>

        <main class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200/80">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Tiêu đề & Địa chỉ</th>
                            <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Giá</th>
                            <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Diện tích</th>
                            <th class="p-4 text-left text-xs font-bold text-gray-500 uppercase">Người đăng</th>
                            <th class="p-4 text-center text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                            <th class="p-4 text-center text-xs font-bold text-gray-500 uppercase">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="listings-table-body" class="divide-y divide-gray-200">
                        <?php if (empty($bai_dang_list)): ?>
                            <tr><td colspan="6" class="p-8 text-center text-gray-500">Không tìm thấy tin đăng nào.</td></tr>
                        <?php else: ?>
                            <?php foreach($bai_dang_list as $bd): ?>
                            <tr id="row-<?= $bd['bai_dang_id'] ?>" class="hover:bg-slate-50 transition duration-150">
                                <td class="p-4 max-w-xs">
                                    <p class="font-medium text-sm text-gray-900 truncate" title="<?= htmlspecialchars($bd['tieu_de']) ?>"><?= htmlspecialchars($bd['tieu_de']) ?></p>
                                    <p class="text-xs text-gray-500 truncate" title="<?= htmlspecialchars($bd['dia_chi_day_du']) ?>"><?= htmlspecialchars($bd['dia_chi_day_du']) ?></p>
                                </td>
                                <td class="p-4 font-semibold text-red-600 text-sm whitespace-nowrap"><?= formatPrice($bd['gia']) ?></td>
                                <td class="p-4 text-gray-700 text-sm whitespace-nowrap"><?= htmlspecialchars($bd['dien_tich']) ?> m²</td>
                                <td class="p-4 text-gray-700 text-sm whitespace-nowrap"><?= htmlspecialchars($bd['ten_nguoi_dang'] ?? 'N/A') ?></td>
                                <td class="p-4 text-center status-cell">
                                    <?php $status_info = getStatusInfo($bd["trang_thai"]); ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full <?= $status_info['classes'] ?>">
                                        <?= $status_info['text'] ?>
                                    </span>
                                </td>
                                <td class="p-4 actions-cell">
                                    <div class="flex justify-center items-center gap-4 text-sm">
                                        <?php if ($bd['trang_thai'] === 'chuaduyet'): ?>
                                            <button type="button" class="action-btn font-medium text-green-600 hover:text-green-800" data-id="<?= $bd['bai_dang_id'] ?>" data-action="duyet">Duyệt</button>
                                        <?php elseif ($bd['trang_thai'] === 'daduyet'): ?>
                                            <button type="button" class="action-btn font-medium text-yellow-600 hover:text-yellow-800" data-id="<?= $bd['bai_dang_id'] ?>" data-action="hoantac">Hoàn tác</button>
                                        <?php endif; ?>
                                        <a href="trangchu.php?page=ct_sanpham&id=<?= $bd['bai_dang_id'] ?>" class="font-medium text-blue-600 hover:text-blue-800">Chi tiết</a>
                                        <button type="button" class="action-btn font-medium text-red-600 hover:text-red-800" data-id="<?= $bd['bai_dang_id'] ?>" data-action="xoa">Xóa</button>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý các hành động Duyệt, Hoàn tác, Xóa
        const tableBody = document.getElementById('listings-table-body');
        const apiUrl = '../../models/cn_trangthai_bds.php';

        if (tableBody) {
            tableBody.addEventListener('click', async function(event) {
                const targetButton = event.target.closest('.action-btn');
                if (!targetButton) return;

                const id = targetButton.dataset.id;
                const action = targetButton.dataset.action;
                
                const messages = {
                    duyet: 'Bạn có chắc muốn duyệt tin này?',
                    hoantac: 'Bạn có chắc muốn hoàn tác? Tin này sẽ trở về trạng thái "Chờ duyệt".',
                    xoa: 'Bạn có chắc chắn muốn xóa tin này? Hành động này không thể hoàn tác.'
                };

                if (!confirm(messages[action] || 'Bạn có chắc?')) return;

                const formData = new FormData();
                formData.append('id', id);
                formData.append('action', action);

                try {
                    const response = await fetch(apiUrl, { method: 'POST', body: formData });
                    if (!response.ok) throw new Error('Yêu cầu mạng thất bại.');
                    
                    const result = await response.json();

                    if (result.status === 'success') {
                        const row = document.getElementById(`row-${id}`);
                        if (action === 'xoa') {
                            row.style.transition = 'opacity 0.3s';
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 300);
                        } else {
                            // Cập nhật "live" cho ô trạng thái và hành động
                            if (result.newStatusHtml) {
                                row.querySelector('.status-cell').innerHTML = result.newStatusHtml;
                            }
                            if (result.newActionsHtml) {
                                row.querySelector('.actions-cell').innerHTML = result.newActionsHtml;
                            }
                        }
                    } else {
                        alert('Lỗi: ' + result.message);
                    }
                } catch (error) {
                    console.error('Lỗi Fetch:', error);
                    alert('Đã xảy ra lỗi khi gửi yêu cầu.');
                }
            });
        }
    });
    </script>
</body>
</html>