FROM docker.team23.de/docker/php:7.4

ADD ./tmp-symfony/var/cache /app/tmp-symfony/var/cache
RUN chown -R www-data:www-data /app/tmp-symfony/var/cache
RUN chmod -R g+w /app/tmp-symfony/var/cache
