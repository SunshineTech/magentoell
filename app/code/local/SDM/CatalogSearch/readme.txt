/**
 * Separation Degrees One
 *
 * Magento catalog search customizations
 *
 * @category  SDM
 * @package   SDM_CatalogSearch
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

Extends the catalog search functionality to allow for the mini search form to live
above the catalog page's layered nav.

Extension Files:

    - app/etc/modules/SDM_CatalogSearch.xml
    - app/code/local/SD/CatalogSearch/*


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.1.1: March 10, 2015
    - Rewrote Mage_CatalogSearch_Model_Resource_Fulltext_Collection for it to
      have custom collection methods from
      SDM_Catalog_Model_Resource_Product_Collection.

v0.1.0: March 10, 2015
    - Inital Release.
