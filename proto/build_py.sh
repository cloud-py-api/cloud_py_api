#!/bin/sh
#Build core proto commands for python. Run from root of project.
SCRIPT_DIR=$(dirname "$0")
cd "$SCRIPT_DIR" || exit
cd .. || exit
python3 -m grpc_tools.protoc -Iproto/ --python_out=pyfrm/py_proto proto/*.proto
python3 -m grpc_tools.protoc -Iproto/ --grpc_python_out=pyfrm/py_proto proto/service.proto
cd pyfrm/py_proto && 2to3 -n -w ./* && cd ../..
