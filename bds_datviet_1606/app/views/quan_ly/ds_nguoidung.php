<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();

    $search = $_GET['search'] ?? '';
    $id = $_SESSION['id_nguoi_dung'] ?? null;

    $keywords = preg_split('/\s+/', $search);

    $where = [];
    $params = [];

    foreach ($keywords as $i => $word) {
        if (!empty($word)) {
            $where[] = "(REPLACE(unaccent(LOWER(i.ho_ten)), ' ', '') ILIKE REPLACE(unaccent(:kw$i), ' ', '')
                OR unaccent(LOWER(nd.ten_dang_nhap)) ILIKE unaccent(:kw$i)
                OR unaccent(LOWER(nd.email)) ILIKE unaccent(:kw$i)
                OR unaccent(LOWER(nd.so_dt)) ILIKE unaccent(:kw$i)
            )";
            $params[":kw$i"] = "%$word%";
        }
    }

    $baseSql = "
        SELECT 
            i.ho_ten, i.gioi_tinh, i.dia_chi, nd.avt, i.ngay_sinh, nd.id,
            nd.ten_dang_nhap, nd.email, nd.so_dt, nd.trang_thai, nd.hoat_dong, nd.ngay_tao,
            ARRAY_AGG(DISTINCT q.vai_tro) AS danh_sach_quyen
        FROM info_nguoi_dung i
        JOIN nguoi_dung nd ON i.id_nguoi_dung = nd.id
        LEFT JOIN giao_dich gd ON nd.id = gd.id_nguoi_dung AND gd.trang_thai = 'hoantat'
        LEFT JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung
        LEFT JOIN quyen q ON pq.id_quyen = q.id
    ";

    if (!empty($where)) {
        $baseSql .= " WHERE " . implode(" OR ", $where);
    }
    
    $baseSql .= "
        GROUP BY 
            nd.id, i.ho_ten, i.gioi_tinh, i.dia_chi, i.ngay_sinh
        
    ";

    // 1. Thực thi câu lệnh
    $stmt = $pdo->prepare($baseSql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Mảng định nghĩa màu và nhãn
    $roleColors = [
        'quantri' => 'bg-red-100 text-red-700', 'moigioi' => 'bg-indigo-100 text-indigo-700', 'khachhang' => 'bg-teal-100 text-teal-700'
    ];
    $labelvaitro = [
        'quantri' => 'Quản trị', 'moigioi' => 'Môi giới', 'khachhang' => 'Khách hàng'
    ];
    $labeltrangthai = [
        'danghoatdong' => 'Hoạt động', 'chuakichhoat' => 'Chờ kích hoạt', 'khoa' => 'Đã khóa'
    ];
    $statusColors = [
        'danghoatdong' => 'bg-green-100 text-green-700 border-green-300', 'chuakichhoat' => 'bg-yellow-100 text-yellow-700 border-yellow-300', 'khoa' => 'bg-red-100 text-red-700 border-red-300'
    ];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
    <style> @media (max-width: 768px) { .hide-on-mobile { display: none; } }</style>
</head>
<body>

    <div class="mb-6 border-b pb-4">
        <h1 class="text-2xl font-bold text-gray-800">Danh sách Người dùng</h1>
        <p class="text-sm mt-2 text-gray-500">Quản lý, tìm kiếm và thực hiện các thao tác trên tài khoản người dùng.</p>
    </div>

    <form id="search-form" method="GET" class="flex items-center mb-4">
        <div class="relative w-72"> 
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" name="search" id="search-input" 
                class="bg-white outline-none border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2" 
                placeholder="Tìm kiếm..." 
                value="<?= htmlspecialchars($search) ?>">
        </div>
        <button id="search-button" type="submit" class="ml-2 px-4 py-2 text-sm font-medium text-white bg-gray-400 rounded-lg hover:bg-gray-500">
            Tìm
        </button>
    </form>

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người dùng</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hide-on-mobile">Vai trò</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hide-on-mobile">Liên hệ</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Không tìm thấy người dùng nào phù hợp.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover border border-gray-200" 
                                             src="../../../storage/pictures/avt/<?= htmlspecialchars($u['avt']) ?>"
                                             onerror="this.onerror=null; this.src='../../../storage/pictures/avt/avt.png';">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-800"><?= htmlspecialchars($u['ho_ten']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hide-on-mobile">
                                <?php
                                    $chuoiQuyen = $u['danh_sach_quyen'] ?? '{}';
                                    $dsQuyenArray = ($chuoiQuyen === '{NULL}') ? [] : explode(',', trim($chuoiQuyen, '{}'));
                                ?>
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach ($dsQuyenArray as $vai_tro): if(empty($vai_tro)) continue; ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $roleColors[$vai_tro] ?? 'bg-gray-100 text-gray-700' ?>">
                                            <?= htmlspecialchars($labelvaitro[$vai_tro] ?? $vai_tro) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="status-badge px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border <?= $statusColors[$u['trang_thai']] ?>"
                                    data-userid="<?= $u['id'] ?>"> 
                                    <?= htmlspecialchars($labeltrangthai[$u['trang_thai']]) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hide-on-mobile">
                                <span class="block"><?= htmlspecialchars($u['so_dt']) ?></span>
                                <span class="block text-xs text-gray-400">Tạo: <?= date("d/m/Y", strtotime($u['ngay_tao'])) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="trangchu.php?page=ct_nguoidung&id=<?= $u['id'] ?>" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <?php if ($u['trang_thai'] === 'danghoatdong'): ?>
                                    <a href="javascript:void(0);" 
                                    class="text-red-600 hover:text-red-900 ml-4 toggle-status-btn"
                                    data-id="<?= $u['id'] ?>"
                                    data-status="khoa">
                                        <i class="fas fa-lock text-sm"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="javascript:void(0);"
                                    class="text-green-600 hover:text-green-900 ml-4 toggle-status-btn"
                                    data-id="<?= $u['id'] ?>"
                                    data-status="danghoatdong">
                                        <i class="fas fa-check-circle text-sm"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // 1. Lấy các phần tử HTML cần thiết qua ID
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const searchButton = document.getElementById('search-button');

        // 2. Hàm để thực hiện submit
        function submitSearch() {
            const searchValue = searchInput.value;

            const encodedSearchValue = encodeURIComponent(searchValue.trim());

            const newUrl = `trangchu.php?page=ds_nguoidung&search=${encodedSearchValue}`;
            const trove = `trangchu.php?page=ds_nguoidung`;
            if (searchValue) {
                window.location.href = newUrl;          
            } else {
                window.location.href = trove;
            }
        }

        // 3. Gán sự kiện nhấn cho nút bấm
        searchButton.addEventListener('click', function(event) {
            event.preventDefault(); 
            submitSearch();
        });

        // 4. Gán sự kiện bỏ focus cho ô tìm kiếm
        searchInput.addEventListener('blur', function() {
            submitSearch(); // thực hiện tìm kiếm khi rời khỏi ô input
        });

        document.addEventListener('DOMContentLoaded', function() {
            const statusButtons = document.querySelectorAll('.toggle-status-btn');

            statusButtons.forEach(button => {
                button.addEventListener('click', async function(e) {
                    if (!confirm('Bạn có chắc chắn muốn thực hiện thao tác này?')) {
                        return;
                    }
                    e.preventDefault();

                    const userId = this.dataset.id;
                    const newStatus = this.dataset.status;

                    const formData = new FormData();
                    formData.append('id', userId);
                    formData.append('new_status', newStatus);

                    try {
                        const response = await fetch('../../models/cn_trangthai_nd.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (!response.ok) {
                            throw new Error(`Lỗi HTTP! Trạng thái: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.status === 'success') {
                            // ----- PHẦN 1: CẬP NHẬT ICON VÀ NÚT BẤM (như cũ) -----
                            const icon = this.querySelector('i');
                            if (result.newState === 'danghoatdong') {
                                this.classList.remove('text-green-600', 'hover:text-green-900');
                                this.classList.add('text-red-600', 'hover:text-red-900');
                                icon.classList.remove('fa-check-circle');
                                icon.classList.add('fa-lock');
                                this.dataset.status = 'khoa';
                            } else {
                                this.classList.remove('text-red-600', 'hover:text-red-900');
                                this.classList.add('text-green-600', 'hover:text-green-900');
                                icon.classList.remove('fa-lock');
                                icon.classList.add('fa-check-circle');
                                this.dataset.status = 'danghoatdong';
                            }

                            // ----- PHẦN 2: CẬP NHẬT SPAN TRẠNG THÁI (phần mới) ----- ✨
                            // Tìm span trạng thái tương ứng dựa trên data-userid
                            const statusSpan = document.querySelector(`.status-badge[data-userid="${userId}"]`);

                            if (statusSpan) {
                                // 1. Cập nhật nội dung text
                                statusSpan.textContent = result.newLabel;
                                
                                // 2. Cập nhật class màu sắc
                                // Định nghĩa các class cơ bản không đổi
                                const baseClasses = 'status-badge px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border';
                                // Gán lại toàn bộ class = class cơ bản + class màu mới từ server
                                statusSpan.className = `${baseClasses} ${result.newClasses}`;
                            }

                        } else {
                            alert('Lỗi: ' + result.message);
                        }

                    } catch (error) {
                        console.error('Không thể thực hiện yêu cầu:', error);
                        alert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.');
                    }
                });
            });
        });
    </script>
</body>
</html>