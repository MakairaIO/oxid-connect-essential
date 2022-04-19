FROM marmaladegmbh/php:8.0

COPY tests/utils/composer.test.json /app/composer.json
COPY tests/utils/config.test.php /app/
COPY . /app/modules/oxid-connect-essential/
COPY  tests/utils/install-mysql-client /usr/bin/
COPY tests/utils/default-command.sh /
COPY tests/utils/generated-services-updater /app/bin/

WORKDIR /app

ENV PARTIAL_MODULE_PATHS=makaira/oxid-connect-essential

RUN install-mysql-client dev && \
    ln -snf /bin/composer2 /bin/composer && \
    /bin/composer install -nq && \
    echo "include __DIR__ . '/../config.test.php';" >> source/config.inc.php && \
    rm -rf app/modules/

CMD /default-command.sh
