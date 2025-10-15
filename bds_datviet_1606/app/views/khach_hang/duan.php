<?php
session_start(); // Khởi tạo session để kiểm tra đăng nhập

// duan.php - Trang hiển thị danh sách dự án bất động sản
include_once __DIR__ . '/../../../config/database.php'; // Sửa đường dẫn

// Kết nối PDO
try {
    $pdo = ketnoicsdl(); // Hàm từ config/database.php
    // Truy vấn JOIN để lấy thông tin dự án và hình ảnh, lấy hình ảnh đầu tiên nếu có nhiều
    $stmt = $pdo->query("
        SELECT bds.id, bds.tieu_de, bds.mo_ta, bds.gia, bds.dien_tich, bds.dia_chi, bds.loai, bds.ngay_dang, ha.url
        FROM bat_dong_san bds
        LEFT JOIN hinh_anh_bds ha ON bds.id = ha.id_bds
        GROUP BY bds.id, bds.tieu_de, bds.mo_ta, bds.gia, bds.dien_tich, bds.dia_chi, bds.loai, bds.ngay_dang
        ORDER BY bds.ngay_dang DESC
        LIMIT 10
    ");
    $du_an = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Kiểm tra dữ liệu trả về
    // var_dump($du_an); // Bỏ comment để kiểm tra
} catch (PDOException $e) {
    $du_an = []; // Fallback nếu có lỗi
    error_log("Lỗi truy vấn dự án: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dự Án Bất Động Sản - Đất Việt BDS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Giả sử có file CSS chung -->
    <style>
        .project-card { margin-bottom: 20px; }
        .project-image { max-height: 200px; object-fit: cover; width: 100%; }
        .project-title { color: #007bff; font-weight: bold; }
        .project-price { color: #28a745; font-weight: bold; }
        .user-info { margin-left: 10px; }
    </style>
</head>
<body>
    <!-- Header chung -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="trangchu.php">
                <img src="../../../public/images/datviet.png" alt="Đất Việt BDS" width="100" height="100"> Đất Việt BDS
            </a>
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Trang Chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="danhsach_bds.php">Bất Động Sản</a></li>
                <li class="nav-item"><a class="nav-link" href="tintuc.php">Tin Tức</a></li>
                <li class="nav-item"><a class="nav-link active" href="duan.php">Dự Án</a></li>
                <li class="nav-item"><a class="nav-link" href="lienhe.php">Liên Hệ</a></li>
            </ul>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="user-info">Chào <?php echo htmlspecialchars($_SESSION['ten_nguoi_dung'] ?? 'Người dùng'); ?>!</span>
                    <a href="dang_xuat.php" class="btn btn-outline-danger ms-2">Đăng xuất</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary me-2">Đăng Nhập</a>
                    <a href="dangky.php" class="btn btn-primary">Đăng Ký</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="text-center mb-4">Danh Sách Dự Án Bất Động Sản</h1>
        <p class="text-center text-muted">Khám phá các dự án bất động sản mới nhất từ Đất Việt BDS.</p>

        <div class="row">
            <?php foreach ($du_an as $index => $project): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card project-card">
                    <!-- Hiển thị hình ảnh từ CSDL với tiền tố đường dẫn -->
                    <img src="<?php echo !empty($project['url']) ? htmlspecialchars('../../../public/' . ltrim($project['url'], '/')) : 'https://via.placeholder.com/680x200?text=Ảnh+Dự+Án+' . ($index + 1); ?>" class="card-img-top project-image" alt="<?php echo htmlspecialchars($project['tieu_de']); ?>">
                    <div class="card-body">
                        <h5 class="card-title project-title"><?php echo htmlspecialchars($project['tieu_de']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($project['mo_ta']); ?></p>
                        <p><strong>Giá:</strong> <?php echo number_format($project['gia'], 0, ',', '.') . ' VNĐ'; ?></p>
                        <p><strong>Diện tích:</strong> <?php echo $project['dien_tich'] . ' m²'; ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($project['dia_chi']); ?></p>
                        <p><strong>Loại:</strong> <?php echo htmlspecialchars($project['loai']); ?></p>
                        <p><strong>Ngày đăng:</strong> <?php echo htmlspecialchars($project['ngay_dang']); ?></p>
                        <a href="chi_tiet_du_an.php?id=<?php echo $project['id']; ?>" class="btn btn-primary">Xem chi tiết</a>
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