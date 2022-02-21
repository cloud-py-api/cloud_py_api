#!/bin/sh

/st_python/bin/python3 -m pip install pytest pytest-benchmark || exit 105

git clone https://github.com/mrecachinas/hexhamming.git || exit 106
/st_python/bin/python3 -m pytest hexhamming || exit 106
git clone https://github.com/bigcat88/pillow_heif.git || exit 106
/st_python/bin/python3 -m pytest pillow_heif || exit 106

echo "TEST OK" && cp /*.tar.zst /host/home/runner/work/cloud_py_api/cloud_py_api/st_pythons/
