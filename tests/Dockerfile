FROM php:8.2-cli
ENV PHP_MEMORY_LIMIT=2048M
RUN echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;
RUN apt-get update -yqq \
    && apt-get install -y libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6 libfreetype6-dev sqlite3 \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
	&& docker-php-ext-install intl mysqli pdo pdo_mysql gd zip