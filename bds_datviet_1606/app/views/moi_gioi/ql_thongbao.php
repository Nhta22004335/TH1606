<?php
// ql_thongbao.php - Trang hiển thị thông báo cho người dùng

// ----------------------------------------------------
// PHẦN 1: KẾT NỐI VÀ LẤY DỮ LIỆU
// ----------------------------------------------------

// Đây là giả định bạn có file database.php và hàm ketnoicsdl()
require_once __DIR__ . "/../../../config/database.php";

$pdo_connection_attempted = true;
$pdo = @ketnoicsdl(); // Thử kết nối thật

$is_mock_pdo = false;
$db_error = null;


// Bắt đầu phiên (MANDATORY)
if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy ID người dùng hiện tại từ session
$id_mg = $_SESSION["id_nguoi_dung"] ?? null; 

// --- DEBUG: THIẾT LẬP MOCK ID NẾU ĐANG CHẠY MOCKPDO ---
// Điều chỉnh ID người dùng khi ở chế độ Mock để hiển thị dữ liệu giả lập
if ($is_mock_pdo && !$id_mg) {
    // Nếu chưa đăng nhập và đang dùng Mock, sử dụng ID giả lập để hiển thị data mẫu
    $id_mg = 'test_user_mock'; 
}

$not_logged_in = false;
if (!$id_mg) {
    $not_logged_in = true;
}

// Xử lý tìm kiếm
$search = trim($_GET["search"] ?? "");
// --- Xử lý xóa thông báo ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $delete_id = $_POST["delete_id"];
    try {
        $sql_del = "DELETE FROM thong_bao WHERE id = :id AND id_nguoi_gui = :id_mg";
        $stmt_del = $pdo->prepare($sql_del);
        $stmt_del->execute(['id' => $delete_id, 'id_mg' => $id_mg]);
        $success_msg = "Đã xóa thông báo thành công!";
    } catch (PDOException $e) {
        $error_msg = "Lỗi khi xóa thông báo: " . $e->getMessage();
    }
}

$thongbaos = [];

if (!$not_logged_in) {
    // Truy vấn lấy thông báo chỉ dành cho người dùng hiện tại
    $sql = "SELECT * FROM thong_bao WHERE id_nguoi_gui= :id_mg";
    $params = ["id_mg" => $id_mg];

    // Đối với PostgreSQL (như bạn đang dùng) dùng ILIKE để tìm kiếm KHÔNG phân biệt chữ hoa/thường
    if ($search) {
        $sql .= " AND (tieu_de ILIKE :search OR noi_dung ILIKE :search)";
        // Thêm ký tự đại diện % vào giá trị tìm kiếm
        $params["search"] = "%$search%"; 
    }
    // Sắp xếp theo thời gian mới nhất
    $sql .= " ORDER BY thoi_gian_gui DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $thongbaos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) { 
        // Bắt lỗi CSDL và lưu lại để hiển thị (QUAN TRỌNG ĐỂ DEBUG)
        $db_error = "Lỗi CSDL: " . htmlspecialchars($e->getMessage()); 
        error_log("Lỗi truy vấn thông báo: " . $e->getMessage());
        $thongbaos = []; 
    }
}

// Logic lưu lịch sử tìm kiếm (giữ nguyên)
if (!$not_logged_in && !empty(trim($search)) && !$is_mock_pdo) {
    try {
        // Thêm cột thoi_gian_tim_kiem và sử dụng $id_mg
        $sql = "INSERT INTO lich_su_tim_kiem (id_nguoi_dung, tu_khoa_tim_kiem, thoi_gian_tim_kiem) VALUES (?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_mg, $search]); 
    } catch (PDOException $e) {
        // Ghi log lỗi khi lưu lịch sử tìm kiếm
    }
}
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Thông báo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full">

