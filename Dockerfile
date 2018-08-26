FROM alterway/php:5.4-apache
# COPY nerdery-app/ /var/www/html/


RUN apt-get update && apt-get install -y mysql-client
RUN docker-php-ext-enable mysql
RUN docker-php-ext-enable mysqli
