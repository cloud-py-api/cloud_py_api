ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN mkdir /cloud_py_api
RUN set -ex && apk update && apk \
    --no-cache add python3 zstd wget apache2 sudo \
    py3-cffi  \
    && chmod +x /entrypoint.sh && python3 -V
RUN chown -R apache:apache /cloud_py_api

CMD ["sh", "-c", "/entrypoint.sh"]
