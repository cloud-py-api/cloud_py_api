ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN mkdir /cloud_py_api
RUN set -ex && yum update -y && yum install -y \
    tar python3 zstd wget sudo httpd \
    && chmod +x /entrypoint.sh && python3 -V
ARG SP_URL
RUN wget -q --no-check-certificate -O standalone.tar.zst $SP_URL
RUN zstd -d standalone.tar.zst && tar xf standalone.tar && rm standalone.tar standalone.tar.zst
RUN mv python/install /cloud_py_api/st_python && rm -rf python && chown -R apache:apache /cloud_py_api
RUN ./cloud_py_api/st_python/bin/python3 -V

CMD ["sh", "-c", "/entrypoint.sh"]
