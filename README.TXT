docker compose up -d

docker compose up --build -d

# Dừng và xóa container + network (dọn sạch)
docker compose down

# Chỉ dừng container (không xóa)
docker compose stop
docker compose start

docker exec -it php-demo ls -la /var/www/html
docker ps

