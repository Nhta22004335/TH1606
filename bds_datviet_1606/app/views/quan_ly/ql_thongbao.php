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

// Lấy từ khóa tìm kiếm (nếu có)
$search = $_GET['search'] ?? '';
$search = trim($search); 

// =================================================================
// 2. TRUY VẤN DỮ LIỆU (CÓ LỌC THEO TỪ KHÓA)
// =================================================================
$sql = "
    SELECT 
        tb.id,
        tb.tieu_de,
        tb.noi_dung,
        tb.loai,
        tb.thoi_gian_gui,
        tb.trang_thai,
        -- Lấy tên người gửi
        inf_gui.ho_ten AS ten_nguoi_gui,
        -- Lấy tên người nhận (chỉ lấy 1 tên vì 1 hàng TB chỉ có 1 người nhận)
        inf_nhan.ho_ten AS ten_nguoi_nhan
    FROM 
        thong_bao AS tb
    LEFT JOIN 
        nguoi_dung AS nd_gui ON tb.id_nguoi_gui = nd_gui.id
    LEFT JOIN 
        info_nguoi_dung AS inf_gui ON nd_gui.id = inf_gui.id_nguoi_dung
    LEFT JOIN 
        nguoi_dung AS nd_nhan ON tb.id_nguoi_dung = nd_nhan.id
    LEFT JOIN 
        info_nguoi_dung AS inf_nhan ON nd_nhan.id = inf_nhan.id_nguoi_dung
";

// Nếu có nhập từ khóa thì thêm điều kiện WHERE
if (!empty($search)) {
    // Tìm kiếm theo tiêu đề, nội dung, người gửi hoặc người nhận
    $sql .= "
        WHERE 
            LOWER(tb.tieu_de) LIKE LOWER(:search)
            OR LOWER(tb.noi_dung) LIKE LOWER(:search)
            OR LOWER(inf_gui.ho_ten) LIKE LOWER(:search)
            OR LOWER(inf_nhan.ho_ten) LIKE LOWER(:search)
    ";
}

$sql .= " ORDER BY tb.thoi_gian_gui DESC;";

$stmt = $pdo->prepare($sql);

// Nếu có từ khóa thì gán giá trị vào câu truy vấn
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$stmt->execute();
$thongbao = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =================================================================
// 3. HÀM HIỂN THỊ VÀ THỐNG KÊ
// =================================================================
function getStatusBadgeTB($status) {
    $map = [
        'chuaxem' => ['text' => 'Chưa xem', 'class' => 'bg-orange-100 text-orange-800'],
        'daxem'  => ['text' => 'Đã xem', 'class' => 'bg-green-100 text-green-800'],
    ];
    $info = $map[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-800'];
    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$info['class']}'>{$info['text']}</span>";
}

