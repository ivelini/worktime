FROM php:8.3-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install main dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    ffmpeg libsm6 libxext6 \
    libicu-dev \
    libmcrypt-dev \
    zlib1g-dev \
    libxml2-dev \
    libonig-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev \
    libfreetype6 libfreetype6-dev \
    locales \
    pkg-config \
    sqlite3 libsqlite3-dev \
    libzip-dev zip unzip\
    git \
    curl \
    wget \
    gnupg \
    unixodbc \
    unixodbc-dev \
    odbcinst \
    odbcinst1debian2 \
    libodbc1 \
    libqt5sql5-odbc

# Install PECL and PEAR extensions
RUN pecl install \
    redis \
    sqlsrv \
    pdo_sqlsrv

RUN printf "; priority=20\nextension=sqlsrv.so\n" > /usr/local/etc/php/php.ini-development \
    printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /usr/local/etc/php/php.ini-development

RUN curl https://packages.microsoft.com/keys/microsoft.asc > /etc/apt/trusted.gpg.d/microsoft.asc \
  && curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list \
  && apt-get update \
  && ACCEPT_EULA=Y apt-get install -y msodbcsql17

# Enable PECL and PEAR extensions
RUN docker-php-ext-enable \
    redis \
    sqlsrv \
    pdo_sqlsrv

# Configure php extensions
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp

# Install php extensions
RUN docker-php-ext-install \
    gd \
    bcmath \
    calendar \
    curl \
    exif \
    iconv \
    intl \
    mbstring \
    opcache \
    pdo \
    pdo_pgsql \
    pdo_sqlite \
    pcntl \
    xml \
    zip

# Copy php.ini configurations
COPY php.ini /usr/local/etc/php/conf.d/40-custom.ini
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Cleanup dev dependencies
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && docker-php-source delete

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER $user

EXPOSE 9000

# The main purpose of a CMD is to provide defaults for an executing container. These defaults can include an executable,
# or they can omit the executable, in which case you must specify an ENTRYPOINT instruction as well.
CMD php-fpm
