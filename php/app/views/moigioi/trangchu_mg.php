<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>NhàĐẹp24h</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <!-- Header -->
    <header class="bg-white shadow p-4 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <img src="../moigioi/anh_mg/logo-homedy.png" alt="Logo" class="h-10" />
            <h1 class="text-xl font-bold text-blue-600">NhàĐẹp24h</h1>
        </div>
        <div class="flex items-center space-x-4">
            <input type="text" placeholder="Tìm kiếm..." class="px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" />
            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Đăng tin</button>
            <img src="../../../../public/assets/auth/0/logo-homedy.png" alt="Avatar" class="h-10 w-10 rounded-full border" />
        </div>
    </header>

    <!-- Sidebar + Content -->
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow h-screen p-4">
            <nav class="space-y-4">
                <a href="?page=tin_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                    <span>📋</span><span>Tin đăng của tôi</span>
                </a>
                <a href="?page=khachhang_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                    <span>👤</span><span>Khách hàng quan tâm</span>
                </a>
                <a href="?page=giaodich_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                    <span>📑</span><span>Hợp đồng / Giao dịch</span>
                </a>
                <a href="?page=thongke_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                    <span>📊</span><span>Thống kê cá nhân</span>
                </a>
                <a href="?page=chat_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                    <span>💬</span><span>Chat & Thông báo</span>
                </a>
                <a href="?page=hoso_mg" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                    <span>⚙️</span><span>Hồ sơ cá nhân</span>
                </a>
                <a href="dangxuat_mg.php" class="flex items-center space-x-2 text-red-600 hover:text-red-800 font-medium">
                    <span>🚪</span><span>Đăng xuất</span>
                </a>
            </nav>
        </aside>

        <!-- Main content -->
        <main class="flex-1 p-6">
            <?php
            // Danh sách page hợp lệ
            $allowed_pages = ['tin_mg', 'khachhang_mg', 'giaodich_mg', 'thongke_mg', 'chat_mg', 'hoso_mg'];

            if (isset($_GET['page']) && in_array($_GET['page'], $allowed_pages)) {
                $page = $_GET['page'];
                $file = __DIR__ . "/{$page}.php";

                if (file_exists($file)) {
                    include $file;
                } else {
                    echo "<p class='text-red-600'>Trang bạn yêu cầu không tồn tại.</p>";
                }
            } else {
                echo "<h2 class='text-2xl font-bold mb-4'>Chào mừng bạn đến NhàĐẹp24h</h2>
                      <p class='text-gray-600'>Hãy chọn chức năng từ menu bên trái.</p>";
            }
            ?>
        </main>
    </div>

</body>
</html>
