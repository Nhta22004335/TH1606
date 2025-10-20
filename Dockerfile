# Sử dụng image PHP 8.3 chính thức với Apache
FROM php:8.3-apache

# --- Cài đặt các gói hệ thống cần thiết ---
# THÊM MỚI: redis-server, memcached, và các thư viện phụ thuộc
RUN apt-get update && apt-get install -y \
    libpq-dev \
    python3-venv \
    python3-pip \
    git \
    wget \
    unzip \
    curl \
    supervisor \
    redis-server \
    memcached \
    libmemcached-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# --- Cài đặt các extension của PHP ---
# THÊM MỚI: Cài đặt và kích hoạt extension cho redis và memcached
RUN pecl install redis memcached \
    && docker-php-ext-enable redis memcached

# Cài đặt extension cho PostgreSQL (giữ nguyên)
RUN docker-php-ext-install pdo pdo_pgsql

# --- Cài đặt Node.js (bản LTS 20) ---
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# --- Cài đặt môi trường ảo Python (giữ nguyên) ---
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
RUN pip install --upgrade pip \
    && pip install argon2-cffi

# --- Thiết lập thư mục làm việc và cài đặt các gói Node.js ---
WORKDIR /var/www/html

# Sao chép package.json và package-lock.json trước để tận dụng cache của Docker
COPY ./bds_datviet_1606/package*.json ./
RUN npm install

# Sao chép toàn bộ mã nguồn của dự án vào container
COPY ./bds_datviet_1606/ /var/www/html/

# ===================================================================
# CẤU HÌNH APACHE ĐỂ CHẠY TỪ THƯ MỤC GỐC (giữ nguyên)
# ===================================================================
RUN a2enmod rewrite
RUN echo '<Directory "/var/www/html">\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/html-permission.conf \
    && a2enconf html-permission
# ===================================================================

# --- Sao chép file cấu hình Supervisor ---
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# --- Cấp quyền cho thư mục web ---
RUN chown -R www-data:www-data /var/www/html

# --- Mở các cổng cần thiết ---
EXPOSE 80
EXPOSE 3000
# EXPOSE 6379 # (Tùy chọn) Mở cổng Redis nếu bạn muốn kết nối từ bên ngoài container
# EXPOSE 11211 # (Tùy chọn) Mở cổng Memcached nếu bạn muốn kết nối từ bên ngoài container

# --- Lệnh khởi động container bằng Supervisor ---
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
