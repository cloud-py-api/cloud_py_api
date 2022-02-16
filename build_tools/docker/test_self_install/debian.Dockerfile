ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN set -ex && apt update && apt install python3-minimal -y && python3 -V && chmod +x /entrypoint.sh

CMD ["sh", "-c", "/entrypoint.sh"]
