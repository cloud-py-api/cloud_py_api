FROM debian:buster

COPY pyfrm ./pyfrm
COPY tests/python/install/debian.sh ./entrypoint.sh

RUN set -ex && apt update && apt install python3-minimal -y && ./entrypoint.sh

# Debian:
# 3.7(minimal,+pip) + standalone


