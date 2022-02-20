ARG BASE_IMAGE
FROM $BASE_IMAGE

ARG ENTRY_POINT
COPY $ENTRY_POINT /entrypoint.sh

ENV ZST_URL_AMD="https://download-ib01.fedoraproject.org/pub/epel/7/x86_64/Packages/z/zstd-1.5.2-1.el7.x86_64.rpm"
ENV ZST_URL_ARM="https://download-ib01.fedoraproject.org/pub/epel/7/aarch64/Packages/z/zstd-1.4.2-1.el7.aarch64.rpm"
ARG TARGETARCH
RUN wget -q --no-check-certificate -O zstd.rpm \
    $(echo $(echo $TARGETARCH | sed 's@amd64@'"$ZST_URL_AMD"'@' | sed 's@arm64@'"$ZST_URL_ARM"'@')) && \
    yum localinstall -y zstd.rpm && rm zstd.rpm

RUN mkdir /cloud_py_api
RUN set -ex && yum update -y && yum install -y \
    wget sudo httpd \
    && chmod +x /entrypoint.sh
ARG SP_URL
RUN wget -q --no-check-certificate -O standalone.tar.zst $SP_URL
RUN zstd -d standalone.tar.zst && tar xf standalone.tar && rm standalone.tar standalone.tar.zst
RUN mv python/install /cloud_py_api/st_python && rm -rf python && chown -R apache:apache /cloud_py_api
RUN ./cloud_py_api/st_python/bin/python3 -V

CMD ["sh", "-c", "/entrypoint.sh"]
