<?php
  // ===== PHẦN LOGIC PHP - ĐÃ TỐI ƯU =====
  $media_list = [
      ["id" => 1, "id_bai_dang" => 1, "loai" => "image", "ten_file" => "cay_xanh.jpg", "duong_dan" => "https://picsum.photos/300?random=1", "kich_thuoc" => 245678, "trang_thai" => "Bình thường"],
      ["id" => 2, "id_bai_dang" => 1, "loai" => "image", "ten_file" => "vuon_rau.png", "duong_dan" => "https://picsum.photos/300?random=2", "kich_thuoc" => 187532, "trang_thai" => "Cảnh báo nhẹ"],
      ["id" => 3, "id_bai_dang" => 2, "loai" => "video", "ten_file" => "gioi_thieu_trang_trai.mp4", "duong_dan" => "https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4", "kich_thuoc" => 10485760, "trang_thai" => "Cảnh báo trung bình"],
      ["id" => 4, "id_bai_dang" => 2, "loai" => "image", "ten_file" => "mo_hinh_trai.jpg", "duong_dan" => "https://picsum.photos/300?random=3", "kich_thuoc" => 356781, "trang_thai" => "Cảnh báo nghiêm trọng"],
      ["id" => 5, "id_bai_dang" => 3, "loai" => "video", "ten_file" => "huong_dan_cham_soc.mp4", "duong_dan" => "https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4", "kich_thuoc" => 8054321, "trang_thai" => "Bình thường"],
      ["id" => 6, "id_bai_dang" => 3, "loai" => "image", "ten_file" => "hat_giong.png", "duong_dan" => "https://picsum.photos/300?random=4", "kich_thuoc" => 123456, "trang_thai" => "Cảnh báo nhẹ"],
      ["id" => 7, "id_bai_dang" => 3, "loai" => "image", "ten_file" => "anh_cuoi_cung.jpeg", "duong_dan" => "https://picsum.photos/300?random=5", "kich_thuoc" => 123456, "trang_thai" => "Bình thường"]
  ];

  // ----- CÁC HÀM HỖ TRỢ -----
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

  // ----- LẤY TRẠNG THÁI TỪ URL (GET PARAMS) -----
  $filter = $_GET['filter'] ?? 'all';
  $view = $_GET['view'] ?? 'grid';
  $searchTerm = $_GET['searchTerm'] ?? '';

  // ----- LOGIC LỌC DỮ LIỆU -----
  $filtered_media = array_filter($media_list, function($item) use ($filter, $searchTerm) {
      // Lọc theo loại (image/video/all)
      $typeMatch = ($filter === 'all' || $item['loai'] === $filter);

      // Lọc theo từ khóa tìm kiếm
      $searchMatch = true;
      if (!empty($searchTerm)) {
          $searchMatch = stripos($item['ten_file'], $searchTerm) !== false || 
                        stripos($item['trang_thai'], $searchTerm) !== false;
      }
      
      return $typeMatch && $searchMatch;
  });

  // ----- CHUẨN BỊ URL CHO CÁC LINK -----
  $baseParams = ['searchTerm' => $searchTerm];
  $url_all = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParams, ['filter' => 'all', 'view' => $view]));
  $url_image = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParams, ['filter' => 'image', 'view' => $view]));
  $url_video = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParams, ['filter' => 'video', 'view' => $view]));

  $baseParamsForView = ['searchTerm' => $searchTerm, 'filter' => $filter];
  $url_view_grid = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParamsForView, ['view' => 'grid']));
  $url_view_list = 'trangchu.php?page=ql_anh_video_bds&' . buildQueryString(array_merge($baseParamsForView, ['view' => 'list']));
?>
<!DOCTYPE html>
<html lang="vi" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <title>Trình Quản lý Media (PHP Version)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="text-gray-800">

