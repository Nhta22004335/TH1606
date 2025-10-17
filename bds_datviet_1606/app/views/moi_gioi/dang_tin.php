<?php
// =================================================================
// PHP LOGIC: Xử lý Đăng Tin Mới vào bảng tin_tuc (PostgreSQL)
// =================================================================

// Yêu cầu file kết nối CSDL
require_once __DIR__ . '/../../../config/database.php'; 

// Khởi tạo session để lấy ID người dùng
if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy ID người dùng từ session.
// LƯU Ý QUAN TRỌNG: 
// Trong môi trường thực tế, ID này phải lấy từ người dùng đã đăng nhập.
// Nếu không tìm thấy, GÁN TẠM MỘT UUID HỢP LỆ ĐỂ TEST!
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11'; 

// --- Dữ liệu Mẫu (Cho Form) ---
$tin = [
    'id' => null,
    'tieu_de' => '', 
    'mo_ta' => '',    
    'chuyen_muc' => '', 
    'trang_thai' => 'choduyet', 
    'anh_tin' => '' 
];

// Định nghĩa các tùy chọn trạng thái cho dropdown
$trangthai_options = [
    'choduyet' => 'Chờ duyệt',
    'dangban' => 'Đang bán',
    'daban' => 'Đã bán',
    'dathue' => 'Đã thuê'
];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['capnhattintuc'])) {
    if (empty($id_nguoi_dung) || $id_nguoi_dung === 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11') {
        echo "<script>alert('❌ Vui lòng đăng nhập với tài khoản Khách hàng hợp lệ để đăng tin!');</script>";
        exit;
    }

    try {
        $pdo = ketnoicsdl(); // Kết nối CSDL
        $tieu_de    = trim($_POST['tieu_de'] ?? '');
        $mo_ta      = trim($_POST['mo_ta'] ?? '');
        $chuyen_muc = trim($_POST['chuyen_muc'] ?? '');
        $trang_thai = $_POST['trang_thai'] ?? 'choduyet';
        
        // --- Xử lý Upload File ---
        $anh_tin_path = '';
        if (!empty($_FILES['anh_tin']['name'])) {
            $uploadDir = __DIR__ . '../../storage/anhtin'; 
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExtension = pathinfo($_FILES['anh_tin']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $fileExtension; // Tạo tên file duy nhất
            $targetFile = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['anh_tin']['tmp_name'], $targetFile)) {
                $anh_tin_path = '../../storage/pictures/anhtin' . $filename; 
            } else {
                echo "<script>alert('❌ Lỗi upload ảnh! Vui lòng kiểm tra quyền ghi thư mục: " . htmlspecialchars($uploadDir) . "');</script>";
                exit;
            }
        } else {
            // Nếu không upload ảnh, dùng giá trị mặc định của bảng
            $anh_tin_path = 'chuacapnhat.png';
        }

        // --- Câu lệnh INSERT (Tương thích PostgreSQL) ---
        $stmt = $pdo->prepare("
            INSERT INTO tin_tuc (id_khach_hang, tieu_de, mo_ta, chuyen_muc, trang_thai, anh_tin)
            VALUES (:id_khach_hang, :tieu_de, :mo_ta, :chuyen_muc, :trang_thai, :anh_tin)
        ");

        $stmt->execute([
            ':id_khach_hang' => $id_nguoi_dung, 
            ':tieu_de' => $tieu_de,
            ':mo_ta' => $mo_ta,
            ':chuyen_muc' => $chuyen_muc,
            ':trang_thai' => $trang_thai,
            ':anh_tin' => $anh_tin_path
        ]);
    echo "<script>
    alert('✅ Đăng tin thành công! Tin của bạn đang chờ duyệt.');
    window.location.href = 'trangchu.php?page=../moi_gioi/ql_tintuc_mg';
</script>";
exit;


    } catch (Exception $e) {
        error_log("Lỗi INSERT tin_tuc: " . $e->getMessage()); 
        echo "<script>alert('❌ Lỗi hệ thống: Không thể lưu tin đăng. Vui lòng liên hệ hỗ trợ. Chi tiết lỗi: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Tin Bất Động Sản Mới</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Tùy chỉnh nhỏ để màu sắc mặc định của Tailwind phù hợp với thương hiệu Blue */
        :root {
            --color-primary: #3b82f6; /* blue-500 */
        }
        /* Ẩn mũi tên mặc định của select */
        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%234a5568'%3e%3cpath d='M7 7l3-3 3 3m0 6l-3 3-3-3' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.5em;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    
    <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-8 sm:p-10 rounded-3xl shadow-3xl border border-blue-100/70">
            
            <header class="mb-8 border-b-2 border-blue-500/10 pb-4 flex items-center gap-4">
                <i class="fas fa-city text-3xl text-blue-600"></i>
                <h1 class="text-3xl font-extrabold text-gray-900">Đăng Tin Mới - Nền Tảng BĐS</h1>
            </header>

            <form action="" method="post" enctype="multipart/form-data" class="space-y-7">

                <input type="hidden" name="id_tin" value="">

                <div>
                    <label for="tieu_de" class="block font-semibold text-gray-700 mb-2 text-base">
                        <i class="fas fa-bullhorn mr-2 text-blue-500"></i> Tiêu đề tin đăng <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="tieu_de" id="tieu_de" 
                            value="<?= htmlspecialchars($tin['tieu_de'] ?? '') ?>"
                            placeholder="Ví dụ: Bán căn hộ cao cấp 3 phòng ngủ tại Q.1" 
                            required
                            class="w-full border border-gray-300 px-5 py-3 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-300 text-base shadow-sm">
                </div>

                <div>
                    <label for="mo_ta" class="block font-semibold text-gray-700 mb-2 text-base">
                        <i class="fas fa-clipboard-list mr-2 text-blue-500"></i> Mô tả chi tiết
                    </label>
                    <textarea name="mo_ta" id="mo_ta" rows="7" 
                              placeholder="Mô tả chi tiết về vị trí, tiện ích, pháp lý và giá bán..." 
                              required
                              class="w-full border border-gray-300 px-5 py-3 rounded-xl resize-y focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-300 text-base shadow-sm"><?= htmlspecialchars($tin['mo_ta'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="chuyen_muc" class="block font-semibold text-gray-700 mb-2 text-base">
                            <i class="fas fa-tag mr-2 text-blue-500"></i> Khu vực/Phân loại BĐS
                        </label>
                        <input type="text" name="chuyen_muc" id="chuyen_muc"
                               value="<?= htmlspecialchars($tin['chuyen_muc'] ?? '') ?>"
                               placeholder="VD: Căn hộ Q.1, Nhà phố Thủ Đức, Đất nền Long An..."
                               required
                               class="w-full border border-gray-300 px-5 py-3 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-300 text-base shadow-sm">
                    </div>

                    <div>
                        <label for="trang_thai" class="block font-semibold text-gray-700 mb-2 text-base">
                            <i class="fas fa-gavel mr-2 text-blue-500"></i> Trạng thái mặc định
                        </label>
                        <select name="trang_thai" id="trang_thai" 
                                 class="w-full border border-gray-300 px-5 py-3 rounded-xl appearance-none bg-white focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-300 cursor-pointer text-base shadow-sm">
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

                <div>
                    <label for="anh_tin" class="block font-semibold text-gray-700 mb-4 text-base">
                        <i class="fas fa-image mr-2 text-blue-500"></i> Tải lên Ảnh đại diện <span class="text-gray-400 font-normal">(Ảnh đẹp giúp tin duyệt nhanh hơn)</span>
                    </label>
                    <input type="file" name="anh_tin" id="anh_tin" 
                            accept="image/*"
                            required
                            class="w-full text-base text-gray-700 file:mr-5 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition duration-300 cursor-pointer">
                    
                    <?php 
                        $hasExistingImage = !empty($tin['anh_tin']);
                        $initialSrc = $hasExistingImage 
                            ? "https://placehold.co/150x90/5C6BC0/FFFFFF?text=Ảnh+mẫu"
                            : '';
                    ?>
                    <div id="imagePreviewContainer" 
                         class="mt-5 p-4 bg-gray-50 border border-gray-200 rounded-xl <?= !$hasExistingImage ? 'hidden' : '' ?>">
                        
                        <p class="font-medium text-sm text-gray-600 mb-3">Ảnh xem trước:</p>
                        <img id="currentImagePreview" 
                              src="<?= $initialSrc ?>" 
                              alt="Ảnh tin tức hiện tại" 
                              class="w-full h-56 object-cover rounded-xl shadow-lg border-4 border-white mx-auto">
                    </div>
                </div>

                <div class="pt-6 border-t border-blue-500/10 mt-7">
                    <button type="submit" id="capnhattintuc" name="capnhattintuc" 
                            class="w-full px-8 py-3 bg-blue-600 text-white font-extrabold rounded-xl hover:bg-blue-700 transition duration-300 shadow-xl shadow-blue-400/50 transform hover:scale-[1.005] text-xl tracking-wide">
                        <i class="fas fa-paper-plane mr-2"></i> Gửi Tin Đăng để Duyệt
                    </button>
                    <p class="text-center text-sm text-gray-500 mt-3">Tin đăng sẽ được kiểm duyệt trong vòng 24 giờ.</p>
                </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('anh_tin');
    const imagePreview = document.getElementById('currentImagePreview');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    // Lưu lại trạng thái ban đầu của ảnh mẫu (nếu có)
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
                // Xử lý khi người dùng hủy chọn file
                if (initialSrc && initialSrc !== imagePreview.src) {
                    // Nếu đã có ảnh mẫu/ảnh cũ, reset về ảnh cũ
                    imagePreview.src = initialSrc;
                    previewContainer.classList.remove('hidden'); 
                } else {
                    // Nếu không có ảnh cũ, ẩn container
                    previewContainer.classList.add('hidden'); 
                }
            }
        });
    }
});
</script>
</body>
</html>