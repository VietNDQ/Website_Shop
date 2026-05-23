FROM php:8.2-fpm-alpine

# Cài đặt extension hệ thống cần thiết
RUN apk update && apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev

# Cài đặt PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl

# Lấy Composer chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Sao chép mã nguồn ứng dụng
COPY . .

# Tạo user hệ thống 'laravel' để chạy dịch vụ an toàn (non-root)
RUN addgroup -g 1000 laravel && \
    adduser -u 1000 -G laravel -s /bin/sh -D laravel

# Phân quyền cho thư mục ứng dụng
RUN chown -R laravel:laravel /var/www

# Sử dụng user laravel để thực thi
USER laravel

EXPOSE 9000

CMD ["php-fpm"]
