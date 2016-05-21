#!/bin/bash

## Run the product migration in chuncks because it requires a lot of RAM
# 16K+ products
# 6K+ ideas

# Absolute path to magento installation
INSTALLDIR=`echo $0 | sed 's/migrate_products\.sh//g'`

# Install data (wipes all data and re-creates all data)
# php $INSTALLDIR/migrate_products.php -d 1 # Do not run

# Migrate products
php $INSTALLDIR/migrate_products.php -p 1 -n 2000 -t products
php $INSTALLDIR/migrate_products.php -p 2 -n 2000 -t products
php $INSTALLDIR/migrate_products.php -p 3 -n 2000 -t products
php $INSTALLDIR/migrate_products.php -p 4 -n 2000 -t products
php $INSTALLDIR/migrate_products.php -p 5 -n 2000 -t products
php $INSTALLDIR/migrate_products.php -p 6 -n 2000 -t products
php $INSTALLDIR/migrate_products.php -p 7 -n 2000 -t products
php $INSTALLDIR/migrate_products.php -p 8 -n 2000 -t products
php $INSTALLDIR/migrate_products.php -p 9 -n 2000 -t products

# Migrate Ideas
php $INSTALLDIR/migrate_products.php -p 1 -n 2000 -t ideas
php $INSTALLDIR/migrate_products.php -p 2 -n 2000 -t ideas
php $INSTALLDIR/migrate_products.php -p 3 -n 2000 -t ideas
php $INSTALLDIR/migrate_products.php -p 4 -n 2000 -t ideas