FROM alpine:3.8
LABEL maintainer="cudacoder"

EXPOSE 443 80 22
COPY fs /

RUN apk add --update --no-cache \
    tini \
    curl \
    nginx \
    openssh \
    supervisor \
    libmcrypt-dev \
    nginx-mod-http-geoip \
    php7 \
    php7-bcmath \
    php7-bz2 \
    php7-calendar \
    php7-ctype \
    php7-curl \
    php7-dom \
    php7-exif \
    php7-fileinfo \
    php7-fpm \
    php7-ftp \
    php7-gd \
    php7-gettext \
    php7-iconv \
	php7-imap \
    php7-intl \
    php7-json \
    php7-ldap \
	php7-mbstring \
    php7-mcrypt \
    php7-memcached \
    php7-mysqli \
	php7-mysqlnd \
	php-odbc \
    php7-opcache \
	php7-openssl \
    php7-pcntl \
    php7-pdo \
	php7-pdo_dblib \
    php7-pdo_mysql \
    php-pdo_odbc \
    php7-pdo_sqlite \
	php7-pear \
    php7-phar \
    php7-posix \
    php7-session \
    php7-shmop \
    php7-simplexml \
    php7-soap \
    php7-sockets \
    php7-sqlite3 \
    php7-sysvmsg \
    php7-sysvsem \
    php7-sysvshm \
    php7-tidy \
	php7-tokenizer \
    php7-wddx \
    php7-xml \
	php7-xmlreader \
    php7-xmlrpc \
    php7-xmlwriter \
	php7-xsl \
	php7-zip \
    php7-zlib \
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    && mkdir -p /var/log/supervisor

RUN apk add --update --no-cache --virtual .build-deps \
    gcc \
    make \
    autoconf \
    libc-dev \
    php7-dev \
    geoip-dev \
    && printf "\n" | pecl install mcrypt-1.0.1 \
    && printf "\n" | pecl install geoip-1.1.1 \
    && printf "\n" | pecl install igbinary \
    && printf "\n" | pecl install msgpack \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man/*

ENTRYPOINT ["/sbin/tini", "--"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]