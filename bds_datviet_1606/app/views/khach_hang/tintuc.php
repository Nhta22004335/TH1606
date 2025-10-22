<?php
<<<<<<< Updated upstream:bds_datviet_1606/app/views/khach_hang/tintuc.php
// tintuc.php - Trang tin tức bất động sản
// Giả sử bạn có kết nối database ở config/database.php hoặc tương tự
// Ở đây, tôi sử dụng dữ liệu từ web search để hiển thị tin tức động (không từ bảng tin_tuc trong DB để lấy từ web khác)
// Nếu muốn dùng DB, uncomment phần dưới.

// Bao gồm header hoặc layout chung (giả sử có chung)
// KẾT NỐI DB (an toàn)
require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl(); // hàm kết nối PDO trong config của bạn// Hoặc đường dẫn phù hợp
// Kết nối DB nếu cần, nhưng ở đây dùng dữ liệu tĩnh từ web

// Dữ liệu tin tức từ web search (cập nhật mới nhất từ các nguồn uy tín)
$tin_tuc = [
    [
        'title' => 'Giá nhà liền thổ khu Đông TP HCM vượt 1 tỷ đồng/m2',
        'description' => 'Giá nhà liền thổ tại khu Đông TP HCM tiếp tục thiết lập đỉnh mới, hơn 38.000 USD (tương đương 1 tỷ đồng) mỗi m2, theo Avison Young Việt Nam.',
        'url' => 'https://vnexpress.net/bat-dong-san',
        'source' => 'VnExpress',
        'date' => '2025-10-14'
    ],
    [
        'title' => 'Sở Xây dựng Đà Nẵng siết phòng chống rửa tiền trong giao dịch bất động sản',
        'description' => 'Sở Xây dựng Đà Nẵng yêu cầu các doanh nghiệp, ngân hàng siết phòng, chống rửa tiền trong mua bán bất động sản, đặc biệt giao dịch 400 triệu đồng trở lên.',
        'url' => 'https://vnexpress.net/bat-dong-san',
        'source' => 'VnExpress',
        'date' => '2025-10-14'
    ],
    [
        'title' => 'Chung cư mới tăng giá 30-40% theo năm, nguy cơ bong bóng bất động sản',
        'description' => 'Chung cư mới tăng giá nhanh lên đến 30-40% theo năm, ảnh hưởng tính bền vững của thị trường, nguy cơ hình thành bong bóng, theo CBRE.',
        'url' => 'https://vnexpress.net/bat-dong-san',
        'source' => 'VnExpress',
        'date' => '2025-10-14'
    ],
    [
        'title' => 'Eras Land hợp tác chiến lược phân phối dự án Costamigo tại Phan Thiết',
        'description' => 'Eras Land hợp tác chiến lược với gần 20 đơn vị, triển khai kế hoạch phân phối dự án Costamigo, bổ sung nguồn cung bất động sản nghỉ dưỡng tại vùng biển Phan Thiết.',
        'url' => 'https://vnexpress.net/bat-dong-san',
        'source' => 'VnExpress',
        'date' => '2025-10-14'
    ],
    [
        'title' => 'Văn Phú đầu tư 52.000 tỷ đồng vào 4 dự án tại TP HCM',
        'description' => 'Văn Phú đẩy mạnh chiến lược mở rộng tại TP HCM và các vùng phụ cận với 4 dự án trọng điểm, tổng vốn đầu tư dự kiến hơn 52.000 tỷ đồng.',
        'image' => 'https://cafefcdn.com/2025/10/15/sunshine-1729008000-1729008001-819-1729008002.jpg?w=680&h=0&q=100&dpr=1&fit=crop&s=3H2z9h0Dq8z4g8q8z4g8q8',
        'url' => 'https://vnexpress.net/bat-dong-san',
        'source' => 'VnExpress',
        'date' => '2025-10-14'
    ],
    [
        'title' => 'Giá căn hộ khu Đông TP HCM tăng 15-30% trong quý III',
        'description' => 'Giá căn hộ tại khu Đông (TP Thủ Đức cũ) tăng mạnh nhất thị trường TP HCM trong quý III, với biên độ bình quân 15-30%, theo Dat Xanh Services.',
        'url' => 'https://vnexpress.net/bat-dong-san',
        'source' => 'VnExpress',
        'date' => '2025-10-14'
    ],
    [
        'title' => 'Doanh nghiệp bất động sản bị phạt hơn nửa tỷ đồng vì vi phạm',
        'description' => 'Với hàng loạt vi phạm, trong đó có việc sử dụng gần 70 tỷ đồng huy động từ phát hành cổ phiếu mà không thông qua cổ đông, một doanh nghiệp bất động sản đã bị phạt hơn nửa tỷ đồng.',
        'url' => 'https://vietnamnet.vn/bat-dong-san',
        'source' => 'VietnamNet',
        'date' => '2025-10-12'
    ],
    [
        'title' => 'Sự kiện ra mắt Cora Tower tại Đà Nẵng thu hút nhà đầu tư',
        'description' => 'Ngày 12/10, sự kiện ra mắt Cora Tower chủ đề “Tâm điểm Ánh Dương” diễn ra tại Đà Nẵng thu hút đông đảo khách hàng và nhà đầu tư khắp mọi miền.',
        'url' => 'https://vietnamnet.vn/bat-dong-san',
        'source' => 'VietnamNet',
        'date' => '2025-10-12'
    ],
    [
        'title' => 'Sunshine Legend City hút 5.000 booking mới sau kỷ lục bán hàng',
        'description' => 'Sau kỷ lục "khớp lệnh" hơn 1.000 căn chỉ sau 5 tiếng, Sunshine Legend City tiếp tục hút 5.000 booking mới.',
        'url' => 'https://cafef.vn/bat-dong-san.chn',
        'source' => 'CafeF',
        'date' => '2025-10-15'
    ],
    [
        'title' => 'Thị trường bất động sản Long Xuyên phân hóa, mở cơ hội đầu tư',
        'description' => 'Thị trường bất động sản Long Xuyên đang cho thấy bức tranh rõ nét về sự phân hóa, mở ra những cơ hội đầu tư đa dạng và bền vững.',
        'url' => 'https://dantri.com.vn/bat-dong-san.htm',
        'source' => 'Dân Trí',
        'date' => '2025-10-14'
    ]
];