<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">

    <header class="mb-8 pb-4 border-b border-gray-200">
        <h1 class="text-3xl font-extrabold text-indigo-700 flex items-center">
            <i class="fas fa-bell mr-3"></i> Hộp thư Thông báo của bạn
        </h1>
        <p class="mt-2 text-base text-gray-500">Xem lại các thông báo quan trọng từ hệ thống và đội ngũ quản trị.</p>
    </header>
    
    <!-- KHỐI DEBUG QUAN TRỌNG
    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800 mb-6 shadow-sm">
        <p><strong>Trạng thái Debug:</strong></p>
        <ul class="list-disc ml-4 mt-1 space-y-0.5">
            <li><strong>ID đang truy vấn:</strong> <code class="bg-blue-100 px-1 py-0.5 rounded"><?= htmlspecialchars($id_mg ?? 'NULL') ?></code></li>
            <li><strong>Chế độ CSDL:</strong> <span class="font-semibold <?= $is_mock_pdo ? 'text-red-600' : 'text-green-600' ?>"><?= $is_mock_pdo ? 'MOCK PDO (GIẢ LẬP)' : 'KẾT NỐI THẬT' ?></span></li>
        </ul>
    </div> -->
    <!-- KẾT THÚC KHỐI DEBUG -->

    <?php if ($db_error): ?>
        <div class='p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-lg font-semibold shadow-md mb-6'>
            <i class="fas fa-exclamation-circle mr-2"></i> LỖI TRUY VẤN: <?= $db_error ?>
        </div>
    <?php endif; ?>

    <?php if ($not_logged_in && !$is_mock_pdo): ?>
        <div class="p-6 text-center bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded-lg shadow-xl">
            <p class="font-semibold text-lg"><i class="fas fa-lock mr-2"></i> Bạn chưa đăng nhập.</p>
            <p class="text-sm mt-1">Vui lòng đăng nhập để xem thông báo cá nhân.</p>
        </div>
    <?php else: ?>

        <form method="GET" action="" class="mb-6">
            <div class="relative">
                <input type="search" name="search" placeholder="Tìm kiếm theo tiêu đề hoặc nội dung..." 
                        value="<?= htmlspecialchars($search) ?>"
                        class="w-full border border-gray-300 rounded-xl px-5 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-lg transition">
                <button type="submit" class="absolute right-0 top-0 mt-3 mr-4 text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        <?php if (!empty($thongbaos)): ?>
            <div class="space-y-4">
                <?php foreach ($thongbaos as $tb):
                    // Xác định trạng thái và style
                    $is_unread = $tb["trang_thai"] === "chuaxem";
                    $row_class = $is_unread 
                        ? 'bg-white border-l-4 border-indigo-500 shadow-xl hover:shadow-2xl' 
                        : 'bg-white border-l-4 border-gray-200 shadow-md hover:shadow-lg';
                    
                    // --- ĐỊNH NGHĨA ICON DỰA TRÊN CỘT 'loai' ---
                    $loai = $tb['loai'] ?? 'hệ thống'; 
                    $icon = 'fas fa-envelope-open-text text-gray-500'; // Mặc định chung
                    $icon_color = 'text-gray-500';

                    if ($loai === 'admin_gửi') {
                        $icon = 'fas fa-user-shield'; // Admin gửi
                        $icon_color = 'text-red-500';
                    } elseif ($loai === 'giaodich') {
                        $icon = 'fas fa-handshake'; // Giao dịch
                        $icon_color = 'text-green-600';
                    } else {
                        $icon = 'fas fa-robot'; // Hệ thống/Khác
                        $icon_color = 'text-blue-500';
                    }
                    // --------------------------------------------------------------------------

                    $title = htmlspecialchars($tb['tieu_de'] ?? 'Thông báo từ Hệ thống');
                    // Cắt ngắn nội dung
                    $content_preview = mb_substr(strip_tags($tb['noi_dung']), 0, 150, 'UTF-8') . (mb_strlen($tb['noi_dung'], 'UTF-8') > 150 ? '...' : '');
                    
                    // Định dạng thời gian
                    $time_formatted = date('H:i, d/m/Y', strtotime($tb['thoi_gian_gui']));
                ?>

                <div class="p-5 rounded-xl flex items-start gap-4 cursor-pointer transition duration-300 <?= $row_class ?>">
                    
                    <div class="flex-shrink-0 pt-1">
                        <i class="<?= $icon ?> text-xl <?= $icon_color ?>"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg font-semibold text-gray-900 truncate <?= $is_unread ? 'font-extrabold' : 'font-semibold' ?>">
                                <?= $title ?>
                            </h3>
                            <span class="text-xs text-gray-400 flex-shrink-0 ml-4"><?= $time_formatted ?></span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 line-clamp-2"><?= $content_preview ?></p>
                        
                        <div class="mt-2 text-xs font-medium flex items-center space-x-3">
                             <?php if ($is_unread): ?>
                                 <span class="text-indigo-600 font-bold flex items-center">
                                     <i class="fas fa-circle text-xs mr-1"></i> Chưa xem
                                 </span>
                            <?php endif; ?>
                            <a href="trangchu.php?page=../moi_gioi/chi_tiet_thong_bao&id=<?= $tb['id'] ?>" class="text-indigo-500 hover:text-indigo-700 transition">Xem chi tiết &rarr;</a>
                            <form action="" method="POST" class="inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa thông báo này?');">
                            <input type="hidden" name="delete_id" value="<?= $tb['id'] ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700 ml-4 transition">
                                <i class="fas fa-trash-alt"></i> Xóa
                            </button>
</form>

                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-10 text-center bg-white rounded-xl shadow-lg border-2 border-dashed border-gray-300">
                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                <p class="font-bold text-xl text-gray-700">Hộp thư trống!</p>
                <p class="text-gray-500 mt-2">
                    <?php if ($search): ?>
                        Không có thông báo nào khớp với từ khóa tìm kiếm "<?= htmlspecialchars($search) ?>".
                    <?php else: ?>
                        Bạn chưa nhận được thông báo nào.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
