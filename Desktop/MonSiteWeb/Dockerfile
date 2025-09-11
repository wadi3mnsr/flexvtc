# PHP + Apache for FlexVTC
FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Copy (build-time). In compose we also mount volumes for live dev.
COPY ./public/ /var/www/html/

WORKDIR /var/www/html