// Nếu muốn lấy từ DB (bảng tin_tuc), uncomment và chỉnh sửa
// $stmt = $pdo->query("SELECT * FROM tin_tuc ORDER BY ngay_tao DESC LIMIT 10");
// $tin_tuc = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin Tức Bất Động Sản - Đất Việt BDS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Giả sử có file CSS chung -->
    <style>
        .news-card { margin-bottom: 20px; }
        .news-title { color: #007bff; font-weight: bold; }
        .news-source { color: #6c757d; font-size: 0.9em; }
    </style>
</head>
<body>
    <!-- Header chung - Giả sử copy từ trang chính -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="trangchu.php">
    <img src="../../../public/images/datviet.png" alt="Đất Việt BDS" width="100" height="100"> Đất Việt BDS
</a>
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Trang Chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="danhsach_bds.php">Bất Động Sản</a></li>
                <li class="nav-item"><a class="nav-link active" href="tintuc.php">Tin Tức</a></li>
                <li class="nav-item"><a class="nav-link" href="lienhe.php">Liên Hệ</a></li>
            </ul>
            <div class="d-flex">
                <a href="login.php" class="btn btn-outline-primary me-2">Đăng Nhập</a>
                <a href="dangky.php" class="btn btn-primary">Đăng Ký</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="text-center mb-4">Tin Tức Bất Động Sản Mới Nhất</h1>
        <p class="text-center text-muted">Cập nhật các tin tức nóng hổi về thị trường bất động sản Việt Nam từ các nguồn uy tín.</p>

        <div class="row">
            <?php foreach ($tin_tuc as $index => $tin): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card news-card">
                    <div class="card-body">
                        <h5 class="card-title news-title"><?php echo htmlspecialchars($tin['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($tin['description']); ?></p>
                        <p class="news-source">
                            <small>
                                Nguồn: <?php echo $tin['source']; ?> | Ngày: <?php echo $tin['date']; ?>
                                <br>
                                <a href="<?php echo $tin['url']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Đọc chi tiết</a>
                            </small>
                        </p>
                        <!-- Citation cho nguồn web -->
                        <small class="text-muted">
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer chung -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p>&copy; 2025 Đất Việt BDS. Tất cả quyền được bảo lưu.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
=======
// tintuc.php - Phiên bản đã sửa: include an toàn + init biến + tránh division by zero

// helper: require 1 trong nhiều đường dẫn (trợ giúp debug)
function require_one_of(array $paths, string $what) {
    foreach ($paths as $p) {
        if (!$p) continue;
        if (file_exists($p)) {
            require_once $p;
            return $p;
        }
    }
    http_response_code(500);
    echo "<h2>File {$what} không tìm thấy.</h2>";
    echo "<p>Đã thử các đường dẫn sau (kiểm tra lại):</p><ul>";
    foreach ($paths as $p) {
        echo "<li><code>" . htmlspecialchars($p) . "</code></li>";
    }
    echo "</ul>";
    exit;
}

/* === Include config/database.php (thêm / sửa đường dẫn nếu cần) === */
$tryDbPaths = [
    __DIR__ . "/../../config/database.php",        // views/khachhang -> app/config
    __DIR__ . "/../../../config/database.php",
    dirname(__DIR__, 2) . "/config/database.php",
    $_SERVER['DOCUMENT_ROOT'] . "/app/config/database.php",
    $_SERVER['DOCUMENT_ROOT'] . "/config/database.php",
];
$dbIncludedFrom = require_one_of($tryDbPaths, 'config/database.php');

/* === Include header.php (thêm / sửa đường dẫn nếu cần) === */
$tryHeaderPaths = [
    __DIR__ . "/header.php",
    __DIR__ . "/../header.php",
    dirname(__DIR__, 2) . "/views/header.php",
    dirname(__DIR__, 2) . "/header.php",
    $_SERVER['DOCUMENT_ROOT'] . "/app/views/header.php",
];
$headerIncludedFrom = require_one_of($tryHeaderPaths, 'header.php');

// Kiểm tra hàm kết nối
if (!function_exists('ketnoicsdl')) {
    echo "<p style='color:darkred;'>Lỗi: hàm <code>ketnoicsdl()</code> chưa được định nghĩa trong file database.php đã include ( " . htmlspecialchars($dbIncludedFrom) . " ).</p>";
    exit;
}

$pdo = ketnoicsdl(); // PDO

/* === Khởi tạo tham số truy vấn (an toàn) === */
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['perPage']) && (int)$_GET['perPage'] > 0 ? (int)$_GET['perPage'] : 10; // default 10
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$chuyen_muc = isset($_GET['chuyen_muc']) ? trim((string)$_GET['chuyen_muc']) : '';
$showAll = isset($_GET['all']) && $_GET['all'] === '1';

/* === Build WHERE và params === */
$whereParts = [];
$params = [];

if (!$showAll) {
    $whereParts[] = "trang_thai <> 'choduyet'";
}

// Detect DB driver to choose LIKE operator
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) ?? 'mysql';
$likeOp = ($driver === 'pgsql') ? 'ILIKE' : 'LIKE';

if ($q !== '') {
    $whereParts[] = "(tieu_de {$likeOp} :q OR mo_ta {$likeOp} :q)";
    $params[':q'] = '%' . $q . '%';
}
if ($chuyen_muc !== '') {
    $whereParts[] = "chuyen_muc = :chuyen_muc";
    $params[':chuyen_muc'] = $chuyen_muc;
}

$whereSql = count($whereParts) ? 'WHERE ' . implode(' AND ', $whereParts) : '';

/* === Count total (an toàn) === */
$countSql = "SELECT COUNT(*) FROM tin_tuc $whereSql";
$stmt = $pdo->prepare($countSql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->execute();
$total = (int)$stmt->fetchColumn();

/* === Tránh chia cho 0 === */
if ($perPage <= 0) $perPage = 10;
$lastPage = $perPage > 0 ? max(1, (int)ceil($total / $perPage)) : 1;
if ($page > $lastPage) $page = $lastPage;
$offset = ($page - 1) * $perPage;

/* === Lấy danh sách tin === */
$listSql = "SELECT id, id_khach_hang, tieu_de, mo_ta, chuyen_muc, trang_thai, anh_tin, ngay_dang
            FROM tin_tuc
            $whereSql
            ORDER BY ngay_dang DESC
            LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($listSql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* === Lấy danh sách chuyên mục cho select filter === */
try {
    $catStmt = $pdo->query("SELECT DISTINCT chuyen_muc FROM tin_tuc WHERE chuyen_muc IS NOT NULL");
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
}

/* === Hiển thị HTML (footer được include ở cuối) === */
?>
<main class="max-w-7xl mx-auto px-6 py-10">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <h1 class="text-3xl font-bold">Tin tức</h1>

    <form method="get" class="flex items-center gap-2">
      <?php if ($showAll): ?>
        <input type="hidden" name="all" value="1">
      <?php endif; ?>

      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Tìm kiếm tiêu đề hoặc mô tả..."
             class="border rounded-l px-3 py-2 w-64 focus:ring focus:border-blue-400 text-sm">
      <select name="chuyen_muc" class="border-t border-b border-r px-3 py-2 text-sm">
        <option value="">Tất cả chuyên mục</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>" <?= $chuyen_muc === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="bg-blue-600 text-white px-4 py-2 rounded-r text-sm">Lọc</button>
    </form>
  </div>

  <?php if ($total === 0): ?>
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded">
      Không tìm thấy bài viết phù hợp.
      <?php if (!$showAll): ?>
        <span class="block text-xs mt-2">Muốn xem cả tin chờ duyệt? <a href="?all=1" class="underline text-blue-600">Bấm vào đây</a>.</span>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="grid md:grid-cols-2 gap-6 mt-6">
    <?php foreach ($newsList as $item): ?>
      <article class="bg-white rounded-lg shadow-sm overflow-hidden">
        <a href="tintuc_chitiet.php?id=<?= urlencode($item['id']) ?>" class="block">
          <div class="h-44 bg-gray-100 overflow-hidden">
            <?php if (!empty($item['anh_tin']) && $item['anh_tin'] !== 'chuacapnhat.png'): ?>
              <img src="<?= htmlspecialchars($item['anh_tin']) ?>" alt="<?= htmlspecialchars($item['tieu_de']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
              <div class="w-full h-full flex items-center justify-center text-gray-400">Không có hình</div>
            <?php endif; ?>
          </div>

          <div class="p-4">
            <h2 class="text-lg font-semibold mb-2"><?= htmlspecialchars($item['tieu_de']) ?></h2>
            <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars(mb_strimwidth(strip_tags($item['mo_ta']), 0, 160, '...')) ?></p>
            <div class="text-xs text-gray-500 flex items-center justify-between">
              <span class="inline-block"><?= htmlspecialchars($item['chuyen_muc'] ?: 'Chưa phân loại') ?></span>
              <span><?= date('d/m/Y', strtotime($item['ngay_dang'])) ?></span>
            </div>
            <div class="mt-2 text-xs">
              <span class="px-2 py-1 rounded text-white text-[11px] <?= $item['trang_thai'] === 'choduyet' ? 'bg-yellow-500' : ($item['trang_thai'] === 'dangban' ? 'bg-green-600' : 'bg-gray-600') ?>">
                <?= htmlspecialchars($item['trang_thai']) ?>
              </span>
            </div>
          </div>
        </a>
      </article>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($lastPage > 1): ?>
    <div class="mt-8 flex justify-center items-center gap-2">
      <?php $qs = $_GET; ?>
      <?php if ($page > 1): $qs['page'] = $page - 1; ?>
        <a class="px-3 py-1 border rounded" href="?<?= http_build_query($qs) ?>">« Trước</a>
      <?php endif; ?>

      <?php
        $start = max(1, $page - 3);
        $end = min($lastPage, $page + 3);
        for ($p = $start; $p <= $end; $p++):
          $qs['page'] = $p;
      ?>
        <a class="px-3 py-1 border rounded <?= $p === $page ? 'bg-blue-600 text-white' : '' ?>" href="?<?= http_build_query($qs) ?>"><?= $p ?></a>
      <?php endfor; ?>

      <?php if ($page < $lastPage): $qs['page'] = $page + 1; ?>
        <a class="px-3 py-1 border rounded" href="?<?= http_build_query($qs) ?>">Tiếp »</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</main>

<?php
// include footer nếu có (thử một số đường dẫn)
$tryFooterPaths = [
    __DIR__ . "/footer.php",
    __DIR__ . "/../footer.php",
    dirname(__DIR__, 2) . "/views/footer.php",
    dirname(__DIR__, 2) . "/footer.php",
    $_SERVER['DOCUMENT_ROOT'] . "/app/views/footer.php",
];
foreach ($tryFooterPaths as $p) {
    if (file_exists($p)) {
        require_once $p;
        break;
    }
}
?>
>>>>>>> Stashed changes:php/app/views/khachhang/tintuc.php
