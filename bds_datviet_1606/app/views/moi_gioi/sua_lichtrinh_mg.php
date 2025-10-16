<?php
// BƯỚC 1: KHỞI TẠO VÀ KẾT NỐI CSDL
// ===============================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "../../../config/database.php";
$pdo = ketnoicsdl();
// Lấy ID người dùng hiện tại, giả sử là môi giới
$id_nguoi_dung_hien_tai = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_nguoi_dung_hien_tai) {
    // Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
    header('Location: login.php');
    exit;
}

// Lấy ID lịch trình từ URL
$id_lich_trinh = $_GET['id'] ?? null;
if (!$id_lich_trinh) {
    die("Lỗi: Không tìm thấy ID lịch trình.");
}

$error_message = '';
$success_message = '';
$schedule = null;
$customers = [];

// BƯỚC 2: LẤY DỮ LIỆU HIỆN TẠI CỦA LỊCH TRÌNH
// ===============================================
try {
    // Truy vấn thông tin lịch trình
    $stmt = $pdo->prepare("SELECT * FROM lich_trinh WHERE id = :id AND id_moi_gioi = :id_moi_gioi");
    $stmt->execute([':id' => $id_lich_trinh, ':id_moi_gioi' => $id_nguoi_dung_hien_tai]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        die("Lỗi: Lịch trình không tồn tại hoặc bạn không có quyền chỉnh sửa.");
    }
    
    // Truy vấn danh sách khách hàng để hiển thị trong dropdown
    // Giả sử người dùng có vai trò là 'khachhang'
    $stmt_customers = $pdo->query("select nd.id, i.ho_ten from nguoi_dung nd
        left join info_nguoi_dung i on i.id_nguoi_dung = nd.id
        left join phan_quyen pq on pq.id_nguoi_dung=nd.id
        left join quyen q on q.id=pq.id_quyen
        where q.vai_tro='khachhang'");
    $customers = $stmt_customers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Lỗi truy vấn CSDL: " . $e->getMessage());
}

// BƯỚC 3: XỬ LÝ KHI NGƯỜI DÙNG SUBMIT FORM (CẬP NHẬT)
// ===============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $id_khach_hang = $_POST['id_khach_hang'] ?? '';
    $tieu_de = trim($_POST['tieu_de'] ?? '');
    $dia_diem = trim($_POST['dia_diem'] ?? '');
    $thoi_gian_bat_dau_str = $_POST['thoi_gian_bat_dau'] ?? '';
    $thoi_gian_ket_thuc_str = $_POST['thoi_gian_ket_thuc'] ?? '';
    $trang_thai = $_POST['trang_thai'] ?? '';
    $ghi_chu = trim($_POST['ghi_chu'] ?? '');

    // --- Validate dữ liệu ---
    if (empty($id_khach_hang) || empty($tieu_de) || empty($thoi_gian_bat_dau_str) || empty($thoi_gian_ket_thuc_str) || empty($trang_thai)) {
        $error_message = "Vui lòng điền đầy đủ các trường bắt buộc (*).";
    } elseif (strtotime($thoi_gian_ket_thuc_str) <= strtotime($thoi_gian_bat_dau_str)) {
        $error_message = "Thời gian kết thúc phải sau thời gian bắt đầu.";
    } else {
        try {
            // Câu lệnh SQL UPDATE
            $sql = "UPDATE lich_trinh SET 
                        id_khach_hang = :id_khach_hang, 
                        tieu_de = :tieu_de, 
                        dia_diem = :dia_diem, 
                        thoi_gian_bat_dau = :thoi_gian_bat_dau, 
                        thoi_gian_ket_thuc = :thoi_gian_ket_thuc, 
                        trang_thai = :trang_thai, 
                        ghi_chu = :ghi_chu 
                    WHERE id = :id AND id_moi_gioi = :id_moi_gioi";

            $stmt = $pdo->prepare($sql);
            
            // Bind các tham số
            $params = [
                ':id_khach_hang' => $id_khach_hang,
                ':tieu_de' => $tieu_de,
                ':dia_diem' => $dia_diem,
                ':thoi_gian_bat_dau' => date('Y-m-d H:i:s', strtotime($thoi_gian_bat_dau_str)),
                ':thoi_gian_ket_thuc' => date('Y-m-d H:i:s', strtotime($thoi_gian_ket_thuc_str)),
                ':trang_thai' => $trang_thai,
                ':ghi_chu' => $ghi_chu,
                ':id' => $id_lich_trinh,
                ':id_moi_gioi' => $id_nguoi_dung_hien_tai
            ];

            if ($stmt->execute($params)) {
                $success_message = "Cập nhật lịch trình thành công!";
                // Tải lại dữ liệu mới nhất sau khi cập nhật
                $stmt = $pdo->prepare("SELECT * FROM lich_trinh WHERE id = :id");
                $stmt->execute([':id' => $id_lich_trinh]);
                $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

                // Tùy chọn: Chuyển hướng về trang lịch trình sau vài giây
                // header('Location: trangchu.php?page=../moi_gioi/lt_canhan');
                // exit;
                // header("refresh:2;url=main.php?page=lichtrinh");
            } else {
                $error_message = "Có lỗi xảy ra, không thể cập nhật lịch trình.";
            }
        } catch (PDOException $e) {
            $error_message = "Lỗi CSDL khi cập nhật: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Lịch Trình</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="h-full">
<div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-8 bg-white p-10 rounded-xl shadow-lg">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                <i class="fa-solid fa-calendar-check text-indigo-600"></i>
                Chỉnh Sửa Lịch Trình
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Cập nhật thông tin chi tiết cho cuộc hẹn của bạn.
            </p>
        </div>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Lỗi!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Thành công!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($success_message) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($schedule): ?>
        <form class="mt-8 space-y-6" action="sua_lichtrinh.php?id=<?= htmlspecialchars($id_lich_trinh) ?>" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                
                <div>
                    <label for="tieu_de" class="font-medium text-gray-700">Tiêu đề <span class="text-red-500">*</span></label>
                    <input id="tieu_de" name="tieu_de" type="text" required class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mt-1" placeholder="Ví dụ: Gặp khách hàng xem nhà" value="<?= htmlspecialchars($schedule['tieu_de'] ?? '') ?>">
                </div>

                <div class="pt-4">
                     <label for="id_khach_hang" class="font-medium text-gray-700">Khách hàng <span class="text-red-500">*</span></label>
                     <select id="id_khach_hang" name="id_khach_hang" required class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mt-1">
                        <option value="">-- Chọn khách hàng --</option>
                        <?php foreach($customers as $customer): ?>
                            <option value="<?= htmlspecialchars($customer['id']) ?>" <?= ($schedule['id_khach_hang'] == $customer['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($customer['ho_ten']) ?>
                            </option>
                        <?php endforeach; ?>
                     </select>
                </div>
                
                <div class="pt-4">
                    <label for="dia_diem" class="font-medium text-gray-700">Địa điểm</label>
                    <input id="dia_diem" name="dia_diem" type="text" class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mt-1" placeholder="Ví dụ: Chung cư The Gold View, Q4" value="<?= htmlspecialchars($schedule['dia_diem'] ?? '') ?>">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
                    <div>
                        <label for="thoi_gian_bat_dau" class="font-medium text-gray-700">Thời gian bắt đầu <span class="text-red-500">*</span></label>
                        <input id="thoi_gian_bat_dau" name="thoi_gian_bat_dau" type="datetime-local" required class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mt-1" value="<?= date('Y-m-d\TH:i', strtotime($schedule['thoi_gian_bat_dau'])) ?>">
                    </div>
                    <div>
                        <label for="thoi_gian_ket_thuc" class="font-medium text-gray-700">Thời gian kết thúc <span class="text-red-500">*</span></label>
                        <input id="thoi_gian_ket_thuc" name="thoi_gian_ket_thuc" type="datetime-local" required class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mt-1" value="<?= date('Y-m-d\TH:i', strtotime($schedule['thoi_gian_ket_thuc'])) ?>">
                    </div>
                </div>

                <div class="pt-4">
                     <label for="trang_thai" class="font-medium text-gray-700">Trạng thái <span class="text-red-500">*</span></label>
                     <select id="trang_thai" name="trang_thai" required class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mt-1">
                        <option value="choxacnhan" <?= ($schedule['trang_thai'] == 'choxacnhan') ? 'selected' : '' ?>>Chờ xác nhận</option>
                        <option value="daxacnhan" <?= ($schedule['trang_thai'] == 'daxacnhan') ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="dahuy" <?= ($schedule['trang_thai'] == 'dahuy') ? 'selected' : '' ?>>Đã hủy</option>
                     </select>
                </div>

                <div class="pt-4">
                    <label for="ghi_chu" class="font-medium text-gray-700">Ghi chú</label>
                    <textarea id="ghi_chu" name="ghi_chu" rows="4" class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mt-1" placeholder="Thêm ghi chú nếu cần..."><?= htmlspecialchars($schedule['ghi_chu'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6">
                <a href="trangchu.php?page=../moi_gioi/lt_canhan" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại lịch trình
                </a>
                <button type="submit" class="group relative flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Lưu thay đổi
                </button>
            </div>
        </form>
        <?php else: ?>
             <div class="text-center">
                <p class="text-red-500">Không thể tải dữ liệu cho lịch trình này.</p>
                <a href="main.php?page=lichtrinh" class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại lịch trình
                </a>
             </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>