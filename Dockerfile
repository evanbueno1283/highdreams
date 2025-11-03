FROM php:8.2-apache

# Enable PHP extensions (optional, kung may DB)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy all files to Apache web root
COPY . /var/www/html/

EXPOSE 80
