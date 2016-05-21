#!/bin/bash

MAGENTO_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/.."
BIN_COMPASS=`which compass`

if [ -z "$1" ]; then
  echo "No argument supplied"
  echo
  echo "Examples:"
  echo "    ./compass.sh clean"
  echo "    ./compass.sh compile"
  echo "    ./compass.sh compile -e develop"
  exit
fi

cd $MAGENTO_ROOT/skin/frontend/sdm/sizzix_us/scss/
$BIN_COMPASS $@
cd $MAGENTO_ROOT/skin/frontend/sdm/sizzix_uk/scss/
$BIN_COMPASS $@
cd $MAGENTO_ROOT/skin/frontend/sdm/ellison_retail/scss/
$BIN_COMPASS $@
cd $MAGENTO_ROOT/skin/frontend/sdm/ellison_edu/scss/
$BIN_COMPASS $@
