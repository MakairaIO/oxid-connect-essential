FROM marmaladegmbh/php:7.4

COPY tests/utils/composer.test.json /app/composer.json
COPY tests/utils/config.test.php /app/
COPY tests/utils/php.test.ini /usr/local/etc/php/conf.d/zz-custom.ini
COPY . /app/modules/oxid-connect-essential/
COPY  tests/utils/install-mysql-client /usr/bin/
COPY tests/utils/run-tests /usr/local/bin/run-tests
COPY tests/utils/generated-services-updater /app/bin/

WORKDIR /app

ENV PARTIAL_MODULE_PATHS=makaira/oxid-connect-essential

RUN install-mysql-client dev && \
    apt-get install -y libmagickwand-6.q16-6 && \
    ln -snf /bin/composer2 /bin/composer && \
    /bin/composer install --no-progress -nq && \
    echo "include __DIR__ . '/../config.test.php';" >> source/config.inc.php && \
    apt-get autoremove -yqq --purge && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    . /app/modules/oxid-connect-essential/tests/utils/.env && \
    chown -R $USER_ID:$GROUP_ID . && \
    rm -f vendor/makaira/oxid-connect-essential && \
    rm -rf source/modules/makaira/oxid-connect-essential && \
    bin/generated-services-updater

ENTRYPOINT ["run-tests"]
CMD ["--unit"]
