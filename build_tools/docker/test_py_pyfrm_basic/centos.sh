#!/bin/sh

ECHO_LINE_BREAK="echo -----------------------------------------------------"
FRM_CONFIG="%7B%22loglvl%22%3A%22DEBUG%22%2C%22frmAppData%22%3A%22%2Fcloud_py_api%22%2C%22dbConfig%22%3A%7B%22dbType%22%3A%22pgsql%22%2C%22dbUser%22%3A%22%22%2C%22dbName%22%3A%22nextcloud%22%7D%7D"
DUMP_FOLDERS="ls -la /cloud_py_api /cloud_py_api/.local /cloud_py_api/st_python /var/www"
CLEAR_FOLDERS="rm -rf /cloud_py_api/.local"
AS_USER="sudo -u apache"
CLONED_PY_PATH="/cloud_py_api/st_python_upd_test"
CLONED_PY_INTP="$CLONED_PY_PATH/bin/python3"
CLONE_PY_ST="$AS_USER cp -r /cloud_py_api/st_python $CLONED_PY_PATH"
INSTALL_OLD_PCKG="$CLONED_PY_INTP -m pip install pipdeptree==2.2.0 pg8000==1.23.0 PyMySQL==1.0.1 protobuf==3.19.1"
CLONE_DUMP_FOLDERS="ls -la /cloud_py_api /cloud_py_api/.local /cloud_py_api/st_python_upd_test /var/www"

cp -r host/home/runner/work/cloud_py_api/cloud_py_api/pyfrm /
if [ -d "/cloud_py_api/st_python" ]; then
  $CLONE_PY_ST
  $ECHO_LINE_BREAK && echo "Standalone python(user): checking." && $ECHO_LINE_BREAK
  if ! $AS_USER /cloud_py_api/st_python/bin/python3 /pyfrm/install.py --config "$FRM_CONFIG" --check --target framework --dev; then
    $DUMP_FOLDERS
    $ECHO_LINE_BREAK && echo "Standalone python(user): installing" && $ECHO_LINE_BREAK
    $AS_USER /cloud_py_api/st_python/bin/python3 /pyfrm/install.py --config "$FRM_CONFIG" --install --target framework --dev || exit 101
  fi
  $DUMP_FOLDERS
  $ECHO_LINE_BREAK && echo "Standalone python(user): updating" && $ECHO_LINE_BREAK
  $AS_USER /cloud_py_api/st_python/bin/python3 /pyfrm/install.py --config "$FRM_CONFIG" --update --target framework --dev || exit 101
  $DUMP_FOLDERS
  $CLEAR_FOLDERS
  $AS_USER "$INSTALL_OLD_PCKG"
  $ECHO_LINE_BREAK && echo "Standalone(clone) python(user): checking." && $ECHO_LINE_BREAK
  if ! $AS_USER $CLONED_PY_INTP /pyfrm/install.py --config "$FRM_CONFIG" --check --target framework --dev; then
    $CLONE_DUMP_FOLDERS
    $ECHO_LINE_BREAK && echo "Standalone(clone) python(user): installing" && $ECHO_LINE_BREAK
    $AS_USER $CLONED_PY_INTP /pyfrm/install.py --config "$FRM_CONFIG" --install --target framework --dev || exit 101
  fi
  $CLONE_DUMP_FOLDERS
  $ECHO_LINE_BREAK && echo "Standalone(clone) python(user): updating" && $ECHO_LINE_BREAK
  $AS_USER $CLONED_PY_INTP /pyfrm/install.py --config "$FRM_CONFIG" --update --target framework --dev || exit 101
  $CLONE_DUMP_FOLDERS
  $CLEAR_FOLDERS
else
  echo "ERROR! System or Standalone python not found. Test failed." && exit 102
fi
echo "Test OK."
exit 0
