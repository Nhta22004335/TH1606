<?php
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