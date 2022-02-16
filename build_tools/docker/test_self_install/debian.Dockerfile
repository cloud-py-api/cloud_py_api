ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

RUN set -ex && apt update && apt install -y python3-minimal zstd wget && chmod +x /entrypoint.sh && python3 -V \


CMD ["sh", "-c", "/entrypoint.sh"]

# zstd -d
# https://github.com/indygreg/python-build-standalone/releases/download/20211017/cpython-3.10.0-x86_64-unknown-linux-gnu-lto-20211017T1616.tar.zst
# https://github.com/indygreg/python-build-standalone/releases/download/20211017/cpython-3.10.0-aarch64-unknown-linux-gnu-lto-20211017T1616.tar.zst

