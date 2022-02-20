ARG BASE_IMAGE
FROM $BASE_IMAGE

ENV ZST_URL_AMD="https://download-ib01.fedoraproject.org/pub/epel/7/x86_64/Packages/z/zstd-1.5.2-1.el7.x86_64.rpm"
ENV ZST_URL_ARM="https://download-ib01.fedoraproject.org/pub/epel/7/aarch64/Packages/z/zstd-1.4.2-1.el7.aarch64.rpm"
ARG TARGETARCH
RUN wget -q --no-check-certificate -O zstd.rpm \
    echo $(echo $TARGETARCH | sed 's@amd64@'"$ZST_URL_AMD"'@' | sed 's@arm64@'"$ZST_URL_ARM"'@') && \
    yum localinstall -y zstd.rpm && rm zstd.rpm

RUN yum update -y && yum install -y \
    sudo \
    && chmod +x /entrypoint.sh

COPY standalone.tar.zst /standalone.tar.zst
COPY LICENSE /LICENSE
ARG ADD_PACKAGES
RUN echo $ADD_PACKAGES > requirements.txt

RUN zstd -d standalone.tar.zst && tar xf standalone.tar && rm standalone.tar standalone.tar.zst
RUN mv python/install /st_python && mv python/licenses /st_python/licenses && mv /LICENSE /st_python/licenses/ \
    && rm -rf python
RUN /st_python/bin/python3 -m pip install --cache-dir /tmp --upgrade pip
RUN /st_python/bin/python3 -m pip install --cache-dir /tmp -r requirements.txt
ARG OUTPUT_NAME
RUN tar -cvf st_python.tar /st_python && zstd -15 /st_python -o $OUT_NAME

CMD ["sh", "-c"]
