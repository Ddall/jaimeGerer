# -- NGINX
FROM nginx:1.17-alpine AS nginx_base

# fix missing www-data user in alpine image
RUN set -x ; \
    addgroup -g 82 -S www-data ; \
    adduser -u 82 -D -S -G www-data www-data && exit 0 ; exit 1

RUN rm /etc/nginx/nginx.conf && rm /etc/nginx/conf.d/default.conf \
    && echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf \
    && echo 'alias ll="ls -l"' >> ~/.bashrc \
    && echo 'alias la="ls -A"' >> ~/.bashrc \
    && echo 'alias l="ls -CF"' >> ~/.bashrc

ADD symfony.conf /etc/nginx/conf.d/
ADD nginx.conf /etc/nginx/nginx.conf
## -- END nginx_base --


FROM nginx_base AS nginx_dev
RUN apk add iputils
## -- END nginx_dev --

FROM nginx_base AS nginx_prod
COPY --from=php_substage_prod --chown=www-data:www-data /var/www/symfony/public/ /var/www/symfony/public/
## -- END nginx_prod --
