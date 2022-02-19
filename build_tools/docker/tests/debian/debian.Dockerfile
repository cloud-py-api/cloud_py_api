ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN set -ex; \
    apt update && \
    apt install -y lsb-release apt-transport-https \
    ca-certificates wget curl git systemd

# INSTALL PYTHON (WITH PIP)
RUN set -ex; \
    apt install -y \
    python3 python3-pip python3-distutils zstd sudo \
    && chmod +x /entrypoint.sh && python3 -V

# UPGRADE PIP & INSTALL PYTEST
RUN set -ex && \
    python3 -m pip install -U pip && \
    python3 -m pip install pytest

# INSTALL PHP AND NECESSARY PHP EXTENSIONS
ARG PHP_VERSION
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" \
    | tee /etc/apt/sources.list.d/php.list && apt update
RUN set -ex && \
    apt install -y php$PHP_VERSION php$PHP_VERSION-dom php$PHP_VERSION-zip \
    php$PHP_VERSION-XMLWriter php$PHP_VERSION-XMLReader libxml2 \
    php$PHP_VERSION-mbstring php$PHP_VERSION-GD php$PHP_VERSION-SimpleXML \
    php$PHP_VERSION-curl
    # apt install php$PHP_VERSION-ctype, php$PHP_VERSION-curl, php$PHP_VERSION-dom, \
    # php$PHP_VERSION-filter, php$PHP_VERSION-hash, php$PHP_VERSION-json, \
    # php$PHP_VERSION-libxml, php$PHP_VERSION-mbstring, php$PHP_VERSION-openssl, \
    # php$PHP_VERSION-posix, php$PHP_VERSION-session, php$PHP_VERSION-SimpleXML, \
    # php$PHP_VERSION-XMLReader, php$PHP_VERSION-XMLWriter, php$PHP_VERSION-zip, \
    # php$PHP_VERSION-zlib, php$PHP_VERSION-bz2, php$PHP_VERSION-cli

# INSTALL COMPOSER
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN sudo php composer-setup.php --install-dir/usr/local/bin --filename=composer

# INSTALL NODEJS & NPM
RUN set -ex; \
    apt install -y nodejs npm && \
    npm --version && \
    npm install -g npm@latest && \
    node --version && \
    npm --version

# INSTALL PDO_MYSQL or PDO_PGSQL AND INIT NEXTCLOUD DB
ARG VER
ARG DB_TYPE
ARG NC_CREATE_USER_SQL
COPY $NC_CREATE_USER_SQL /create_user.sql

RUN wget https://repo.mysql.com//mysql-apt-config_0.8.18-1_all.deb && \
    apt install ./mysql-apt-config_0.8.18-1_all.deb -y && apt update

RUN set -ex; \
    DB_PKG=$(echo $DB_TYPE | sed 's/mysql/mysql-server/') && \
    DB_INIT=$(echo $DB_TYPE | sed 's/mysql/sudo mysql -u root -p/') && \
    DB_PKG=$(echo $DB_PKG | sed 's/pgsql/postgresql/') && \
    DB_INIT=$(echo $DB_INIT | sed 's/pgsql/sudo -u postgres psql/') && \
    apt install -y php$PHP_VERSION-$DB_TYPE $DB_PKG && \
    DB_SERVICE=$(echo $DB_TYPE | sed 's/mysql/mysql/') && \
    DB_SERVICE=$(echo $DB_TYPE | sed 's/pgsql/postgresql/') && \
    systemctl enable $DB_SERVICE && sudo service $DB_SERVICE start && \
    $DB_INIT < /create_user.sql

# INSTALL NEXTLOUD AND CONFIGURE FOR DEBUGGING
ARG NEXTCLOUD_VERSION
RUN set -ex; \
    git clone https://github.com/nextcloud/server.git --recursive --depth 1 -b $NEXTCLOUD_VERSION nextcloud \
    && php -f nextcloud/occ maintenance:install --database-host 127.0.0.1 \
    --database-name nextcloud --database-user nextcloud --database-pass nextcloud \
    --admin-user admin --admin-pass admin --database $DB_TYPE \
    && php -f nextcloud/occ config:system:set debug --type bool --value true

# INSTALL SERVERINFO APP
RUN git clone https://github.com/nextcloud/serverinfo.git nextcloud/apps/serverinfo \
    && php -f nextcloud/occ app:enable serverinfo

# INSTALL CLOUD_PY_API APP
RUN git clone https://github.com/bigcat88/cloud_py_api.git nextcloud/apps/cloud_py_api \
    && php -f nextcloud/occ app:enable cloud_py_api

CMD ["sh", "-c", "/entrypoint.sh"]
