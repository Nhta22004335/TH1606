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