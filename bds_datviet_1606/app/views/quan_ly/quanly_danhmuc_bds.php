<?php
    require_once "../../../config/database.php"; 

    try {
        $pdo = ketnoicsdl();
    } catch (PDOException $e) { die("Lỗi kết nối CSDL: " . $e->getMessage()); }

    // Logic lấy dữ liệu danh mục và đếm số lượng BĐS
    $sql = "
        SELECT 
            dm.id, dm.ma_danh_muc, dm.ten_danh_muc, COUNT(bds.id) AS bds_count 
        FROM danh_muc dm
        LEFT JOIN bat_dong_san bds ON dm.id = bds.id_danh_muc
        GROUP BY dm.id, dm.ma_danh_muc, dm.ten_danh_muc
        ORDER BY dm.ten_danh_muc ASC
    ";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50 h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục Bất động sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        [x-cloak] { display: none !important; } 
        .modal { transition: opacity 0.3s ease-out; }
        .modal-content { transition: transform 0.3s ease-out; }
    </style>
</head>
<body
      x-data="{ 
          isAddModalOpen: false, 
          isEditModalOpen: false, 
          editingCategory: { id: null, ma_danh_muc: '', ten_danh_muc: '' },
          showToast: false, 
          toastMessage: '', 
          toastType: 'success', 

          openEditModal(category) { 
              this.editingCategory = JSON.parse(JSON.stringify(category)); 
              this.isEditModalOpen = true; 
          },

          displayToast(detail) { 
              console.log('Toast Event Received:', detail); // Log để kiểm tra
              this.toastMessage = detail.message; 
              this.toastType = detail.type || 'success'; 
              this.showToast = true; 
              setTimeout(() => this.showToast = false, 3500); 
          },

          async submitAddForm() {
              const form = document.getElementById('add-category-form');
              const formData = new FormData(form);
              // !!! THAY THẾ BẰNG ĐƯỜNG DẪN TUYỆT ĐỐI CHÍNH XÁC !!!
              const apiUrl = '../../models/them_danhmuc_bds_qt.php'; 
              
              try {
                  const response = await fetch(apiUrl, { method: 'POST', body: formData });
                  const result = await response.json();
                  if (result.success) {
                      this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Thêm thành công!', type: 'success' }, bubbles: true }));
                      setTimeout(() => window.location.reload(), 2000); 
                  } else {
                      this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Không thể thêm danh mục.', type: 'error' }, bubbles: true }));
                  }
              } catch (error) {
                  console.error('Lỗi Fetch khi thêm:', error);
                  this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Lỗi kết nối khi thêm.', type: 'error' }, bubbles: true }));
              } finally {
                  this.isAddModalOpen = false; 
              }
          },

          async submitEditForm() {
              const form = document.getElementById('edit-category-form');
              const formData = new FormData(form);
              // !!! THAY THẾ BẰNG ĐƯỜNG DẪN TUYỆT ĐỐI CHÍNH XÁC !!!
              const apiUrl = '../../models/sua_danhmuc_bds_qt.php'; 

              try {
                  const response = await fetch(apiUrl, { method: 'POST', body: formData });
                  const result = await response.json();
                  if (result.success) {
                       this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Cập nhật thành công!', type: 'success' }, bubbles: true }));
                       setTimeout(() => window.location.reload(), 2000); 
                  } else {
                       this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Không thể cập nhật.', type: 'error' }, bubbles: true }));
                  }
              } catch (error) {
                  console.error('Lỗi Fetch khi sửa:', error);
                  this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Lỗi kết nối khi sửa.', type: 'error' }, bubbles: true }));
              } finally {
                  this.isEditModalOpen = false; 
              }
          },

          async deleteCategory(categoryId, categoryCount) {
              if (categoryCount > 0) {
                   this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Không thể xóa danh mục đang được sử dụng.', type: 'error' }, bubbles: true }));
                  return;
              }
              if (!confirm('Bạn có chắc chắn muốn xóa danh mục này không? Hành động này không thể hoàn tác.')) return;

              const formData = new FormData();
              formData.append('id', categoryId);
              // !!! THAY THẾ BẰNG ĐƯỜNG DẪN TUYỆT ĐỐI CHÍNH XÁC !!!
              const apiUrl = '../../models/xoa_danhmuc_bds_qt.php'; 

              try {
                  const response = await fetch(apiUrl, { method: 'POST', body: formData });
                  const result = await response.json();
                  if (result.success) {
                      const card = document.getElementById(`category-card-${categoryId}`);
                      if (card) {
                          card.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                          card.style.opacity = '0';
                          card.style.transform = 'scale(0.95)';
                          setTimeout(() => card.remove(), 300);
                      }
                      this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Xóa thành công!', type: 'success' }, bubbles: true }));
                  } else {
                      this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message || 'Xóa không thành công.', type: 'error' }, bubbles: true }));
                  }
              } catch (error) {
                  console.error('Lỗi Fetch khi xóa:', error);
                  this.$el.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Lỗi kết nối khi xóa.', type: 'error' }, bubbles: true }));
              }
          }
      }">
    <div class="max-w-7xl mx-auto">
        <header class="mb-6 border-b pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-500">Quản lý Danh mục</h1>
                <p class="mt-1 text-sm text-slate-600">Thêm, sửa và xem các loại hình bất động sản.</p>
            </div>
            <button @click="isAddModalOpen = true" class="mt-4 sm:mt-0 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition">
                <i class="fas fa-plus"></i> Thêm Danh mục mới
            </button>
        </header>

        <main id="category-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (empty($categories)): ?>
                <p class="col-span-full text-center text-gray-500">Chưa có danh mục nào.</p>
            <?php else: ?>
                <?php foreach($categories as $cat): ?>
                <div id="category-card-<?= $cat['id'] ?>" class="category-card bg-white rounded-xl shadow-lg border border-gray-200/80 p-5 flex flex-col justify-between hover:shadow-xl transition-shadow duration-300">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($cat['ten_danh_muc']) ?></h2>
                        <p class="text-xs font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded w-fit my-2"><?= htmlspecialchars($cat['ma_danh_muc']) ?></p>
                        <p class="text-sm text-slate-500 flex items-center gap-2">
                            <i class="fa-solid fa-building text-indigo-500"></i> Có <strong><?= $cat['bds_count'] ?></strong> bất động sản
                        </p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-200 flex justify-end gap-2">
                        <button @click="openEditModal(<?= htmlspecialchars(json_encode($cat)) ?>)" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-md transition" title="Sửa"><i class="fas fa-edit"></i> Sửa</button>
                        <button @click="deleteCategory('<?= $cat['id'] ?>', <?= $cat['bds_count'] ?>)" 
                                class="delete-btn flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-md transition" 
                                title="Xóa">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>

    <div id="add-modal" x-show="isAddModalOpen" x-cloak 
         class="modal fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
         <form id="add-category-form" @submit.prevent="submitAddForm()" 
               class="modal-content bg-white w-full max-w-md rounded-xl shadow-lg p-6">
             <h2 class="text-xl font-bold text-slate-800 mb-4">Thêm Danh mục mới</h2>
             <div class="space-y-4">
                 <div><label for="add-ten" class="block text-sm font-medium text-slate-700 mb-1">Tên Danh mục</label><input type="text" id="add-ten" name="ten_danh_muc" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"></div>
                 <div><label for="add-ma" class="block text-sm font-medium text-slate-700 mb-1">Mã Danh mục (viết liền, không dấu, chữ thường)</label><input type="text" id="add-ma" name="ma_danh_muc" required pattern="[a-z0-9]+" title="Chỉ nhập chữ thường không dấu và số" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"></div>
             </div>
             <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-200"><button type="button" @click="isAddModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50">Hủy</button><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-md">Thêm</button></div>
        </form>
    </div>

    <div id="edit-modal" x-show="isEditModalOpen" x-cloak 
         class="modal fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <form id="edit-category-form" @submit.prevent="submitEditForm()" 
              class="modal-content bg-white w-full max-w-md rounded-xl shadow-lg p-6">
             <h2 class="text-xl font-bold text-slate-800 mb-4">Sửa Danh mục</h2>
             <input type="hidden" name="id" :value="editingCategory.id">
             <div class="space-y-4">
                 <div><label for="edit-ten" class="block text-sm font-medium text-slate-700 mb-1">Tên Danh mục</label><input type="text" id="edit-ten" name="ten_danh_muc" x-model="editingCategory.ten_danh_muc" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"></div>
                 <div><label for="edit-ma" class="block text-sm font-medium text-slate-700 mb-1">Mã Danh mục (viết liền, không dấu, chữ thường)</label><input type="text" id="edit-ma" name="ma_danh_muc" x-model="editingCategory.ma_danh_muc" required pattern="[a-z0-9]+" title="Chỉ nhập chữ thường không dấu và số" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"></div>
             </div>
             <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-200"><button type="button" @click="isEditModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50">Hủy</button><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-md">Lưu thay đổi</button></div>
        </form>
    </div>
    
    <div x-show="showToast" x-cloak 
         @show-toast.document="displayToast($event.detail)" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="fixed bottom-5 right-5 w-full max-w-sm p-4 rounded-xl shadow-2xl text-white font-semibold z-50" 
         :class="{ 'bg-gradient-to-r from-green-500 to-green-600': toastType === 'success', 'bg-gradient-to-r from-red-500 to-red-600': toastType === 'error' }">
        <div class="flex items-center">
            <i class="fas fa-2x mr-4" :class="{ 'fa-check-circle': toastType === 'success', 'fa-exclamation-triangle': toastType === 'error' }"></i>
            <span x-text="toastMessage"></span>
        </div>
    </div>

</body>
</html>