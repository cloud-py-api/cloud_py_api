#!/bin/sh

ECHO_LINE_BREAK="echo -----------------------------------------------------"
FRM_CONFIG="%7B%22loglvl%22%3A%22DEBUG%22%2C%22frmAppData%22%3A%22%2Fcloud_py_api%22%2C%22dbConfig%22%3A%7B%22dbType%22%3A%22pgsql%22%2C%22dbUser%22%3A%22%22%2C%22dbName%22%3A%22nextcloud%22%7D%7D"
DUMP_FOLDERS="ls -la /cloud_py_api /cloud_py_api/.local /cloud_py_api/st_python /var/www"
CLEAR_FOLDERS="rm -rf /cloud_py_api/.local"
AS_USER="sudo -u www-data"
INSTALL_PIP="apt install python3-pip"
INSTALL_PACKAGES="pip3 install pipdeptree pg8000 PyMySQL protobuf SQLAlchemy"

cp -r host/home/runner/work/cloud_py_api/cloud_py_api/pyfrm /
if [ -d "/cloud_py_api/st_python" ]; then
  $ECHO_LINE_BREAK && echo "Standalone python(user): checking." && $ECHO_LINE_BREAK
  if ! $AS_USER /cloud_py_api/st_python/bin/python3 /pyfrm/install.py --config "$FRM_CONFIG" --check --target framework --dev; then
    $DUMP_FOLDERS
    $ECHO_LINE_BREAK && echo "Standalone python(user): installing" && $ECHO_LINE_BREAK
    $AS_USER /cloud_py_api/st_python/bin/python3 /pyfrm/install.py --config "$FRM_CONFIG" --install --target framework --dev || exit 101
  fi
  $DUMP_FOLDERS
  $CLEAR_FOLDERS
elif ! python3 -V; then
  echo "ERROR! System or Standalone python not found. Test failed." && exit 102
fi
if python3 -V; then
  $ECHO_LINE_BREAK && echo "Sys python(user): checking." && $ECHO_LINE_BREAK
  if ! $AS_USER python3 /pyfrm/install.py --config "$FRM_CONFIG" --check --target framework --dev; then
    $DUMP_FOLDERS
    $ECHO_LINE_BREAK && echo "Sys python(user): installing." && $ECHO_LINE_BREAK
    $AS_USER python3 /pyfrm/install.py --config "$FRM_CONFIG" --install --target framework --dev || exit 101
  fi
  $DUMP_FOLDERS
  $CLEAR_FOLDERS
  $ECHO_LINE_BREAK && echo "Sys python(root): checking." && $ECHO_LINE_BREAK
  if ! python3 /pyfrm/install.py --config "$FRM_CONFIG" --check --target framework --dev; then
    $DUMP_FOLDERS
    $ECHO_LINE_BREAK && echo "Sys python(root): installing." && $ECHO_LINE_BREAK
    python3 /pyfrm/install.py --config "$FRM_CONFIG" --install --target framework --dev || exit 101
  fi
  $DUMP_FOLDERS
  $CLEAR_FOLDERS
  $ECHO_LINE_BREAK && echo "Installing packages globally" && $ECHO_LINE_BREAK
  $INSTALL_PIP && $INSTALL_PACKAGES
  $ECHO_LINE_BREAK && echo "Sys python w packages(user): checking." && $ECHO_LINE_BREAK
  if ! $AS_USER python3 /pyfrm/install.py --config "$FRM_CONFIG" --check --target framework --dev; then
    $DUMP_FOLDERS
    $ECHO_LINE_BREAK && echo "Sys python w packages(user): installing." && $ECHO_LINE_BREAK
    $AS_USER python3 /pyfrm/install.py --config "$FRM_CONFIG" --install --target framework --dev || exit 101
  fi
  $DUMP_FOLDERS
  $CLEAR_FOLDERS
fi
echo "Test OK."
exit 0
