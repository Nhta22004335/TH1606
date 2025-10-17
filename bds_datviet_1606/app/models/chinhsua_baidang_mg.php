<?php
// Bắt buộc phải khởi động session để sử dụng $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// =================================================================
// 1. KẾT NỐI CSDL & XÁC THỰC
// =================================================================
require_once "../../../config/database.php";
try {
    $pdo = ketnoicsdl();
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
}

$current_user_id = $_SESSION['id_nguoi_dung'] ?? null;
if (!$current_user_id) {
    header("Location: /login.php"); // Chuyển hướng nếu chưa đăng nhập
    exit();
}

$post_id = $_GET['id']??'';

// =================================================================
// 2. XỬ LÝ KHI FORM ĐƯỢC GỬI ĐI (METHOD POST)
// =================================================================
$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Kiểm tra lại quyền sở hữu trước khi cập nhật
    $sql_check_owner = "SELECT id_bat_dong_san FROM bai_dang WHERE id = :post_id AND id_nguoi_dung = :user_id";
    $stmt_check = $pdo->prepare($sql_check_owner);
    $stmt_check->execute([':post_id' => $post_id, ':user_id' => $current_user_id]);
    $bds_id_result = $stmt_check->fetch(PDO::FETCH_ASSOC);

   
        $id_bat_dong_san = $bds_id_result['id_bat_dong_san'];

        // Lấy dữ liệu từ form
        $tieu_de = trim($_POST['tieu_de']);
        $mo_ta = trim($_POST['mo_ta']);
        $gia = filter_var($_POST['gia'], FILTER_SANITIZE_NUMBER_INT);
        $hinh_thuc = $_POST['hinh_thuc'];
        $dien_tich = trim($_POST['dien_tich']);
        $khu_vuc = trim($_POST['khu_vuc']);

        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        $pdo->beginTransaction();
        try {
            // Cập nhật bảng `bat_dong_san`
            $sql_update_bds = "UPDATE bat_dong_san SET 
                                dien_tich = :dien_tich, 
                                khu_vuc = :khu_vuc
                            WHERE id = :bds_id";
            $stmt_bds = $pdo->prepare($sql_update_bds);
            $stmt_bds->execute([
                ':dien_tich' => $dien_tich,
                ':khu_vuc' => $khu_vuc,
                ':bds_id' => $id_bat_dong_san
            ]);

            // Cập nhật bảng `bai_dang`
            $sql_update_post = "UPDATE bai_dang SET 
                                tieu_de = :tieu_de, 
                                mo_ta = :mo_ta, 
                                gia = :gia, 
                                hinh_thuc = :hinh_thuc
                            WHERE id = :post_id";
            $stmt_post = $pdo->prepare($sql_update_post);
            $stmt_post->execute([
                ':tieu_de' => $tieu_de,
                ':mo_ta' => $mo_ta,
                ':gia' => $gia,
                ':hinh_thuc' => $hinh_thuc,
                ':post_id' => $post_id
            ]);

            // Commit transaction
            $pdo->commit();
            
            // Đặt thông báo thành công và chuyển hướng
            $_SESSION['success_message'] = "Cập nhật bài đăng thành công!";
            

        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $pdo->rollBack();
            $error_message = "Có lỗi xảy ra trong quá trình cập nhật: " . $e->getMessage();
        }
    
}


// =================================================================
// 3. LẤY DỮ LIỆU HIỆN TẠI CỦA BÀI ĐĂNG ĐỂ HIỂN THỊ TRÊN FORM
// =================================================================
// Thêm điều kiện `bd.id_nguoi_dung` để bảo mật
$sql_fetch = "
    SELECT bd.*, bds.dien_tich, bds.khu_vuc
    FROM bai_dang AS bd
    JOIN bat_dong_san AS bds ON bd.id_bat_dong_san = bds.id
    WHERE bd.id = :post_id AND bd.id_nguoi_dung = :user_id
";
$stmt_fetch = $pdo->prepare($sql_fetch);
$stmt_fetch->execute([':post_id' => $post_id, ':user_id' => $current_user_id]);
$post = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy bài đăng hoặc không đúng chủ sở hữu
if (!$post) {
    die("Không tìm thấy bài đăng hoặc bạn không có quyền truy cập.");
}
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa: <?= htmlspecialchars($post['tieu_de']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; }
        .sticky-sidebar { position: sticky; top: 2rem; }
    </style>
</head>
<body class="h-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-8">
            <nav class="mb-4 text-sm font-medium text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center"><a href="trangchu.php?page=../moi_gioi/ql_baidang_mg" class="hover:text-indigo-600">Quản lý bài đăng</a></li>
                    <li class="flex items-center mx-2"><i class="fa-solid fa-chevron-right text-xs"></i></li>
                    <li class="flex items-center text-gray-800">Chỉnh sửa bài đăng</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Chỉnh sửa bài đăng</h1>
            <p class="mt-1 text-sm text-gray-600">Cập nhật thông tin cho bất động sản của bạn.</p>
        </div>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <strong class="font-bold">Lỗi!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
             <input type="hidden" name="post_id" value="<?= $post_id ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 lg:gap-8">
                <main class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Thông tin cơ bản</h2>
                        <div class="space-y-4">
                            <div>
                                <label for="tieu_de" class="block text-sm font-medium text-gray-700">Tiêu đề bài đăng</label>
                                <input type="text" name="tieu_de" id="tieu_de" value="<?= htmlspecialchars($post['tieu_de']) ?>" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="hinh_thuc" class="block text-sm font-medium text-gray-700">Hình thức</label>
                                <select id="hinh_thuc" name="hinh_thuc" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="ban" <?= $post['hinh_thuc'] == 'ban' ? 'selected' : '' ?>>Bán</option>
                                    <option value="thue" <?= $post['hinh_thuc'] == 'thue' ? 'selected' : '' ?>>Cho thuê</option>
                                </select>
                            </div>
                        </div>
                    </div>
                     <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Chi tiết bất động sản</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="dien_tich" class="block text-sm font-medium text-gray-700">Diện tích (m²)</label>
                                <input type="text" name="dien_tich" id="dien_tich" value="<?= htmlspecialchars($post['dien_tich']) ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                             
                             
                        </div>
                    </div>
                     <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Vị trí & Mô tả</h2>
                        <div class="space-y-4">
                            <div>
                                <label for="khu_vuc" class="block text-sm font-medium text-gray-700">Địa chỉ / Khu vực</label>
                                <input type="text" name="khu_vuc" id="khu_vuc" value="<?= htmlspecialchars($post['khu_vuc']) ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="mo_ta" class="block text-sm font-medium text-gray-700">Mô tả chi tiết</label>
                                <textarea id="mo_ta" name="mo_ta" rows="8" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?= htmlspecialchars($post['mo_ta']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </main>

                <aside class="mt-8 lg:mt-0">
                    <div class="sticky-sidebar space-y-6">
                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Mức giá</h2>
                            <div>
                                <label for="gia" class="block text-sm font-medium text-gray-700">Giá (VNĐ)</label>
                                <input type="number" name="gia" id="gia" value="<?= htmlspecialchars($post['gia']) ?>" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Hành động</h2>
                            <div class="space-y-3">
                                <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fa-solid fa-save mr-2"></i> Lưu thay đổi
                                </button>
                                <a href="trangchu.php?page=../moi_gioi/ql_baidang_mg&id=<?= $post_id ?>" class="w-full flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 transition-colors">
                                    Hủy bỏ
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</body>
</html>