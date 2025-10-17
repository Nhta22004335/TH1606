<?php
// File: phan_quyen.php
require_once "../../../config/database.php";
$pdo = ketnoicsdl();

// 1. Lấy danh sách tất cả người dùng và các quyền của họ
$stmt_users = $pdo->query("
    SELECT 
        nd.id,
        nd.ten_dang_nhap,
        nd.email,
        nd.avt,
        COALESCE(ARRAY_AGG(q.id) FILTER (WHERE q.id IS NOT NULL), '{}') AS danh_sach_id_quyen
    FROM nguoi_dung nd
    LEFT JOIN phan_quyen pq ON nd.id = pq.id_nguoi_dung
    LEFT JOIN quyen q ON pq.id_quyen = q.id
    GROUP BY nd.id
    ORDER BY nd.ngay_tao DESC
");
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// 2. Lấy danh sách tất cả các quyền có trong hệ thống để tạo cột
$stmt_roles = $pdo->query("SELECT id, vai_tro FROM quyen ORDER BY vai_tro");
$all_roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

// 3. Mảng nhãn và icon cho vai trò
$roleConfig = [
    'quantri' => ['label' => 'Quản trị', 'icon' => 'fa-shield-alt'],
    'moigioi' => ['label' => 'Môi giới', 'icon' => 'fa-briefcase'],
    'khachhang' => ['label' => 'Khách hàng', 'icon' => 'fa-user-tag']
];

$roleToggleColors = [
    'quantri' => [
        'focus' => 'peer-focus:ring-red-300',
        'checked' => 'peer-checked:bg-red-500'
    ],
    'moigioi' => [
        'focus' => 'peer-focus:ring-yellow-300',
        'checked' => 'peer-checked:bg-yellow-600'
    ],
    'khachhang' => [
        'focus' => 'peer-focus:ring-teal-300',
        'checked' => 'peer-checked:bg-teal-500'
    ],
    'default' => [
        'focus' => 'peer-focus:ring-gray-300',
        'checked' => 'peer-checked:bg-gray-500'
    ]
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phân quyền người dùng</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        /* Style cho custom toggle switch */
        .togglke-checbox:checked {
            right: 0;
            border-color: #4f46e5; /* indigo-600 */
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #4f46e5; /* indigo-600 */
        }
    </style>
</head>
<body x-data="toastManager()">

<div class="max-w-7xl mx-auto">

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Người dùng</th>
                        <?php foreach ($all_roles as $role): ?>
                            <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas <?= $roleConfig[$role['vai_tro']]['icon'] ?? 'fa-user' ?>"></i>
                                    <span><?= $roleConfig[$role['vai_tro']]['label'] ?? ucfirst($role['vai_tro']) ?></span>
                                </div>
                            </th>
                        <?php endforeach; ?>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="<?= count($all_roles) + 2 ?>" class="text-center py-10 text-gray-500">Không có người dùng nào.</td></tr>
                    <?php else: ?>
                        <?php foreach($users as $user): ?>
                            <?php $current_role_ids_str = htmlspecialchars(trim($user['danh_sach_id_quyen'], '{}')); ?>
                            <tr x-data="userRoleRow('<?= $user['id'] ?>', '<?= $current_role_ids_str ?>')" class="hover:bg-indigo-50/50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="relative flex-shrink-0 h-11 w-11">
                                            <img class="h-11 w-11 rounded-full object-cover" src="../../../storage/pictures/avt/<?= htmlspecialchars($user['avt']) ?>" onerror="this.onerror=null; this.src='../../../storage/pictures/avt/avt.png';">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($user['ten_dang_nhap']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <?php foreach ($all_roles as $role): ?>
                                     <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <?php $colors = $roleToggleColors[$role['vai_tro']] ?? $roleToggleColors['default']; ?>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" value="<?= $role['id'] ?>" x-model="selectedRoles" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 <?= $colors['focus'] ?> after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white <?= $colors['checked'] ?>"></div>
                                        </label>
                                    </td>
                                <?php endforeach; ?>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button @click="save()" :disabled="!isDirty || isSaving" x-cloak class="px-5 py-2 w-32 text-sm font-semibold rounded-lg transition-all duration-300 transform"
                                            :class="{
                                                'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-300 scale-100 hover:scale-105': isDirty && !isSaving,
                                                'bg-gray-200 text-gray-500 cursor-not-allowed': !isDirty || isSaving
                                            }">
                                        <span x-show="!isSaving"><i class="fas fa-save mr-2"></i> Lưu</span>
                                        <span x-show="isSaving"><i class="fas fa-spinner fa-spin"></i> Đang lưu...</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div x-show="show" x-cloak
     @show-toast.window="showToast($event.detail)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform translate-y-4"
     class="fixed bottom-5 right-5 w-full max-w-sm p-4 rounded-xl shadow-2xl text-white font-semibold"
     :class="{ 'bg-gradient-to-r from-green-500 to-green-600': type === 'success', 'bg-gradient-to-r from-red-500 to-red-600': type === 'error' }">
    <div class="flex items-center">
        <i class="fas fa-2x mr-4" :class="{ 'fa-check-circle': type === 'success', 'fa-exclamation-triangle': type === 'error' }"></i>
        <span x-text="message"></span>
    </div>
</div>

<script>

    function toastManager() {
        return {
            show: false, message: '', type: 'success',
            showToast(detail) {
                this.message = detail.message; this.type = detail.type; this.show = true;
                setTimeout(() => this.show = false, 3000);
            }
        };
    }
    function userRoleRow(userId, currentRoleIdsStr) {
        return {
            userId: userId,
            selectedRoles: currentRoleIdsStr ? currentRoleIdsStr.split(',').filter(id => id) : [],
            initialRoles: currentRoleIdsStr ? currentRoleIdsStr.split(',').filter(id => id) : [],
            isSaving: false,
            get isDirty() {
                const sortedCurrent = [...this.selectedRoles].sort();
                const sortedInitial = [...this.initialRoles].sort();
                return JSON.stringify(sortedCurrent) !== JSON.stringify(sortedInitial);
            },
            async save() {
                if (!this.isDirty) return;
                if (this.selectedRoles.length === 0) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Người dùng phải có ít nhất một vai trò!', type: 'error' } }));
                    return;
                }
                this.isSaving = true;
                try {
                    const response = await fetch('../../models/xuly_phanquyen_qt.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ user_id: this.userId, role_ids: this.selectedRoles })
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Cập nhật thành công!', type: 'success' } }));
                        this.initialRoles = [...this.selectedRoles];
                    } else {
                        window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: result.message, type: 'error' } }));
                    }
                } catch (error) {
                    console.error('Lỗi khi gửi yêu cầu:', error);
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Lỗi kết nối máy chủ.', type: 'error' } }));
                } finally {
                    this.isSaving = false;
                }
            }
        }
    }
</script>

</body>
</html>