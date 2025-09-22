docker compose up -d

docker compose up --build -d

# Dừng và xóa container + network (dọn sạch)
docker compose down

# Chỉ dừng container (không xóa)
docker compose stop
docker compose start

docker exec -it php-demo ls -la /var/www/html
docker ps


<!-- Nút Phản hồi -->
<!-- <a href="#phanhoi"
  class="fixed right-0 z-50
         md:top-[20%] md:-translate-y-1/2 md:transform md:-rotate-90 md:origin-bottom-right
         bottom-16 md:bottom-auto
         bg-blue-600 border border-blue-600 
         px-2 py-2 min-w-[120px]
         text-white tracking-wide font-semibold shadow-lg 
         rounded-md md:rounded-none md:rounded-tl-md md:rounded-tr-md
         flex items-center justify-center gap-2
         hover:bg-blue-700 transition">
  <i class="fa-regular fa-face-smile"></i> Phản hồi
</a> -->

<!-- Nút Trang chủ -->
<!-- <a href="sanpham.php"
  class="fixed right-0 z-50
         md:top-1/2 md:-translate-y-1/2 md:transform md:-rotate-90 md:origin-bottom-right
         bottom-4 md:bottom-auto
         bg-white border border-blue-600 
         px-2 py-2 min-w-[120px]
         text-blue-600 font-semibold shadow-md 
         rounded-md md:rounded-none md:rounded-tl-md md:rounded-tr-md
         flex items-center justify-center gap-2
         hover:bg-blue-50 transition">
  <i class="fa-solid fa-house"></i> Trang chủ
</a> -->

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập hệ thống</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body 
  class="h-screen w-screen bg-cover bg-center flex items-center justify-center"
  style="background-image: url('https://images.unsplash.com/photo-1505691723518-36a5ac3be353?auto=format&fit=crop&w=1350&q=80');">

  <div class="bg-white/30 backdrop-blur-lg p-8 rounded-2xl shadow-2xl w-96 border border-white/40">
    <h2 class="text-2xl font-bold text-center text-gray-900 mb-6 drop-shadow">Đăng nhập hệ thống</h2>
    
    <form method="POST" action="">
      <div class="mb-4">
        <input type="text" name="username" placeholder="Tên đăng nhập" 
          class="w-full px-4 py-2 border border-white/40 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white/60 backdrop-blur-sm placeholder-gray-700" required>
      </div>
      <div class="mb-4">
        <input type="password" name="password" placeholder="Mật khẩu" 
          class="w-full px-4 py-2 border border-white/40 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white/60 backdrop-blur-sm placeholder-gray-700" required>
      </div>
      <button type="submit" 
        class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white py-2 rounded-lg hover:opacity-90 transition duration-200">
        Đăng nhập
      </button>
    </form>

    <?php if (!empty($error)) : ?>
      <p class="text-red-500 text-center mt-4"><?= $error ?></p>
    <?php endif; ?>

    <div class="flex justify-between items-center mt-6 text-sm text-white drop-shadow">
      <a href="forgot_password.php" class="hover:underline">Quên mật khẩu?</a>
      <a href="register.php" class="hover:underline">Đăng ký</a>
    </div>
  </div>

</body>
</html>