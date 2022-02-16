ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN set -ex && apk update && apk --no-cache add python3 && chmod +x /entrypoint.sh && python3 -V && ls -la . && ls -la /bin

CMD ["sh", "-c", "/entrypoint.sh"]
