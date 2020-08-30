FROM yiisoftware/yii2-php:7.2-apache
RUN docker-php-ext-install zip
RUN docker-php-ext-install gd
RUN docker-php-ext-enable zip
RUN docker-php-ext-enable gd


RUN apt-get -y install libz-dev libmemcached-dev libmemcached11 libmemcachedutil2 build-essential
&& pecl install memcached
&& echo extension=memcached.so >> /usr/local/etc/php/conf.d/memcached.ini
&& apt-get remove -y build-essential libmemcached-dev libz-dev
&& apt-get autoremove -y
&& apt-get clean
&& rm -rf /tmp/pear


RUN chmod 2775 /app/data
RUN find /app/data -type d -exec sudo chmod 2775 {} +
RUN find /app/data -type d -exec sudo chmod 0664 {} +
