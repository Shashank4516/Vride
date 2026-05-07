## Root Dockerfile for Railway.
## Runs the PHP app from ./backend/public while keeping ./frontend and /img available (symlink targets).
FROM composer:2 AS composer_stage
WORKDIR /app/backend
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.2-cli
WORKDIR /app
COPY --from=composer_stage /app/backend/vendor ./backend/vendor
COPY . .

ENV PORT=8080
CMD sh -c "exec php -S 0.0.0.0:${PORT} -t backend/public"
