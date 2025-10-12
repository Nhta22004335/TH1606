<?php
// g_thongbao_kh.php

// Giả lập PDO và dữ liệu nếu cần
if (!isset($pdo)) {
    class MockPDO {
        public function prepare($sql) { return $this; }
        public function execute($params = []) {}
        public function fetchAll($fetch_style = 0) {
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
    $sql = "SELECT nd.id, info.ho_ten, nd.email FROM nguoi_dung nd JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung JOIN quyen q ON pq.id_quyen = q.id LEFT JOIN info_nguoi_dung info ON nd.id = info.id_nguoi_dung WHERE q.vai_tro = 'khachhang' ORDER BY info.ho_ten ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $khach_hang = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn khách hàng: " . $e->getMessage());
}

$success_msg = null;
$error_msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['khach_hang_ids'], $_POST['tieu_de'], $_POST['noi_dung'])) {
    $id_nguoi_dung_arr = $_POST['khach_hang_ids'];
    $tieu_de = trim($_POST['tieu_de']);
    $noi_dung = trim($_POST['noi_dung']);

    if (!empty($id_nguoi_dung_arr) && !empty($tieu_de) && !empty($noi_dung)) {
        // ... (Logic gửi thông báo của bạn ở đây) ...
        $success_msg = "Đã gửi thông báo cho " . count($id_nguoi_dung_arr) . " khách hàng thành công!";
    } else {
        $error_msg = "Vui lòng chọn khách hàng và điền đầy đủ tiêu đề, nội dung.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Thông báo cho Khách hàng</title>
    <style>
        [x-cloak] { display: none !important; }
        .card-selected {
            border-color: #4f46e5 !important; /* indigo-600 */
            background-color: #eef2ff; /* indigo-50 */
        }
    </style>
</head>
<body class="h-full">

<div class="" x-data="notificationApp()">

    <header class="mb-6 border-b pb-4">
        <h1 class="text-2xl font-bold text-slate-800">Gửi thông báo</h1>
        <p class="mt-2 text-sm text-slate-600">Chọn khách hàng từ danh sách và soạn thảo nội dung để gửi đi.</p>
    </header>

    <?php if ($success_msg): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6" role="alert"><p><?= htmlspecialchars($success_msg) ?></p></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6" role="alert"><p><?= htmlspecialchars($error_msg) ?></p></div>
    <?php endif; ?>

    <form method="post" action="" class="lg:grid lg:grid-cols-12 lg:gap-8">

        <aside class="lg:col-span-5 xl:col-span-4">
            <div class="bg-white p-5 shadow-lg rounded-lg">
                <div class="mb-4">
                    <input type="text" placeholder="Tìm kiếm khách hàng..." x-model="search" class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-sm">
                </div>
                <div class="flex items-center justify-between border-t border-b border-slate-200 py-2 mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="selectAllCheckbox" @change="toggleSelectAll" :checked="isAllVisibleSelected" class="h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        <label for="selectAllCheckbox" class="ml-2 text-sm text-slate-700">Chọn tất cả</label>
                    </div>
                    <div class="text-sm font-medium text-slate-600">
                        Đã chọn: <span x-text="selectedIds.length" class="text-indigo-600 font-bold"></span> / <span x-text="filteredCustomers.length"></span>
                    </div>
                </div>

                <ul class="space-y-3 max-h-[60vh] overflow-y-auto pr-2">
                    <template x-for="customer in filteredCustomers" :key="customer.id">
                        <li @click="toggleSelection(customer.id)"
                            class="p-3 rounded-lg border-2 border-slate-200 flex items-start gap-3 cursor-pointer transition-colors duration-200"
                            :class="{ 'card-selected': selectedIds.includes(customer.id), 'hover:border-slate-300': !selectedIds.includes(customer.id) }">
                            <input type="checkbox" :checked="selectedIds.includes(customer.id)" @click.stop="toggleSelection(customer.id)" class="mt-1 h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 flex-shrink-0">
                            <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-lg flex-shrink-0">
                                <span x-text="customer.ho_ten.charAt(0).toUpperCase()"></span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800 text-sm" x-text="customer.ho_ten"></p>
                                <p class="text-slate-500 text-xs break-all" x-text="customer.email"></p>
                            </div>
                        </li>
                    </template>
                    <li x-show="filteredCustomers.length === 0" class="text-center py-8 text-slate-500 text-sm">
                        Không tìm thấy khách hàng nào.
                    </li>
                </ul>
            </div>
        </aside>

        <main class="lg:col-span-7 xl:col-span-8 lg:sticky lg:top-8 self-start mt-8 lg:mt-0">
            <div class="bg-white p-6 shadow-lg rounded-lg">
                <h2 class="text-xl font-bold text-slate-800 border-b pb-4 mb-6">Soạn thông báo</h2>
                
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="khach_hang_ids[]" :value="id">
                </template>

                <div class="space-y-6">
                    <div>
                        <label for="recipient-info" class="block text-sm font-medium text-slate-900">Gửi đến</label>
                        <div id="recipient-info" class="mt-2 p-3 bg-slate-50 rounded-md text-sm text-slate-700">
                            <span x-text="selectedIds.length > 0 ? `Tổng cộng ${selectedIds.length} khách hàng đã chọn.` : 'Vui lòng chọn ít nhất một khách hàng từ danh sách bên trái.'"></span>
                        </div>
                    </div>
                    <div>
                        <label for="tieu_de" class="block text-sm font-medium text-slate-900">Tiêu đề <span class="text-red-500">*</span></label>
                        <input type="text" id="tieu_de" name="tieu_de" required maxlength="100" class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>
                    <div>
                        <label for="noi_dung" class="block text-sm font-medium text-slate-900">Nội dung <span class="text-red-500">*</span></label>
                        <textarea id="noi_dung" name="noi_dung" rows="10" required class="mt-2 block w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition resize-y"></textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t mt-6">
                    <button type="submit"
                            :disabled="selectedIds.length === 0"
                            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-transform transform hover:scale-105 disabled:bg-slate-400 disabled:cursor-not-allowed disabled:transform-none">
                        <i class="fa-solid fa-paper-plane mr-2"></i>
                        Gửi thông báo
                    </button>
                </div>
            </div>
        </main>

    </form>
</div>

<script>
function notificationApp() {
    return {
        search: '',
        selectedIds: [],
        customers: <?= json_encode(array_map(function($kh){
            return [
                'id' => $kh['id'],
                'ho_ten' => !empty($kh['ho_ten']) ? $kh['ho_ten'] : '[Chưa cập nhật tên]',
                'email' => $kh['email']
            ];
        }, $khach_hang)) ?>,

        get filteredCustomers() {
            if (this.search === '') return this.customers;
            const searchLower = this.search.toLowerCase();
            return this.customers.filter(c =>
                c.ho_ten.toLowerCase().includes(searchLower) ||
                c.email.toLowerCase().includes(searchLower)
            );
        },

        get isAllVisibleSelected() {
            if (this.filteredCustomers.length === 0) return false;
            return this.filteredCustomers.every(c => this.selectedIds.includes(c.id));
        },

        toggleSelection(id) {
            const index = this.selectedIds.indexOf(id);
            if (index > -1) {
                this.selectedIds.splice(index, 1);
            } else {
                this.selectedIds.push(id);
            }
        },

        toggleSelectAll(event) {
            let visibleIds = this.filteredCustomers.map(c => c.id);
            if (event.target.checked) {
                // Add only those not already selected
                let newIds = visibleIds.filter(id => !this.selectedIds.includes(id));
                this.selectedIds.push(...newIds);
            } else {
                // Remove all visible ids from selection
                this.selectedIds = this.selectedIds.filter(id => !visibleIds.includes(id));
            }
        }
    }
}
</script>

</body>
</html>