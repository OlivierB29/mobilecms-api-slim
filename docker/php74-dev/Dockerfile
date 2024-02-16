FROM php:7.4-fpm

# Install mbstring extension and libonig-dev
RUN apt-get update && apt-get install -y \
    zip \
    p7zip-full \
    git

# Install XML extension
# RUN apt-get update && apt-get install -y \
#    libxml2-dev \
#    && docker-php-ext-install -j$(nproc) xml

# Install Xdebug extension
RUN pecl install xdebug-2.9.8 \
    && docker-php-ext-enable xdebug

# TODO enable xdebug
#RUN echo '[XDebug]' >> /usr/local/etc/php/php.ini
#RUN echo 'xdebug.remote_enable = 1' >> /usr/local/etc/php/php.ini
#RUN echo 'xdebug.remote_autostart = 1' >> /usr/local/etc/php/php.ini

#RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install GD extension
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd


# Install libonig-dev and imagick
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libmagickwand-dev --no-install-recommends

RUN docker-php-ext-install -j$(nproc) mbstring exif

RUN pecl install imagick \
    && docker-php-ext-enable imagick
   # && docker-php-ext-install mbstring



RUN sed -i '/PDF/s/none/read|write/' /etc/ImageMagick-6/policy.xml

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer