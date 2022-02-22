#!/bin/sh

export OS_PCKG_INSTALL_CMD="apk add --no-cache py3-pip"
export OS_PCKG_PY_INSTALL="pip3 install pipdeptree pg8000 PyMySQL protobuf SQLAlchemy"

python3 "host/home/runner/work/cloud_py_api/cloud_py_api/$D_DIR/entrypoint.py" || exit 101
echo "Test OK."
exit 0
