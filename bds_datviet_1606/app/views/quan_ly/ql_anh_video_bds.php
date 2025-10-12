<?php
 // ===== PHẦN LOGIC PHP CỦA BẠN - GIỮ NGUYÊN HOÀN TOÀN =====
 $media_list = [
     ["id" => 1, "id_bai_dang" => 1, "loai" => "image", "ten_file" => "cay_xanh.jpg", "duong_dan" => "https://picsum.photos/300?random=1", "kich_thuoc" => 245678, "trang_thai" => "Bình thường"],
     ["id" => 2, "id_bai_dang" => 1, "loai" => "image", "ten_file" => "vuon_rau.png", "duong_dan" => "https://picsum.photos/300?random=2", "kich_thuoc" => 187532, "trang_thai" => "Cảnh báo nhẹ"],
     ["id" => 3, "id_bai_dang" => 2, "loai" => "video", "ten_file" => "gioi_thieu_trang_trai.mp4", "duong_dan" => "https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4", "kich_thuoc" => 10485760, "trang_thai" => "Cảnh báo trung bình"],
 ];

 function getStatusInfo($status) {
     $map = [
         "Bình thường" => ['text' => "Bình thường", 'classes' => "bg-green-100 text-green-800"],
         "Cảnh báo nhẹ" => ['text' => "Cảnh báo nhẹ", 'classes' => "bg-yellow-100 text-yellow-800"],
         "Cảnh báo trung bình" => ['text' => "Cảnh báo TB", 'classes' => "bg-orange-100 text-orange-800"],
         "Cảnh báo nghiêm trọng" => ['text' => "Nghiêm trọng", 'classes' => "bg-red-100 text-red-800"]
     ];
     return $map[$status] ?? ['text' => $status, 'classes' => "bg-gray-100 text-gray-800"];
 }
 function formatFileSize($bytes) {
     if ($bytes >= 1048576) { return number_format($bytes / 1048576, 2) . ' MB'; }
     elseif ($bytes >= 1024) { return number_format($bytes / 1024, 2) . ' KB'; }
     return $bytes . ' bytes';
 }
 function buildQueryString(array $params): string {
     return http_build_query(array_filter($params));
 }

 $filter = $_GET['filter'] ?? 'all';
 $view = $_GET['view'] ?? 'grid';
 $searchTerm = $_GET['searchTerm'] ?? '';
 
 $filtered_media = array_filter($media_list, function($item) use ($filter, $searchTerm) {
     $typeMatch = ($filter === 'all' || $item['loai'] === $filter);
     $searchMatch = true;
     if (!empty($searchTerm)) {
         $searchMatch = stripos($item['ten_file'], $searchTerm) !== false || 
                        stripos($item['trang_thai'], $searchTerm) !== false;
     }
     return $typeMatch && $searchMatch;
 });

 $baseParams = ['searchTerm' => $searchTerm];
 $url_all = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParams, ['filter' => 'all', 'view' => $view]));
 $url_image = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParams, ['filter' => 'image', 'view' => $view]));
 $url_video = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParams, ['filter' => 'video', 'view' => $view]));

 $baseParamsForView = ['searchTerm' => $searchTerm, 'filter' => $filter];
 $url_view_grid = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParamsForView, ['view' => 'grid']));
 $url_view_list = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParamsForView, ['view' => 'list']));
?>
<!DOCTYPE html>
<html lang="vi" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Media</title>
</head>
<body class="text-gray-800">

