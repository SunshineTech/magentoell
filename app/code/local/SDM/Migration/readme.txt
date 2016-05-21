/**
 * Separation Degrees One
 *
 * Install required attributes
 *
 * @category  SDM
 * @package   SDM_Migration
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension installs base properties for the Magento instance. Such basic
properties include attributes, attribute sets, attribute groups, etc. Additionally,
it migrates the catalog and related data, which includes taxonomy, machine
compatibility, etc.

Note that the actual migration process is set up as shell script in order to
allow the ability to run the migration via the command line only.

IMPORTANAT:
After installing this extension and prior to running any import scripts, the
websites must be configured to use the created root directories from
System > Manage Stores.

Extension Files:

    - app/etc/modules/SDM_Migration.xml
    - app/code/local/SDM/Migration/*
    - shell/sdm/migrate_products.php


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.6.3: February 13, 2015
    - Discount category attribute created and data update implemented.

v0.6.2: February 10, 2015
    - Compatibility product line re-added.

v0.6.1: February 10, 2015
    - Instruction images added with labels.
    - Made product line and subproduct line taxonomy to be multi-select.

v0.6.0: February 03, 2015
    - Migration script now cleans up all categories except the necessary base
      ones when installed.

v0.5.0: January 28, 2015
    - Attriubute update for SDM_Compatibility and data migration customization.

v0.4.0: January 22, 2015
    - Processed data for SDM_Taxamony.
    - Integrated migration with SDM_Taxonomy.

v0.3.0: December 18, 2014
    - Installed root and sub-categories for all websites.

v0.2.0: December 17, 2014
    - Installed attribute groups and attribute sets.
    - Assigned attributes to the created sets.

v0.1.0: December 16, 2014
    - Inital Release.
    - Installed base attributes.


--------------------------------------------------------------------------------

@todo:

- Retailer pricing
    - Need custom source model for discount categories
    - Need new attributes for discount categories and min. qty.


- Missing asset table for taxonomy tags.
- Artists and designers (artist and designer tags) have their own models.
- SDM_Migration_Helper_Data::ellisonUrlParamsToMagento() better be done by hand
  by Ellison. After dev site is up, give them an Excel sheet they can fill out
  and I can use the manual work to update all link records.
- Some simple products are "value packs". These have "value pack contents" assets,
  which will need to be migrated.

Questions for John
1. 'landing_pages' may also need additional table(s) similar to the navigation.



php shell/indexer.php -reindex catalog_product_attribute
