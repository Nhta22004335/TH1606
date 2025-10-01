FROM php:8.3-apache

# --- Cài PDO, PostgreSQL, Python venv + các gói cơ bản ---
RUN apt-get update && apt-get install -y \
    libpq-dev \
    python3-venv \
    python3-pip \
    git \
    wget \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# --- Tạo Python virtualenv ---
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

# --- Cập nhật pip và cài thư viện Python cơ bản ---
RUN pip install --upgrade pip \
    && pip install argon2-cffi

# --- Copy PHP source code ---
COPY ./php/ /var/www/html/

# --- Apache config & permissions ---
RUN echo '<Directory "/var/www/html">\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/html.conf \
    && a2enconf html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
