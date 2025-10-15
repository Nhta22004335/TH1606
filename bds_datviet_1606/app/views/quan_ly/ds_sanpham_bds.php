<?php
    require_once "../../../config/database.php";

    // --- CÁC HÀM HELPER VÀ LOGIC LẤY DỮ LIỆU (Giữ nguyên) ---
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
        return $map[$status] ?? ['text' => 'Không rõ', 'classes' => 'bg-gray-100 text-gray-800'];
    }

    $pdo = ketnoicsdl();
    $search = $_GET['search'] ?? '';

    $sql = "SELECT id, tieu_de, gia, dien_tich, dia_chi, ngay_dang, trang_thai FROM bat_dong_san";
    $params = [];

    // --- THAY ĐỔI CHÍNH NẰM Ở ĐÂY ---
    if (!empty($search)) {
        // 1. Nối các cột cần tìm kiếm thành một chuỗi duy nhất
        $searchable_columns = "tieu_de || ' ' || dia_chi";
        
        // 2. Áp dụng unaccent và replace cho cả cột và từ khóa tìm kiếm
        // Điều này giúp tìm kiếm không phân biệt dấu, khoảng trắng và chữ hoa/thường
        $sql .= " WHERE REPLACE(unaccent({$searchable_columns}), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
        
        $params[':search'] = '%' . $search . '%';
    }
    // --- KẾT THÚC THAY ĐỔI ---
    
    $sql .= " ORDER BY ngay_dang DESC, id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bất động sản</title>
</head>
<body class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <header class="mb-6 border-b pb-4">
            <h1 class="text-2xl font-bold text-gray-800">Quản lý Bất động sản</h1>
            <p class="text-sm mt-2 text-gray-500">Xem, tìm kiếm và quản lý các tin đăng bất động sản.</p>
        </header>

        <form id="search-form" method="GET" class="flex items-center mb-6">
            <input type="hidden" name="page" value="ds_sanpham_bds">
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="search-input" name="search" class="bg-white outline-none border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2" 
                       placeholder="Tìm kiếm tin đăng..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="ml-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Tìm</button>
        </form>

        <main class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200/80">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Tên Bất động sản</th>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Giá</th>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Diện tích</th>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                            <th class="p-3 text-left text-xs font-bold text-gray-500 uppercase">Ngày đăng</th>
                            <th class="p-3 text-center text-xs font-bold text-gray-500 uppercase">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body" class="divide-y divide-gray-200">
                        <?php if (empty($products)): ?>
                            <tr><td colspan="6" class="p-8 text-center text-gray-500">Không tìm thấy bất động sản nào.</td></tr>
                        <?php else: ?>
                            <?php foreach($products as $p): ?>
                                <tr id="product-row-<?= $p['id'] ?>" class="hover:bg-slate-50 transition duration-150">
                                    <td class="p-4">
                                        <p class="font-medium text-sm text-gray-900 line-clamp-1"><?= htmlspecialchars($p['tieu_de']) ?></p>
                                        <p class="text-xs text-gray-500 line-clamp-1"><?= htmlspecialchars($p['dia_chi']) ?></p>
                                    </td>
                                    <td class="p-4 font-semibold text-red-600 text-sm"><?= formatPrice($p['gia']) ?></td>
                                    <td class="p-4 text-gray-700 text-sm"><?= htmlspecialchars($p['dien_tich']) ?> m²</td>
                                    <td class="p-4 status-cell">
                                        <?php $status_info = getStatusInfo($p["trang_thai"]); ?>
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full <?= $status_info['classes'] ?>">
                                            <?= $status_info['text'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-500 text-sm"><?= date("d/m/Y", strtotime($p['ngay_dang'])) ?></td>
                                    <td class="p-4 actions-cell">
                                        <div class="flex justify-center items-center gap-4">
                                            <?php if ($p['trang_thai'] === 'chuaduyet'): ?>
                                                <button type="button" class="action-btn text-green-600 hover:text-green-800 text-sm font-medium" data-id="<?= $p['id'] ?>" data-action="duyet">Duyệt</button>
                                            <?php elseif ($p['trang_thai'] === 'daduyet'): ?>
                                                <button type="button" class="action-btn text-yellow-600 hover:text-yellow-800 text-sm font-medium" data-id="<?= $p['id'] ?>" data-action="hoantac">Hoàn tác</button>
                                            <?php endif; ?>
                                            
                                            <a href="trangchu.php?page=ct_sanpham&id=<?= $p['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Chi tiết</a>
                                            
                                            <button type="button" class="action-btn text-red-600 hover:text-red-800 text-sm font-medium" data-id="<?= $p['id'] ?>" data-action="xoa">Xóa</button>
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

        function submitSearch() {
            const searchValue = document.getElementById('search-input').value.trim();

            const encodedSearchValue = encodeURIComponent(searchValue.trim());

            const newUrl = `trangchu.php?page=ds_sanpham_bds&search=${encodedSearchValue}`;
            const trove = `trangchu.php?page=ds_sanpham_bds`;
            if (searchValue) {
                window.location.href = newUrl;          
            } else {
                window.location.href = trove;
            }
        }

        document.getElementById('search-input').addEventListener('blur', function() {
            submitSearch(); // thực hiện tìm kiếm khi rời khỏi ô input
        });

        // Xử lý tìm kiếm
        document.getElementById('search-form').addEventListener('submit', function(event) {
            event.preventDefault();
            submitSearch();
        });

        // Xử lý các hành động Duyệt, Hoàn tác, Xóa
        const productTableBody = document.getElementById('product-table-body');
        const apiUrl = '../../models/cn_trangthai_bds.php'; // Đường dẫn tuyệt đối đến file API

        if (productTableBody) {
            productTableBody.addEventListener('click', async function(event) {
                const targetButton = event.target.closest('.action-btn');
                if (!targetButton) return;

                const id = targetButton.dataset.id;
                const action = targetButton.dataset.action;
                
                const messages = {
                    duyet: 'Bạn có chắc muốn duyệt tin này?',
                    hoantac: 'Bạn có chắc muốn hoàn tác? Tin này sẽ trở về trạng thái Chờ duyệt.',
                    xoa: 'Bạn có chắc chắn muốn xóa tin này? Hành động này không thể hoàn tác.'
                };

                if (!confirm(messages[action] || 'Bạn có chắc?')) return;

                const formData = new FormData();
                formData.append('id', id);
                formData.append('action', action);

                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        body: formData
                    });
                    if (!response.ok) throw new Error('Yêu cầu mạng thất bại.');
                    
                    const result = await response.json();

                    if (result.status === 'success') {
                        const row = document.getElementById(`product-row-${id}`);
                        if (action === 'xoa') {
                            row.style.transition = 'opacity 0.3s';
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 300);
                        } else {
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
        }
    });
    </script>
</body>
</html>