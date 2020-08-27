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

# RUN apt-get update && apt-get install -y \
#     libpq-dev \
#     libmemcached-dev \
#     curl

# # ... 

# # Install Memcached for php 7
# RUN git clone -b php7 https://github.com/php-memcached-dev/php-memcached /usr/src/php/ext/memcached \
#     && docker-php-ext-configure /usr/src/php/ext/memcached \
#     --disable-memcached-sasl \
#     && docker-php-ext-install /usr/src/php/ext/memcached \
#     && rm -rf /usr/src/php/ext/memcached