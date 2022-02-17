ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

ENV SP_BASE_URL="https://github.com/indygreg/python-build-standalone/releases/download/20211017/cpython-3.10.0"
ENV SP_AMD64="$SP_BASE_URL-x86_64-unknown-linux-gnu-lto-20211017T1616.tar.zst"
ENV SP_ARM64="$SP_BASE_URL-aarch64-unknown-linux-gnu-lto-20211017T1616.tar.zst"
ENV WGET_CMD="wget -q --no-check-certificate -O standalone.tar.zst"

RUN set -ex && yum update -y && yum install -y python3.10 zstd wget && chmod +x /entrypoint.sh && python3 -V
ARG TARGETARCH
RUN if [ "$TARGETARCH" = "amd64" ] ; then $WGET_CMD $SP_AMD64 ; else $WGET_CMD $SP_ARM64 ; fi
RUN zstd -d standalone.tar.zst && tar xf standalone.tar && rm standalone.tar standalone.tar.zst
RUN mv python/install st_python && rm -rf python
# https://github.com/indygreg/python-build-standalone/issues/113
RUN yum install libxcrypt-compat
RUN ./st_python/bin/python3 -V

CMD ["sh", "-c", "/entrypoint.sh"]
