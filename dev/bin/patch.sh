#!/bin/sh

DEFAULT_PATH='app/code/local/SDM/'
BIN_PATCH=`which patch`
BIN_PHPCS=`which phpcs`
BIN_PERL=`which perl`
ROOT_DIR=`echo $0 | sed 's/patch\.sh//g'`"../.."
PATCH_FILE=patch.diff

if [ "$1" = "help" ]; then
    echo "Generates phpcs patch"
    echo
    echo "Usage: ./dev/bin/patch.sh <path>"
    echo "If no path is specified, the default '$DEFAULT_PATH' will be used"
    exit 0
elif [ ! "$1" = "" ]; then
    TEST_PATH=$1
else
    TEST_PATH=$DEFAULT_PATH
fi

COMMAND="$BIN_PHPCS --report-diff=$ROOT_DIR/$PATCH_FILE --encoding=utf-8 --standard=$ROOT_DIR/dev/standard/phpcs.xml $ROOT_DIR/$TEST_PATH"
echo
echo "Generating patch, please wait..."
echo $COMMAND
$COMMAND

if [ $? -ne 1 ]; then
    echo "Failed to create patch"
    exit 1
fi

$BIN_PERL -pi -e 's/^(.+)---/$1\n---/g' $ROOT_DIR/$PATCH_FILE

echo 

read -p "Patch $PATCH_FILE has been created.  Would you like to apply it? [Y/n] " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    exit 1
fi

COMMAND="$BIN_PATCH -p0 -ui $ROOT_DIR/$PATCH_FILE"
echo
echo "Patching..."
echo $COMMAND
$COMMAND

rm -f $ROOT_DIR/$PATCH_FILE

echo "Done"
