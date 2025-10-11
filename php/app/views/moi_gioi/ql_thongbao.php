<?php
// Bắt đầu phiên (Giữ nguyên logic PHP)
require_once __DIR__ . "/../../../config/database.php";
$pdo = ketnoicsdl();

if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy ID người dùng từ session
$id_mg = $_SESSION["id_nguoi_dung"] ?? null;
if (!$id_mg) {
    $not_logged_in = true;
} else {
    $not_logged_in = false;
}

// Xử lý tìm kiếm
$search = trim($_GET["search"] ?? "");
$thongbaos = [];

if (!$not_logged_in) {
    // Truy vấn lấy thông báo từ CSDL
    $sql = "SELECT * FROM thong_bao WHERE id_nguoi_dung = :id_mg";
    $params = ["id_mg" => $id_mg];

    if ($search) {
        // Tìm kiếm theo tiêu đề hoặc nội dung
        $sql .= " AND (tieu_de ILIKE :search OR noi_dung ILIKE :search)";
        $params["search"] = "%$search%";
    }
    $sql .= " ORDER BY thoi_gian_gui DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $thongbaos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Xử lý lỗi
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Thông Báo Cá Nhân</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJ8S+anWHD9+lWlI/Bw4g8q6uL+yqT2S8cRAB6XQp9r/9C7M/dFm3J8mN/K2uYmQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .notification-item {
            cursor: pointer;
        }
        .notification-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">


    <!-- Header và Tiêu đề -->
    <header class="mb-6 border-b border-gray-200 pb-4">
        <h1 class="flex items-center text-2xl font-bold text-gray-800">
            <i class="fas fa-bell mr-4 text-2xl text-yellow-400 animate-pulse"></i> Hộp Thư Thông Báo
        </h1>
        <p class="text-sm text-gray-500 mt-1">Quản lý các thông báo từ hệ thống và quản trị viên.</p>
    </header>

    <?php if ($not_logged_in): ?>
        <div class='p-6 bg-red-100 text-red-800 rounded-xl text-center font-semibold shadow-md'>
            <i class="fas fa-exclamation-triangle mr-2"></i> Vui lòng đăng nhập để xem thông báo!
        </div>
    <?php else: ?>
        <!-- Thanh tìm kiếm và Hành động -->
        <div class="bg-white p-3 rounded-xl shadow-lg mb-8 flex flex-col md:flex-row md:items-center justify-between space-y-3 md:space-y-0 md:space-x-3">
            <!-- Form Tìm kiếm -->
            <form method="GET" class="w-full md:w-3/5 relative flex items-center">
                <?php if (isset($_GET['page'])): ?>
                    <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page']) ?>">
                <?php endif; ?>

                <input type="text" name="search" placeholder="Tìm kiếm theo tiêu đề hoặc nội dung..."
                        value="<?= htmlspecialchars($search) ?>"
                        class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-gray-200 rounded-lg 
                                focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all duration-300">
                <i class="fas fa-search absolute left-3 text-gray-400 text-base"></i>
            </form>

            <!-- Buttons Group -->
            <div class="flex space-x-3 w-full md:w-auto">
                <!-- Nút Làm mới -->
                <a href="?page=<?php echo htmlspecialchars($_GET['page'] ?? '../moi_gioi/ql_thongbao'); ?>"
                   class="flex-1 md:flex-none bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-lg 
                          text-sm transition-all duration-300 shadow-md flex items-center justify-center">
                    <i class="fas fa-sync-alt mr-2"></i> Làm mới
                </a>
                <!-- Nút Xóa tất cả (Giữ nguyên) -->
                <button type="button" onclick="deleteAllNotifications()"
                        class="flex-1 md:flex-none bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2.5 rounded-lg 
                               text-sm transition-all duration-300 shadow-md flex items-center justify-center">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa tất cả
                </button>
            </div>
        </div>

        <!-- Danh sách Thông báo -->
        <div id="notificationList" class="space-y-4">
            <?php if (!empty($thongbaos)): ?>
                <?php foreach ($thongbaos as $tb):
                    $is_unread = $tb["trang_thai"] === "chuaxem";
                    $row_class = $is_unread 
                        ? 'bg-white border-l-4 border-indigo-500 shadow-lg' 
                        : 'bg-white border-l-4 border-gray-200 shadow-md';
                    
                    // $icon = ($tb['hinh_thuc'] === 'email') ? 'fas fa-envelope text-red-500' : 'fas fa-comment-alt text-blue-500';
                    $title = htmlspecialchars($tb['tieu_de'] ?? 'Thông báo từ Hệ thống');
                    $content_preview = mb_substr(strip_tags($tb['noi_dung']), 0, 150, 'UTF-8') . (mb_strlen($tb['noi_dung'], 'UTF-8') > 150 ? '...' : '');
                ?>
                    <div id="notif-<?= $tb['id'] ?>" 
                         class="group notification-item p-5 rounded-xl flex items-center space-x-4 transition duration-200 <?= $row_class ?>"
                         data-id="<?= $tb['id'] ?>" 
                         onclick="markAsRead(<?= $tb['id'] ?>)">
                        
                        <!-- Icon Thông báo -->
                        <div class="flex-shrink-0">
                            <i class="<?= $icon ?> text-2xl"></i>
                        </div>

                        <!-- Nội dung chính -->
                        <div class="flex-1 min-w-0">
                            <!-- Tiêu đề & Trạng thái -->
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-bold text-lg <?= $is_unread ? 'text-indigo-700' : 'text-gray-800' ?> truncate">
                                    <?= $title ?>
                                </h3>
                                <?php if ($is_unread): ?>
                                    <span class="px-3 py-1 text-xs font-bold bg-yellow-100 text-yellow-700 rounded-full flex-shrink-0">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Mới
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full flex-shrink-0">
                                        <i class="fas fa-check-circle mr-1"></i> Đã đọc
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Nội dung rút gọn -->
                            <p class="text-gray-600 text-sm mb-1">
                                <?= $content_preview ?>
                            </p>
                            
                            <!-- Thời gian -->
                            <p class="text-xs text-gray-400">
                                <i class="far fa-clock mr-1"></i> Gửi lúc: <?= date('H:i d/m/Y', strtotime($tb["thoi_gian_gui"])) ?>
                            </p>
                        </div>

                        <!-- Nút Xóa Đơn (Mới - Xuất hiện khi hover) -->
                        <button type="button" 
                                onclick="event.stopPropagation(); deleteSingleNotification(<?= $tb['id'] ?>);"
                                class="flex-shrink-0 text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 p-2 rounded-full transition duration-200"
                                title="Xóa thông báo này">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Trạng thái không có dữ liệu -->
                <div class="bg-indigo-50 border border-indigo-200 text-indigo-700 p-8 rounded-xl text-center shadow-inner mt-10">
                    <i class="fas fa-inbox text-5xl mb-3"></i>
                    <p class="font-semibold text-lg">Không tìm thấy thông báo nào.</p>
                    <p class="text-sm mt-1">
                        <?php if ($search): ?>
                            Không có thông báo nào khớp với từ khóa "<?= htmlspecialchars($search) ?>".
                        <?php else: ?>
                            Hộp thư của bạn hiện đang trống.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>


<script>
    // Hàm giả lập đánh dấu đã đọc
    function markAsRead(notificationId) {
        // 1. Cập nhật giao diện ngay lập tức
        const item = document.getElementById(`notif-${notificationId}`);
        if (item && item.classList.contains('border-indigo-500')) {
            item.classList.remove('border-indigo-500');
            item.classList.add('border-gray-200');
            item.querySelector('h3').classList.remove('text-indigo-700');
            item.querySelector('h3').classList.add('text-gray-800');
            
            const statusSpan = item.querySelector('.font-bold.bg-yellow-100');
            if (statusSpan) {
                statusSpan.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Đã đọc';
                statusSpan.classList.remove('bg-yellow-100', 'text-yellow-700', 'font-bold');
                statusSpan.classList.add('bg-green-100', 'text-green-700', 'font-medium');
            }
        }

        // 2. (Giả lập) Gửi yêu cầu AJAX đến server để cập nhật CSDL
        // console.log(`Thông báo ${notificationId} được gửi yêu cầu đánh dấu đã đọc.`);
    }

    // HÀM MỚI: Xóa MỘT thông báo
    function deleteSingleNotification(notificationId) {
        if (!confirm(`Bạn có chắc chắn muốn xóa thông báo ID ${notificationId} không?`)) {
            return;
        }

        // --- GIẢ LẬP LOGIC AJAX XÓA ĐƠN ---
        
        // Trong môi trường thực, bạn sẽ fetch đến endpoint PHP xử lý xóa:
        /*
        fetch('/api/delete_notification.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Xóa phần tử khỏi DOM
                const item = document.getElementById(`notif-${notificationId}`);
                if (item) item.remove();
                alert("Thông báo đã được xóa thành công!");
            } else {
                alert("Lỗi: Không thể xóa thông báo. Vui lòng thử lại.");
            }
        })
        .catch(err => alert("Lỗi kết nối server."));
        */
        
        // Hiện tại: dùng alert và xóa khỏi DOM giả lập
        const item = document.getElementById(`notif-${notificationId}`);
        if (item) {
            item.remove();
            alert(`✅ Thông báo ID ${notificationId} đã được xóa thành công (giả lập).`);
        } else {
            alert("Lỗi: Không tìm thấy thông báo để xóa.");
        }
    }

    // Hàm XÓA TẤT CẢ thông báo
    function deleteAllNotifications() {
        if (!confirm("⚠️ CẢNH BÁO: Bạn có chắc chắn muốn xóa TẤT CẢ thông báo không? Hành động này không thể hoàn tác.")) {
            return;
        }
        
        // Hiện tại: dùng alert và reload giả lập
        alert("✅ Yêu cầu xóa tất cả thông báo đã được gửi. (Chức năng xóa thật cần endpoint server)");
        window.location.reload(); 
    }
</script>
</body>
</html>
