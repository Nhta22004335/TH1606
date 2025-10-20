<?php 
// =================================================================
// 1. KẾT NỐI CƠ SỞ DỮ LIỆU & HÀM HỖ TRỢ
// =================================================================
require_once "../../../config/database.php"; 
try {
    $pdo = ketnoicsdl(); 
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
}

$search = $_GET['search'] ?? '';
$search = trim($search); 

// =================================================================
// 2. TRUY VẤN DỮ LIỆU
// =================================================================
$sql = "
    SELECT 
        tb.id, tb.tieu_de, tb.noi_dung, tb.loai, tb.thoi_gian_gui, tb.trang_thai,
        inf_gui.ho_ten AS ten_nguoi_gui,
        nd_gui.avt AS avt_nguoi_gui,
        inf_nhan.ho_ten AS ten_nguoi_nhan
    FROM thong_bao AS tb
    LEFT JOIN nguoi_dung AS nd_gui ON tb.id_nguoi_gui = nd_gui.id
    LEFT JOIN info_nguoi_dung AS inf_gui ON nd_gui.id = inf_gui.id_nguoi_dung
    LEFT JOIN nguoi_dung AS nd_nhan ON tb.id_nguoi_dung = nd_nhan.id
    LEFT JOIN info_nguoi_dung AS inf_nhan ON nd_nhan.id = inf_nhan.id_nguoi_dung
";
$params = [];
if (!empty($search)) {
    $sql .= " WHERE LOWER(tb.tieu_de) LIKE LOWER(:search) OR LOWER(tb.noi_dung) LIKE LOWER(:search) OR LOWER(inf_gui.ho_ten) LIKE LOWER(:search) OR LOWER(inf_nhan.ho_ten) LIKE LOWER(:search)";
    $params[':search'] = "%$search%";
}
$sql .= " ORDER BY tb.thoi_gian_gui DESC;";
$stmt = $pdo->prepare($sql);
if (!empty($search)) { $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR); }
$stmt->execute();
$thongbao_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =================================================================
// 3. HÀM HIỂN THỊ VÀ THỐNG KÊ
// =================================================================
function getNotificationIcon($type) {
    $map = [
        'hethong'   => ['icon' => 'fa-solid fa-bullhorn', 'color' => 'bg-indigo-500'],
        'giaodich'  => ['icon' => 'fa-solid fa-receipt', 'color' => 'bg-green-500'],
        'taikhoan'  => ['icon' => 'fa-solid fa-user-shield', 'color' => 'bg-red-500'],
        'binhluan'  => ['icon' => 'fa-solid fa-comments', 'color' => 'bg-blue-500'],
    ];
    $info = $map[$type] ?? ['icon' => 'fa-solid fa-bell', 'color' => 'bg-gray-500'];
    return "<div class='w-10 h-10 rounded-full {$info['color']} flex items-center justify-center text-white ring-8 ring-white'><i class='{$info['icon']}'></i></div>";
}