<div class="flex">
    <aside class="w-64 h-96 bg-white border border-gray-200 p-5 rounded-lg flex-shrink-0 flex flex-col">
        <div class="flex items-center gap-2 mb-8">
            <i class="fa-solid fa-photo-film text-2xl text-blue-600"></i>
            <h1 class="text-xl font-bold text-gray-800">Media Manager</h1>
        </div>
        <nav class="space-y-2">
            <a href="<?= $url_all ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition <?= $filter === 'all' ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                <i class="fa-solid fa-grip w-5 text-center"></i> Tất cả
            </a>
            <a href="<?= $url_image ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition <?= $filter === 'image' ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                <i class="fa-solid fa-image w-5 text-center"></i> Ảnh
            </a>
            <a href="<?= $url_video ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition <?= $filter === 'video' ? 'bg-blue-50 text-blue-700 font-semibold' : '' ?>">
                <i class="fa-solid fa-video w-5 text-center"></i> Video
            </a>
        </nav>
        <div class="mt-auto">
            <button class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition">
                <i class="fa-solid fa-cloud-arrow-up"></i> Tải lên
            </button>
        </div>
    </aside>

    <main class="flex-1 p-6 overflow-y-auto">
        <header class="flex items-center justify-between mb-6 sticky top-0 bg-slate-50/80 backdrop-blur-sm py-2 z-10">
            <form method="GET" action="trangchu.php" class="relative w-96">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="fas fa-search text-gray-400"></i></div>
                <input type="search" name="searchTerm" value="<?= htmlspecialchars($searchTerm) ?>" class="w-full bg-white border border-gray-300 text-sm outline-none rounded-lg pl-10 p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tìm theo tên file, trạng thái...">
                <input type="hidden" name="page" value="ql_anh_video_bds">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
            </form>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500"><?= count($filtered_media) ?> mục</span>
                <div class="bg-gray-200 p-1 rounded-lg flex">
                    <a href="<?= $url_view_grid ?>" class="px-3 py-1 rounded-md transition <?= $view === 'grid' ? 'bg-white shadow' : '' ?>"><i class="fa-solid fa-grip"></i></a>
                    <a href="<?= $url_view_list ?>" class="px-3 py-1 rounded-md transition <?= $view === 'list' ? 'bg-white shadow' : '' ?>"><i class="fa-solid fa-list"></i></a>
                </div>
            </div>
        </header>

        <div class="<?= $view === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5' : 'space-y-2' ?>">
            <?php foreach ($filtered_media as $item): 
                $statusInfo = getStatusInfo($item['trang_thai']);
                $formattedSize = formatFileSize($item['kich_thuoc']);
            ?>
                <div class="media-item bg-white border rounded-lg shadow-sm hover:shadow-md transition cursor-pointer <?= $view === 'grid' ? 'flex flex-col' : 'flex items-center p-2' ?>">
                    
                    <div class="<?= $view === 'grid' ? 'relative' : 'w-16 h-12 flex-shrink-0' ?>">
                        <?php if ($item['loai'] === 'image'): ?>
                            <img src="<?= htmlspecialchars($item['duong_dan']) ?>" class="<?= $view === 'grid' ? 'w-full h-40 object-cover rounded-t-lg' : 'w-full h-full object-cover rounded-md' ?>">
                        <?php else: // video ?>
                            <div class="<?= $view === 'grid' ? 'w-full h-40 bg-gray-800 rounded-t-lg flex items-center justify-center' : 'w-full h-full bg-gray-800 rounded-md flex items-center justify-center' ?>">
                                <i class="fa-solid fa-play text-white text-2xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="w-full <?= $view === 'grid' ? 'p-3 flex-1 flex flex-col' : 'ml-3 flex-1' ?>">
                        <p class="font-semibold text-sm truncate" title="<?= htmlspecialchars($item['ten_file']) ?>"><?= htmlspecialchars($item['ten_file']) ?></p>
                        <div class="flex items-center justify-between text-xs text-gray-500 <?= $view === 'grid' ? 'mt-2' : 'mt-1' ?>">
                             <span class="px-2 py-0.5 font-semibold rounded-full <?= $statusInfo['classes'] ?>"><?= $statusInfo['text'] ?></span>
                             <span><?= $formattedSize ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($filtered_media)): ?>
            <div class="text-center py-16 text-gray-500">
                <i class="fa-solid fa-box-open text-4xl mb-2"></i>
                <p>Không tìm thấy media nào.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>