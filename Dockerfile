ARG PHP_VERSION="php:8.2-fpm-alpine"
FROM ${PHP_VERSION}

ARG TZ="Asia/Shanghai"
ARG CONTAINER_PACKAGE_URL="mirrors.ustc.edu.cn"
RUN if [ $CONTAINER_PACKAGE_URL ] ; then sed -i "s/dl-cdn.alpinelinux.org/${CONTAINER_PACKAGE_URL}/g" /etc/apk/repositories ; fi


RUN curl -o /usr/bin/composer https://mirrors.aliyun.com/composer/composer.phar \
    && chmod +x /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer

RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
RUN apk --no-cache add shadow && usermod -u 1000 www-data && groupmod -g 1000 www-data


COPY  . /var/www

WORKDIR /var/www

RUN composer install --ignore-platform-reqs --no-dev --no-interaction -o -vvv