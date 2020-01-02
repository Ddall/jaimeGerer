# See https://github.com/docker-library/php/blob/master/7.1/fpm/Dockerfile
FROM alterway/php:5.6-fpm
RUN apt-get update && apt-get install zip unzip

RUN docker-php-ext-install exif gd soap json curl pdo pdo_mysql
RUN docker-php-ext-enable ftp intl mbstring mcrypt mysql zip
ADD config/99-extra-config.ini /usr/local/etc/php/conf.d/

WORKDIR /var/www/symfony
