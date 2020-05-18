FROM php:7.1-apache

ENV TERM="xterm-256color"
ENV COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update && apt-get install -y \
      bash-completion \
      rsync \
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

# Install mongo
RUN apt-get update && apt-get upgrade -y && apt-get install openssl libssl-dev libcurl4-openssl-dev -y
RUN pecl install mongodb \
&& echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/ext-mongodb.ini

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

# Install NodeJS 8.11.4
#RUN curl -O https://nodejs.org/dist/v8.11.4/node-v8.11.4-linux-x64.tar.xz \
#    && mkdir /usr/local/lib/nodejs \
#    && tar -xJf node-v8.11.4-linux-x64.tar.xz -C /usr/local/lib/nodejs \
#    && rm node-v8.11.4-linux-x64.tar.xz \
#    && export NODEJS_HOME=/usr/local/lib/nodejs/node-v8.11.4-linux-x64/bin \
#    && export PATH=$NODEJS_HOME:$PATH \
#    && ln -s /usr/local/lib/nodejs/node-v8.11.4-linux-x64/bin/node /usr/bin/node \
#    && ln -s /usr/local/lib/nodejs/node-v8.11.4-linux-x64/bin/npm /usr/bin/npm

#RUN npm install --global gulp-cli \
#&& ln -s /usr/local/lib/nodejs/node-v8.11.4-linux-x64/bin/gulp /usr/bin/gulp  

# Add composer
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
      && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
      && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('    /tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
      && php /tmp/composer-setup.php --version=1.7.2 --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot \
      && rm -f /tmp/composer-setup.*

# Instal Symfony
RUN mkdir -p /usr/local/bin
RUN curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony
RUN chmod a+x /usr/local/bin/symfony

# Adding PHP config files
COPY config/php.ini /usr/local/etc/php/php.ini
COPY config/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Entrypoint
COPY php-entrypoint.sh /php-entrypoint.sh
RUN chmod +x /php-entrypoint.sh
ENTRYPOINT ["/php-entrypoint.sh"]
