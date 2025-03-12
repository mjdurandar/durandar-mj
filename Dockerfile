# Use PHP 8.2 with FPM (FastCGI Process Manager) for better performance
FROM php:8.2-fpm

# Set up build arguments for a non-root user
ARG user=appuser
ARG uid=1000

# Install necessary system dependencies only
RUN apt update && apt install -y \
    git \
    curl \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && apt clean && rm -rf /var/lib/apt/lists/*

# Install only the required PHP extensions for Laravel
RUN docker-php-ext-install pdo_mysql mbstring bcmath gd

# Install Composer (dependency manager for PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set PHP upload limits (increase if necessary)
RUN echo "upload_max_filesize=32M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=32M" >> /usr/local/etc/php/conf.d/uploads.ini

# Create a new user for security
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

# Set the working directory to the Laravel app folder
WORKDIR /var/www

# Use non-root user for security
USER $user
