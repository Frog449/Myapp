# ==============================================================================
# Dockerfile for CaffeBook PHP Backend on Render.com & Docker
# ==============================================================================
FROM php:8.2-apache

# Set metadata
LABEL maintainer="CaffeBook Team"
LABEL description="CaffeBook PHP Backend Server for Render Cloud Deployment"

# Install system packages & PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    mysqli \
    gd \
    zip \
    bcmath \
    opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable essential Apache modules
RUN a2enmod rewrite headers

# Set working directory to Apache document root
WORKDIR /var/www/html

# Copy all API backend files into the document root
COPY api/ /var/www/html/

# Copy the custom entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set appropriate directory ownership and permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# Expose HTTP port (Render dynamically assigns $PORT at runtime, e.g. 10000)
EXPOSE 80 10000

# Execute custom entrypoint script
ENTRYPOINT ["docker-entrypoint.sh"]
