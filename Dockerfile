# FROM registry.gitlab.com/gkc_team/devops-groups/gkc-docker-image-php
FROM php:7.4.8-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    mariadb-client \
    libpng-dev \
    libpq-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libgmp-dev \
    libonig-dev \
    libzip-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    nano

# Install extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl pdo_pgsql gd gmp mysqli mbstring opcache zip

#RUN apt install php7.4-common php7.4-mysql php7.4-xml php7.4-xmlrpc php7.4-curl php7.4-gd php7.4-imagick php7.4-cli php7.4-dev php7.4-imap php7.4-mbstring php7.4-opcache php7.4-soap php7.4-zip php7.4-intl -y

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy upload.ini to config
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Set working directory
WORKDIR /var/www

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

RUN composer install
RUN composer dump-autoload

RUN chown -R www:www /var/www
# Change current user to www
USER www

# Run swagger API Document
RUN cd development && chmod +x swagger.sh && ./swagger.sh

# Set working directory
WORKDIR /var/www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
