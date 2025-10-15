<?php
    require_once "../../../config/database.php";

    // --- BƯỚC 1: XỬ LÝ CÁC HÀNH ĐỘNG (POST REQUESTS) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
        $pdo = ketnoicsdl();
        $id = $_POST['id'];
        $action = $_POST['action'];
        $message = "Hành động không hợp lệ.";
        $msg_type = 'error';

        try {
            // Xác định hành động và thực thi
            if (in_array($action, ['dangxuly', 'hoantat', 'dahuy'])) {
                $stmt = $pdo->prepare("UPDATE giao_dich SET trang_thai = :trang_thai WHERE id = :id");
                $stmt->execute([':trang_thai' => $action, ':id' => $id]);
                $message = "Cập nhật trạng thái giao dịch thành công!";
                $msg_type = 'success';
            } elseif ($action === 'xoa') {
                $stmt = $pdo->prepare("DELETE FROM giao_dich WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $message = "Đã xóa giao dịch thành công!";
                $msg_type = 'success';
            }
        } catch (PDOException $e) {
            $message = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
        }

        $redirect_url = "trangchu.php?page=ql_thanhtoan&message=" . urlencode($message) . "&msg_type=" . $msg_type;
        echo "<script>window.location.href = '$redirect_url';</script>";
        exit();
    }

    // --- LOGIC LẤY DỮ LIỆU ĐỂ HIỂN THỊ (GET REQUESTS) ---
    $pdo = ketnoicsdl();
    $search = $_GET['search'] ?? '';
    $selectedStatus = $_GET['trangthai'] ?? ''; // Lấy trạng thái từ filter

    // Mảng ánh xạ trạng thái
    $status_map = [
        'choxuly' => ['text' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-800'],
        'dangxuly' => ['text' => 'Đang xử lý', 'class' => 'bg-blue-100 text-blue-800'],
        'hoantat' => ['text' => 'Hoàn tất', 'class' => 'bg-green-100 text-green-800'],
        'dahuy' => ['text' => 'Đã hủy', 'class' => 'bg-red-100 text-red-800']
    ];

    // Xây dựng câu truy vấn hiệu quả
    $sql = "
        SELECT gd.id, nd.ten_dang_nhap, bds.tieu_de, gd.loai, gd.ngay_giao_dich, gd.trang_thai
        FROM giao_dich gd
        LEFT JOIN nguoi_dung nd ON gd.id_nguoi_dung = nd.id
        LEFT JOIN bat_dong_san bds ON gd.id_bds = bds.id
    ";
    $params = [];
    $where = [];

    if (!empty($search)) {
        $where[] = "(nd.ten_dang_nhap ILIKE :search OR bds.tieu_de ILIKE :search)";
        $params[':search'] = "%$search%";
    }
    // Tối ưu: Lọc trạng thái trực tiếp trong SQL
    if (!empty($selectedStatus)) {
        $where[] = "gd.trang_thai = :trangthai";
        $params[':trangthai'] = $selectedStatus;
    }

    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY gd.ngay_giao_dich DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $giaodich = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="space-y-6">
    <?php if (!empty($_GET['message'])): ?>
        <div id="alertBox" class="p-4 rounded-md border <?= ($_GET['msg_type'] ?? 'error') === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' ?>">
            <?= htmlspecialchars(urldecode($_GET['message'])) ?>
        </div>
    <?php endif; ?>

    <header class="mb-6 border-b pb-4">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý Giao dịch</h1>
        <p class="text-sm mt-2 text-gray-500">Theo dõi và quản lý tất cả các giao dịch trên hệ thống.</p>
    </header>

    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-3">
            <input type="hidden" name="page" value="ql_thanhtoan">
            <div class="relative flex-grow">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" placeholder="Tìm người dùng, bất động sản..." 
                       value="<?= htmlspecialchars($search) ?>"
                       class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <select name="trangthai" class="px-3 py-2 text-sm border border-gray-300 rounded-md w-full md:w-48 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Tất cả trạng thái</option>
                <?php foreach ($status_map as $key => $status): ?>
                    <option value="<?= $key ?>" <?= ($selectedStatus == $key) ? 'selected' : '' ?>><?= $status['text'] ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 md:flex-none px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Lọc</button>
                <a href="?page=ql_thanhtoan" class="flex-1 md:flex-none text-center px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300">Hủy</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Người dùng</th>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Bất động sản</th>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Loại GD</th>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Ngày Giao Dịch</th>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                    <th class="py-3 px-4 text-center text-xs font-bold text-gray-500 uppercase">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($giaodich)): ?>
                    <tr><td colspan="6" class="p-8 text-center text-gray-500">Không có giao dịch nào phù hợp.</td></tr>
                <?php else: ?>
                    <?php foreach($giaodich as $gd): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-medium text-gray-900"><?= htmlspecialchars($gd['ten_dang_nhap'] ?? 'N/A') ?></td>
                        <td class="p-3 text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($gd['tieu_de'] ?? 'N/A') ?>"><?= htmlspecialchars($gd['tieu_de'] ?? 'N/A') ?></td>
                        <?php
                            $loai_map = [
                                'mua' => 'Mua Bất Động Sản',
                                'ban' => 'Bán Bất Động Sản',
                                'thue' => 'Thuê Bất Động Sản'
                            ];
                        ?>
                        <td class="p-3 text-gray-600 capitalize">
                            <?= htmlspecialchars($loai_map[$gd['loai']] ?? 'Không xác định') ?>
                        </td>
                        <td class="p-3 text-gray-600 whitespace-nowrap"><?= date("d/m/Y H:i", strtotime($gd['ngay_giao_dich'])) ?></td>
                        <td class="p-3">
                            <?php $status_info = $status_map[$gd['trang_thai']] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-100 text-gray-800']; ?>
                            <span class="px-2.5 py-0.5 text-xs font-medium rounded-full <?= $status_info['class'] ?>"><?= $status_info['text'] ?></span>
                        </td>
                        <td class="p-3 text-center">
                            <div class="relative inline-block text-left" data-menu>
                                <button type="button" data-menu-button class="p-1.5 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div data-menu-items class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden z-10">
                                    <div class="py-1" role="none">
                                        <?php if ($gd['trang_thai'] === 'choxuly'): ?>
                                            <form method="POST" class="block"><input type="hidden" name="id" value="<?= $gd['id'] ?>"><input type="hidden" name="action" value="dangxuly"><button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Bắt đầu xử lý</button></form>
                                        <?php endif; ?>
                                        <?php if ($gd['trang_thai'] === 'dangxuly'): ?>
                                            <form method="POST" class="block"><input type="hidden" name="id" value="<?= $gd['id'] ?>"><input type="hidden" name="action" value="hoantat"><button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Hoàn tất</button></form>
                                        <?php endif; ?>
                                        <?php if (in_array($gd['trang_thai'], ['choxuly', 'dangxuly'])): ?>
                                            <form method="POST" class="block"><input type="hidden" name="id" value="<?= $gd['id'] ?>"><input type="hidden" name="action" value="dahuy"><button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Hủy giao dịch</button></form>
                                        <?php endif; ?>
                                        <a href="trangchu.php?page=ct_giaodich&id=<?= $gd['id'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Xem chi tiết</a>
                                        <div class="border-t my-1"></div>
                                        <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn giao dịch này?');" class="block">
                                            <input type="hidden" name="id" value="<?= $gd['id'] ?>">
                                            <input type="hidden" name="action" value="xoa">
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Tự động ẩn thông báo và làm sạch URL
    const alertBox = document.getElementById("alertBox");
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => {
                alertBox.remove();
                // Xóa message khỏi URL mà không reload trang
                const url = new URL(window.location.href);
                url.searchParams.delete('message');
                url.searchParams.delete('msg_type');
                history.replaceState(null, '', url.toString());
            }, 500);
        }, 3000);
    }

    // Logic cho dropdown menu hành động
    document.querySelectorAll('[data-menu-button]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            // Đóng tất cả các menu khác
            document.querySelectorAll('[data-menu-items]').forEach(m => {
                if (m !== button.nextElementSibling) m.classList.add('hidden');
            });
            // Mở menu hiện tại
            const menu = button.nextElementSibling;
            menu.classList.toggle('hidden');
        });
    });

    // Đóng menu khi click ra ngoài
    window.addEventListener('click', () => {
        document.querySelectorAll('[data-menu-items]').forEach(menu => menu.classList.add('hidden'));
    });
});
</script>