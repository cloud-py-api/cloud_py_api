FROM debian:buster

COPY pyfrm ./

RUN set -ex && apt update && apt install python3.7-minimal

# Debian:
# 3.7(minimal,+pip) + standalone


