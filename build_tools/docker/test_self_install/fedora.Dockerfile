ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN set -ex && yum update && yum install -y -q python3.10 && chmod +x /entrypoint.sh && python3 -V

CMD ["sh", "-c", "/entrypoint.sh"]