$stats = [
    'total'   => count($thongbao_list),
    'chuaxem' => count(array_filter($thongbao_list, fn($t) => $t['trang_thai'] === 'chuaxem')),
];
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Thông báo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>     
        .notification-card.unread { background-color: #eef2ff; border-color: #c7d2fe; }
        .notification-card.unread .sender-name { font-weight: 700; color: #1e293b; }
        .modal.hidden { display: none; }
        .modal { transition: opacity 0.3s ease-out; }
        .modal-content { transition: transform 0.3s ease-out; }
        .modal.hidden .modal-content { transform: scale(0.95); }
    </style>
</head>
<body>

    <div class="max-w-6xl mx-auto">
        <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-500">Hộp thư Thông báo</h1>
                <p class="mt-1 text-sm text-slate-600">Tổng quan tất cả thông báo hệ thống và giao dịch.</p>
            </div>
            <div class="flex-shrink-0 grid grid-cols-2 gap-4">
                <div class="bg-white p-3 rounded-lg shadow-sm border flex items-center gap-3">
                    <div class="flex-shrink-0 bg-orange-100 p-2.5 rounded-full"><i class="fas fa-envelope text-orange-600 fa-fw"></i></div>
                    <div><dt class="text-sm font-medium text-slate-500">Chưa xem</dt><dd class="mt-0.5 text-xl font-semibold text-slate-900"><?= $stats['chuaxem'] ?></dd></div>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm border flex items-center gap-3">
                    <div class="flex-shrink-0 bg-blue-100 p-2.5 rounded-full"><i class="fas fa-layer-group text-blue-600 fa-fw"></i></div>
                    <div><dt class="text-sm font-medium text-slate-500">Tổng cộng</dt><dd class="mt-0.5 text-xl font-semibold text-slate-900"><?= $stats['total'] ?></dd></div>
                </div>
            </div>
        </header>

        <main>
            <form action="" id="search-form" method="GET" class="mb-6">
                 <input type="hidden" name="page" value="ql_thongbao">
                 <div class="relative">
                     <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                     <input type="search" name="search" id="search-input" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm theo tiêu đề, nội dung, người gửi/nhận..." 
                            class="w-full pl-11 pr-4 py-3 text-sm bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                 </div>
            </form>

            <div class="relative">
                <div class="absolute left-5 top-2 h-full w-0.5 bg-slate-200" aria-hidden="true"></div>
                
                <ul id="notification-timeline" class="space-y-6">
                    <?php if (empty($thongbao_list)): ?>
                        <li class="p-12 text-center text-gray-500">
                            <i class="fa-solid fa-inbox fa-3x text-slate-300"></i>
                            <p class="mt-4 font-semibold">Hộp thư trống</p>
                            <p class="text-sm">Không tìm thấy thông báo nào.</p>
                        </li>
                    <?php endif; ?>

                    <?php foreach($thongbao_list as $tb): 
                        $is_unread = $tb['trang_thai'] === 'chuaxem';
                    ?>
                    <li id="notification-item-<?= $tb['id'] ?>" class="relative pl-14">
                        <div class="absolute left-0 top-1">
                            <?= getNotificationIcon($tb['loai']) ?>
                        </div>
                        
                        <div class="notification-card bg-white rounded-lg border shadow-sm p-4 transition-all <?= $is_unread ? 'unread border-indigo-200' : 'read border-slate-200' ?>">
                            <div class="flex items-start justify-between">
                                <div class-="flex-1">
                                    <p class="text-slate-500">
                                        <span class="sender-name font-medium text-sm"><?= htmlspecialchars($tb['ten_nguoi_gui'] ?? 'Hệ thống') ?></span>
                                        <i class="fa-solid fa-arrow-right-long text-xs text-slate-400 mx-1.5"></i>
                                        <span class="text-sm text-slate-600"><?= htmlspecialchars($tb['ten_nguoi_nhan'] ?? 'Tất cả') ?></span>
                                    </p>
                                    <h3 class="font-semibold text-sm text-slate-800 mt-1"><?= htmlspecialchars($tb['tieu_de']) ?></h3>
                                    <time class="text-xs text-slate-400"><?= date('H:i, d/m/Y', strtotime($tb['thoi_gian_gui'])) ?></time>
                                </div>
                                <div class="flex gap-2">
                                    <?php if ($is_unread): ?>
                                        <a href="trangchu.php?page=giaodien_dieuchinh_thongbao&id=<?= $tb['id'] ?>" class="btn-edit p-2 text-sm text-blue-600 font-semibold hover:underline" title="Sửa thông báo">Sửa</a>
                                    <?php endif; ?>
                                    
                                    <button class="btn-view p-2 text-sm text-indigo-600 font-semibold hover:underline" 
                                            data-details='<?= htmlspecialchars(json_encode($tb)) ?>'
                                            data-id="<?= $tb['id'] ?>">
                                        Xem
                                    </button>
                                    <button class="btn-delete p-2 text-sm text-red-500 font-semibold hover:underline" 
                                            data-id="<?= $tb['id'] ?>" 
                                            title="Xóa">Xóa</button>
                                </div>
                            </div>
                            
                            <div class="content-full hidden"><?= htmlspecialchars($tb['noi_dung']) ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </main>
    </div>

    <div id="detailModal" class="modal fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 hidden transition-opacity duration-300 opacity-0">
        <div id="modalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-lg transition-transform duration-300 transform scale-95">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-xl font-semibold text-indigo-600" id="modal-title"></h3>
                <button id="modal-close-btn" class="p-2 text-gray-400 hover:text-gray-700 rounded-full"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4">
                 <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="font-medium text-gray-500">Từ</dt><dd id="modal-sender" class="mt-1 text-gray-800 font-semibold"></dd></div>
                    <div><dt class="font-medium text-gray-500">Tới</dt><dd id="modal-recipient" class="mt-1 text-gray-800"></dd></div>
                    <div><dt class="font-medium text-gray-500">Loại</dt><dd id="modal-type" class="mt-1 text-gray-800"></dd></div>
                    <div><dt class="font-medium text-gray-500">Thời gian</dt><dd id="modal-time" class="mt-1 text-gray-800"></dd></div>
                 </div>
                 <div class="border-t pt-4">
                    <dt class="font-medium text-gray-500 text-sm">Nội dung</dt>
                    <dd id="modal-content-body" class="mt-2 text-gray-700 whitespace-pre-wrap p-4 bg-gray-50 rounded-md border max-h-60 overflow-y-auto"></dd>
                 </div>
            </div>
            <div class="p-4 border-t flex justify-end">
                <button type="button" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm" onclick="closeModal()">Đóng</button>
            </div>
        </div>
    </div>
    <script>

    const modal = document.getElementById('detailModal');
    const modalContent = document.getElementById('modalContent');
    const closeModalBtn = document.getElementById('modal-close-btn');

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
    function openModal() {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 10);
    }
    closeModalBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (!modalContent.contains(e.target)) closeModal();
    });


    // Hàm Xóa thông báo (API)
    async function deleteNotification(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa thông báo này?')) return;
        
        try {
            const formData = new FormData();
            formData.append('id', id);
            const response = await fetch('../../models/xoa_thongbao_qt.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                const card = document.getElementById(`notification-item-${id}`);
                if (card) {
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-20px)';
                    setTimeout(() => card.remove(), 300);
                }
                alert('Đã xóa thông báo.');
                location.reload(); // Tải lại để cập nhật thống kê
            } else {
                alert(result.message || 'Xóa thất bại.');
            }
        } catch (error) {
            console.error('Lỗi khi xóa:', error);
            alert('Lỗi kết nối khi xóa.');
        }
    }

    // Gán sự kiện click cho các nút
    document.getElementById('notification-timeline').addEventListener('click', (event) => {
        const viewBtn = event.target.closest('.btn-view');
        const deleteBtn = event.target.closest('.btn-delete');

        if (viewBtn) {
            const details = JSON.parse(viewBtn.dataset.details);
            // Đổ dữ liệu vào modal
            document.getElementById('modal-title').textContent = details.tieu_de;
            document.getElementById('modal-content-body').textContent = details.noi_dung;
            document.getElementById('modal-sender').textContent = details.ten_nguoi_gui || 'Hệ thống';
            document.getElementById('modal-recipient').textContent = details.ten_nguoi_nhan || 'Tất cả';
            document.getElementById('modal-type').textContent = details.loai;
            document.getElementById('modal-time').textContent = new Date(details.thoi_gian_gui).toLocaleString('vi-VN');
            openModal();
            return;
        }

        if (deleteBtn) {
            deleteNotification(deleteBtn.dataset.id);
            return;
        }
    });

    // --- Tự động tìm kiếm ---
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('search-form');
        if(searchForm){
            const searchInput = document.getElementById('search-input');
            let searchTimeout;
            searchInput.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchForm.submit();
                }, 500); 
            });
            searchForm.addEventListener('submit', function() {
                clearTimeout(searchTimeout);
            });
        }
    });
</script>
</body>
</html>