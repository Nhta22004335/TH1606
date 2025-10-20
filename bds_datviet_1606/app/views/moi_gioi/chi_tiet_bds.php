<?php
// ===================================================================
// PHẦN 1: XỬ LÝ LOGIC & DỮ LIỆU (BACKEND)
// ===================================================================

require_once "../../../config/database.php";
$pdo = ketnoicsdl();

if (session_status() === PHP_SESSION_NONE) session_start();

// --- 1. XÁC THỰC NGƯỜI DÙNG ---
$id_nguoi_dung_session = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_nguoi_dung_session) {
    // Điều hướng về trang đăng nhập nếu chưa đăng nhập
    header("Location: ../auth/dangnhap.html");
    exit;
}



// --- 2. TRUY VẤN THÔNG TIN BẤT ĐỘNG SẢN (CHỈ LẤY CÁC CỘT TỪ ĐỊNH NGHĨA BẢNG) ---
// *LƯU Ý QUAN TRỌNG: Cột 'gia' và 'mo_ta' đã bị loại bỏ vì không có trong CREATE TABLE*
$stmt = $pdo->prepare("
    SELECT 
        bds.id AS id_bds,
        
        -- Thông tin cơ bản
        bds.trang_thai,
        bds.ngay_tao AS ngay_dang,
        bds.id_chu_so_huu,
        
        -- THÔNG TIN VỊ TRÍ
        bds.dia_chi_day_du AS tieu_de, -- Dùng địa chỉ đầy đủ làm tiêu đề hiển thị
        bds.dia_chi_day_du,
        bds.ma_tinh_thanh,
        bds.ma_quan_huyen,
        bds.ma_phuong_xa,
        bds.vi_do,
        bds.kinh_do,
        
        -- THÔNG TIN CƠ BẢN
        bds.dien_tich_dat,
        bds.dien_tich_su_dung,
        bds.mat_tien,
        bds.duong_vao,
        bds.huong_nha,
        
        -- THÔNG TIN CẤU TRÚC
        bds.so_tang,
        bds.so_phong_ngu,
        bds.so_phong_tam,
        
        -- THÔNG TIN PHÁP LÝ
        bds.thong_tin_phap_ly,
        
        -- CỘT ĐẶC BIỆT
        bds.dac_diem_chi_tiet,

        dm.ten_danh_muc AS loai,
        COALESCE(ha.url, 'chuacapnhat.jpg') AS anh_dai_dien
    FROM bat_dong_san bds
    LEFT JOIN danh_muc dm ON bds.id_danh_muc = dm.id
    LEFT JOIN LATERAL (
        SELECT url FROM hinh_anh_bds WHERE id_bds = bds.id ORDER BY ngay_tao DESC LIMIT 1
    ) ha ON TRUE
    WHERE bds.id = :id_bds
      AND bds.id_chu_so_huu = :id_session_check
");

$stmt->execute([
    ':id_bds' => $id_bds,
    ':id_session_check' => $id_nguoi_dung_session
]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sp) {
    echo "<p class='text-center text-red-600 mt-10 font-semibold'>❌ Bất động sản không tồn tại HOẶC bạn không có quyền xem mục này.</p>";
    exit;
}

// --- Hàm định dạng giá (Giữ nguyên nhưng sẽ hiển thị 'Thỏa thuận' do thiếu cột 'gia') ---
function format_price_vn($price) {
    $price = (float)$price; 
    if ($price >= 1000000000) return rtrim(rtrim(number_format($price / 1000000000, 2, ',', ''), '0'), ',') . ' tỷ';
    elseif ($price >= 1000000) return number_format($price / 1000000, 0, ',', '.') . ' triệu';
    elseif ($price > 0) return number_format($price, 0, ',', '.') . ' VNĐ';
    else return 'Thỏa thuận';
}
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// --- 3. Lấy danh sách đánh giá (Giữ nguyên) ---
$stmt = $pdo->prepare("
    SELECT dg.diem, dg.binh_luan, dg.ngay_tao, i.ho_ten, ha.url AS hinh
    FROM danh_gia_bds dg
    LEFT JOIN info_nguoi_dung i ON i.id_nguoi_dung = dg.id_nguoi_dung
    LEFT JOIN hinh_anh_danh_gia_bds ha ON ha.id_dg_bds = dg.id
    WHERE dg.id_bds = :id_bds AND dg.trang_thai = 'hien'
    ORDER BY dg.ngay_tao DESC
");
$stmt->execute([':id_bds' => $sp['id_bds']]);
$ds_danh_gia = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chi tiết BĐS - <?= e($sp['tieu_de']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-50">

<div class="max-w-6xl mx-auto mt-8 bg-white rounded-xl shadow-md overflow-hidden">
    <div class="flex flex-col md:flex-row">
        <div class="md:w-1/2">
            <img src="../../../storage/pictures/bds/<?= e($sp['anh_dai_dien']) ?>?t=<?= time() ?>" 
                onerror="this.onerror=null;this.src='../../../storage/pictures/bds/chuacapnhat.jpg';"
                alt="Ảnh bất động sản" 
                class="w-full h-96 object-cover">
        </div>

        <div class="md:w-1/2 p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4"><?= e($sp['tieu_de']) ?></h1>

            <div class="space-y-3 text-gray-700">
                <p class="text-3xl font-extrabold text-red-600"><i class="fas fa-money-bill-wave"></i> <?= format_price_vn($sp['gia'] ?? 0) ?></p>
                
                <p><i class="fas fa-tag text-green-500"></i> <strong>Loại:</strong> <?= e($sp['loai']) ?></p>
                <p><i class="fas fa-map-marker-alt text-red-500"></i> <strong>Địa chỉ:</strong> <?= e($sp['dia_chi_day_du']) ?></p>
                <p><i class="fas fa-ruler-combined text-indigo-500"></i> <strong>Diện tích đất:</strong> <?= e($sp['dien_tich_dat']) ?> m²</p>
                <p><i class="fas fa-info-circle text-gray-500"></i> <strong>Trạng thái:</strong> <span class="uppercase font-semibold text-blue-700"><?= e($sp['trang_thai']) ?></span></p>
                <p><i class="fas fa-calendar-alt text-purple-500"></i> <strong>Ngày đăng:</strong> <?= date("d/m/Y", strtotime($sp['ngay_dang'])) ?></p>
            </div>
        </div>
    </div>
    
    <div class="p-6 border-t">
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Thông số chi tiết</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-700">
            <p><i class="fas fa-building text-blue-500"></i> <strong>Số tầng:</strong> <?= e($sp['so_tang']) ?></p>
            <p><i class="fas fa-bed text-pink-500"></i> <strong>Phòng ngủ:</strong> <?= e($sp['so_phong_ngu']) ?></p>
            <p><i class="fas fa-toilet text-cyan-500"></i> <strong>Phòng tắm:</strong> <?= e($sp['so_phong_tam']) ?></p>
            <p><i class="fas fa-drafting-compass text-green-500"></i> <strong>DTSD:</strong> <?= e($sp['dien_tich_su_dung']) ?> m²</p>
            
            <p><i class="fas fa-road text-amber-500"></i> <strong>Mặt tiền:</strong> <?= e($sp['mat_tien']) ?> m</p>
            <p><i class="fas fa-road text-amber-500"></i> <strong>Đường vào:</strong> <?= e($sp['duong_vao']) ?> m</p>
            <p><i class="fas fa-compass text-red-500"></i> <strong>Hướng nhà:</strong> <?= e($sp['huong_nha']) ?></p>
            <p><i class="fas fa-gavel text-gray-500"></i> <strong>Pháp lý:</strong> <?= e($sp['thong_tin_phap_ly']) ?></p>
        </div>
    </div>
    
    <div class="p-6 border-t">
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Thông tin chi tiết khác (JSONB)</h2>
        <?php if (!empty($sp['dac_diem_chi_tiet'])): ?>
             <pre class="bg-gray-100 p-4 rounded text-xs overflow-auto"><?= e(json_encode($sp['dac_diem_chi_tiet'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php else: ?>
             <p class="text-gray-500 italic">Không có cột 'mo_ta'. Dữ liệu chi tiết nằm ở cột JSONB 'dac_diem_chi_tiet'.</p>
        <?php endif; ?>
    </div>
    
    <div class="p-6 border-t">
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Vị trí (Tọa độ)</h2>
        <p><strong>Vĩ độ (Latitude):</strong> <span class="font-mono"><?= e($sp['vi_do'] ?? 'N/A') ?></span></p>
        <p><strong>Kinh độ (Longitude):</strong> <span class="font-mono"><?= e($sp['kinh_do'] ?? 'N/A') ?></span></p>
        <p class="text-gray-500 italic mt-2">Sử dụng Vĩ độ/Kinh độ để hiển thị trên bản đồ.</p>
    </div>

    <div class="flex justify-center gap-4 p-6 border-t">
        <a href="trangchu.php?page=../moi_gioi/sp_canhan" 
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg font-medium">
            ⬅ Quay lại danh sách
        </a>
        <a href="trangchu.php?page=../moi_gioi/sua_san_pham&id=<?= e($sp['id_bds']) ?>" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">
            ✏️ Sửa thông tin
        </a>
    </div>
</div>

---

<div class="max-w-6xl mx-auto mt-6 bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-xl font-semibold mb-4">Đánh giá BĐS này</h3>
    <p class="text-red-500 mb-4">⚠️ Lưu ý: Đây là trang quản lý. Bạn không nên tự đánh giá tài sản của mình.</p>
    <form action="trangchu.php?page=../../../models/xu_ly_danh_gia" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id_bds" value="<?= e($sp['id_bds']) ?>">
        <div>
            <label for="diem" class="block font-medium">Điểm (1-5):</label>
            <select name="diem" id="diem" required class="border rounded px-2 py-1">
                <?php for($i=1;$i<=5;$i++): ?>
                    <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label for="binh_luan" class="block font-medium">Bình luận:</label>
            <textarea name="binh_luan" id="binh_luan" rows="4" placeholder="Viết cảm nhận của bạn..." required
                      class="border rounded w-full px-2 py-1"></textarea>
        </div>

        <div>
            <label for="hinh_anh" class="block font-medium">Ảnh (nếu có):</label>
            <input type="file" name="hinh_anh[]" id="hinh_anh" multiple class="border rounded px-2 py-1 w-full">
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium">
            Gửi đánh giá
        </button>
    </form>
</div>

---

<div class="max-w-6xl mx-auto mt-6 bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-xl font-semibold mb-4">Danh sách đánh giá</h3>
    <?php if($ds_danh_gia): ?>
        <?php foreach($ds_danh_gia as $dg): ?>
            <div class="border-b py-4">
                <p class="font-medium"><?= e($dg['ho_ten'] ?? 'Khách') ?> đánh giá <?= e($dg['diem']) ?> ⭐</p>
                <p class="text-gray-700"><?= nl2br(e($dg['binh_luan'])) ?></p>
                <?php if($dg['hinh']): ?>
                    <img src="../../../storage/pictures/danh_gia/<?= e($dg['hinh']) ?>" 
                         alt="Ảnh đánh giá" class="w-32 h-32 object-cover mt-2 rounded">
                <?php endif; ?>
                <p class="text-xs text-gray-500 mt-1"><?= date("d/m/Y H:i", strtotime($dg['ngay_tao'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Chưa có đánh giá nào.</p>
    <?php endif; ?>
</div>

</body>
</html>