<?php
if(!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Đăng tin bất động sản</title>
</head>
<body class="bg-gradient-to-r from-gray-50 to-gray-100 min-h-screen">

  <div class="max-w-4xl mx-auto py-12 px-6">
    
    <!-- Tiêu đề -->
    <div class="text-center mb-10">
      <h1 class="text-4xl font-extrabold text-blue-700 flex items-center justify-center gap-2">
        🏡 Đăng tin bất động sản
      </h1>
      <p class="text-gray-600 mt-2">Vui lòng điền đầy đủ thông tin để tin đăng hiển thị chuyên nghiệp hơn</p>
    </div>

    <!-- Form -->
    <form method="POST" enctype="multipart/form-data" 
          class="bg-white p-8 shadow-2xl rounded-2xl space-y-6">
      
      <!-- Tiêu đề tin -->
      <div>
        <label class="block text-gray-700 font-semibold mb-1">Tiêu đề tin</label>
        <input type="text" name="tieu_de" required
          placeholder="VD: Bán căn hộ chung cư 2 phòng ngủ, Quận 7"
          class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                 focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
      </div>

      <!-- Mô tả -->
      <div>
        <label class="block text-gray-700 font-semibold mb-1">Mô tả chi tiết</label>
        <textarea name="mo_ta" rows="5" required
          placeholder="Nhập mô tả chi tiết bất động sản..."
          class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                 focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"></textarea>
      </div>

      <!-- Loại tin & Hình thức -->
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Loại bất động sản</label>
          <select name="loai" required
            class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                   focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition">
            <option value="">-- Chọn loại --</option>
            <option value="can_ho">Căn hộ</option>
            <option value="nha_o">Nhà ở</option>
            <option value="dat">Đất nền</option>
            <option value="biet_thu">Biệt thự</option>
            <option value="van_phong">Văn phòng</option>
          </select>
        </div>
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Hình thức</label>
          <select name="hinh_thuc" required
            class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                   focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition">
            <option value="">-- Chọn hình thức --</option>
            <option value="ban">Bán</option>
            <option value="cho_thue">Cho thuê</option>
          </select>
        </div>
      </div>

      <!-- Giá & Diện tích -->
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Giá (VNĐ)</label>
          <input type="number" name="gia" required
            placeholder="Ví dụ: 2500000000"
            class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                   focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
        </div>
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Diện tích (m²)</label>
          <input type="number" name="dien_tich" required
            placeholder="Ví dụ: 80"
            class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                   focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
        </div>
      </div>

      <!-- Số phòng -->
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Phòng ngủ</label>
          <input type="number" name="so_phong" min="0"
            class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                   focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
        </div>
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Phòng vệ sinh</label>
          <input type="number" name="so_wc" min="0"
            class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                   focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
        </div>
      </div>

      <!-- Địa chỉ -->
      <div>
        <label class="block text-gray-700 font-semibold mb-1">Địa chỉ</label>
        <input type="text" name="dia_chi" required
          placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"
          class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                 focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
      </div>

      <!-- Upload hình ảnh & video -->
      <div>
        <label class="block text-gray-700 font-semibold mb-1">Hình ảnh</label>
        <input type="file" name="hinh_anh[]" multiple accept="image/*"
          class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                 focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
      </div>
      <div>
        <label class="block text-gray-700 font-semibold mb-1">Video (tùy chọn)</label>
        <input type="file" name="video" accept="video/*"
          class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                 focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
      </div>

      <!-- Liên hệ -->
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Tên liên hệ</label>
          <input type="text" name="ten_lien_he" 
            value="<?= htmlspecialchars($_SESSION['ten_dang_nhap'] ?? '') ?>"
            class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                   focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
        </div>
        <div>
          <label class="block text-gray-700 font-semibold mb-1">Số điện thoại</label>
          <input type="text" name="so_dt"
            value="<?= htmlspecialchars($_SESSION['so_dt'] ?? '') ?>"
            class="w-full p-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white 
                   focus:ring-2 focus:ring-blue-400 outline-none shadow-sm transition"/>
        </div>
      </div>

      <!-- Nút submit -->
      <div class="text-center">
        <button type="submit" 
          class="px-10 py-3 rounded-xl font-semibold text-white shadow-lg 
                 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-indigo-600 hover:to-pink-500 
                 transition-all duration-300 transform hover:scale-105">
          🚀 Đăng tin ngay
        </button>
      </div>
    </form>
  </div>
</body>
</html>
