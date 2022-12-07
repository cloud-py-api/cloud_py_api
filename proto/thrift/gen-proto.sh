#!/bin/sh

# Build core proto files for PHP and Python

thrift -r --gen php:server --gen py test.thrift
# thrift -r --gen php:server --gen py core.thrift
# thrift -r --gen php:server --gen py fs.thrift
# thrift -r --gen php:server --gen py db.thrift
# thrift -r --gen php:server --gen py service.thrift
