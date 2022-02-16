ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

ENV SP_AMD64="https://github.com/indygreg/python-build-standalone/releases/download/20211017/cpython-3.10.0-x86_64-unknown-linux-gnu-lto-20211017T1616.tar.zst"
ENV SP_ARM64="https://github.com/indygreg/python-build-standalone/releases/download/20211017/cpython-3.10.0-aarch64-unknown-linux-gnu-lto-20211017T1616.tar.zst"

RUN set -ex && apt update && apt install -y python3-minimal zstd wget && chmod +x /entrypoint.sh && python3 -V
ARG TARGETARCH
RUN if [ "$TARGETARCH" = "amd64" ] ; then SP_URL=$SP_AMD64 ; else SP_URL=SP_ARM64 ; fi
ARG SP_URL
RUN wget -q --no-check-certificate -O standalone.zst $SP_URL && zstd -d standalone.zst && ls -la .

CMD ["sh", "-c", "/entrypoint.sh"]
