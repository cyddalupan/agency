FROM ubuntu:20.04
ENV DEBIAN_FRONTEND=noninteractive

# Install Apache and PHP dependencies
RUN apt update && apt install -y \
    apache2 \
    software-properties-common \
    wget \
    libapache2-mod-fcgid \
    libxml2-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libjpeg-dev \
    libpng-dev \
    libgd-dev \
    libmysqlclient-dev \
    libicu-dev \
    libonig-dev \
    libreadline-dev \
    libzip-dev \
    build-essential \
    autoconf \
    pkg-config \
    zlib1g-dev \
    php7.4-fpm \
    php7.4-mysql \
    php7.4-gd \
    php7.4-xml \
    php7.4-mbstring \
    php7.4-json \
    php7.4-curl \
    php7.4-zip

# Configure PHP-FPM
COPY php-fpm.conf /etc/php/7.4/fpm/php-fpm.conf
COPY www.conf /etc/php/7.4/fpm/pool.d/www.conf

# Start PHP-FPM service
RUN echo "#!/bin/bash
/usr/sbin/php-fpm7.4 -F &
apache2ctl -D FOREGROUND" > /usr/local/bin/start-apache-php \
    && chmod +x /usr/local/bin/start-apache-php

# Adjust CMD to start both Apache and PHP-FPM
CMD ["bash", "-xc", "/usr/local/bin/start-apache-php"]

# Enable Apache modules
RUN a2enmod proxy_fcgi setenvif rewrite

# Copy project files
# COPY . /var/www/html # Removed for volume mounting

# Remove default Apache site and copy custom config
RUN rm /etc/apache2/sites-enabled/000-default.conf
COPY docker-apache.conf /etc/apache2/sites-available/helloworld.conf
RUN a2ensite helloworld.conf

# Expose port 80
EXPOSE 80
