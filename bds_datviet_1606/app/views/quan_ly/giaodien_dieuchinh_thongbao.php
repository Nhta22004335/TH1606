<?php
// =================================================================
// 1. KẾT NỐI CƠ SỞ DỮ LIỆU & LẤY DỮ LIỆU
// =================================================================
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../../../config/database.php"; 

try {
    $pdo = ketnoicsdl(); 
} catch (PDOException $e) {
    die("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
}

$id_tb = $_GET['id'] ?? null;
if (!$id_tb) {
    die("Không tìm thấy ID thông báo.");
}

// Lấy thông tin chi tiết của thông báo
$sql_tb = "SELECT * FROM thong_bao WHERE id = ?";
$stmt_tb = $pdo->prepare($sql_tb);
$stmt_tb->execute([$id_tb]);
$notification = $stmt_tb->fetch(PDO::FETCH_ASSOC);

if (!$notification) {
    die("Không tìm thấy thông báo này.");
}

// Kiểm tra trạng thái. Nếu đã xem, không cho sửa.
$is_editable = ($notification['trang_thai'] === 'chuaxem');

// Lấy danh sách tất cả người dùng để chọn người nhận
$sql_users = "
    SELECT nd.id, info.ho_ten 
    FROM nguoi_dung nd
    JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung
    ORDER BY info.ho_ten ASC
";
$user_list = $pdo->query($sql_users)->fetchAll(PDO::FETCH_ASSOC);

// Các loại thông báo
$loai_map = [
    'hethong'   => 'Hệ thống',
    'giaodich'  => 'Giao dịch',
    'taikhoan'  => 'Tài khoản',
    'binhluan'  => 'Bình luận'
];
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Thông báo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        [x-cloak] { display: none !important; } 
    </style>
</head>
<body
      x-data="{ 
          showToast: false, toastMessage: '', toastType: 'success', 
          displayToast(detail) { 
              this.toastMessage = detail.message; 
              this.toastType = detail.type || 'success'; 
              this.showToast = true; 
              setTimeout(() => this.showToast = false, 3000); 
          },
          
          async submitEditForm() {
              const form = document.getElementById('edit-notification-form');
              const formData = new FormData(form);
              
              // !!! QUAN TRỌNG: Sửa lại đường dẫn này cho đúng với cấu trúc web của bạn !!!
              // Ví dụ: '/app/models/xuly_sua_thongbao.php'
              const apiUrl = '../../models/xuly_dieuchinh_thongbao_qt.php'; 
              
              try {
                  const response = await fetch(apiUrl, { method: 'POST', body: formData });
                  const result = await response.json();

                  if (result.success) {
                      this.displayToast({ message: result.message || 'Cập nhật thành công!', type: 'success' });
                      setTimeout(() => {
                          window.location.href = 'trangchu.php?page=quanly_thongbao'; // Quay về trang quản lý
                      }, 1500);
                  } else {
                      this.displayToast({ message: result.message || 'Cập nhật thất bại.', type: 'error' });
                  }
              } catch (error) {
                  console.error('Lỗi Fetch khi sửa:', error);
                  this.displayToast({ message: 'Lỗi kết nối khi sửa.', type: 'error' });
              }
          }
      }">

    <div class="max-w-7xl mx-auto">
        <header class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-500">Soạn thảo & Điều chỉnh Thông báo</h1>
                <p class="mt-1 text-sm text-slate-600">ID: <?= htmlspecialchars($id_tb) ?></p>
            </div>
            <a href="trangchu.php?page=quanly_thongbao" class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="fa-solid fa-xmark"></i> Đóng
            </a>
        </header>

        <form id="edit-notification-form" @submit.prevent="submitEditForm" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- CỘT BÊN TRÁI: SOẠN THẢO -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tiêu đề -->
                <div class="bg-white rounded-xl shadow-lg border border-slate-200">
                    <label for="tieu_de" class="block text-sm font-medium text-slate-700 p-4 border-b border-slate-200">Tiêu đề</label>
                    <input type="text" id="tieu_de" name="tieu_de" required <?= !$is_editable ? 'readonly' : '' ?>
                           value="<?= htmlspecialchars($notification['tieu_de']) ?>"
                           class="w-full border-0 px-4 py-3 text-lg outline-none font-medium text-slate-800 focus:ring-0 rounded-b-xl disabled:bg-slate-50">
                </div>
                
                <!-- Nội dung -->
                <div class="bg-white rounded-xl shadow-lg border border-slate-200">
                    <label for="noi_dung" class="block text-sm font-medium text-slate-700 p-4 border-b border-slate-200">Nội dung</label>
                    <textarea id="noi_dung" name="noi_dung" rows="5" required <?= !$is_editable ? 'readonly' : '' ?>
                              class="w-full border-0 p-4 text-sm text-slate-700 outline-none focus:ring-0 rounded-b-xl resize-none disabled:bg-slate-50"><?= htmlspecialchars($notification['noi_dung']) ?></textarea>
                </div>
            </div>

            <!-- CỘT BÊN PHẢI: THUỘC TÍNH & HÀNH ĐỘNG -->
            <div class="lg:col-span-1 lg:sticky lg:top-6 space-y-4">
                <!-- Card Thuộc tính -->
                <div class="bg-white rounded-xl shadow-lg border border-slate-200">
                    <h3 class="p-4 text-base font-semibold text-slate-800 border-b border-slate-200">Thuộc tính</h3>
                    <div class="p-5 space-y-4">
                        <div>
                            <label for="id_nguoi_dung" class="block text-sm font-medium text-slate-700 mb-1">Người nhận</label>
                            <select id="id_nguoi_dung" name="id_nguoi_dung" <?= !$is_editable ? 'disabled' : '' ?>
                                    class="w-full outline-none border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-slate-100 disabled:text-slate-500">
                                <option value="">-- Gửi cho Hệ thống/Tất cả --</option>
                                <?php foreach ($user_list as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= ($user['id'] == $notification['id_nguoi_dung']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['ho_ten']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="loai" class="block text-sm font-medium text-slate-700 mb-1">Loại thông báo</label>
                            <select id="loai" name="loai" <?= !$is_editable ? 'disabled' : '' ?>
                                    class="w-full outline-none border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-slate-100 disabled:text-slate-500">
                                <?php foreach ($loai_map as $key => $ten): ?>
                                    <option value="<?= $key ?>" <?= ($key == $notification['loai']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ten) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card Hành động -->
                <?php if ($is_editable): ?>
                <div class="bg-white rounded-xl shadow-lg border border-slate-200">
                    <h3 class="p-4 text-base font-semibold text-slate-800 border-b border-slate-200">Hành động</h3>
                    <div class="p-5 flex justify-end gap-3">
                        <a href="trangchu.php?page=quanly_thongbao" class="px-4 py-2 bg-white border border-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50">Hủy</a>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-md">Lưu thay đổi</button>
                    </div>
                </div>
                <?php else: ?>
                <!-- Card Cảnh báo -->
                <div class="p-4 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-800">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-lock fa-lg"></i>
                        <div class="flex-1">
                            <h3 class="font-semibold">Thông báo đã bị khóa</h3>
                            <p class="text-sm">Không thể chỉnh sửa vì đã được người dùng xem.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <input type="hidden" name="id" value="<?= htmlspecialchars($id_tb) ?>">
        </form>
    </div>

    <!-- Toast Notification -->
    <div x-show="showToast" x-cloak @show-toast.window="displayToast($event.detail)"
         class="fixed bottom-5 right-5 w-full max-w-sm p-4 rounded-xl shadow-2xl text-white font-semibold z-50" 
         :class="{ 'bg-gradient-to-r from-green-500 to-green-600': toastType === 'success', 'bg-gradient-to-r from-red-500 to-red-600': toastType === 'error' }">
        <div class="flex items-center">
            <i class="fas fa-2x mr-4" :class="{ 'fa-check-circle': toastType === 'success', 'fa-exclamation-triangle': toastType === 'error' }"></i>
            <span x-text="toastMessage"></span>
        </div>
    </div>

</body>
</html>