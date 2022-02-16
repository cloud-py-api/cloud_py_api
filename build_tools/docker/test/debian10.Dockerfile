FROM debian:buster

COPY pyfrm ./

RUN set -ex && apt update && apt install python3.7-minimal && ls -la .

# Debian:
# 3.7(minimal,+pip) + standalone


