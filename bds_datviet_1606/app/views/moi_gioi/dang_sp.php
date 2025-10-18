<?php
// Giả sử session đã được khởi tạo
// if(!isset($_SESSION['id_nguoi_dung'])) {
//     header("Location: ../auth/dangnhap.html");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng tin Bất động sản Mới</title>
    <style>
        /* Tùy chỉnh mũi tên cho select box */
        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
</head>
<body class="h-full">

<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    
    <header class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Tạo tin đăng mới</h1>
        <p class="mt-2 text-base text-slate-600">Cung cấp thông tin chi tiết để thu hút khách hàng tiềm năng.</p>
    </header>

    <form method="POST" action="trangchu.php?page=../../models/xl_dang_sp" enctype="multipart/form-data">
        <div class="lg:grid lg:grid-cols-3 lg:gap-12">

            <aside class="lg:col-span-1 lg:sticky lg:top-8 self-start hidden lg:block">
                <nav class="space-y-4">
                    <a href="#section-basic-info" class="flex items-center gap-3 px-4 py-2 text-base font-semibold text-indigo-600 bg-indigo-50 rounded-lg">
                        <i class="fa-solid fa-file-lines w-5 text-center"></i>
                        <span>Thông tin cơ bản</span>
                    </a>
                    <a href="#section-details" class="flex items-center gap-3 px-4 py-2 text-base font-medium text-slate-700 hover:bg-slate-100 rounded-lg">
                        <i class="fa-solid fa-map-location-dot w-5 text-center"></i>
                        <span>Thông số & Vị trí</span>
                    </a>
                    <a href="#section-media" class="flex items-center gap-3 px-4 py-2 text-base font-medium text-slate-700 hover:bg-slate-100 rounded-lg">
                        <i class="fa-solid fa-photo-film w-5 text-center"></i>
                        <span>Hình ảnh & Video</span>
                    </a>
                </nav>
            </aside>

            <div class="lg:col-span-2 space-y-10">

                <section id="section-basic-info" class="bg-white p-6 shadow-lg rounded-lg">
                    <h2 class="text-xl font-bold text-slate-800 border-b pb-4 mb-6">Thông tin cơ bản</h2>
                    <div class="space-y-6">
                        <div>
                            <label for="tieu_de" class="block text-sm font-medium text-slate-900">Tiêu đề tin <span class="text-red-500">*</span></label>
                            <p class="text-sm text-slate-500 mt-1">Tên gọi hấp dẫn, ngắn gọn, nêu bật điểm chính của BĐS.</p>
                            <input type="text" id="tieu_de" name="tieu_de" required
                                placeholder="VD: Bán căn hộ 2PN, 80m², full nội thất, view sông Sài Gòn"
                                class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"/>
                        </div>
                        <div>
                            <label for="mo_ta" class="block text-sm font-medium text-slate-900">Mô tả chi tiết <span class="text-red-500">*</span></label>
                            <p class="text-sm text-slate-500 mt-1">Mô tả về tiện ích, nội thất, tình trạng pháp lý, và các điểm nổi bật khác.</p>
                            <textarea id="mo_ta" name="mo_ta" rows="6" required
                                placeholder="Nội dung mô tả..."
                                class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"></textarea>
                        </div>
                    </div>
                </section>

                <section id="section-details" class="bg-white p-6 shadow-lg rounded-lg">
                    <h2 class="text-xl font-bold text-slate-800 border-b pb-4 mb-6">Thông số & Vị trí</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="loai" class="block text-sm font-medium text-slate-900">Loại bất động sản <span class="text-red-500">*</span></label>
                            <select id="loai" name="loai" required class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                                <option value="" disabled selected>-- Chọn loại --</option>
                                <option value="canho">Căn hộ</option>
                                <option value="nhapho">Nhà phố</option>
                                <option value="datnen">Đất nền</option>
                                <option value="bietthu">Biệt thự</option>
                            </select>
                        </div>
                       <div>
                        <label for="hinh_thuc" class="block text-sm font-medium text-slate-700">Hình thức</label>
                        <select name="hinh_thuc" id="hinh_thuc" class="mt-1 block w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                            <option value="ban" <?= (strtolower($product['hinh_thuc']) == 'ban') ? 'selected' : '' ?>>Bán</option>
                            <option value="cho_thue" <?= (strtolower($product['hinh_thuc']) == 'cho_thue') ? 'selected' : '' ?>>Cho thuê</option>
                        </select>
                    </div>
                                            <div>
                            <label for="gia" class="block text-sm font-medium text-slate-900">Giá (VNĐ) <span class="text-red-500">*</span></label>
                            <input type="number" id="gia" name="gia" required placeholder="VD: 2500000000" class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"/>
                        </div>
                         <div>
                            <label for="dien_tich" class="block text-sm font-medium text-slate-900">Diện tích (m²) <span class="text-red-500">*</span></label>
                            <input type="number" id="dien_tich" name="dien_tich" step="0.1" required placeholder="VD: 80" class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"/>
                        </div>
                        <!-- <div>
                            <label for="khu_vuc" class="block text-sm font-medium text-slate-900">Khu vực (Tỉnh/Thành) <span class="text-red-500">*</span></label>
                            <input type="text" id="khu_vuc" name="khu_vuc" required placeholder="VD: TP. Hồ Chí Minh" class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"/>
                        </div> -->
                        <div>
                            <label for="khu_vuc" class="block text-sm font-medium text-slate-900">
                                Khu vực (Tỉnh/Thành) <span class="text-red-500">*</span>
                            </label>
                            <select id="khu_vuc" name="khu_vuc" required
                                class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                                <option value="">-- Chọn tỉnh/thành --</option>
                                <option value="Hà Nội">Hà Nội</option>
                                <option value="Huế">Huế</option>
                                <option value="Lai Châu">Lai Châu</option>
                                <option value="Điện Biên">Điện Biên</option>
                                <option value="Sơn La">Sơn La</option>
                                <option value="Lạng Sơn">Lạng Sơn</option>
                                <option value="Quảng Ninh">Quảng Ninh</option>
                                <option value="Thanh Hóa">Thanh Hóa</option>
                                <option value="Nghệ An">Nghệ An</option>
                                <option value="Hà Tĩnh">Hà Tĩnh</option>
                                <option value="Cao Bằng">Cao Bằng</option>
                                <option value="Tuyên Quang">Tuyên Quang</option>
                                <option value="Lào Cai">Lào Cai</option>
                                <option value="Thái Nguyên">Thái Nguyên</option>
                                <option value="Phú Thọ">Phú Thọ</option>
                                <option value="Bắc Ninh">Bắc Ninh</option>
                                <option value="Hưng Yên">Hưng Yên</option>
                                <option value="Hải Phòng">Hải Phòng</option>
                                <option value="Ninh Bình">Ninh Bình</option>
                                <option value="Quảng Trị">Quảng Trị</option>
                                <option value="Đà Nẵng">Đà Nẵng</option>
                                <option value="Quảng Ngãi">Quảng Ngãi</option>
                                <option value="Gia Lai">Gia Lai</option>
                                <option value="Khánh Hòa">Khánh Hòa</option>
                                <option value="Lâm Đồng">Lâm Đồng</option>
                                <option value="Đắk Lắk">Đắk Lắk</option>
                                <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
                                <option value="Đồng Nai">Đồng Nai</option>
                                <option value="Tây Ninh">Tây Ninh</option>
                                <option value="Cần Thơ">Cần Thơ</option>
                                <option value="Vĩnh Long">Vĩnh Long</option>
                                <option value="Đồng Tháp">Đồng Tháp</option>
                                <option value="Cà Mau">Cà Mau</option>
                            </select>
                        </div>

                        <div>
                            <label for="dia_chi" class="block text-sm font-medium text-slate-900">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
                            <input type="text" id="dia_chi" name="dia_chi" required placeholder="Số nhà, đường, phường/xã, quận/huyện" class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"/>
                        </div>
                    </div>
                </section>

                <section id="section-media" class="bg-white p-6 shadow-lg rounded-lg" x-data="imagePreview()">
                    <h2 class="text-xl font-bold text-slate-800 border-b pb-4 mb-6">Hình ảnh & Video</h2>
                    <div class="space-y-6">
                        <div>
                            <label for="hinh_anh" class="block text-sm font-medium text-slate-900">Hình ảnh (tối đa 5 ảnh) <span class="text-red-500">*</span></label>
                             <p class="text-sm text-slate-500 mt-1">Ảnh đầu tiên sẽ là ảnh đại diện. Kéo thả để sắp xếp lại.</p>
                            <input @change="updatePreview" type="file" id="hinh_anh" name="hinh_anh[]" multiple required accept="image/*" class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition"/>
                            <div x-show="previews.length > 0" class="mt-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                                <template x-for="preview in previews" :key="preview.id">
                                    <div class="relative aspect-square">
                                        <img :src="preview.url" class="w-full h-full object-cover rounded-md shadow">
                                        <button @click="removePreview(preview.id)" type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label for="video" class="block text-sm font-medium text-slate-900">Video (tùy chọn)</label>
                            <p class="text-sm text-slate-500 mt-1">Tải lên một video ngắn giới thiệu về bất động sản.</p>
                            <input type="file" id="video" name="video" accept="video/*" class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 transition"/>
                        </div>
                    </div>
                </section>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-transform transform hover:scale-105">
                        <i class="fa-solid fa-upload mr-2"></i>
                        Đăng tin
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    // JavaScript cho Image Preview
    function imagePreview() {
        return {
            previews: [],
            updatePreview(event) {
                this.previews = [];
                const files = event.target.files;
                for (let i = 0; i < files.length; i++) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previews.push({ id: i, url: e.target.result });
                    };
                    reader.readAsDataURL(files[i]);
                }
            },
            removePreview(id) {
                this.previews = this.previews.filter(p => p.id !== id);
                // Cần thêm logic để xóa file khỏi input nếu cần
            }
        }
    }
</script>

</body>
</html>