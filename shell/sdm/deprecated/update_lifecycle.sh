## Run the lifecycle update in chuncks to avoid memory issues.

#!/bin/bash

# Absolute path to magento installation
# PAGESIZE=2500, PAGENUM={1..11} to save all products
INSTALLDIR=`echo $0 | sed 's/update_lifecycle\.sh//g'`
PAGESIZE=2500

for PAGENUM in `seq 1 11` # {1..11}
do
    cmd="update_lifecycle.php -p $PAGENUM -n $PAGESIZE"
    echo ">>> Running $INSTALLDIR$cmd"
    php $INSTALLDIR/$cmd
done