ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

ENV SP_BASE_URL="https://github.com/indygreg/python-build-standalone/releases/download/20211017/cpython-3.10.0"
ENV SP_AMD64="$SP_BASE_URL-x86_64-unknown-linux-gnu-lto-20211017T1616.tar.zst"
ENV SP_ARM64="$SP_BASE_URL-aarch64-unknown-linux-gnu-lto-20211017T1616.tar.zst"
ENV WGET_CMD_PY="wget -q --no-check-certificate -O standalone.tar.zst"

RUN mkdir /cloud_py_api
RUN set -ex && yum update -y && yum install -y \
    wget \
    && chmod +x /entrypoint.sh
ARG TARGETARCH
RUN if [ "$TARGETARCH" = "amd64" ] ; then $WGET_CMD_PY $SP_AMD64 ; else $WGET_CMD_PY $SP_ARM64 ; fi
ENV ZST_URL_AMD="https://download-ib01.fedoraproject.org/pub/epel/7/x86_64/Packages/z/zstd-1.5.2-1.el7.x86_64.rpm"
ENV ZST_URL_ARM="https://download-ib01.fedoraproject.org/pub/epel/7/aarch64/Packages/z/zstd-1.4.2-1.el7.aarch64.rpm"
ENV WGET_CMD_ZST="wget -q --no-check-certificate -O zstd.rpm"
RUN if [ "$TARGETARCH" = "amd64" ] ; then $WGET_CMD_ZST $ZST_URL_AMD ; else $WGET_CMD_ZST $ZST_URL_ARM; fi
RUN yum localinstall -y zstd.rpm && rm zstd.rpm
RUN zstd -d standalone.tar.zst && tar xf standalone.tar && rm standalone.tar standalone.tar.zst
RUN mv python/install /cloud_py_api/st_python && rm -rf python
RUN ./cloud_py_api/st_python/bin/python3 -V

CMD ["sh", "-c", "/entrypoint.sh"]
