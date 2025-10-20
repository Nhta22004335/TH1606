<?php
// sua_san_pham.php

// 1. CẤU HÌNH BAN ĐẦU (SESSION, KẾT NỐI CSDL)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// --- Lấy ID người dùng và ID sản phẩm ---
$id_nguoi_dung = $_SESSION['id_nguoi_dung'] ?? null;
// Lấy ID BẤT ĐỘNG SẢN (id_bds) từ URL
$id_bds = $_GET['id'] ?? null; 

// Khởi tạo biến trạng thái lỗi
$error_message = null;
$product = null;
$danh_muc_options = [];

// Hàm lọc dữ liệu
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// 2. KẾT NỐI CSDL & LOGIC KIỂM TRA LỖI/QUYỀN TRUY CẬP
try {
    if (!$id_nguoi_dung) {
        $error_message = "Bạn cần đăng nhập để thực hiện chức năng này. Vui lòng kiểm tra lại trạng thái đăng nhập.";
    } elseif (!preg_match('/^[0-9a-fA-F-]{36}$/', $id_bds)) {
        $error_message = "❌ ID Bất động sản không hợp lệ.";
    } else {
        // 2a. Lấy danh mục để điền vào dropdown
        $stmtDm = $pdo->query("SELECT id, ten_danh_muc FROM danh_muc ORDER BY ten_danh_muc");
        $danh_muc_options = $stmtDm->fetchAll(PDO::FETCH_ASSOC);

        // 2b. Truy vấn chi tiết BĐS
        // CHỈ LẤY CÁC CỘT TỪ bat_dong_san
        $sql = "
            SELECT 
                bds.*
            FROM bat_dong_san bds
            WHERE 
                bds.id = :id_bds 
                AND bds.id_chu_so_huu = :id_nguoi_dung 
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_bds' => $id_bds, 
            ':id_nguoi_dung' => $id_nguoi_dung // Kiểm tra id_chu_so_huu
        ]);
        
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Cần đảm bảo cột 'trang_thai' tồn tại và có giá trị mặc định
        $product['trang_thai'] = $product['trang_thai'] ?? 'chuaduyet'; 

        if (!$product) {
            $error_message = "Bất động sản không tồn tại hoặc bạn không có quyền chỉnh sửa mục này.";
        }
    }
} catch (PDOException $e) {
    error_log("Database Error in sua_san_pham.php: " . $e->getMessage()); 
    $error_message = "LỖI KẾT NỐI DỮ LIỆU. Vui lòng thử lại."; 
}

// 3. MAP DỮ LIỆU CỐ ĐỊNH CHO GIAO DIỆN (Giữ nguyên)
$status_map = [
    'chuaduyet' => ['label' => 'Chờ duyệt', 'class' => 'bg-yellow-100 text-yellow-800'],
    'daduyet'   => ['label' => 'Đã duyệt',  'class' => 'bg-green-100 text-green-800'],
    'huy'       => ['label' => 'Đã hủy',    'class' => 'bg-red-100 text-red-800'],
    'default'   => ['label' => 'Không rõ',  'class' => 'bg-gray-100 text-gray-700']
];
$current_status = $product ? ($status_map[$product['trang_thai']] ?? $status_map['default']) : $status_map['default'];

