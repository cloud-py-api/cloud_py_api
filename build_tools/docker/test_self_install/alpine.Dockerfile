ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN set -ex && apk update && apk --no-cache add python3 && ls -la . && chmod +x /entrypoint.sh && python3 -V && ls -la . && ls -la /bin

ENTRYPOINT ["/entrypoint.sh"]
