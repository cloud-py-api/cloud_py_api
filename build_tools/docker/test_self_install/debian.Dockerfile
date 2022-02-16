ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN set -ex && apt update && apt install python3-minimal -y && chmod +x /entrypoint.sh && ls -la /

CMD ["sh", "-c", "/entrypoint.sh"]

# COPY pyfrm ./pyfrm
# Debian:
# 3.7(minimal,+pip) + standalone


