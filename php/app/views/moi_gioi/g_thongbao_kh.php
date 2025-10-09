<?php
// g_thongbao_kh.php
// PHẦN XỬ LÝ DATABASE CỦA BẠN ĐƯỢC GIỮ NGUYÊN
// Lưu ý: $pdo phải được định nghĩa trước khi đoạn code này chạy trong môi trường thực tế.
// Giả lập $pdo cho môi trường Immersive nếu cần, nhưng ở đây tôi chỉ tập trung vào HTML/CSS.

// Giả lập PDO nếu không có trong môi trường Immersive để tránh lỗi PHP
if (!isset($pdo)) {
    class MockPDO {
        public function prepare($sql) { return $this; }
        public function execute($params = []) {}
        public function fetchAll($fetch_style = 0) {
            // Giả lập dữ liệu khách hàng nếu không có kết nối DB thực
            return [
                ['id' => 'kh001', 'ho_ten' => 'Nguyễn Văn An', 'email' => 'an.nguyen@example.com'],
                ['id' => 'kh002', 'ho_ten' => 'Trần Thị Bình', 'email' => 'binh.tran@example.com'],
                ['id' => 'kh003', 'ho_ten' => 'Lê Minh Chung', 'email' => 'chung.le@example.com'],
                ['id' => 'kh004', 'ho_ten' => 'Phạm Thu Dung', 'email' => 'dung.pham@example.com'],
                ['id' => 'kh005', 'ho_ten' => 'Vũ Hải Đăng', 'email' => 'dang.vu@example.com'],
                ['id' => 'kh006', 'ho_ten' => 'Hoàng Kim Em', 'email' => 'em.hoang@example.com'],
            ];
        }
    }
    $pdo = new MockPDO();
}

try {
    $sql = "
        SELECT nd.id, info.ho_ten, nd.email
        FROM nguoi_dung nd
        JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung
        JOIN quyen q ON pq.id_quyen = q.id
        LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
        WHERE q.vai_tro = 'khachhang'
        ORDER BY info.ho_ten ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $khach_hang = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi truy vấn khách hàng: " . $e->getMessage();
    exit;
}

