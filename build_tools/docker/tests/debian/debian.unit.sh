#!/bin/sh

ECHO_LINE_BREAK="echo -----------------------------------------------------"

cd nextcloud/apps/cloud_py_api

$ECHO_LINE_BREAK
echo "PHP UNIT TESTS START"
$ECHO_LINE_BREAK


composer install
composer test:unit

$ECHO_LINE_BREAK
echo "PHP UNIT TESTS END"
$ECHO_LINE_BREAK


$ECHO_LINE_BREAK
echo "JS UNIT TESTS START"
$ECHO_LINE_BREAK

npm install
npm run test

$ECHO_LINE_BREAK
echo "JS UNIT TESTS END"
$ECHO_LINE_BREAK


# $ECHO_LINE_BREAK
# echo "Python UNIT TESTS START"
# $ECHO_LINE_BREAK

# pytest

# $ECHO_LINE_BREAK
# echo "Python UNIT TESTS END"
# $ECHO_LINE_BREAK

exit 0
