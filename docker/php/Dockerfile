FROM php:7.4-fpm-alpine AS php_base

RUN apk --update add \
        zip unzip tzdata \
        g++ libzip-dev libxslt-dev zlib-dev icu-dev \
    && docker-php-ext-install \
        bcmath intl json opcache pdo_mysql sockets zip \
    && apk del \
        g++ \
    && rm -rf /var/cache/apk/* \
#    Set timezone
    && cp /usr/share/zoneinfo/Europe/Paris /etc/localtime \
    && echo "Europe/Paris" >  /etc/timezone

COPY 98-extra-config.ini /usr/local/etc/php/conf.d/

# Aliases
RUN echo 'alias sf="php bin/console"' >> ~/.bashrc && \
    echo 'alias ll="ls -l"' >> ~/.bashrc && \
    echo 'alias la="ls -A"' >> ~/.bashrc && \
    echo 'alias l="ls -CF"' >> ~/.bashrc

WORKDIR /var/www/symfony
## -- END php_base --

FROM php_base AS php_builder

RUN apk add --update --no-cache \
        curl \
        composer \
        nodejs npm \
    && rm -rf /var/cache/apk/* \
    && npm install --global yarn
## -- END php_builder --

FROM php_builder AS php_dev
COPY 99-extra-config-dev.ini /usr/local/etc/php/conf.d/

RUN apk add --update --no-cache \
        iputils \
        bash \
    && rm -rf /var/cache/apk/*

RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS} \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug \
  && apk del pcre-dev ${PHPIZE_DEPS}

RUN composer global require hirak/prestissimo
## -- END php_dev --
