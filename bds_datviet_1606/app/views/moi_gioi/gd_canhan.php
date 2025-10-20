<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "../../../config/database.php";

$id_nguoi_dung_hien_tai = $_SESSION['id_nguoi_dung'] ?? null;
if (!$id_nguoi_dung_hien_tai) {
    header("Location: ../auth/dangnhap.php");
    exit;
}

$pdo = ketnoicsdl();

$search = $_GET['search'] ?? '';
$boloc_json = $_GET['boloc'] ?? '{}'; // Lấy boloc
$filters = json_decode($boloc_json, true) ?? []; // Gán filters từ boloc

$sql = "
    SELECT gd.id, nd.ten_dang_nhap, bds.dia_chi_day_du as tieu_de, gd.loai, gd.ngay_giao_dich, gd.trang_thai
    FROM giao_dich gd
    LEFT JOIN nguoi_dung nd ON gd.id_nguoi_dung = nd.id
    LEFT JOIN bat_dong_san bds ON gd.id_bds = bds.id
";
$params = [];
$where = [];
$where[] = "gd.id_nguoi_ban = :id_nguoi_ban_hien_tai";
$params[':id_nguoi_ban_hien_tai'] = $id_nguoi_dung_hien_tai;

if (!empty($search)) {
    $where[] = "(nd.ten_dang_nhap ILIKE :search OR bds.tieu_de ILIKE :search)";
    $params[':search'] = "%$search%";
}

// THAY ĐỔI: Chuyển bộ lọc trạng thái vào SQL cho hiệu quả
if (isset($filters['trangthai']) && $filters['trangthai'] !== '') {
    $where[] = "gd.trang_thai = :trangthai";
    $params[':trangthai'] = $filters['trangthai'];
}
// KẾT THÚC THAY ĐỔI

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY gd.ngay_giao_dich DESC";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $giaodich = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Lỗi CSDL: " . $e->getMessage());
    $giaodich = [];
}

if (!empty(trim($search))) {
    try {
        $sql_search = "INSERT INTO lich_su_tim_kiem (id_nguoi_dung, tu_khoa_tim_kiem) VALUES (?, ?)";
        $stmt_search = $pdo->prepare($sql_search);
        // THAY ĐỔI: $id không tồn tại, phải dùng $id_nguoi_dung_hien_tai
        $stmt_search->execute([$id_nguoi_dung_hien_tai, $search]);
    } catch (PDOException $e) {
        // error_log("Lỗi khi lưu lịch sử tìm kiếm: " . $e->getMessage());
    }
}

