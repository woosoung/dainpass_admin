FROM php:8.1-apache

# 환경 설정
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Seoul

# 기본 도구들만 설치 (문제되는 패키지 제외)
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    wget \
    sudo \
    vim \
    git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP 확장을 위한 필수 라이브러리만 설치
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libpq-dev \
    libbz2-dev \
    libgmp-dev \
    libxslt1-dev \
    libsqlite3-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP 기본 확장 설치 (복잡한 것들 제외)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        zip \
        mbstring \
        xml \
        curl \
        pdo_mysql \
        mysqli \
        pgsql \
        pdo_pgsql \
        bcmath \
        bz2 \
        gmp \
        intl \
        xsl \
        sqlite3

# 간단한 PECL 확장만 설치
RUN pecl install redis apcu \
    && docker-php-ext-enable redis apcu \
    && pecl clear-cache

# Apache 모듈 활성화
RUN a2enmod rewrite headers

# Composer 설치
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# AWS SDK 설치
WORKDIR /var/www/html
RUN mkdir -p lib \
    && cd lib \
    && composer init --no-interaction --require="aws/aws-sdk-php:^3.0" \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && mv vendor/aws lib/aws \
    && rm -rf vendor composer.json composer.lock \
    && composer clear-cache

# 애플리케이션 파일 복사
COPY . /var/www/html/

# 권한 설정 및 정리
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && rm -rf /var/www/html/install

EXPOSE 80
CMD ["apache2-foreground"]