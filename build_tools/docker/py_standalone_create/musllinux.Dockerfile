ARG BASE_IMAGE
FROM $BASE_IMAGE

RUN apk update && apk --no-cache add \
    zstd sudo \
    && chmod +x /entrypoint.sh

COPY standalone.tar.zst /standalone.tar.zst
COPY LICENSE /LICENSE
ARG ADD_PACKAGES
RUN echo $ADD_PACKAGES > requirements.txt

RUN zstd -d standalone.tar.zst && tar xf standalone.tar && rm standalone.tar standalone.tar.zst
RUN mv python/install /st_python && mv python/licenses /st_python/licenses && mv /LICENSE /st_python/licenses/ \
    && rm -rf python
RUN /st_python/bin/python3 -m pip install --cache-dir /tmp --upgrade pip
RUN /st_python/bin/python3 -m pip install --cache-dir /tmp -r requirements.txt
ARG OUTPUT_NAME
RUN tar -cvf st_python.tar /st_python && zstd -15 /st_python -o $OUT_NAME

CMD ["sh", "-c"]
