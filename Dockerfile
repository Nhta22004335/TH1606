FROM php:8.3-apache

# --- Cài PDO, PostgreSQL, Python venv + thư viện hệ thống cần thiết ---
RUN apt-get update && apt-get install -y \
    libpq-dev \
    python3-venv \
    python3-pip \
    git \
    wget \
    unzip \
    libgl1 \
    libglib2.0-0 \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# --- Tạo Python virtualenv ---
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

# --- Cài torch CPU + ultralytics + OpenCV + Pillow + numpy ---
RUN pip install --upgrade pip \
    && pip install argon2-cffi \
    && pip install "torch>=2.5.0" "torchvision>=0.15.0" "torchaudio>=2.5.0" --index-url https://download.pytorch.org/whl/cpu \
    && pip install ultralytics opencv-python pillow numpy

# --- Copy PHP source code ---
COPY ./php/ /var/www/html/

# --- Tải YOLO model ---
RUN wget -O /var/www/html/yolov8n.pt https://github.com/ultralytics/assets/releases/download/v0.0.0/yolov8n.pt

# --- Apache config & permissions ---
RUN echo '<Directory "/var/www/html">\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/html.conf \
    && a2enconf html
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
