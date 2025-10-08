<?php
if(!isset($_SESSION['id_nguoi_dung'])) {
    // Giả sử session đã được khởi tạo ở nơi khác
    header("Location: ../auth/dangnhap.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>Đăng tin bất động sản</title>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-5xl mx-auto py-12 px-4 md:px-6">
    
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-blue-700 flex items-center justify-center gap-3">
            <i class="fa-solid fa-house-circle-check text-blue-500"></i>
            ĐĂNG BẤT ĐỘNG SẢN MỚI
        </h1>
        <p class="text-gray-600 mt-3">Hãy cung cấp thông tin chính xác và đầy đủ để tin đăng được duyệt nhanh hơn.</p>
    </div>

    <form method="POST" action="trangchu.php?page=../../models/xl_dang_sp" enctype="multipart/form-data" 
          class="bg-white p-8 shadow-2xl rounded-2xl border border-gray-100 space-y-8">
      
        <div class="space-y-6 border-b pb-6">
            <h2 class="text-2xl font-bold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-blue-500"></i> Thông tin Cơ bản
            </h2>
            
            <div>
                <label class="block text-sm text-gray-700 font-semibold mb-1">Tiêu đề tin <span class="text-red-500">*</span></label>
                <input type="text" name="tieu_de" required
                  placeholder="VD: Bán căn hộ chung cư 2 phòng ngủ, Quận 7, view sông"
                  class="w-full p-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none shadow-sm transition"/>
            </div>

            <div>
                <label class="block text-sm text-gray-700 font-semibold mb-1">Mô tả chi tiết <span class="text-red-500">*</span></label>
                <textarea name="mo_ta" rows="6" required
                  placeholder="Nhập mô tả chi tiết: tiện ích, nội thất, pháp lý..."
                  class="w-full p-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none shadow-sm transition"></textarea>
            </div>
        </div>

        <div class="space-y-6 border-b pb-6">
            <h2 class="text-2xl font-bold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-green-500"></i> Thông số & Vị trí
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-700 font-semibold mb-1">Loại bất động sản <span class="text-red-500">*</span></label>
                    <select name="loai" required
                      class="w-full p-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none shadow-sm transition appearance-none">
                        <option value="">-- Chọn loại --</option>
                        <option value="canho">Căn hộ</option>
                        <option value="nhapho">Nhà phố</option>
                        <option value="datnen">Đất nền</option>
                        <option value="bietthu">Biệt thự</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 font-semibold mb-1">Hình thức <span class="text-red-500">*</span></label>
                    <select name="hinh_thuc" required
                      class="w-full p-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none shadow-sm transition appearance-none">
                        <option value="">-- Chọn hình thức --</option>
                        <option value="ban">Bán</option>
                        <option value="thue">Cho thuê</option>
                        </select>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-700 font-semibold mb-1">Giá (VNĐ) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-dong-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="number" name="gia" required
                          placeholder="Ví dụ: 2,500,000,000"
                          class="w-full p-3 pl-8 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none shadow-sm transition"/>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 font-semibold mb-1">Diện tích (m²) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-ruler-combined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="number" name="dien_tich" required
                          placeholder="Ví dụ: 80"
                          class="w-full p-3 pl-8 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none shadow-sm transition"/>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-700 font-semibold mb-1">Khu vực (Tỉnh/Thành) <span class="text-red-500">*</span></label>
                    <input type="text" name="khu_vuc" required
                      placeholder="VD: TP. Hồ Chí Minh"
                      class="w-full p-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none shadow-sm transition"/>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 font-semibold mb-1">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
                    <input type="text" name="dia_chi" required
                      placeholder="Số nhà, đường, quận/huyện"
                      class="w-full p-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none shadow-sm transition"/>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <h2 class="text-2xl font-bold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-photo-film text-purple-500"></i> Hình ảnh & Video
            </h2>
            
            <div>
                <label class="block text-sm text-gray-700 font-semibold mb-1">Hình ảnh (Nhiều ảnh) <span class="text-red-500">*</span></label>
                <input type="file" name="hinh_anh[]" multiple required accept="image/*"
                  class="w-full p-3 file:mr-4 file:py-2 file:px-4
                         file:rounded-full file:border-0
                         file:text-sm file:font-semibold
                         file:bg-blue-50 file:text-blue-700
                         hover:file:bg-blue-100 transition duration-300
                         rounded-xl border border-gray-300 bg-white shadow-sm"/>
            </div>
            
            <div>
                <label class="block text-sm text-gray-700 font-semibold mb-1">Video (Tùy chọn)</label>
                <input type="file" name="video" accept="video/*"
                  class="w-full p-3 file:mr-4 file:py-2 file:px-4
                         file:rounded-full file:border-0
                         file:text-sm file:font-semibold
                         file:bg-purple-50 file:text-purple-700
                         hover:file:bg-purple-100 transition duration-300
                         rounded-xl border border-gray-300 bg-white shadow-sm"/>
            </div>
        </div>


        <div class="text-center pt-4">
            <button type="submit" 
              class="px-12 py-3 rounded-xl font-bold text-lg text-white shadow-xl 
                     bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-indigo-700 hover:to-blue-600 
                     transition-all duration-300 transform hover:scale-[1.02]">
                <i class="fa-solid fa-upload mr-2"></i> HOÀN TẤT VÀ ĐĂNG SẢN PHẨM
            </button>
        </div>
    </form>
</div>
</body>
</html>