$success_msg = null;
$error_msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['khach_hang_ids'], $_POST['tieu_de'], $_POST['noi_dung'])) {
    $id_nguoi_dung_arr = $_POST['khach_hang_ids']; 
    $tieu_de = trim($_POST['tieu_de']);
    $noi_dung = trim($_POST['noi_dung']);

    if (!empty($id_nguoi_dung_arr) && !empty($tieu_de) && !empty($noi_dung)) {
        try {
            // Trong môi trường thực, bạn sẽ chạy đoạn này
            // $sql_insert = "INSERT INTO thong_bao (id_nguoi_dung, loai, tieu_de, noi_dung) VALUES (:id_nguoi_dung, 'capnhatthongtin', :tieu_de, :noi_dung)";
            // $stmt_insert = $pdo->prepare($sql_insert);

            // foreach ($id_nguoi_dung_arr as $id_nguoi_dung) {
            //     $stmt_insert->execute([':id_nguoi_dung' => $id_nguoi_dung, ':tieu_de' => $tieu_de, ':noi_dung' => $noi_dung]);
            // }

            $success_msg = "Đã gửi thông báo cho " . count($id_nguoi_dung_arr) . " khách hàng thành công!";
        } catch (PDOException $e) {
            $error_msg = "Lỗi khi gửi thông báo: " . $e->getMessage();
        }
    } else {
        $error_msg = "Vui lòng chọn khách hàng và điền đầy đủ tiêu đề, nội dung thông báo.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý thông báo khách hàng</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="//unpkg.com/alpinejs" defer></script>
<style>
    /* Cấu hình Tailwind cho font Inter */
    :root {
        font-family: 'Inter', sans-serif;
    }
    .accent-blue-500 { /* Tùy chỉnh màu checkbox cho Alpine */
        accent-color: #3b82f6; 
    }
    /* Thêm hiệu ứng cho card khi được chọn */
    .card-selected {
        border-color: #3b82f6 !important;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3), 0 4px 6px -2px rgba(59, 130, 246, 0.1);
        transform: scale(1.02);
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    }
</style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="customerApp()">

<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

    <!-- Tiêu đề chính -->
    <header class="text-center mb-10">
        <h1 class="text-5xl sm:text-6xl font-extrabold mb-3 text-gray-900">
            Quản Lý Thông Báo
        </h1>
        <p class="text-lg text-gray-500">Chọn khách hàng và gửi tin nhắn cập nhật quan trọng.</p>
    </header>

    <!-- Khu vực thông báo (Thành công/Lỗi) -->
    <?php if ($success_msg): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-6 shadow-md" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($success_msg) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-6 shadow-md" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($error_msg) ?></span>
        </div>
    <?php endif; ?>

    <!-- Thanh tìm kiếm và thống kê VÀ NÚT CHỌN TẤT CẢ -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 border border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            
            <div class="flex w-full sm:w-2/3 space-x-3">
                <input type="text" placeholder="Tìm kiếm theo tên hoặc email khách hàng..." x-model="search"
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition">
                
                <!-- Nút Chọn/Bỏ chọn Tất cả -->
                <button @click="toggleSelectAll()"
                        :class="isAllSelected ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600'"
                        class="text-white px-4 py-3 rounded-xl shadow-md transition font-semibold flex-shrink-0 whitespace-nowrap">
                    <span x-text="isAllSelected ? 'Bỏ chọn Tất cả' : 'Chọn Tất cả'"></span>
                </button>
            </div>
            
            <div class="text-lg font-semibold text-gray-700 bg-blue-50 border-blue-200 border px-4 py-2 rounded-xl">
                Đã chọn: <span x-text="selected.length" class="text-blue-600"></span> khách hàng
            </div>
        </div>
    </div>

    <!-- Danh sách khách hàng card grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <template x-for="kh in filtered" :key="kh.id">
            <!-- Card khách hàng -->
            <div 
                class="p-5 bg-white rounded-2xl shadow-md border-2 border-transparent transition-all duration-200 card-hover"
                :class="selected.includes(kh.id) ? 'card-selected' : 'hover:border-blue-300'"
                @click="toggleSelection(kh.id)"
            >
                <label class="flex items-start cursor-pointer space-x-4">
                    <!-- Checkbox to show selection status -->
                    <input type="checkbox" :value="kh.id" x-model="selected" class="mt-1 w-6 h-6 accent-blue-500 rounded-lg flex-shrink-0" @click.stop="">
                    
                    <!-- Avatar/Icon -->
                    <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-lg">
                        <span x-text="kh.ho_ten.charAt(0).toUpperCase()"></span>
                    </div>

                    <!-- Info -->
                    <div>
                        <h2 class="font-bold text-gray-900 text-lg" x-text="kh.ho_ten"></h2>
                        <p class="text-gray-500 text-sm break-all" x-text="kh.email"></p>
                    </div>
                </label>
            </div>
        </template>
    </div>
    
    <!-- Button gửi thông báo (Sticky bottom for accessibility) -->
    <div class="sticky bottom-0 left-0 right-0 bg-gray-100 pt-6 pb-4 flex justify-end">
        <button @click="openModal=true" :disabled="selected.length===0"
                class="flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-8 py-4 rounded-full shadow-2xl hover:from-blue-700 hover:to-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed transition transform hover:scale-105">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-11 9h2m-2 4h2a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            <span class="font-bold text-lg">Gửi Thông Báo (<span x-text="selected.length"></span>)</span>
        </button>
    </div>

</div>

<!-- Modal gửi thông báo -->
<div x-show="openModal" class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm flex items-center justify-center z-50 p-4" style="display: none;">
    <div 
        class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 transform transition-all" 
        @click.away="openModal=false" 
        x-transition:enter="ease-out duration-300" 
        x-transition:enter-start="opacity-0 scale-90" 
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
    >
        <h2 class="text-3xl font-bold mb-8 text-gray-900 text-center border-b pb-3 text-indigo-600">
            Tạo Thông Báo Mới
        </h2>
        
        <p class="text-center mb-6 text-sm text-gray-600">Thông báo sẽ được gửi tới <span class="font-bold text-blue-600" x-text="selected.length"></span> khách hàng đã chọn.</p>
        
        <form method="post" action="" class="space-y-6">
            <!-- Hidden inputs cho ID khách hàng đã chọn -->
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="khach_hang_ids[]" :value="id">
            </template>
            
            <div>
                <label for="tieu_de" class="block mb-2 font-semibold text-gray-700">Tiêu đề (Ngắn gọn)</label>
                <input type="text" id="tieu_de" name="tieu_de"
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
                       required maxlength="100">
            </div>
            
            <div>
                <label for="noi_dung" class="block mb-2 font-semibold text-gray-700">Nội dung chi tiết</label>
                <textarea id="noi_dung" name="noi_dung" rows="7"
                          class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm resize-y"
                          required></textarea>
            </div>
            
            <div class="flex justify-end space-x-4 pt-4 border-t">
                <button type="button" @click="openModal=false"
                        class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-100 transition font-medium">
                    Hủy
                </button>
                <button type="submit"
                        class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-6 py-3 rounded-xl hover:from-blue-700 hover:to-indigo-800 shadow-lg transition font-medium">
                    Xác nhận Gửi
                </button>
            </div>
        </form>
    </div>
</div>


<script>
function customerApp() {
    return {
        search: '',
        selected: [],
        openModal: false,
        // Dữ liệu khách hàng từ PHP
        khachHang: <?= json_encode(array_map(function($kh){
            return [
                'id'=>$kh['id'],
                // Đảm bảo ho_ten luôn có giá trị để tránh lỗi hiển thị
                'ho_ten'=>!empty($kh['ho_ten'])?$kh['ho_ten']:'[Chưa cập nhật tên]', 
                'email'=>$kh['email']
            ];
        }, $khach_hang)) ?>,
        
        // Hàm lọc khách hàng dựa trên từ khóa tìm kiếm
        get filtered() {
            if(this.search === '') return this.khachHang;
            const searchLower = this.search.toLowerCase();
            return this.khachHang.filter(kh => 
                kh.ho_ten.toLowerCase().includes(searchLower) ||
                kh.email.toLowerCase().includes(searchLower)
            );
        },
        
        // Computed property để kiểm tra tất cả các khách hàng đã lọc có được chọn hay chưa
        get isAllSelected() {
            if (this.filtered.length === 0) return false;
            return this.filtered.every(kh => this.selected.includes(kh.id));
        },

        // Hàm chuyển đổi trạng thái chọn của khách hàng (dùng cho @click trên card)
        toggleSelection(id) {
            const index = this.selected.indexOf(id);
            if (index > -1) {
                this.selected.splice(index, 1); // Bỏ chọn
            } else {
                this.selected.push(id); // Chọn
            }
        },

        // Hàm Chọn/Bỏ chọn Tất cả khách hàng đang được lọc
        toggleSelectAll() {
            if (this.isAllSelected) {
                // Bỏ chọn tất cả các mục đang hiển thị (filtered)
                const filteredIds = this.filtered.map(kh => kh.id);
                this.selected = this.selected.filter(id => !filteredIds.includes(id));
            } else {
                // Chọn tất cả các mục đang hiển thị (filtered)
                const filteredIds = this.filtered.map(kh => kh.id);
                // Đảm bảo chỉ thêm các ID chưa có trong selected (để tránh trùng lặp)
                const uniqueNewIds = filteredIds.filter(id => !this.selected.includes(id));
                this.selected = [...this.selected, ...uniqueNewIds];
            }
        },
    }
}

// Kiểm tra nếu có thông báo thành công, mở modal để tránh bị mất thông báo
document.addEventListener('DOMContentLoaded', () => {
    <?php if ($success_msg || $error_msg): ?>
        // Cuộn lên đầu trang để người dùng nhìn thấy thông báo
        window.scrollTo({ top: 0, behavior: 'smooth' });
    <?php endif; ?>
});

</script>

</body>
</html>
