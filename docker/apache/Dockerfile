# from https://www.drupal.org/docs/8/system-requirements/drupal-8-php-requirements
FROM drupal:9-apache
# TODO switch to buster once https://github.com/docker-library/php/issues/865 is resolved in a clean way (either in the PHP image or in PHP itself)

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -

# install the PHP extensions we need
RUN apt-get update \
	&& apt-get install -y git mariadb-client wget nano pv unzip nodejs rsync\
	&& rm -rf /var/lib/apt/lists/*

# install drush launcher, from https://github.com/drush-ops/drush-launcher#installation---phar,
RUN composer require drush/drush \
	&& wget -O drush.phar https://github.com/drush-ops/drush-launcher/releases/latest/download/drush.phar \
	&& chmod +x drush.phar \
	&& mv drush.phar /usr/local/bin/drush \
	&& drush self-update
# && drush init

# install Xdebug, from https://xdebug.org/docs/install
RUN pecl install xdebug \
	&& docker-php-ext-enable xdebug

# enable Xdebug remote debugging
RUN { \
	echo 'xdebug.mode=debug'; \
	echo 'xdebug.discover_client_host=true'; \
	echo 'xdebug.client_host=host.docker.internal'; \
	echo 'xdebug.start_with_request=yes'; \
	echo 'memory_limit = 2048M'; \
	echo 'error_log = /var/log/php_error.log'; \
	echo 'xdebug.log = /var/log/xdebug.log'; \
	} >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN { \
	echo 'max_execution_time = 120'; \
	} >> /usr/local/etc/php/php.ini

# Node modules
RUN npm i -g gulp-cli

# install phpredis extension
ENV PHPREDIS_VERSION 5.3.4
RUN curl -L -o /tmp/redis.tar.gz https://github.com/phpredis/phpredis/archive/$PHPREDIS_VERSION.tar.gz \
	&& tar xfz /tmp/redis.tar.gz \
	&& rm -r /tmp/redis.tar.gz \
	&& mkdir /usr/src/php/ext -p \
	&& mv phpredis-$PHPREDIS_VERSION /usr/src/php/ext/redis \
	&& docker-php-ext-install redis

# install and config mhsendmail for mailhog, from https://devilbox.readthedocs.io/en/latest/custom-container/enable-mailhog.html#tl-dr
RUN curl -fSL "https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64" -o mhsendmail \
	&& chmod +x mhsendmail \
	&& mv mhsendmail /usr/local/bin/ \
	&& echo "sendmail_path = '/usr/local/bin/mhsendmail --smtp-addr=\"mailhog:1025\"'" >> /usr/local/etc/php/conf.d/mailhog.ini

# preemptively creates log files, and give them to www-data
RUN cd /var/log \
	&& touch php_error.log xdebug.log \
	&& chown 1000:1000 php_error.log xdebug.log

ENV TERM xterm
