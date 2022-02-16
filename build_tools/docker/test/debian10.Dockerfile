FROM debian:buster

COPY tests/python/install/debian.sh /entrypoint.sh

RUN set -ex && apt update && apt install python3-minimal -y && chmod +x /entrypoint.sh && ls -la /

CMD ["/entrypoint.sh"]

# COPY pyfrm ./pyfrm
# Debian:
# 3.7(minimal,+pip) + standalone


