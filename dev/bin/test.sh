#!/bin/sh

DEFAULT_PATH='app/code/local/SDM/'
BIN_PHPCS=`which phpcs`
BIN_PHPMD=`which phpmd`
ROOT_DIR=`echo $0 | sed 's/test\.sh//g'`"../.."

if [ "$1" = "help" ]; then
    echo "Runs integration tests"
    echo
    echo "Usage: ./dev/bin/test.sh <path>"
    echo "If no path is specified, the default '$DEFAULT_PATH' will be used"
    exit 0
elif [ ! "$1" = "" ]; then
    TEST_PATH=$1
else
    TEST_PATH=$DEFAULT_PATH
fi

COMMAND="$BIN_PHPCS -s --colors --standard=$ROOT_DIR/dev/standard/phpcs.xml $ROOT_DIR/$TEST_PATH"
echo
echo $COMMAND
echo
$COMMAND

COMMAND="$BIN_PHPMD $ROOT_DIR/$TEST_PATH text dev/standard/phpmd.xml "
echo
echo $COMMAND
echo
$COMMAND