<div class="space-y-6">

    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản lý Media</h1>
            <p class="text-sm text-gray-500 mt-1">Duyệt, tìm kiếm và quản lý tất cả các tệp ảnh và video.</p>
        </div>
        <button class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition">
            <i class="fa-solid fa-cloud-arrow-up"></i> Tải lên Media
        </button>
    </header>

    <div class="bg-white p-3 rounded-lg shadow-sm border">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="bg-gray-100 p-1 rounded-lg flex text-sm font-medium w-full md:w-auto">
                <a href="<?= $url_all ?>" class="flex-1 text-center px-4 py-1.5 rounded-md transition <?= $filter === 'all' ? 'bg-white shadow text-indigo-600' : 'text-gray-500 hover:bg-gray-200' ?>">Tất cả</a>
                <a href="<?= $url_image ?>" class="flex-1 text-center px-4 py-1.5 rounded-md transition <?= $filter === 'image' ? 'bg-white shadow text-indigo-600' : 'text-gray-500 hover:bg-gray-200' ?>">Ảnh</a>
                <a href="<?= $url_video ?>" class="flex-1 text-center px-4 py-1.5 rounded-md transition <?= $filter === 'video' ? 'bg-white shadow text-indigo-600' : 'text-gray-500 hover:bg-gray-200' ?>">Video</a>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="trangchu.php" class="relative flex-grow">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <input type="search" name="searchTerm" value="<?= htmlspecialchars($searchTerm) ?>" class="w-full bg-white border border-gray-300 text-sm outline-none rounded-lg pl-10 p-2.5 focus:ring-2 focus:ring-indigo-500" placeholder="Tìm kiếm...">
                    <input type="hidden" name="page" value="ql_anh_video_bds">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                    <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
                </form>
                <div class="bg-gray-100 p-1 rounded-lg flex text-sm flex-shrink-0">
                    <a href="<?= $url_view_grid ?>" class="px-3 py-1.5 rounded-md transition <?= $view === 'grid' ? 'bg-white shadow text-indigo-600' : 'text-gray-500' ?>" title="Xem dạng lưới"><i class="fa-solid fa-grip"></i></a>
                    <a href="<?= $url_view_list ?>" class="px-3 py-1.5 rounded-md transition <?= $view === 'list' ? 'bg-white shadow text-indigo-600' : 'text-gray-500' ?>" title="Xem dạng bảng"><i class="fa-solid fa-list"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div>
        <?php if ($view === 'grid'): ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
                <?php foreach ($filtered_media as $item): ?>
                    <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition cursor-pointer flex flex-col">
                        <div class="relative">
                            <?php if ($item['loai'] === 'image'): ?>
                                <img src="<?= htmlspecialchars($item['duong_dan']) ?>" class="w-full h-32 object-cover rounded-t-lg">
                            <?php else: ?>
                                <div class="w-full h-32 bg-gray-800 rounded-t-lg flex items-center justify-center"><i class="fa-solid fa-play text-white text-2xl"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 flex-1 flex flex-col">
                            <p class="font-semibold text-sm truncate" title="<?= htmlspecialchars($item['ten_file']) ?>"><?= htmlspecialchars($item['ten_file']) ?></p>
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                               <?php $statusInfo = getStatusInfo($item['trang_thai']); ?>
                               <span class="px-2 py-0.5 font-medium rounded-full <?= $statusInfo['classes'] ?>"><?= $statusInfo['text'] ?></span>
                               <span><?= formatFileSize($item['kich_thuoc']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left font-medium text-gray-500 uppercase tracking-wider">Tên File</th>
                            <th class="p-3 text-left font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="p-3 text-left font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Kích thước</th>
                            <th class="p-3 text-center font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($filtered_media as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-10 flex-shrink-0 bg-gray-200 rounded-md flex items-center justify-center">
                                        <?php if ($item['loai'] === 'image'): ?>
                                            <img src="<?= htmlspecialchars($item['duong_dan']) ?>" class="w-full h-full object-cover rounded-md">
                                        <?php else: ?>
                                            <i class="fa-solid fa-film text-gray-500"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="truncate">
                                        <p class="font-semibold text-gray-800 truncate" title="<?= htmlspecialchars($item['ten_file']) ?>"><?= htmlspecialchars($item['ten_file']) ?></p>
                                        <p class="text-xs text-gray-500"><?= ucfirst($item['loai']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                <?php $statusInfo = getStatusInfo($item['trang_thai']); ?>
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full <?= $statusInfo['classes'] ?>"><?= $statusInfo['text'] ?></span>
                            </td>
                            <td class="p-3 text-gray-600 hidden md:table-cell"><?= formatFileSize($item['kich_thuoc']) ?></td>
                            <td class="p-3">
                                <div class="flex justify-center items-center gap-4">
                                    <button class="text-gray-400 hover:text-indigo-600 transition" title="Chỉnh sửa"><i class="fas fa-pencil-alt"></i></button>
                                    <button class="text-gray-400 hover:text-red-600 transition" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (empty($filtered_media)): ?>
            <div class="text-center py-16 text-gray-500">
                <i class="fa-solid fa-box-open text-4xl mb-4"></i>
                <p class="font-semibold">Không tìm thấy media nào</p>
                <p class="text-sm">Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>