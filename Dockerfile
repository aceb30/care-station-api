
# Start from a modern PHP 8.2 base image
FROM php:8.2-fpm

# Set the working directory for the app
WORKDIR /var/www/html

# --- Install System Dependencies ---
# We need zip (for composer) and libpq-dev (for Postgres)
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# --- Install PHP Extensions ---
# Install the Postgres (pdo_pgsql) extension
RUN docker-php-ext-install pdo pdo_pgsql

# --- Install Composer ---
# Get the latest composer executable
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- Copy Application Files ---
# Copy composer files first to cache dependencies
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist

# Copy the rest of the application code
COPY . .

# --- Permissions ---
# Set the correct permissions for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# --- Run the App ---
# Expose port 8000
EXPOSE 8000

# Start the dev server. --host=0.0.0.0 makes it accessible to Docker
#CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]