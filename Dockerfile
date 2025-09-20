FROM php:8.3-apache

# Cài PDO và PostgreSQL driver
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Cài thêm Python 3 và pip + thư viện argon2
RUN apt-get update && apt-get install -y python3 python3-pip python3-venv \
    && python3 -m venv /opt/venv \
    && /opt/venv/bin/pip install argon2-cffi

# Copy source code
COPY ./php/ /var/www/html/

# Cho phép Apache đọc toàn bộ thư mục
RUN echo '<Directory "/var/www/html">\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/html.conf \
    && a2enconf html

# Set quyền cho Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
