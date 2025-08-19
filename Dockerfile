# 1. 베이스 이미지 선택
# PHP 8.2 버전과 Apache 웹 서버가 미리 설치된 공식 이미지를 사용합니다.
FROM php:8.2-apache

# 2. 필요한 PHP 확장 프로그램 설치
# Admin 페이지가 DB에 연결하는 방식에 맞는 확장 프로그램을 설치합니다.
# 보통 mysqli와 pdo_mysql을 설치하면 대부분의 경우에 잘 동작합니다.
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# 3. Apache의 mod_rewrite 활성화 (깔끔한 URL을 위해 권장)
RUN a2enmod rewrite

# 4. 소스 코드 복사
# 현재 프로젝트 폴더의 모든 파일을 컨테이너의 웹 루트 폴더(/var/www/html/)로 복사합니다.
COPY . /var/www/html/