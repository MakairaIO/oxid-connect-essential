FROM marmaladegmbh/php:7.4

COPY tests/utils/composer.test.json /app/composer.json
COPY tests/utils/config.test.php /app/
COPY . /app/modules/oxid-connect-essential/
COPY  tests/utils/install-mysql-client /usr/bin/
COPY tests/utils/default-command.sh /
COPY tests/utils/generated-services-updater /app/bin/

WORKDIR /app

ENV PARTIAL_MODULE_PATHS=makaira/oxid-connect-essential

RUN install-mysql-client dev && \
    apt install -y libmagickwand-6.q16-6 && \
    ln -snf /bin/composer2 /bin/composer && \
    /bin/composer install -nq && \
    echo "include __DIR__ . '/../config.test.php';" >> source/config.inc.php && \
    rm -rf app/modules/ && \
    apt autoremove -yqq --purge && \
    apt clean && \
    rm -rf /var/lib/apt/lists/*


CMD /default-command.sh