$status_map = [
    'choxuly'  => ['text' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-800'],
    'pending' => ['text' => 'Đang xử lý', 'class' => 'bg-blue-100 text-blue-800'],
    'hoantat'  => ['text' => 'Hoàn tất',   'class' => 'bg-green-100 text-green-800'],
    'dahuy'    => ['text' => 'Đã hủy',    'class' => 'bg-red-100 text-red-800']
];
?>

<div class="space-y-6">

    <header class="pb-4 border-b">
        <h1 class="text-2xl font-bold text-gray-800">Giao dịch cá nhân</h1>
        <p class="text-sm text-gray-500 mt-1">Quản lý các giao dịch bạn đã thực hiện hoặc đang theo dõi.</p>
    </header>

    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-3">
            <input type="hidden" name="page" value="gd_canhan">
            
            <div class="relative flex-grow">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                <input type="text" name="search" placeholder="Tìm người dùng, bất động sản..." 
                       value="<?= htmlspecialchars($search) ?>"
                       class="w-full pl-10 pr-4 px-4 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
            </div>

            <select name="trangthai" id="trangthai" class="px-4 py-2 border border-gray-300 rounded-md text-sm w-full md:w-48 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <option value="">Tất cả trạng thái</option>
                <option value="choxuly" <?= (($filters['trangthai'] ?? '') == 'choxuly') ? 'selected' : '' ?>>Chờ xử lý</option>
                <option value="dangxuly" <?= (($filters['trangthai'] ?? '') == 'dangxuly') ? 'selected' : '' ?>>Đang xử lý</option>
                <option value="hoantat" <?= (($filters['trangthai'] ?? '') == 'hoantat') ? 'selected' : '' ?>>Hoàn tất</option>
                <option value="dahuy" <?= (($filters['trangthai'] ?? '') == 'dahuy') ? 'selected' : '' ?>>Đã hủy</option>
            </select>

            <div class="flex gap-2">
                <button id="btnloc" type="button" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-indigo-600 text-sm text-white font-medium rounded-md hover:bg-indigo-700 transition">
                    Lọc
                </button>
                <a href="?page=gd_canhan" class="flex-1 md:flex-none flex items-center justify-center px-3 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Người Mua</th>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Bất Động Sản</th>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Ngày Giao Dịch</th>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Trạng Thái</th>
                    <th class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase">Hành Động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (count($giaodich) > 0): ?>
                    <?php foreach($giaodich as $gd): ?>
                        <tr class="hover:bg-gray-50" id="row-<?= $gd['id'] ?>">
                            <td class="p-3 font-medium text-gray-900"><?= htmlspecialchars($gd['ten_dang_nhap'] ?? 'N/A') ?></td>
                            <td class="p-3 text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($gd['tieu_de'] ?? 'N/A') ?>">
                                <?= htmlspecialchars($gd['tieu_de'] ?? 'N/A') ?>
                            </td>
                            <td class="p-3 text-gray-600 whitespace-nowrap"><?= date("d/m/Y H:i", strtotime($gd['ngay_giao_dich'])) ?></td>
                            <td class="p-3">
                                <?php $status_info = $status_map[$gd['trang_thai']] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-100 text-gray-800']; ?>
                                <span id="status-badge-<?= $gd['id'] ?>" class="px-2.5 py-0.5 text-xs font-medium rounded-full <?= $status_info['class'] ?>">
                                    <?= $status_info['text'] ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <?php 
                                        $current_status = $gd['trang_thai'];
                                        $is_disabled = in_array($current_status, ['hoantat', 'dahuy']);
                                    ?>
                                    <select id="select-status-<?= $gd['id'] ?>" class="px-2 py-1 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 transition" <?= $is_disabled ? 'disabled' : '' ?>>
                                        <option value="choxuly" <?= $current_status == 'choxuly' ? 'selected' : '' ?>>Chờ xử lý</option>
                                        <option value="dangxuly" <?= $current_status == 'dangxuly' ? 'selected' : '' ?>>Đang xử lý</option>
                                        <option value="hoantat" <?= $current_status == 'hoantat' ? 'selected' : '' ?>>Hoàn tất</option>
                                        <option value="dahuy" <?= $current_status == 'dahuy' ? 'selected' : '' ?>>Hủy</option>
                                    </select>
                                    <button onclick="capNhatTrangThai('<?= $gd['id'] ?>')" 
                                            class="px-2 py-1 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 transition <?= $is_disabled ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                            <?= $is_disabled ? 'disabled' : '' ?>>
                                        Lưu
                                    </button>
                                    <a href="../../views/quan_ly/trangchu.php?page=../moi_gioi/ct_tiendo_gd&id=<?= $gd['id'] ?>" class="text-gray-400 hover:text-indigo-600 transition" title="Xem tiến độ">
                                        <i class="fas fa-tasks"></i>
                                    </a>
                                </div>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            <i class="fas fa-folder-open text-4xl mb-2"></i>
                            <p class="font-medium">Chưa có giao dịch nào</p>
                            <p class="text-sm">Bạn chưa thực hiện giao dịch nào trên hệ thống.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Hàm lọc (giữ nguyên)
    function apdungloc() {
        const statusEl = document.getElementById('trangthai');
        const filters = {};
        if (statusEl && statusEl.value.trim() !== "") {
            filters.trangthai = statusEl.value.trim();
        }
        const boloc = encodeURIComponent(JSON.stringify(filters));
        
        const searchParams = new URLSearchParams(window.location.search);
        const search = searchParams.get('search') || '';
        
        let newUrl = "trangchu.php?page=gd_canhan";
        if (search) {
            newUrl += "&search=" + encodeURIComponent(search);
        }
        if (Object.keys(filters).length > 0) {
            newUrl += "&boloc=" + boloc;
        }
        window.location.href = newUrl;
    }
    document.getElementById("btnloc").addEventListener("click", apdungloc);

    // Mảng map trạng thái (dùng cho JS)
    const statusMap = {
        'choxuly': { text: 'Chờ xử lý', class: 'bg-yellow-100 text-yellow-800' },
        'dangxuly': { text: 'Đang xử lý', class: 'bg-blue-100 text-blue-800' },
        'hoantat': { text: 'Hoàn tất', class: 'bg-green-100 text-green-800' },
        'dahuy': { text: 'Đã hủy', class: 'bg-red-100 text-red-800' }
    };

    // Hàm mới: Cập nhật trạng thái
    function capNhatTrangThai(giaoDichId) {
        const selectEl = document.getElementById('select-status-' + giaoDichId);
        const newStatus = selectEl.value;
        const buttonEl = selectEl.nextElementSibling; // Nút "Lưu"

        if (!newStatus) return;

        // Tạm khóa nút
        buttonEl.disabled = true;
        buttonEl.textContent = 'Đang...';

        fetch('../api/cap_nhat_trang_thai.php', { // <-- ĐÂY LÀ TỆP API MỚI
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id_giao_dich: giaoDichId,
                trang_thai_moi: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cập nhật giao diện không cần tải lại trang
                const badgeEl = document.getElementById('status-badge-' + giaoDichId);
                const statusInfo = statusMap[newStatus];
                
                if (badgeEl && statusInfo) {
                    badgeEl.textContent = statusInfo.text;
                    badgeEl.className = 'px-2.5 py-0.5 text-xs font-medium rounded-full ' + statusInfo.class;
                }
                
                // Khóa select và button nếu trạng thái là 'hoantat' hoặc 'dahuy'
                if (newStatus === 'hoantat' || newStatus === 'dahuy') {
                    selectEl.disabled = true;
                    buttonEl.disabled = true;
                    buttonEl.classList.add('opacity-50', 'cursor-not-allowed');
                }
                
                alert('Cập nhật trạng thái thành công!');
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi kết nối.');
        })
        .finally(() => {
            // Mở lại nút
            if (newStatus !== 'hoantat' && newStatus !== 'dahuy') {
                buttonEl.disabled = false;
            }
            buttonEl.textContent = 'Lưu';
        });
    }
</script>