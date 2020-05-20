FROM php:7.1-apache

ENV TERM="xterm-256color"
ENV COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update && apt-get install -y \
      bash-completion \
      vim \
      git \
      curl \
      zip \
      libzip-dev \
      sudo \
      libmcrypt-dev \
      libicu-dev \
      zlib1g-dev \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libpng-dev \
      libwebp-dev

RUN docker-php-ext-install \
    -j$(nproc) iconv \
    intl \
    mysqli \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    zip \
    opcache \
    mcrypt \
    -j$(nproc) calendar

# Adding sudo to www-data user
RUN usermod -aG sudo www-data
RUN echo "www-data     ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Granting ownership to Apache user
RUN chown -R www-data:www-data /var/www
RUN chsh -s /bin/bash www-data

RUN docker-php-ext-configure gd --with-webp-dir=/usr/include/ --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN pecl install apcu && docker-php-ext-enable apcu

# Self-signed certificate
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/ssl-cert-snakeoil.key \
    -out /etc/ssl/certs/ssl-cert-snakeoil.pem \
    -subj "/C=AT/ST=Vienna/L=Vienna/O=Security/OU=Development/CN=example.com"

# enable mod_rewrite apache
RUN a2enmod rewrite
RUN a2enmod ssl

# Add composer
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
      && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
      && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('    /tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
      && php /tmp/composer-setup.php --version=1.7.2 --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot \
      && rm -f /tmp/composer-setup.*

# Adding PHP config files
COPY config/php.ini /usr/local/etc/php/php.ini
COPY config/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Entrypoint
COPY php-entrypoint.sh /php-entrypoint.sh
RUN chmod +x /php-entrypoint.sh
ENTRYPOINT ["/php-entrypoint.sh"]
