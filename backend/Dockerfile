# VRide backend — Railway / Docker entry.
# Railway: create a service from this repo, set Root Directory to `backend`,
# expose HTTP (Railway sets PORT).
FROM composer:2 AS composer_stage
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.2-cli
WORKDIR /app
COPY --from=composer_stage /app/vendor ./vendor
COPY . .

ENV PORT=8080
CMD sh -c "exec php -S 0.0.0.0:\${PORT} -t public"
