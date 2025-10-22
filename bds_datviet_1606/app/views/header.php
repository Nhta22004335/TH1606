sudo tee /var/www/html/app/views/header.php > /dev/null <<'PHP'
<?php
// Simple header used when a full header is not available.
// Bạn có thể mở file này và tinh chỉnh HTML/CSS theo dự án.
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Trang - Tin tức</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
<header class="bg-white shadow-sm">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
    <a href="/trangchu.php" class="flex items-center gap-3">
      <img src="/public/assets/anhht/0/datviet.png" alt="Logo" class="h-10 w-10 object-contain">
      <span class="text-xl font-bold">Đất Việt</span>
    </a>

    <nav class="hidden md:flex space-x-6">
      <a href="/trangchu.php" class="hover:text-blue-600">Trang chủ</a>
      <a href="/tintuc.php" class="hover:text-blue-600">Tin tức</a>
      <a href="/danhmucduan.php" class="hover:text-blue-600">Dự án</a>
      <a href="/trangchu.php?page=lienhe" class="hover:text-blue-600">Liên hệ</a>
    </nav>

    <div class="flex items-center gap-3">
      <a href="/dangnhap.php" class="text-sm px-3 py-1 border rounded">Đăng nhập</a>
    </div>
  </div>
</header>
<!-- header end -->
PHP
