/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension implements Ellison's taxonomy product attributes, which serve as
"rich" attriutes as well as other features that require the taxonomy's functions.

A taxonomy attribute, or item, has the ability to be enabled for specific
websites. Additionally, a start and an end date could be assigned on a website
level to give it a active date range. If a website assignment is missing dates,
it is treated as if a valid date has been assigned. This is done only during
indexing of the taxonomy.

The taxonomy may be used for other features that can take advantage of its
functions.

Extension Files:

    - app/etc/modules/SDM_Taxonomy.xml
    - app/code/local/SD/Taxonomy/*
    - skin/adminhtml/sdm/ellison/js/taxonomy.js
    - app/design/adminhtml/sdm/ellison/layout/taxonomy.xml
    - app/design/adminhtml/sdm/ellison/template/taxonomy/product.phtml
    - skin/adminhtml/sdm/ellison/css/taxonomy.css


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.4.8: April 15, 2015
    - Removed 'discount_price' column as discounted prices must be computed
      at the time of price reindexing and varies by store.

v0.4.7: March 30, 2015
    - Added "Special" taxonomy item as Ellison's catalog promotion rule. This
      replaces Mage_CatalogRule, which does not work out-of-box with Ellison's
      promotion structure.

v0.4.6: March 13, 2015
    - Added pdp_description column.

v0.4.5: March 13, 2015
    - Added start/end dates and website ID columns and their rendering in the
      admin.
    - Date range and website assignment validated when indexing taxonomy options.

v0.4.3 - v0.4.4: February 27, 2015
    - Column modifications.

v0.4.2: February 12, 2015
    - Added Discount categories to the taxonomy.
    - Add column 'position'

v0.4.1: February 11, 2015
    - Modified some columns.

v0.2.0 - 0.4.0: January, 2015
    - Update source models for the taxonomy attributes.
    - Implemented additional fields for the taxonomy to allow artists and
      designers to work.

v0.1.0: Jan 12, 2015
  - Inital Release
