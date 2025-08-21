FROM php:8.1-apache

# 환경 설정 (캐시 최적화를 위해 먼저)
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Seoul

# 시스템 패키지 설치 (변경이 적은 레이어)
RUN apt-get update && apt-get install -y \
    # 기본 개발 도구들
    software-properties-common lsb-release gnupg2 tzdata \
    sudo vim rsync git net-tools \
    # PHP 확장을 위한 라이브러리들
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libzip-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    libpq-dev libmagickwand-dev libldap2-dev libbz2-dev \
    libgmp-dev libxslt1-dev libsqlite3-dev libtidy-dev \
    libssh2-1-dev libmemcached-dev \
    # 기본 도구들
    curl zip unzip wget \
    && rm -rf /var/lib/apt/lists/*

# PHP 기본 확장 설치 (변경이 적은 레이어)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install -j$(nproc) \
        gd pdo_mysql mysqli zip mbstring xml curl \
        pgsql pdo_pgsql bcmath bz2 gmp intl xsl \
        ldap sqlite3 tidy soap

# PECL 확장 설치 (변경이 적은 레이어)
RUN pecl install redis apcu imagick mongodb igbinary msgpack ssh2-1.3.1 \
    && docker-php-ext-enable redis apcu imagick mongodb igbinary msgpack ssh2

# Apache 모듈 활성화
RUN a2enmod rewrite headers

# Composer 설치 (변경이 적은 레이어)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Composer 설치
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 그누보드 파일 복사
COPY . /var/www/html/

# AWS SDK 설치 (기존 lib/aws 디렉토리가 있다면 제거 후 재설치)
WORKDIR /var/www/html
RUN if [ -d "lib/aws" ]; then rm -rf lib/aws; fi \
    && mkdir -p lib \
    && cd lib \
    && composer init --no-interaction --require="aws/aws-sdk-php:^3.0" \
    && composer install --no-dev --optimize-autoloader \
    && mv vendor/aws lib/aws \
    && rm -rf vendor composer.json composer.lock \
    || echo "AWS SDK installation failed, using existing files"

# 권한 설정 및 정리
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && rm -rf /var/www/html/install

EXPOSE 80
CMD ["apache2-foreground"]