// Thống kê đơn giản
$stats = [
    'total'  => count($thongbao),
    'chuaxem' => count(array_filter($thongbao, fn($t) => $t['trang_thai'] === 'chuaxem')),
];
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Thông báo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full p-4 sm:p-8">

    <div class="max-w-7xl mx-auto">
        <header>
            <div class="sm:flex sm:items-center sm:justify-between mb-6 pb-4 border-b">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Quản lý Thông báo</h1>
                    <p class="mt-2 text-sm text-slate-600">Theo dõi, tìm kiếm và xóa thông báo đã gửi.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Tổng số thông báo</dt><dd class="mt-1 text-3xl font-semibold text-slate-900"><?= $stats['total'] ?></dd></dl></div></div>
                <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Chưa xem</dt><dd class="mt-1 text-3xl font-semibold text-orange-600"><?= $stats['chuaxem'] ?></dd></dl></div></div>
            </div>
        </header>

        <main class="mt-8">
            <form action="" method="GET" class="flex gap-4 mb-6">
                <input 
                    type="text" 
                    name="search" 
                    value="<?= htmlspecialchars($search) ?>" 
                    placeholder="Tìm theo tiêu đề, nội dung, người gửi/nhận..." 
                    class="flex-grow border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >
                <button 
                    type="submit" 
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-indigo-700"
                >
                    <i class="fa-solid fa-magnifying-glass mr-1"></i>Tìm
                </button>
            </form>
        
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Tiêu đề & Nội dung</th>
                                <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Người gửi/Loại</th>
                                <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Người nhận</th>
                                <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Thời gian</th>
                                <th scope="col" class="px-6 py-3 text-center text-sm font-semibold text-slate-600">Trạng thái</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Hành động</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($thongbao)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-slate-500 italic">Không tìm thấy thông báo nào.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($thongbao as $tb): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 max-w-xs">
                                        <p class="font-semibold text-slate-800 text-sm" title="<?= htmlspecialchars($tb['tieu_de']) ?>">
                                            <?= htmlspecialchars($tb['tieu_de']) ?>
                                        </p>
                                        <p class="text-xs text-slate-500 truncate mt-1" title="<?= htmlspecialchars($tb['noi_dung']) ?>">
                                            <?= htmlspecialchars(substr($tb['noi_dung'], 0, 50)) ?><?= (strlen($tb['noi_dung']) > 50) ? '...' : '' ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                        <div class="font-medium"><?= htmlspecialchars($tb['ten_nguoi_gui'] ?? 'Hệ thống') ?></div>
                                        <span class="text-xs text-indigo-500 italic">(<?= htmlspecialchars($tb['loai']) ?>)</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                        <?= htmlspecialchars($tb['ten_nguoi_nhan'] ?? 'Không rõ') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        <?= date('H:i d/m/Y', strtotime($tb['thoi_gian_gui'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?= getStatusBadgeTB($tb['trang_thai']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <button 
                                            class="text-indigo-600 hover:text-indigo-900 p-2 btn-view-tb" 
                                            data-id="<?= htmlspecialchars($tb['id']) ?>" 
                                            data-title="<?= htmlspecialchars($tb['tieu_de']) ?>"
                                            data-content="<?= htmlspecialchars($tb['noi_dung']) ?>"
                                            data-sender="<?= htmlspecialchars($tb['ten_nguoi_gui'] ?? 'Hệ thống') ?>"
                                            data-recipient="<?= htmlspecialchars($tb['ten_nguoi_nhan'] ?? 'Tất cả') ?>"
                                            data-type="<?= htmlspecialchars($tb['loai']) ?>"
                                            data-time="<?= date('H:i d/m/Y', strtotime($tb['thoi_gian_gui'])) ?>"
                                            title="Xem nội dung chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button 
                                            class="text-red-600 hover:text-red-900 p-2 btn-delete-tb" 
                                            data-id="<?= htmlspecialchars($tb['id']) ?>" 
                                            title="Xóa thông báo">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="notificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50 hidden" onclick="closeModal()">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full m-4" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-xl font-semibold text-indigo-600">Nội dung Thông báo</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-xs text-slate-500 flex justify-between">
                    <span><i class="fa-solid fa-user-tag mr-1"></i> Gửi bởi: <strong id="modal-sender"></strong></span>
                    <span><i class="fa-solid fa-user-check mr-1"></i> Người nhận: <strong id="modal-recipient"></strong></span>
                </p>
                <p class="text-xs text-slate-500 flex justify-between">
                    <span><i class="fa-solid fa-layer-group mr-1"></i> Loại: <strong id="modal-type"></strong></span>
                    <span><i class="fa-solid fa-clock mr-1"></i> Thời gian: <strong id="modal-time"></strong></span>
                </p>
                <hr>
                <h4 class="text-lg font-bold text-slate-900" id="modal-title"></h4>
                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                    <p class="text-slate-700 whitespace-pre-wrap" id="modal-content"></p>
                </div>
            </div>
            <div class="p-4 border-t flex justify-end">
                <button type="button" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700" onclick="closeModal()">Đóng</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('notificationModal');

        function closeModal() {
            modal.classList.add('hidden');
        }

        document.querySelectorAll('.btn-view-tb').forEach(button => {
            button.addEventListener('click', function() {
                const title = this.getAttribute('data-title');
                const content = this.getAttribute('data-content');
                const sender = this.getAttribute('data-sender');
                const recipient = this.getAttribute('data-recipient');
                const type = this.getAttribute('data-type');
                const time = this.getAttribute('data-time');

                // Cập nhật nội dung Modal
                document.getElementById('modal-title').textContent = title;
                document.getElementById('modal-content').textContent = content;
                document.getElementById('modal-sender').textContent = sender;
                document.getElementById('modal-recipient').textContent = recipient;
                document.getElementById('modal-type').textContent = type;
                document.getElementById('modal-time').textContent = time;

                // Hiển thị Modal
                modal.classList.remove('hidden');
            });
        });
        
        // Đóng Modal khi nhấn ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-delete-tb').forEach(button => {
                button.addEventListener('click', function() {
                    const tbId = this.getAttribute('data-id');
                    
                    if (!confirm("Bạn có chắc chắn muốn xóa thông báo này vĩnh viễn?")) {
                        return;
                    }

                    // Gọi API xóa thông báo
                    fetch('../../models/xoa_thongbao_qt.php', { // Giả định đường dẫn API xóa
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: tbId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.status === 'success') {
                            location.reload(); // Tải lại trang sau khi xóa thành công
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi:', error);
                        alert('Đã xảy ra lỗi khi xóa thông báo.');
                    });
                });
            });
        });
    </script>
</body>
</html>