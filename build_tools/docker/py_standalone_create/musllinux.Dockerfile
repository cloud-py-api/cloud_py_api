ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN apk update && apk --no-cache add \
    zstd sudo \
    && chmod +x /entrypoint.sh

COPY standalone.tar.zst /standalone.tar.zst
COPY LICENSE /LICENSE

RUN zstd -d standalone.tar.zst && tar xf standalone.tar && rm standalone.tar standalone.tar.zst
RUN mv python/install /st_python && mv python/licenses /st_python/licenses && mv /LICENSE /st_python/licenses/ \
    && rm -rf python
RUN /st_python/bin/python3 -m pip install --cache-dir /tmp --upgrade pip
ARG ADD_PACKAGES
RUN /st_python/bin/python3 -m pip install --cache-dir /tmp $ADD_PACKAGES
ARG OUTPUT_NAME
RUN tar -cvf st_python.tar /st_python && zstd -15 /st_python -o $OUT_NAME

CMD ["sh", "-c", "/entrypoint.sh"]
