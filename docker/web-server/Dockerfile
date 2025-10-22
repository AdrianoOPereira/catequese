
FROM php:7.4-apache

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libzip-dev \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY server.crt /etc/apache2/ssl/server.crt
COPY server.key /etc/apache2/ssl/server.key
COPY dev.conf /etc/apache2/sites-enabled/dev.conf

# Extensões PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql zip \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# Instala Xdebug 2.9.8 (compatível com PHP 7.4)
RUN pecl install xdebug-2.9.8 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=VSCODE" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Ativa módulos Apache
RUN a2enmod rewrite \
    && a2enmod ssl

# Configura PHP ini
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# Se quiser modo dev, comente a linha de cima e descomente a de baixo:
# RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"


# --- Ajuste para Railway ---

# Configura PHP ini
# RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# Se quiser modo dev, comente a linha de cima e descomente a de baixo:
# RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"


# --- Ajuste para Railway ---

# Expõe a porta 8080 (a que o Railway espera)
EXPOSE 8080

# Altera a configuração do Apache para escutar na porta 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/:80/:8080/g' /etc/apache2/sites-enabled/000-default.conf || true \
    && sed -i 's/:80/:8080/g' /etc/apache2/sites-enabled/dev.conf || true

# Define a variável de ambiente PORT (boa prática)
ENV PORT=8080

# Inicia o Apache em primeiro plano (importante para containers no Railway)
CMD ["apache2-foreground"]


# Define o ServerName para evitar warnings do Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
