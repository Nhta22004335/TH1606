<?php
// Nếu chưa đăng nhập thì quay lại trang login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_nguoi_dung'])) {
    header("Location: ../auth/dangnhap.html");
    exit;
}

require_once '../../../config/database.php';
$pdo = ketnoicsdl();

$id_khach = $_SESSION['id_nguoi_dung'];

// --- CẬP NHẬT TRUY VẤN: Thêm cột `anh_tin` và `luot_xem` để giao diện đẹp hơn ---
$stmt = $pdo->prepare("SELECT id, tieu_de, mo_ta, chuyen_muc, trang_thai, ngay_dang, anh_tin, luot_xem
                       FROM tin_tuc
                       WHERE id_khach_hang = ? 
                       ORDER BY ngay_dang DESC");
$stmt->execute([$id_khach]);
$tin_dang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- HÀM HELPER ---
// Hàm rút gọn chuỗi theo số từ
function truncate_string($string, $word_limit) {
    $words = explode(' ', $string);
    if (count($words) > $word_limit) {
        return implode(' ', array_slice($words, 0, $word_limit)) . '...';
    }
    return $string;
}

// Hàm tạo badge trạng thái
function getStatusBadge($status) {
    $map = [
        'choduyet' => ['text' => 'Chờ duyệt', 'class' => 'bg-orange-100 text-orange-800'],
        'dangban'  => ['text' => 'Đang hiển thị', 'class' => 'bg-green-100 text-green-800'],
        'daban'    => ['text' => 'Đã bán', 'class' => 'bg-slate-100 text-slate-800'],
        'dathue'   => ['text' => 'Đã thuê', 'class' => 'bg-slate-100 text-slate-800'],
    ];
    $info = $map[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-800'];
    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$info['class']}'>{$info['text']}</span>";
}

// --- TÍNH TOÁN THỐNG KÊ ---
$stats = [
    'active'  => count(array_filter($tin_dang, fn($p) => $p['trang_thai'] === 'dangban')),
    'pending' => count(array_filter($tin_dang, fn($p) => $p['trang_thai'] === 'choduyet')),
    'total_views' => array_sum(array_column($tin_dang, 'luot_xem')),
];
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tin đăng</title>
</head>
<body class="h-full">

    <header>
        <div class="sm:flex sm:items-center sm:justify-between mb-6 pb-4 border-b">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Bài đăng của tôi</h1>
                <p class="mt-2 text-sm text-slate-600">Quản lý, chỉnh sửa và theo dõi hiệu suất các tin bạn đã đăng.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="trangchu.php?page=dang_sp" class="inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fa-solid fa-plus"></i> Đăng tin mới
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Tin đang hiển thị</dt><dd class="mt-1 text-3xl font-semibold text-green-600"><?= $stats['active'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Tin chờ duyệt</dt><dd class="mt-1 text-3xl font-semibold text-orange-500"><?= $stats['pending'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Tổng lượt xem</dt><dd class="mt-1 text-3xl font-semibold text-slate-900"><?= number_format($stats['total_views']) ?></dd></dl></div></div>
        </div>
    </header>

    <main class="mt-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-4 border-b border-slate-200">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <input type="text" placeholder="Tìm theo tiêu đề..." class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <select class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Tất cả trạng thái</option>
                        <option value="dangban">Đang hiển thị</option>
                        <option value="choduyet">Chờ duyệt</option>
                    </select>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Tiêu đề</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Chuyên mục</th>
                            <th scope="col" class="px-6 py-3 text-center text-sm font-semibold text-slate-600">Trạng thái</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Ngày đăng</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Hành động</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($tin_dang)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="text-center py-16 px-4">
                                        <i class="fa-solid fa-file-circle-question fa-3x text-slate-400 mb-4"></i>
                                        <h3 class="text-lg font-semibold text-slate-800">Bạn chưa có bài đăng nào</h3>
                                        <p class="text-slate-500 mt-1">Hãy bắt đầu tạo bài đăng đầu tiên của bạn.</p>
                                        <a href="trangchu.php?page=dang_sp" class="mt-4 inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                            <i class="fa-solid fa-plus"></i> Đăng tin ngay
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tin_dang as $tin): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <img src="../../../storage/pictures/anhtin/<?= htmlspecialchars($tin['anh_tin'] ?: 'chuacapnhat.png') ?>" class="w-24 h-16 rounded-md object-cover flex-shrink-0 hidden sm:block">
                                            <div>
                                                <p class="font-semibold text-slate-800 text-sm" title="<?= htmlspecialchars($tin['tieu_de']) ?>"><?= htmlspecialchars(truncate_string($tin['tieu_de'], 10)) ?></p>
                                                <p class="text-xs text-slate-500" title="<?= htmlspecialchars($tin['mo_ta']) ?>"><?= htmlspecialchars(truncate_string($tin['mo_ta'], 15)) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= htmlspecialchars($tin['chuyen_muc']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center"><?= getStatusBadge($tin['trang_thai']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500"><?= date('d/m/Y H:i', strtotime($tin['ngay_dang'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <div class="flex items-center justify-center gap-4 text-slate-500">
                                            <a href="trangchu.php?page=../../models/cn_tin_mg&id=<?= $tin['id'] ?>" class="hover:text-indigo-600" title="Chỉnh sửa">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="trangchu.php?page=../../models/xoa_tin_mg&id=<?= $tin['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa tin này?')" class="hover:text-red-600" title="Xóa">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>