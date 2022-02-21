ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

# https://github.com/indygreg/python-build-standalone/issues/113
RUN yum -y install libxcrypt-compat

RUN set -ex && yum update -y && yum install -y \
    python3.10 zstd wget sudo httpd \
    && chmod +x /entrypoint.sh && python3 -V
ARG SP_URL
RUN mkdir /cloud_py_api && cd /cloud_py_api && wget -q --no-check-certificate -O standalone.tar.zst $SP_URL && \
    zstd -d standalone.tar.zst && tar xf standalone.tar && rm standalone.tar standalone.tar.zst && \
    chown -R apache:apache /cloud_py_api && ./st_python/bin/python3 -V

CMD ["sh", "-c", "/entrypoint.sh"]
