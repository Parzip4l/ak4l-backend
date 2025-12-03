# Gunakan base image resmi PHP 8.2 FPM
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install dependensi sistem
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libldap2-dev \
    libgd-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --- BAGIAN PENTING YANG DITAMBAHKAN ---
# 1. Konfigurasi lokasi library LDAP (Wajib di image docker linux)
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/

# 2. Install ekstensi PHP (Tambahkan 'ldap' ke dalam list)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl ldap
# ---------------------------------------

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy semua file proyek ke dalam container
COPY . /var/www

# Install dependensi Composer
# Gunakan 'composer install' jika lock file sudah ada dan valid
# Jika sering error version conflict, bisa ganti jadi 'composer update'
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Ubah kepemilikan file agar dapat ditulis oleh web server
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 untuk PHP-FPM
EXPOSE 9000

# Perintah default untuk menjalankan PHP-FPM
CMD ["php-fpm"]