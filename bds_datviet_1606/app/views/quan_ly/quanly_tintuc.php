<?php
    require_once "../../../config/database.php";
    $pdo = ketnoicsdl();
    $search_term = $_GET['search'] ?? '';
    
    $sql = "SELECT t.id, t.tieu_de, t.mo_ta, t.chuyen_muc, t.anh_tin, t.ngay_dang, t.trang_thai, COALESCE(t.luot_xem, 0) AS luot_xem, u.so_dt AS sdt, u.avt, COALESCE(i.ho_ten, u.ten_dang_nhap) AS ten_moi_gioi
            FROM tin_tuc t JOIN nguoi_dung u ON t.id_khach_hang = u.id
            LEFT JOIN info_nguoi_dung i ON u.id = i.id_nguoi_dung
            WHERE t.trang_thai IN ('choduyet', 'dangban', 'daban', 'dathue')";

    $params = [];

    if (!empty($search_term)) {
        $searchable_columns = "t.tieu_de || ' ' || t.mo_ta || ' ' || COALESCE(i.ho_ten, u.ten_dang_nhap)";
        $sql .= " AND REPLACE(unaccent({$searchable_columns}), ' ', '') ILIKE REPLACE(unaccent(:search), ' ', '')";
        $params[':search'] = "%" . $search_term . "%";
    }

    $sql .= " ORDER BY t.ngay_dang DESC, luot_xem DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $all_data = $stmt->fetchAll();
    $danh_sach_tin = [];
    foreach ($all_data as $tin) {
        $danh_sach_tin[] = [
            'id' => $tin['id'],
            'img' => htmlspecialchars($tin['anh_tin']),
            'tieude' => htmlspecialchars($tin['tieu_de']),
            'chuyenmuc' => htmlspecialchars($tin['chuyen_muc']),
            'moigioi' => htmlspecialchars($tin['ten_moi_gioi']),
            'avt' => htmlspecialchars($tin['avt']),
            'ngay' => date('d/m/Y', strtotime($tin['ngay_dang'])),
            'view' => $tin['luot_xem'],
            'trang_thai' => $tin['trang_thai']
        ];
    }
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tin tức Bất động sản</title>
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        <?php foreach ($danh_sach_tin as $index => $tin) : ?>
        .list-item-<?= $index ?> { animation-delay: <?= $index * 0.07 ?>s; }
        <?php endforeach; ?>
    </style>
