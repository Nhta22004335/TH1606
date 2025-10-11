<?php
// PHẦN LOGIC PHP CỦA BẠN - GIỮ NGUYÊN HOÀN TOÀN
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
$filters = [];
if (isset($_GET['boloc'])) {
    $filters = json_decode($_GET['boloc'], true) ?? [];
}

$sql = "
    SELECT gd.id, nd.ten_dang_nhap, bds.tieu_de, gd.loai, gd.ngay_giao_dich, gd.trang_thai
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

// THÊM MỚI: Mảng ánh xạ trạng thái để code gọn gàng và dễ bảo trì
$status_map = [
    'choxuly'  => ['text' => 'Chờ xử lý', 'class' => 'bg-yellow-100 text-yellow-800'],
    'dangxuly' => ['text' => 'Đang xử lý', 'class' => 'bg-blue-100 text-blue-800'],
    'hoantat'  => ['text' => 'Hoàn tất',   'class' => 'bg-green-100 text-green-800'],
    'dahuy'    => ['text' => 'Đã hủy',     'class' => 'bg-red-100 text-red-800']
];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <header class="pb-4 border-b">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Giao Dịch Của Tôi</h1>
        <p class="text-gray-500 mt-1">Quản lý các giao dịch bạn đã thực hiện hoặc đang theo dõi.</p>
    </header>

    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-3">
            <input type="hidden" name="page" value="gd_canhan">
            
            <div class="relative flex-grow">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                <input type="text" name="search" placeholder="Tìm người dùng, bất động sản..." 
                       value="<?= htmlspecialchars($search) ?>"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
            </div>

            <select name="trangthai" id="trangthai" class="border-gray-300 rounded-md text-sm w-full md:w-48 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <option value="">Tất cả trạng thái</option>
                <option value="choxuly" <?= (($filters['trangthai'] ?? '') == 'choxuly') ? 'selected' : '' ?>>Chờ xử lý</option>
                <option value="dangxuly" <?= (($filters['trangthai'] ?? '') == 'dangxuly') ? 'selected' : '' ?>>Đang xử lý</option>
                <option value="hoantat" <?= (($filters['trangthai'] ?? '') == 'hoantat') ? 'selected' : '' ?>>Hoàn tất</option>
                <option value="dahuy" <?= (($filters['trangthai'] ?? '') == 'dahuy') ? 'selected' : '' ?>>Đã hủy</option>
            </select>

            <div class="flex gap-2">
                <button id="btnloc" type="button" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition">
                    <i class="fas fa-filter"></i> Lọc
                </button>
                <a href="?page=gd_canhan" class="flex-1 md:flex-none flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-md hover:bg-gray-300 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left font-medium text-gray-500 uppercase tracking-wider">Người Mua</th>
                    <th class="p-3 text-left font-medium text-gray-500 uppercase tracking-wider">Bất Động Sản</th>
                    <th class="p-3 text-left font-medium text-gray-500 uppercase tracking-wider">Ngày Giao Dịch</th>
                    <th class="p-3 text-left font-medium text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                    <th class="p-3 text-center font-medium text-gray-500 uppercase tracking-wider">Hành Động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (count($giaodich) > 0): ?>
                    <?php foreach($giaodich as $gd): ?>
                        <?php
                            $match = true;
                            if (isset($filters['trangthai']) && $filters['trangthai'] !== '' && $filters['trangthai'] !== $gd['trang_thai']) {
                                $match = false;
                            }
                        ?>
                        <?php if ($match): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-900"><?= htmlspecialchars($gd['ten_dang_nhap'] ?? 'N/A') ?></td>
                            <td class="p-3 text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($gd['tieu_de'] ?? 'N/A') ?>">
                                <?= htmlspecialchars($gd['tieu_de'] ?? 'N/A') ?>
                            </td>
                            <td class="p-3 text-gray-600 whitespace-nowrap"><?= date("d/m/Y H:i", strtotime($gd['ngay_giao_dich'])) ?></td>
                            <td class="p-3">
                                <?php $status_info = $status_map[$gd['trang_thai']] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-100 text-gray-800']; ?>
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full <?= $status_info['class'] ?>">
                                    <?= $status_info['text'] ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="flex justify-center items-center gap-4">
                                    <a href="../../views/quan_ly/trangchu.php?page=../moi_gioi/ct_tiendo_gd&id=<?= $gd['id'] ?>" class="text-gray-400 hover:text-indigo-600 transition" title="Xem tiến độ">
                                        <i class="fas fa-tasks"></i>
                                    </a>
                                    <button onclick="xoaGiaoDich('<?= $gd['id'] ?>')" class="text-gray-400 hover:text-red-600 transition" title="Xóa giao dịch">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
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
    // Giữ nguyên Javascript của bạn và thêm hàm xóa
    function apdungloc() {
        const statusEl = document.getElementById('trangthai');
        const filters = {};
        if (statusEl && statusEl.value.trim() !== "") {
            filters.trangthai = statusEl.value.trim();
        }
        const boloc = encodeURIComponent(JSON.stringify(filters));
        
        // Giữ lại tham số tìm kiếm
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

    function xoaGiaoDich(id) {
        if (confirm('Bạn có chắc chắn muốn xóa giao dịch này không?')) {
            // Logic xóa của bạn có thể đặt ở đây (ví dụ: submit form ẩn hoặc fetch API)
            console.log('Xóa giao dịch ID:', id);
        }
    }
</script>