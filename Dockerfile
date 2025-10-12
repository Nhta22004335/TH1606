# Sử dụng image PHP 8.3 chính thức với Apache
FROM php:8.3-apache

# --- Cài đặt các gói hệ thống cần thiết ---
# Bao gồm các công cụ build, extension cho PostgreSQL, Python, Node.js, và Supervisor
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

# --- Cài đặt các extension của PHP ---
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
# Bước `npm install` sẽ không chạy lại nếu các file này không thay đổi
COPY ./bds_datviet_1606/package*.json ./
RUN npm install

# Sao chép toàn bộ mã nguồn của dự án vào container
COPY ./bds_datviet_1606/ /var/www/html/

# ===================================================================
# CẤU HÌNH APACHE ĐỂ CHẠY TỪ THƯ MỤC GỐC
# ===================================================================

# 1. Kích hoạt module rewrite của Apache để sử dụng .htaccess
RUN a2enmod rewrite

# 2. Cấu hình quyền cho thư mục gốc /var/www/html để .htaccess hoạt động
# Apache sẽ mặc định sử dụng /var/www/html làm DocumentRoot
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

# --- Lệnh khởi động container bằng Supervisor ---
# Supervisor sẽ khởi chạy và quản lý tất cả các dịch vụ (Apache, WebSocket, Tailwind)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]