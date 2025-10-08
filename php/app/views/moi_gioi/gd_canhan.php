<?php
// Bắt đầu phiên SESSION nếu chưa bắt đầu (Cần thiết để lấy $_SESSION['id_nguoi_dung'])
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../../../config/database.php";

// 1. LẤY ID NGƯỜI DÙNG ĐANG ĐĂNG NHẬP
$id_nguoi_dung_hien_tai = $_SESSION['id_nguoi_dung'] ?? null; 

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!$id_nguoi_dung_hien_tai) {
    // Nếu chưa đăng nhập, chuyển hướng hoặc hiển thị lỗi
    header("Location: ../auth/dangnhap.php"); 
    exit;
}

$pdo = ketnoicsdl();

$search = $_GET['search'] ?? '';
$filters = [];

if (isset($_GET['boloc'])) {
    // Nên kiểm tra và xử lý lỗi JSON decode
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

// --- THÊM ĐIỀU KIỆN LỌC CHÍNH: id_nguoi_ban phải là người dùng hiện tại ---
$where[] = "gd.id_nguoi_ban = :id_nguoi_ban_hien_tai";
$params[':id_nguoi_ban_hien_tai'] = $id_nguoi_dung_hien_tai;

// Thêm điều kiện tìm kiếm
if (!empty($search)) {
    $where[] = "(nd.ten_dang_nhap ILIKE :search OR bds.tieu_de ILIKE :search)";
    $params[':search'] = "%$search%";
}

// Xây dựng mệnh đề WHERE
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY gd.ngay_giao_dich DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $giaodich = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Xử lý lỗi CSDL (ví dụ: thiếu cột id_nguoi_ban)
    error_log("Lỗi CSDL: " . $e->getMessage());
    $giaodich = [];
}
?>

<!-- Giao diện -->
<div class="p-6">

    <h1 class="flex text-2xl font-bold mb-4 text-gray-600">
        <img src="../../../public/assets/anhht/0/invoice.gif" class="w-10 h-10 mr-2"> Quản lý Giao dịch
    </h1>

<!-- Bộ lọc -->
<div class="flex items-center gap-3 mb-4">
    <label for="trangthai" class="font-medium text-gray-700">Trạng thái:</label>
    <select name="trangthai" id="trangthai" 
        class="border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="" <?= (($filters['trangthai'] ?? '') == '' ? 'selected' : '') ?>>-- Tất cả --</option>
        <option value="choxuly" <?= (($filters['trangthai'] ?? '') == 'choxuly') ? 'selected' : '' ?>>Chờ xử lý</option>
        <option value="dangxuly" <?= (($filters['trangthai'] ?? '') == 'dangxuly') ? 'selected' : '' ?>>Đang xử lý</option>
        <option value="hoantat" <?= (($filters['trangthai'] ?? '') == 'hoantat') ? 'selected' : '' ?>>Hoàn tất</option>
        <option value="dahuy" <?= (($filters['trangthai'] ?? '') == 'dahuy') ? 'selected' : '' ?>>Đã hủy</option>
    </select>
    <button id="btnloc" 
        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        <i class="fa-solid fa-filter"></i> Áp dụng
    </button>
</div>


    <!-- Bảng giao dịch -->
    <table class="w-full border-collapse border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Người dùng</th>
                <th class="border p-2">Bất động sản</th>
                <th class="border p-2">Loại</th>
                <th class="border p-2">Ngày giao dịch</th>
                <th class="border p-2">Trạng thái</th>
                <th class="border p-2">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($giaodich as $gd): ?>
                <?php
                    $match = true;
                    if (isset($filters['trangthai']) && $filters['trangthai'] !== $gd['trang_thai']) $match = false;
                ?>
                <?php if ($match): ?>
                    <tr class="text-center">
                        <td class="border p-2"><?= htmlspecialchars($gd['ten_dang_nhap'] ?? 'N/A') ?></td>
                        <td class="border p-2"><?= htmlspecialchars($gd['tieu_de'] ?? 'N/A') ?></td>
                        <td class="border p-2 capitalize"><?= $gd['loai'] ?></td>
                        <td class="border p-2"><?= date("d/m/Y H:i", strtotime($gd['ngay_giao_dich'])) ?></td>
                        <td class="border p-2">
                            <?php if($gd['trang_thai']=="choxuly"): ?>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded">Chờ xử lý</span>
                            <?php elseif($gd['trang_thai']=="dangxuly"): ?>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">Đang xử lý</span>
                            <?php elseif($gd['trang_thai']=="hoantat"): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Hoàn tất</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded">Đã hủy</span>
                            <?php endif; ?>
                        </td>
                        <td class="border p-2 flex justify-center gap-2">

                            <!-- Nút Đánh dấu -->
                            <a href="../../views/quan_ly/trangchu.php?page=../moi_gioi/ct_tiendo_gd&id=<?= $gd['id'] ?>" 
                                class="flex items-center gap-1 bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg">
                                <i class="fa-solid fa-bookmark"></i> Xem tiến độ
                            </a>

                            <!-- Nút Xóa -->
                            <button onclick="xoaGiaoDich('<?= $gd['id'] ?>')" 
                                class="flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg">
                                <i class="fa-solid fa-trash"></i> Xóa
                            </button>
                        </td>

                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<script>
    function apdungloc() {
        const keys = ["trangthai"];
        let filters = {};

        keys.forEach(key => {
            const el = document.getElementById('trangthai');
            if (el && el.value.trim() !== "") {
                filters[key] = el.value.trim();
            }
        });

        const boloc = encodeURIComponent(JSON.stringify(filters));
        window.location.href = "trangchu.php?page=ql_thanhtoan&boloc=" + boloc;
    }
    document.getElementById("btnloc").addEventListener("click", () => apdungloc());

    function xemChiTiet(id) {
        window.location.href = "trangchu.php?page=ct_giaodich&id=" + id;
    }
</script>
