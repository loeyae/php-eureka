FROM php:7.1-cli-buster
RUN cp /etc/apt/sources.list /etc/apt/sources.list.bak && \
    sed -i \
        -e 's/deb.debian.org/mirrors.aliyun.com/g' \
        -e 's/security.debian.org/mirrors.aliyun.com/g' \
        /etc/apt/sources.list && \
    apt-get update && \
    apt-get -y install gnupg2 && \
    apt-get update && \
    apt-get -y install \
            g++ \
            git \
            imagemagick \
            libyaml-dev \
            libcurl3-dev \
            libicu-dev \
            libfreetype6-dev \
            libjpeg-dev \
            libjpeg62-turbo-dev \
            libmagickwand-dev \
            libpq-dev \
            libpng-dev \
            libxml2-dev \
            libzip-dev \
            zlib1g-dev \
            unzip \
            libcurl4-openssl-dev \
            libssl-dev \
        --no-install-recommends && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
        mkdir -p /var/work/

RUN docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-png-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-configure bcmath && \
    docker-php-ext-configure sysvshm && \
    docker-php-ext-install \
        soap \
        zip \
        bcmath \
        exif \
        gd \
        iconv \
        intl \
        opcache \
        pdo_mysql


# Install PECL extensions
# see http://stackoverflow.com/a/8154466/291573) for usage of `printf`
RUN printf "\n" | pecl install \
#        swoole \
#        imagick \
#        mongodb \
        yaml && \
    docker-php-ext-enable \
#        swoole \
#        imagick \
#        mongodb \
        yaml

# Check if Xdebug extension need to be compiled
#RUN cd /tmp && \
#    git clone git://github.com/xdebug/xdebug.git && \
#    cd xdebug && \
#    git checkout 2.7.2 && \
#    phpize && \
#    ./configure --enable-xdebug && \
#    make && \
#    make install && \
#    rm -rf /tmp/xdebug

# Add configuration files
COPY image-files/ /

# Add GITHUB_API_TOKEN support for composer
RUN chmod 700 \
        /usr/local/bin/composer.phar \
        /usr/local/bin/composer

# Install composer
RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ && \
    composer global require --optimize-autoloader \
        "hirak/prestissimo:^0.3.9" && \
    composer global dumpautoload --optimize && \
    composer clear-cache

# Enable mod_rewrite for images with apache
RUN if command -v a2enmod >/dev/null 2>&1; then \
        a2enmod rewrite headers \
    ;fi


COPY src/ /var/work/src/
COPY *.php /var/work/
COPY composer.json /var/work

WORKDIR /var/work

RUN composer install --no-dev

EXPOSE 8080
CMD ["php", "/var/work/Application.php"]