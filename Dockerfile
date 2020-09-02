FROM yiisoftware/yii2-php:7.2-apache
RUN docker-php-ext-install zip
RUN docker-php-ext-install gd
RUN docker-php-ext-enable zip
RUN docker-php-ext-enable gd

RUN apt-get update
RUN apt-get install -y software-properties-common
RUN apt-get -y install libz-dev libmemcached-dev libmemcached11 libmemcachedutil2 build-essential
RUN pecl install memcached
RUN echo extension=memcached.so >> /usr/local/etc/php/conf.d/memcached.ini
RUN apt-get remove -y build-essential libmemcached-dev libz-dev
RUN apt-get autoremove -y
RUN apt-get clean
RUN rm -rf /tmp/pear

#RUN apt-get install php7.2-mbstring
# RUN apt-get install php7.2-xml
# RUN apt-get install php7.2-gd
# RUN apt-get install php7.2-zip
# RUN apt-get install php7.2-curl


# RUN chmod 2775 /app/data
# RUN find /app/data -type d -exec sudo chmod 2775 {} +
# RUN find /app/data -type d -exec sudo chmod 0664 {} +
