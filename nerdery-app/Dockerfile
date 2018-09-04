FROM php:5.6-apache
# COPY nerdery-app/ /var/www/html/

RUN docker-php-ext-install mysql mysqli pdo_mysql
RUN docker-php-ext-enable mysql mysqli pdo_mysql

#RUN touch /tmp/mysql.sock && chmod +755 /tmp/mysql.sock && cd /var && mkdir mysql && cd mysql && ln -s /tmp/mysql.sock mysql.sock