$huong_options = ["Đông", "Tây", "Nam", "Bắc", "Đông Bắc", "Đông Nam", "Tây Bắc", "Tây Nam", "Không xác định"];
// Giả định bạn có một danh sách Tỉnh/Thành phố đầy đủ
$provinces = [
    "Hà Nội","TP. Hồ Chí Minh","Huế","Lai Châu","Điện Biên","Sơn La","Lạng Sơn","Quảng Ninh","Thanh Hóa",
    "Nghệ An","Hà Tĩnh","Cao Bằng","Tuyên Quang","Lào Cai","Thái Nguyên","Phú Thọ",
    "Bắc Ninh","Hưng Yên","Hải Phòng","Ninh Bình","Quảng Trị","Đà Nẵng","Quảng Ngãi",
    "Gia Lai","Khánh Hòa","Lâm Đồng","Đắk Lắk","Đồng Nai","Tây Ninh",
    "Cần Thơ","Vĩnh Long","Đồng Tháp","Cà Mau"
];
sort($provinces);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa BĐS: <?= $product ? e($product['dia_chi_day_du']) : 'Lỗi Truy Cập' ?></title>
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
        <h1 class="text-3xl font-bold text-slate-900">Chỉnh sửa Bất động sản Gốc</h1>
        <p class="text-slate-500 mt-1">Cập nhật tất cả các thuộc tính vật lý và pháp lý của BĐS.</p>
    </div>

    <?php if ($error_message): ?>
        <div class="max-w-7xl mx-auto bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Lỗi: </strong>
            <span class="block sm:inline"><?= e($error_message) ?></span>
        </div>
    <?php else: ?>
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <form id="main-form" action="../../models/xuly_capnhat_spcn.php" method="POST" class="space-y-6">
                <input type="hidden" name="id_bds" value="<?= e($id_bds) ?>"> 
                
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-3 mb-4">
                        <i class="fas fa-info-circle text-sky-500"></i>
                        Vị trí & Danh mục
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-2">
                            <label for="id_danh_muc" class="block text-sm font-medium text-slate-700">Loại Bất động sản (Danh mục)</label>
                            <select name="id_danh_muc" id="id_danh_muc" required class="mt-1 block w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                                <?php
                                foreach ($danh_muc_options as $dm) { 
                                    $sel = ($product['id_danh_muc'] == $dm['id']) ? 'selected' : ''; 
                                    echo "<option value='".e($dm['id'])."' $sel>".e($dm['ten_danh_muc'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="ma_tinh_thanh" class="block text-sm font-medium text-slate-700">
                                Tỉnh / Thành phố
                            </label>
                            <select name="ma_tinh_thanh" id="ma_tinh_thanh" required
                                class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                                <option value="">--- Chọn Tỉnh/Thành phố ---</option>
                                <?php 
                                foreach ($provinces as $p) {
                                    $selected = ($product['ma_tinh_thanh'] === $p) ? 'selected' : '';
                                    echo "<option value='".e($p)."' $selected>".e($p)."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                         <div class="col-span-1">
                            <label for="huong_nha" class="block text-sm font-medium text-slate-700">Hướng nhà</label>
                            <select name="huong_nha" id="huong_nha" class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                                <?php
                                foreach ($huong_options as $opt) {
                                    $sel = ($product['huong_nha'] == $opt) ? 'selected' : '';
                                    echo "<option value='".e($opt)."' $sel>".e($opt)."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-3 mb-4">
                        <i class="fas fa-ruler text-teal-500"></i>
                        Thông số Kỹ thuật & Cấu trúc
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                        <div>
                            <label for="dien_tich_dat" class="block text-sm font-medium text-slate-700">DT Đất (m²)</label>
                            <input type="number" name="dien_tich_dat" id="dien_tich_dat" value="<?= e($product['dien_tich_dat']) ?>" min="1" step="0.01"
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label for="dien_tich_su_dung" class="block text-sm font-medium text-slate-700">DT Sử dụng (m²)</label>
                            <input type="number" name="dien_tich_su_dung" id="dien_tich_su_dung" value="<?= e($product['dien_tich_su_dung']) ?>" min="0" step="0.01"
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div>

                        <div>
                            <label for="mat_tien" class="block text-sm font-medium text-slate-700">Mặt tiền (m)</label>
                            <input type="number" name="mat_tien" id="mat_tien" value="<?= e($product['mat_tien']) ?>" min="0" step="0.01"
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label for="duong_vao" class="block text-sm font-medium text-slate-700">Đường vào (m)</label>
                            <input type="number" name="duong_vao" id="duong_vao" value="<?= e($product['duong_vao']) ?>" min="0" step="0.01"
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div>

                        <div>
                            <label for="so_tang" class="block text-sm font-medium text-slate-700">Số Tầng</label>
                            <input type="number" name="so_tang" id="so_tang" value="<?= e($product['so_tang']) ?>" min="0" 
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label for="so_phong_ngu" class="block text-sm font-medium text-slate-700">P. Ngủ</label>
                            <input type="number" name="so_phong_ngu" id="so_phong_ngu" value="<?= e($product['so_phong_ngu']) ?>" min="0" 
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label for="so_phong_tam" class="block text-sm font-medium text-slate-700">P. Tắm</label>
                            <input type="number" name="so_phong_tam" id="so_phong_tam" value="<?= e($product['so_phong_tam']) ?>" min="0" 
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-3 mb-4">
                        <i class="fas fa-map-marked-alt text-red-500"></i>
                        Địa chỉ chi tiết & Pháp lý
                    </h2>
                    <div class="space-y-6">
                        <div class="sm:col-span-2">
                            <label for="dia_chi_day_du" class="block text-sm font-medium text-slate-700">Địa chỉ đầy đủ (Rất quan trọng)</label>
                            <textarea name="dia_chi_day_du" id="dia_chi_day_du" rows="2" required 
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"><?= e($product['dia_chi_day_du']) ?></textarea>
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label for="thong_tin_phap_ly" class="block text-sm font-medium text-slate-700">Thông tin Pháp lý</label>
                            <textarea name="thong_tin_phap_ly" id="thong_tin_phap_ly" rows="5" placeholder="Ví dụ: Sổ hồng chính chủ, Giấy tờ viết tay, Đã có GCNQSDĐ..."
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"><?= e($product['thong_tin_phap_ly']) ?></textarea>
                        </div>
                    </div>
                </div>
                
                </form>
        </div>

        <div class="lg:col-span-1">
            <div class="sticky top-8 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-slate-800">Trạng thái BĐS</h3>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $current_status['class'] ?>">
                            <?= e($current_status['label']) ?>
                        </span>
                    </div>

                    <div class="pt-4 border-t border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-3 mb-4">
                            <i class="fa-solid fa-image text-indigo-500"></i> Hình ảnh
                        </h2>
                        <div class="flex flex-col items-center justify-center space-y-4">
                            <?php
                            $id_bds_hinh_anh = $product['id'] ?? null; 

                            if ($id_bds_hinh_anh) {
                                try {
                                    $stmtImg = $pdo->prepare("SELECT url FROM hinh_anh_bds WHERE id_bds = :id_bds ORDER BY ngay_tao DESC LIMIT 1");
                                    $stmtImg->execute([':id_bds' => $id_bds_hinh_anh]); 
                                    $img = $stmtImg->fetch(PDO::FETCH_ASSOC);
                                } catch (PDOException $e) {
                                    $img = null;
                                }
                            } else {
                                $img = null;
                            }
                            ?>

                            <div id="preview-container" class="relative w-48 h-48 border-2 border-dashed border-slate-300 rounded-lg flex items-center justify-center bg-gray-50 overflow-hidden">
                                <?php if ($img): ?>
                                <img id="preview-image" src="../../../storage/pictures/bds/<?= e($img['url']) ?>?t=<?= time() ?>" class="object-cover w-full h-full" alt="Ảnh BĐS">
                                <?php else: ?>
                                    <div id="no-image" class="text-gray-400 text-sm flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-image-slash text-3xl mb-2"></i>
                                        Chưa có ảnh
                                    </div>
                                <?php endif; ?>
                            </div>

                            <form id="uploadForm" enctype="multipart/form-data">
                                <input type="hidden" name="id_bds" value="<?= e($id_bds_hinh_anh) ?>"> 
                                <input type="file" name="file_anh" accept="image/*" id="fileInput" class="hidden" onchange="uploadAnh()">
                                <button type="button" onclick="document.getElementById('fileInput').click()" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg shadow transition">
                                    <i class="fa-solid fa-upload"></i> Tải ảnh lên
                                </button>
                            </form>
                            <p class="text-xs text-slate-500 italic">Ảnh mới nhất sẽ là ảnh đại diện.</p>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-200 mt-4">
                        <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-3 mb-4">
                            <i class="fas fa-location-arrow text-gray-500"></i> Tọa độ GPS
                        </h2>
                        <div>
                            <label for="vi_do" class="block text-sm font-medium text-slate-700">Vĩ độ (Latitude)</label>
                            <input type="text" name="vi_do" id="vi_do" value="<?= e($product['vi_do']) ?>" step="0.0000001" placeholder="Vĩ độ (VD: 21.028511)"
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div class="mt-3">
                            <label for="kinh_do" class="block text-sm font-medium text-slate-700">Kinh độ (Longitude)</label>
                            <input type="text" name="kinh_do" id="kinh_do" value="<?= e($product['kinh_do']) ?>" step="0.0000001" placeholder="Kinh độ (VD: 105.854122)"
                            class="mt-1 block w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <button type="submit" form="main-form" class="w-full px-5 py-3 rounded-lg text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 shadow transition flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                        <a href="trangchu.php?page=../moi_gioi/sp_canhan" class="w-full block text-center px-5 py-2.5 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition">
                            Hủy
                        </a>
                    </div>
                </div>

                <div class="bg-slate-50 border border-dashed rounded-xl p-5">
                    <h3 class="font-semibold text-slate-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-lightbulb text-yellow-500"></i> Gợi ý
                    </h3>
                    <ul class="text-sm text-slate-600 space-y-2 list-disc list-inside">
                        <li>**Địa chỉ đầy đủ** và **Tọa độ** giúp khách hàng tìm kiếm dễ dàng hơn.</li>
                        <li>**Thông số kỹ thuật** cần chính xác theo sổ đỏ/giấy tờ.</li>
                        <li>Đừng quên cập nhật **hình ảnh mới nhất** cho sản phẩm.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Logic JS/AJAX chỉ cần chạy khi không có lỗi (product tồn tại)
<?php if (!$error_message): ?>
function uploadAnh() {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    if (!file) return alert("Vui lòng chọn ảnh.");

    const formData = new FormData();
    formData.append('file_anh', file);
    formData.append('id_bds', document.querySelector('input[name="id_bds"]').value);
    formData.append('action', 'upload_image'); 

    fetch("../../models/xuly_capnhat_spcn.php", {
        method: "POST",
        body: formData
    })
    .then(res => {
        if (!res.ok) return res.text().then(text => { throw new Error(`HTTP ${res.status}: ${text.substring(0, 100)}...`); });
        return res.json();
    })
    .then(data => {
        console.log('Phản hồi tải ảnh:', data);
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
           previewImg.src = `../../../storage/pictures/bds/${data.filename}?t=${new Date().getTime()}`;
           alert("✅ Cập nhật ảnh đại diện thành công!");
           fileInput.value = "";
        } else {
            alert("❌ " + data.message);
        }
    })
    .catch(err => {
        console.error("Lỗi Upload:", err);
        alert("Lỗi upload, vui lòng thử lại! Chi tiết lỗi: " + err.message);
    });
}

// Xử lý sự kiện submit form chính
document.getElementById('main-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update_data'); // Giả định xuly_capnhat_spcn.php xử lý action này

    fetch(this.action, {
        method: "POST",
        body: formData
    })
    .then(res => {
        if (!res.ok) return res.text().then(text => { throw new Error(`HTTP ${res.status}: ${text.substring(0, 100)}...`); });
        return res.json();
    })
    .then(data => {
        if (data.status === "success") {
            alert("✅ " + data.message);
            window.location.reload(); 
        } else {
            alert("❌ Lỗi cập nhật: " + data.message);
        }
    })
    .catch(err => {
        console.error("Lỗi Submit Form:", err);
        alert("Lỗi kết nối, không thể lưu thay đổi! Vui lòng kiểm tra console log.");
    });
});
<?php endif; ?>
</script>
</body>
</html>