</head>
<body>
<div>
    <header class="pb-4 border-b border-gray-200">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Danh sách tin đăng</h1>
                <p class="mt-2 text-sm text-gray-500">Quản lý, tìm kiếm và xem tất cả các tin đăng trên hệ thống.</p>
            </div>
            <form action="" id="search-form" method="GET" class="relative">
                <input type="hidden" name="page" value="ql_tintuc">
                <label for="search-input" class="sr-only">Tìm kiếm</label>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg></div>
                <input type="text" name="search" id="search-input" class="block w-full rounded-md outline-none border-0 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-1 focus:ring-inset focus:ring-blue-600 sm:text-sm" placeholder="Tìm tiêu đề, người đăng..." value="<?= htmlspecialchars($search_term) ?>">
            </form>
        </div>
    </header>

    <main class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="hidden sm:grid grid-cols-12 gap-x-6 border-b border-gray-200 pb-3 text-sm font-semibold text-gray-600">
                    <div class="col-span-5 pl-2">Bài đăng</div>
                    <div class="col-span-2">Chuyên mục</div>
                    <div class="col-span-2">Ngày đăng</div>
                    <div class="col-span-1 text-center">Lượt xem</div>
                    <div class="col-span-2 text-center">Hành động</div>
                </div>

                <div id="news-list-container" class="mt-4 space-y-4">
                    <?php if (empty($danh_sach_tin)) : ?>
                        <div class="text-center py-20 px-6 bg-white rounded-xl shadow-sm">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                            <h3 class="mt-2 text-lg font-semibold text-gray-800">Không tìm thấy tin đăng</h3>
                            <p class="mt-1 text-sm text-gray-500"><?= !empty($search_term) ? 'Không có kết quả nào khớp với từ khóa "<b>' . htmlspecialchars($search_term) . '</b>".' : 'Hiện tại chưa có tin đăng nào trong hệ thống.' ?></p>
                        </div>
                    <?php else : ?>
                        <?php foreach ($danh_sach_tin as $index => $tin) : ?>
                            <div id="news-item-<?= $tin['id'] ?>" class="list-item-<?= $index ?> grid grid-cols-1 sm:grid-cols-12 gap-x-6 gap-y-3 items-center p-4 bg-white rounded-xl shadow-sm hover:shadow-lg hover:scale-[1.01] transition-all duration-300 opacity-0 animate-fade-in">
                                <div class="col-span-1 sm:col-span-5">
                                    <div class="flex items-center gap-4">
                                        <img src="../../../storage/pictures/anhtin/<?= $tin['img'] ?>" alt="<?= $tin['tieude'] ?>" class="h-16 w-24 rounded-lg object-cover flex-shrink-0">
                                        <div>
                                            <a href="#" class="font-semibold text-gray-800 hover:text-blue-600 transition-colors line-clamp-2 leading-tight"><?= $tin['tieude'] ?></a>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <img src="../../../storage/pictures/avt/<?= $tin['avt'] ?>" class="h-6 w-6 rounded-full object-cover">
                                                <span class="text-xs text-gray-500"><?= $tin['moigioi'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-1 sm:col-span-2">
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10"><?= ucfirst($tin['chuyenmuc']) ?></span>
                                </div>
                                <div class="col-span-1 sm:col-span-2 text-sm text-gray-500"><?= $tin['ngay'] ?></div>
                                <div class="col-span-1 sm:col-span-1 text-sm text-gray-600 font-medium text-center"><?= number_format($tin['view']) ?></div>
                                <div class="col-span-1 sm:col-span-2 actions-cell">
                                    <div class="flex justify-center items-center gap-x-4">
                                        <a href="#" class="text-xs text-blue-600 hover:text-blue-800 transition-colors"><i class="fa-solid fa-eye text-base"></i></a>
                                        <?php if ($tin['trang_thai'] === 'choduyet'): ?>
                                            <button type="button" class="text-xs action-btn text-green-600 hover:text-green-800 transition-colors" data-id="<?= $tin['id'] ?>" data-action="approve"><i class="fa-solid fa-check-circle text-base"></i></button>
                                        <?php else: ?>
                                            <button type="button" class="text-xs action-btn text-yellow-400 hover:text-yellow-500 transition-colors" data-id="<?= $tin['id'] ?>" data-action="undo"><i class="fa-solid fa-rotate-left text-base"></i></button>
                                        <?php endif; ?>
                                        <button type="button" class="text-xs action-btn text-red-600 hover:text-red-800 transition-colors" data-id="<?= $tin['id'] ?>" data-action="delete"><i class="fa-solid fa-trash-can text-base"></i></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tự động submit form tìm kiếm
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');

    // 2. Hàm để thực hiện submit
    function submitSearch() {
        const searchValue = searchInput.value;

        const encodedSearchValue = encodeURIComponent(searchValue.trim());

        const newUrl = `trangchu.php?page=ql_tintuc&search=${encodedSearchValue}`;
        const trove = `trangchu.php?page=ql_tintuc`;
        if (searchValue) {
            window.location.href = newUrl;          
        } else {
            window.location.href = trove;
        }
    }

    // 4. Gán sự kiện bỏ focus cho ô tìm kiếm
    searchInput.addEventListener('blur', function() {
        submitSearch(); // thực hiện tìm kiếm khi rời khỏi ô input
    });

    // Xử lý các hành động (Duyệt, Hoàn tác, Xóa) bằng fetch
    const newsListContainer = document.getElementById('news-list-container');
    const apiUrl = '../../models/cn_trangthai_tintuc.php'; // Đảm bảo đường dẫn này chính xác

    if (newsListContainer) {
        newsListContainer.addEventListener('click', async function(event) {
            const targetButton = event.target.closest('.action-btn');
            if (!targetButton) return;

            const id = targetButton.dataset.id;
            const action = targetButton.dataset.action;
            
            const messages = {
                approve: 'Bạn có chắc muốn duyệt tin đăng này?',
                undo: 'Bạn có chắc muốn hoàn tác? Tin đăng sẽ trở về trạng thái "Chờ duyệt".',
                delete: 'Bạn có chắc chắn muốn XÓA vĩnh viễn tin đăng này?'
            };

            if (!confirm(messages[action] || 'Bạn có chắc chắn?')) return;

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
                    const itemDiv = document.getElementById(`news-item-${id}`);
                    if (action === 'delete') {
                        itemDiv.style.transition = 'opacity 0.3s, transform 0.3s';
                        itemDiv.style.opacity = '0';
                        itemDiv.style.transform = 'scale(0.95)';
                        setTimeout(() => itemDiv.remove(), 300);
                    } else { // approve hoặc undo
                        // Cập nhật lại HTML của các nút hành động
                        const actionsCell = itemDiv.querySelector('.actions-cell');
                        if (actionsCell) {
                            actionsCell.innerHTML = result.newActionsHtml;
                        }
                    }
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch(error) {
                console.error('Lỗi Fetch:', error);
                alert('Đã xảy ra lỗi khi gửi yêu cầu đến máy chủ.');
            }
        });
    }
});
</script>
</body>
</html>