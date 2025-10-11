FROM php:8.3-apache

# --- Cài các gói hệ thống cần thiết ---
# Thêm curl để tải script Node.js và supervisor để quản lý tiến trình
RUN apt-get update && apt-get install -y \
    libpq-dev \
    python3-venv \
    python3-pip \
    git \
    wget \
    unzip \
    curl \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# --- Cài các extension của PHP ---
RUN docker-php-ext-install pdo pdo_pgsql

# --- Cài Node.js (khuyến nghị bản LTS 20) ---
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# --- Cài Python (giữ nguyên) ---
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
RUN pip install --upgrade pip \
    && pip install argon2-cffi

# --- Thiết lập thư mục làm việc và sao chép mã nguồn ---
WORKDIR /var/www/html

# Sao chép mã nguồn PHP
COPY ./bds_datviet_1606/ /var/www/html/

# Sao chép và cài đặt ứng dụng WebSocket
# Giả sử bạn có một thư mục con là 'socket-server' chứa server.js và package.json
COPY ./bds_datviet_1606/socket-server/package*.json ./socket-server/
RUN cd socket-server && npm install
COPY ./bds_datviet_1606/socket-server/ ./socket-server/

# --- Cấu hình Apache (giữ nguyên) ---
RUN a2enmod rewrite
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN echo '<Directory "/var/www/html/public">\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/html-public-permission.conf \
    && a2enconf html-public-permission

# --- Cấu hình Supervisor ---
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# --- Cấp quyền ---
RUN chown -R www-data:www-data /var/www/html

# --- Mở các cổng cần thiết ---
EXPOSE 80
EXPOSE 3000

# --- Lệnh khởi động container bằng Supervisor ---
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]