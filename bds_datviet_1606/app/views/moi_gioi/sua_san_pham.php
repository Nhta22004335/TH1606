<?php
// Bắt đầu phiên
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// --- BẢO MẬT: Lấy ID người dùng và ID sản phẩm ---
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
$id_bds = $_GET['id'] ?? null;

if (!$id_nguoi_dung) exit("Bạn cần đăng nhập để thực hiện chức năng này.");
if (!$id_bds) exit("Không tìm thấy ID sản phẩm.");

// Truy vấn sản phẩm
$sql = "SELECT * FROM bat_dong_san WHERE id = :id_bds AND id_nguoi_dung = :id_nguoi_dung";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_bds' => $id_bds, ':id_nguoi_dung' => $id_nguoi_dung]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) exit("Sản phẩm không tồn tại hoặc bạn không có quyền chỉnh sửa sản phẩm này.");

function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

$status_map = [
    'chuaduyet' => ['label' => 'Chờ duyệt', 'class' => 'bg-yellow-100 text-yellow-800'],
    'daduyet'   => ['label' => 'Đã duyệt',  'class' => 'bg-green-100 text-green-800'],
    'daban'     => ['label' => 'Đã bán',    'class' => 'bg-red-100 text-red-800'],
    'dathue'    => ['label' => 'Đã thuê',   'class' => 'bg-blue-100 text-blue-800'],
    'default'   => ['label' => 'Không rõ',  'class' => 'bg-gray-100 text-gray-700']
];
$current_status = $status_map[$product['trang_thai']] ?? $status_map['default'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa: <?= e($product['tieu_de']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-slate-100">

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto mb-8">
        <a href="trangchu.php?page=../moi_gioi/sp_canhan" class="text-sm text-slate-600 hover:text-sky-600 flex items-center gap-2 mb-4">
            <i class="fas fa-arrow-left"></i>
             Quay lại danh sách
        </a>
        <h1 class="text-3xl font-bold text-slate-900">Chỉnh sửa Bất động sản</h1>
        <p class="text-slate-500 mt-1">Cập nhật thông tin chi tiết cho sản phẩm của bạn.</p>
    </div>

    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- FORM CHÍNH -->
        <div class="lg:col-span-2">
            <form id="main-form" action="../../models/xuly_capnhat_spcn.php" method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?= e($product['id']) ?>">
                
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-3 mb-4">
                        <i class="fas fa-info-circle text-sky-500"></i>
                        Thông tin cơ bản
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-2">
                            <label for="tieu_de" class="block text-sm font-medium text-slate-700">Tiêu đề tin đăng</label>
                            <input type="text" name="tieu_de" id="tieu_de" value="<?= e($product['tieu_de']) ?>" required 
                            class="mt-1 block w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition">
                        </div>
                        <div>
                            <label for="hinh_thuc" class="block text-sm font-medium text-slate-700">Hình thức</label>
                            <select name="hinh_thuc" id="hinh_thuc" class="mt-1 block w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                                <option value="Bán" <?= ($product['hinh_thuc'] == 'Bán') ? 'selected' : '' ?>>Bán</option>
                                <option value="Cho thuê" <?= ($product['hinh_thuc'] == 'Cho thuê') ? 'selected' : '' ?>>Cho thuê</option>
                            </select>
                        </div>
                        <div>
                            <label for="loai" class="block text-sm font-medium text-slate-700">Loại Bất động sản</label>
                            <select name="loai" id="loai" class="mt-1 block w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                                <?php
                                $loai_options = ["canho", "nhapho", "datnen", "bietthu"];
                                foreach ($loai_options as $opt) {
                                    $sel = ($product['loai'] == $opt) ? 'selected' : '';
                                    echo "<option value='$opt' $sel>$opt</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-3 mb-4">
                        <i class="fas fa-dollar-sign text-emerald-500"></i>
                        Giá & Diện tích
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="gia" class="block text-sm font-medium text-slate-700">Mức giá</label>
                            <div class="relative">
                                <input type="number" name="gia" id="gia" value="<?= e($product['gia']) ?>" required min="0" 
                                class="mt-1 block w-full px-4 py-2.5 pr-12 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                                <span class="absolute inset-y-0 right-4 flex items-center text-slate-500 text-sm">VNĐ</span>
                            </div>
                        </div>
                        <div>
                            <label for="dien_tich" class="block text-sm font-medium text-slate-700">Diện tích</label>
                            <div class="relative">
                                <input type="number" name="dien_tich" id="dien_tich" value="<?= e($product['dien_tich']) ?>" required min="1" step="0.1" 
                                class="mt-1 block w-full px-4 py-2.5 pr-10 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                                <span class="absolute inset-y-0 right-4 flex items-center text-slate-500 text-sm">m²</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-3 mb-4">
                        <i class="fas fa-map-marker-alt text-red-500"></i>
                        Vị trí & Mô tả
                    </h2>
                    <div class="space-y-6">
                        <!-- <div>
                            <label for="khu_vuc" class="block text-sm font-medium text-slate-700">Tỉnh / Thành phố</label>
                            <input type="text" name="khu_vuc" id="khu_vuc" value="<?= e($product['khu_vuc']) ?>" 
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div> -->
                        <div>
                        <label for="khu_vuc" class="block text-sm font-medium text-slate-700">
                            Tỉnh / Thành phố
                        </label>
                        <select name="khu_vuc" id="khu_vuc"
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                            <?php 
                            $provinces = [
                                "Hà Nội","Huế","Lai Châu","Điện Biên","Sơn La","Lạng Sơn","Quảng Ninh","Thanh Hóa",
                                "Nghệ An","Hà Tĩnh","Cao Bằng","Tuyên Quang","Lào Cai","Thái Nguyên","Phú Thọ",
                                "Bắc Ninh","Hưng Yên","Hải Phòng","Ninh Bình","Quảng Trị","Đà Nẵng","Quảng Ngãi",
                                "Gia Lai","Khánh Hòa","Lâm Đồng","Đắk Lắk","TP. Hồ Chí Minh","Đồng Nai","Tây Ninh",
                                "Cần Thơ","Vĩnh Long","Đồng Tháp","Cà Mau"
                            ];

                            foreach ($provinces as $p) {
                                $selected = ($product['khu_vuc'] === $p) ? 'selected' : '';
                                echo "<option value='$p' $selected>$p</option>";
                            }
                            ?>
                        </select>
                        </div>

                        <div>
                            <label for="dia_chi" class="block text-sm font-medium text-slate-700">Địa chỉ chi tiết</label>
                            <textarea name="dia_chi" id="dia_chi" rows="2" class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"><?= e($product['dia_chi']) ?></textarea>
                        </div>
                        <div>
                            <label for="mo_ta" class="block text-sm font-medium text-slate-700">Mô tả chi tiết</label>
                            <textarea name="mo_ta" id="mo_ta" rows="5" class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"><?= e($product['mo_ta']) ?></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- CỘT PHẢI -->
        <div class="lg:col-span-1">
            <div class="sticky top-8 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-slate-800">Trạng thái</h3>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $current_status['class'] ?>">
                            <?= e($current_status['label']) ?>
                        </span>
                    </div>

                    <!-- Upload ảnh -->
                    <div class="pt-4 border-t border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-3 mb-4">
                            <i class="fa-solid fa-image text-indigo-500"></i> Hình ảnh bất động sản
                        </h2>
                        <div class="flex flex-col items-center justify-center space-y-4">
                            <?php
                            $stmtImg = $pdo->prepare("SELECT url FROM hinh_anh_bds WHERE id_bds = :id_bds ORDER BY ngay_tao DESC LIMIT 1");
                            $stmtImg->execute([':id_bds' => $id_bds]);
                            $img = $stmtImg->fetch(PDO::FETCH_ASSOC);
                            ?>

                            <div id="preview-container" class="relative w-48 h-48 border-2 border-dashed border-slate-300 rounded-lg flex items-center justify-center bg-gray-50 overflow-hidden">
                                <?php if ($img): ?>
                                    <img id="preview-image" src="../../../storage/pictures/bds/<?= e($img['url']) ?>" class="object-cover w-full h-full" alt="Ảnh BĐS">
                                <?php else: ?>
                                    <div id="no-image" class="text-gray-400 text-sm flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-image-slash text-3xl mb-2"></i>
                                        Chưa có ảnh
                                    </div>
                                <?php endif; ?>
                            </div>

                            <form id="uploadForm" enctype="multipart/form-data">
                                <input type="hidden" name="id_bds" value="<?= e($id_bds) ?>">
                                <input type="file" name="file_anh" accept="image/*" id="fileInput" class="hidden" onchange="uploadAnh()">
                                <button type="button" onclick="document.getElementById('fileInput').click()" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg shadow transition">
                                    <i class="fa-solid fa-upload"></i> Tải ảnh lên
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <button type="submit" form="main-form" class="w-full px-5 py-3 rounded-lg text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 shadow transition flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                        <a href="trangchu.php?page=../moi_gioi/sanpham_canhan" class="w-full block text-center px-5 py-2.5 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition">
                            Hủy
                        </a>
                    </div>
                </div>

                <div class="bg-slate-50 border border-dashed rounded-xl p-5">
                    <h3 class="font-semibold text-slate-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-lightbulb text-yellow-500"></i> Gợi ý
                    </h3>
                    <ul class="text-sm text-slate-600 space-y-2 list-disc list-inside">
                        <li><b>Tiêu đề hấp dẫn</b> sẽ thu hút nhiều người xem hơn.</li>
                        <li><b>Mô tả chi tiết</b> và trung thực giúp tăng độ tin cậy.</li>
                        <li>Đừng quên cập nhật <b>hình ảnh mới nhất</b> cho sản phẩm.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function uploadAnh() {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    if (!file) return alert("Vui lòng chọn ảnh.");

    const formData = new FormData();
    formData.append('file_anh', file);
    // Tên biến phải là 'id_bds' để khớp với logic xử lý trong file xuly_capnhat_spcn.php
    formData.append('id_bds', document.querySelector('input[name="id"]').value); 

    // Gọi đến file xử lý duy nhất
    fetch("../../models/xuly_capnhat_spcn.php", { 
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            const preview = document.getElementById('preview-container');
            const noImageDiv = document.getElementById('no-image');
            if (noImageDiv) noImageDiv.remove(); 
            
            let previewImg = document.getElementById('preview-image');
            if (!previewImg) {
                previewImg = document.createElement('img');
                previewImg.id = 'preview-image';
                previewImg.className = 'object-cover w-full h-full';
                previewImg.alt = 'Ảnh BĐS';
                preview.appendChild(previewImg);
            }
            previewImg.src = `../../../storage/pictures/bds/${data.filename}`;
            
            fileInput.value = ""; 
            // window.location.href = 'trangchu.php?page=../moi_gioi/sp_canhan';
        } else {
            alert("❌ " + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Lỗi upload, vui lòng thử lại!");
    });
}
</script>
</body>
</html>
