FROM ubuntu:22.04

ARG WWWGROUP
WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install base dependencies
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    git \
    unzip

# Add PHP repository
RUN add-apt-repository ppa:ondrej/php -y && \
    apt-get update

# Install PHP-FPM and required extensions
RUN apt-get install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-sqlite3 \
    php8.2-gd \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-redis \
    nginx

# Configure PHP-FPM
RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.2/fpm/php.ini && \
    sed -i 's/listen = \/run\/php\/php8.2-fpm.sock/listen = 9000/' /etc/php/8.2/fpm/pool.d/www.conf

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Create user and set permissions
RUN groupadd --force -g $WWWGROUP sail && \
    useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail && \
    chown -R sail:sail /var/www/html

# Copy configuration files
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/php/php.ini /etc/php/8.2/fpm/conf.d/99-sail.ini

# Entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

USER sail

ENTRYPOINT ["entrypoint.sh"]
