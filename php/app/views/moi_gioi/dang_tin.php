<?php
// Giữ nguyên logic PHP gốc (cần thay thế bằng logic UPDATE/INSERT thực tế)
require_once __DIR__ . '/../../../config/database.php'; 

// Kiểm tra đăng nhập (Giả lập session start nếu chưa có)
if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy ID người dùng từ session
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? 101; // Giả định ID người dùng nếu chưa đăng nhập

// --- Dữ liệu Mẫu ---
// Vì đây là trang ĐĂNG MỚI, ta sẽ để các giá trị mặc định là rỗng
$tin = [
    'id' => null,
    'tieu_de' => '', 
    'mo_ta' => '',   
    'chuyen_muc' => '', 
    'trang_thai' => 'choduyet', 
    'anh_tin' => '' 
];

// Định nghĩa các tùy chọn trạng thái
$trangthai_options = [
    'choduyet' => 'Chờ duyệt',
    'dangban' => 'Đang bán',
    'daban' => 'Đã bán',
    'dathue' => 'Đã thuê'
];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['capnhattintuc'])) {
    // --- BẮT ĐẦU LOGIC XỬ LÝ SERVER ---
    $file = $_FILES['anh_tin'] ?? null;
    $pdo = ketnoicsdl();

    // Giả lập xử lý file upload thành công
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        // Đây là nơi logic INSERT thực tế sẽ xảy ra
        echo "<script>alert('✅ Đăng tin thành công! Tin của bạn đang được duyệt.');</script>";
    } else {
         echo "<script>alert('❌ Lỗi đăng tin: Vui lòng kiểm tra lại dữ liệu và ảnh.');</script>";
    }
    // --- KẾT THÚC LOGIC XỬ LÝ SERVER ---
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Tin Bất Động Sản Mới</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJ8S+anWHD9+lWlI/Bw4g8q6uL+yqT2S8cRAB6XQp9r/9C7M/dFm3J8mN/K2uYmQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Thay đổi: max-w-3xl -> max-w-2xl -->
    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Thay đổi: p-8 sm:p-10 -> p-6 sm:p-8 -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-2xl border border-blue-100">
            
            <!-- Header -->
            <header class="mb-6 border-b border-gray-200 pb-4 flex items-center gap-3">
                <!-- Thay đổi: text-4xl -> text-2xl -->
                <i class="fas fa-house-circle-check text-2xl text-blue-600"></i>
                <!-- Thay đổi: text-3xl -> text-2xl -->
                <h1 class="text-2xl font-extrabold text-gray-800">Đăng Tin Bất Động Sản Mới</h1>
            </header>

            <!-- Thay đổi: space-y-8 -> space-y-6 -->
            <form action="" method="post" enctype="multipart/form-data" class="space-y-6">

                <input type="hidden" name="id_tin" value="">

                <!-- Tiêu đề -->
                <div>
                    <!-- Thay đổi: text-lg -> text-base -->
                    <label for="tieu_de" class="block font-bold text-gray-700 mb-2 text-base">
                        <i class="fas fa-bullseye mr-2 text-blue-500"></i> Tiêu đề tin đăng <span class="text-red-500">*</span>
                    </label>
                    <!-- Thay đổi: px-5 py-3 text-lg -> px-4 py-2.5 text-base, rounded-xl -> rounded-lg -->
                    <input type="text" name="tieu_de" id="tieu_de" 
                           value="<?= htmlspecialchars($tin['tieu_de'] ?? '') ?>"
                           placeholder="Ví dụ: Bán căn hộ cao cấp 3 phòng ngủ tại Q.1" 
                           required
                           class="w-full border border-gray-300 px-4 py-2.5 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-200 text-base">
                </div>

                <!-- Mô tả -->
                <div>
                    <!-- Thay đổi: text-lg -> text-base -->
                    <label for="mo_ta" class="block font-bold text-gray-700 mb-2 text-base">
                        <i class="fas fa-pen-alt mr-2 text-blue-500"></i> Mô tả chi tiết
                    </label>
                    <!-- Thay đổi: px-5 py-3 text-lg -> px-4 py-2.5 text-base, rounded-xl -> rounded-lg -->
                    <textarea name="mo_ta" id="mo_ta" rows="5" 
                              placeholder="Mô tả chi tiết về vị trí, tiện ích, pháp lý và giá bán..." 
                              required
                              class="w-full border border-gray-300 px-4 py-2.5 rounded-lg resize-y focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-200 text-base"><?= htmlspecialchars($tin['mo_ta'] ?? '') ?></textarea>
                </div>

                <!-- Chuyên mục & Trạng thái -->
                <!-- Thay đổi: gap-8 -> gap-6 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Chuyên mục -->
                    <div>
                        <!-- Thay đổi: text-lg -> text-base -->
                        <label for="chuyen_muc" class="block font-bold text-gray-700 mb-2 text-base">
                            <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i> Khu vực/Chuyên mục
                        </label>
                        <!-- Thay đổi: px-5 py-3 text-lg -> px-4 py-2.5 text-base, rounded-xl -> rounded-lg -->
                        <input type="text" name="chuyen_muc" id="chuyen_muc"
                               value="<?= htmlspecialchars($tin['chuyen_muc'] ?? '') ?>"
                               placeholder="VD: Nhà phố, Căn hộ, Đất nền..."
                               required
                               class="w-full border border-gray-300 px-4 py-2.5 rounded-lg focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-200 text-base">
                    </div>

                    <!-- Trạng thái -->
                    <div>
                        <!-- Thay đổi: text-lg -> text-base -->
                        <label for="trang_thai" class="block font-bold text-gray-700 mb-2 text-base">
                            <i class="fas fa-shield-alt mr-2 text-blue-500"></i> Trạng thái mặc định
                        </label>
                        <!-- Thay đổi: px-5 py-3 text-lg -> px-4 py-2.5 text-base, rounded-xl -> rounded-lg -->
                        <select name="trang_thai" id="trang_thai" 
                                class="w-full border border-gray-300 px-4 py-2.5 rounded-lg appearance-none bg-white focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-200 cursor-pointer text-base">
                            <?php 
                            $default_status = 'choduyet';
                            foreach ($trangthai_options as $key => $label): 
                                $selected = $default_status === $key ? 'selected' : '';
                            ?>
                                <option value="<?= $key ?>" <?= $selected ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Ảnh tin -->
                <div>
                    <!-- Thay đổi: text-lg -> text-base -->
                    <label for="anh_tin" class="block font-bold text-gray-700 mb-4 text-base">
                        <i class="fas fa-camera mr-2 text-blue-500"></i> Tải lên Ảnh đại diện
                    </label>
                    <!-- Thay đổi file button size: file:py-3 file:px-6 file:text-base -> file:py-2.5 file:px-5 file:text-sm -->
                    <input type="file" name="anh_tin" id="anh_tin" 
                           accept="image/*"
                           required
                           class="w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-5 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition duration-200">
                    
                    <!-- Container Hiển thị ảnh xem trước -->
                    <?php 
                        $hasExistingImage = !empty($tin['anh_tin']);
                        $initialSrc = $hasExistingImage 
                            ? "https://placehold.co/150x90/5C6BC0/FFFFFF?text=Ảnh+mẫu"
                            : '';
                    ?>
                    <div id="imagePreviewContainer" 
                         class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg <?= !$hasExistingImage ? 'hidden' : '' ?>">
                        
                        <!-- Thay đổi: text-base -> text-sm -->
                        <p class="font-medium text-sm text-gray-600 mb-3">Ảnh xem trước:</p>
                        <img id="currentImagePreview" 
                             src="<?= $initialSrc ?>" 
                             alt="Ảnh tin tức hiện tại" 
                            
                             class="w-full max-w-lg h-40 object-cover rounded-xl shadow-md border-2 border-white mx-auto">
                    </div>
                </div>

                <!-- Nút Đăng Tin -->
                <div class="pt-4 border-t mt-6">
                    <!-- Thay đổi: px-8 py-4 text-xl -> px-6 py-3 text-lg -->
                    <button type="submit" id="capnhattintuc" name="capnhattintuc" 
                            class="w-full px-6 py-3 bg-blue-600 text-white font-extrabold rounded-xl hover:bg-blue-700 transition duration-300 shadow-xl shadow-blue-300/50 transform hover:scale-[1.01] text-lg">
                        <i class="fas fa-paper-plane mr-2"></i> Đăng Tin Ngay
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('anh_tin');
    const imagePreview = document.getElementById('currentImagePreview');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    // Lưu lại trạng thái ban đầu
    const initialSrc = imagePreview.src;

    if (fileInput && imagePreview && previewContainer) {
        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    // Đảm bảo container hiển thị khi có ảnh mới
                    previewContainer.classList.remove('hidden'); 
                }
                
                reader.readAsDataURL(file);
            } else {
                // Nếu người dùng hủy chọn file, và không có ảnh cũ, ẩn container
                if (initialSrc && initialSrc !== imagePreview.src) {
                    imagePreview.src = initialSrc;
                    previewContainer.classList.remove('hidden'); 
                } else {
                    previewContainer.classList.add('hidden'); 
                }
            }
        });
    }
});
</script>
</body>
</html>
