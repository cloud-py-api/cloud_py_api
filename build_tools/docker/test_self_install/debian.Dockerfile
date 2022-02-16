ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

ENV SP_AMD64="https://github.com/indygreg/python-build-standalone/releases/download/20211017/cpython-3.10.0-x86_64-unknown-linux-gnu-lto-20211017T1616.tar.zst"
ENV SP_ARM64="https://github.com/indygreg/python-build-standalone/releases/download/20211017/cpython-3.10.0-aarch64-unknown-linux-gnu-lto-20211017T1616.tar.zst"

RUN set -ex && apt update && apt install -y python3-minimal zstd wget && chmod +x /entrypoint.sh && python3 -V
ARG TARGETARCH
RUN if [ "$TARGETARCH" = "linux/amd64" ] ; then echo "see $TARGETARCH" ; else echo "not see $TARGETARCH" ; fi
ARG TARGETPLATFORM
ARG TARGETVARIANT
ARG BUILDPLATFORM
ARG BUILDPLATFORM
RUN echo $TARGETPLATFORM $TARGETVARIANT $BUILDPLATFORM $BUILDARCH

CMD ["sh", "-c", "/entrypoint.sh"]

#
#
#    wget -q --no-check-certificate -O "standalone.zstd"

# zstd -d
#

