<?php
// Dữ liệu demo cho trang quản lý bài đăng
$baidang = [
    [
        'id' => 1,
        'tieu_de' => 'Bán gấp căn hộ 2PN The Gold View, view sông, full nội thất',
        'anh_bia' => 'https://picsum.photos/300/200?random=1',
        'gia' => '3.5 tỷ',
        'dien_tich' => '80m²',
        'dia_chi' => 'Quận 4, TP.HCM',
        'ten_moigioi' => 'Nguyễn Văn An',
        'avatar_moigioi' => 'https://i.pravatar.cc/100?u=an',
        'ngay_dang' => '2025-10-10 08:30:00',
        'trang_thai' => 'cho_duyet', // Trạng thái chờ duyệt
        'luot_xem' => 0,
    ],
    [
        'id' => 2,
        'tieu_de' => 'Cho thuê nhà phố mặt tiền kinh doanh sầm uất tại Quận 1',
        'anh_bia' => 'https://picsum.photos/300/200?random=2',
        'gia' => '50 triệu/tháng',
        'dien_tich' => '120m²',
        'dia_chi' => 'Quận 1, TP.HCM',
        'ten_moigioi' => 'Trần Thị Bình',
        'avatar_moigioi' => 'https://i.pravatar.cc/100?u=binh',
        'ngay_dang' => '2025-10-09 15:00:00',
        'trang_thai' => 'da_duyet', // Trạng thái đã duyệt
        'luot_xem' => 1280,
    ],
    [
        'id' => 3,
        'tieu_de' => 'Cần bán đất nền dự án A, vị trí đắc địa gần hồ',
        'anh_bia' => 'https://picsum.photos/300/200?random=3',
        'gia' => '5 tỷ',
        'dien_tich' => '100m²',
        'dia_chi' => 'TP. Thủ Đức, TP.HCM',
        'ten_moigioi' => 'Lê Minh Chung',
        'avatar_moigioi' => 'https://i.pravatar.cc/100?u=chung',
        'ngay_dang' => '2025-10-08 11:20:00',
        'trang_thai' => 'da_duyet',
        'luot_xem' => 950,
    ],
    [
        'id' => 4,
        'tieu_de' => 'Biệt thự sân vườn đẳng cấp, có hồ bơi riêng',
        'anh_bia' => 'https://picsum.photos/300/200?random=4',
        'gia' => '25 tỷ',
        'dien_tich' => '500m²',
        'dia_chi' => 'Quận 7, TP.HCM',
        'ten_moigioi' => 'Nguyễn Văn An',
        'avatar_moigioi' => 'https://i.pravatar.cc/100?u=an',
        'ngay_dang' => '2025-10-07 18:00:00',
        'trang_thai' => 'het_han', // Trạng thái đã hết hạn
        'luot_xem' => 3450,
    ],
    [
        'id' => 5,
        'tieu_de' => 'Căn hộ dịch vụ mini cho thuê, gần khu công nghệ cao',
        'anh_bia' => 'https://picsum.photos/300/200?random=5',
        'gia' => '8 triệu/tháng',
        'dien_tich' => '35m²',
        'dia_chi' => 'Quận 9, TP.HCM',
        'ten_moigioi' => 'Trần Thị Bình',
        'avatar_moigioi' => 'https://i.pravatar.cc/100?u=binh',
        'ngay_dang' => '2025-10-10 09:00:00',
        'trang_thai' => 'cho_duyet', // Trạng thái chờ duyệt
        'luot_xem' => 5,
    ],
];

// Hàm helper để tạo badge trạng thái
function getStatusBadge($status) {
    $map = [
        'cho_duyet' => ['text' => 'Chờ duyệt', 'class' => 'bg-orange-100 text-orange-800'],
        'da_duyet'  => ['text' => 'Đang hiển thị', 'class' => 'bg-green-100 text-green-800'],
        'het_han'   => ['text' => 'Hết hạn', 'class' => 'bg-red-100 text-red-800'],
        'da_ban'    => ['text' => 'Đã bán', 'class' => 'bg-slate-100 text-slate-800'],
    ];
    $info = $map[$status] ?? ['text' => 'Không rõ', 'class' => 'bg-gray-100 text-gray-800'];
    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$info['class']}'>{$info['text']}</span>";
}

// Tính toán các chỉ số thống kê
$stats = [
    'pending' => count(array_filter($baidang, fn($p) => $p['trang_thai'] === 'cho_duyet')),
    'active'  => count(array_filter($baidang, fn($p) => $p['trang_thai'] === 'da_duyet')),
    'expired' => count(array_filter($baidang, fn($p) => $p['trang_thai'] === 'het_han')),
    'total'   => count($baidang),
];

