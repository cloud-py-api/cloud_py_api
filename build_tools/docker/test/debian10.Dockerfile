FROM debian:buster

RUN set -ex && apt update && apt install python3.7-minimal

# COPY
# Debian:
# 3.7(minimal,+pip) + standalone


