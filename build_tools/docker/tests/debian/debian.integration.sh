#!/bin/sh

ECHO_LINE_BREAK="echo -----------------------------------------------------"

$ECHO_LINE_BREAK
echo "PHP INTEGRATION TESTS START"
$ECHO_LINE_BREAK

cd nextcloud/apps/cloud_py_api
composer install
composer test:integration

$ECHO_LINE_BREAK
echo "PHP INTEGRATION TESTS END"
$ECHO_LINE_BREAK

exit 0