/**
 * Hàm rút gọn một chuỗi theo số lượng từ.
 * @param string $string Chuỗi cần rút gọn.
 * @param int $word_limit Giới hạn số từ.
 * @return string Chuỗi đã rút gọn.
 */
function truncate_string($string, $word_limit) {
    $words = explode(' ', $string);
    if (count($words) > $word_limit) {
        return implode(' ', array_slice($words, 0, $word_limit)) . '...';
    }
    return $string;
}

?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bài đăng</title>
    <style>
        [x-cloak] { display: none !important; }
        summary::marker { content: ''; }
    </style>
</head>
<body class="h-full">


    
    <header>
        <div class="sm:flex sm:items-center sm:justify-between mb-6 pb-4 border-b">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Quản lý Bài đăng</h1>
                <p class="mt-2 text-sm text-slate-600">Tổng quan và kiểm duyệt tất cả bài đăng của môi giới.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="#" class="inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fa-solid fa-plus"></i> Tạo bài đăng mới
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Chờ duyệt</dt><dd class="mt-1 text-3xl font-semibold text-orange-500"><?= $stats['pending'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Đang hiển thị</dt><dd class="mt-1 text-3xl font-semibold text-green-600"><?= $stats['active'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Hết hạn / Đã bán</dt><dd class="mt-1 text-3xl font-semibold text-red-600"><?= $stats['expired'] ?></dd></dl></div></div>
            <div class="bg-white overflow-hidden shadow rounded-lg"><div class="p-5"><dl><dt class="text-sm font-medium text-slate-500 truncate">Tổng số bài</dt><dd class="mt-1 text-3xl font-semibold text-slate-900"><?= $stats['total'] ?></dd></dl></div></div>
        </div>
    </header>

    <main class="mt-8">
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <input type="text" placeholder="Tìm theo tiêu đề, địa chỉ..." class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <select class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="cho_duyet">Chờ duyệt</option>
                    <option value="da_duyet">Đang hiển thị</option>
                    <option value="het_han">Hết hạn</option>
                </select>
                <input type="text" placeholder="Tên môi giới..." class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <button class="w-full sm:w-auto bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-indigo-700">Lọc</button>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="p-4 text-left"><input type="checkbox" class="h-4 w-4 text-indigo-600 border-slate-300 rounded"></th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Bài đăng</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Môi giới</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-slate-600">Ngày đăng</th>
                            <th scope="col" class="px-6 py-3 text-center text-sm font-semibold text-slate-600">Lượt xem</th>
                            <th scope="col" class="px-6 py-3 text-center text-sm font-semibold text-slate-600">Trạng thái</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Hành động</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($baidang as $post): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4"><input type="checkbox" class="h-4 w-4 text-indigo-600 border-slate-300 rounded"></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img src="<?= htmlspecialchars($post['anh_bia']) ?>" class="w-24 h-16 rounded-md object-cover flex-shrink-0">
                                        <div>
                                            <p class="font-semibold text-slate-800 text-sm" title="<?= htmlspecialchars($post['tieu_de']) ?>">
                                                <?= htmlspecialchars(truncate_string($post['tieu_de'], 8)) ?>
                                            </p>
                                            <p class="text-xs text-slate-500"><?= htmlspecialchars($post['gia']) ?> &bull; <?= htmlspecialchars($post['dien_tich']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <img src="<?= htmlspecialchars($post['avatar_moigioi']) ?>" class="w-8 h-8 rounded-full">
                                        <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($post['ten_moigioi']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500"><?= date('d/m/Y H:i', strtotime($post['ngay_dang'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-slate-700"><?= number_format($post['luot_xem']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center"><?= getStatusBadge($post['trang_thai']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <details class="relative inline-block text-left">
                                        <summary class="list-none cursor-pointer p-2 text-slate-500 hover:text-slate-800"><i class="fa-solid fa-ellipsis-vertical"></i></summary>
                                        <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                            <div class="py-1" role="menu">
                                                <?php if ($post['trang_thai'] === 'cho_duyet'): ?>
                                                    <a href="#" class="block px-4 py-2 text-sm text-green-700 hover:bg-slate-100" role="menuitem">Duyệt bài</a>
                                                    <a href="#" class="block px-4 py-2 text-sm text-red-700 hover:bg-slate-100" role="menuitem">Từ chối</a>
                                                <?php else: ?>
                                                     <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" role="menuitem">Gỡ bài</a>
                                                <?php endif; ?>
                                                <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100" role="menuitem">Xem chi tiết</a>
                                            </div>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 flex items-center justify-between border-t border-slate-200">
                <span class="text-sm text-slate-600">Hiển thị <strong>1-<?= count($baidang) ?></strong> trên <strong><?= count($baidang) ?></strong> kết quả</span>
                </div>
        </div>
    </main>


</body>
</html>