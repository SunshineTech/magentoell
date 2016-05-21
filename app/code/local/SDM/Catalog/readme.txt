/**
 * Separation Degrees Media
 *
 * Magento catalog customizations
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension implements custom functionality of SDM extensions related to the
Mage_Catalog module.

Compatibility is implemented in SDM_Compatibility. See that extensions for more
details.

Media gallery is used to store instruction images. An image label must be provided
in order for an image to be registered as an insturction image by starting the
label with "instruction:". Use lower case and include the colon, followed by
the figure label. For example, "instruction:Figure A".

Rendered related products are composed of manually assigned products from the
admin, as well as dynamically generated products that are selected according
to the taxonomy tags in addition to the product line tag, website assignments,
and product type. Manually assigned products are displayed first. Multiple tag
assignments of the same taxonomy consider all of these tags to be valid matching
parameters (OR condition).

AdjustWare_Nav installed for improved layered navigation functionality. SDM_Catalog
was updated to work with this third-party extension in the filtering and counting
aspects.

In order to get the custom prices (e.g. UK's Euro) to work everywhere, collections
must be modified in multiple methods. Various collections/Zend_Db_Select include,
but not limited to, layered navigation's main colleciton, collection to count
filter block's range and counts, Zend_Db_Select to to which price range is applied,
etc.

Extension Files:

    - app/etc/modules/SDM_Catalog.xml
    - app/code/local/SD/Catalog/*
    - app/design/adminhtml/default/ellison/*
    - app/design/frontend/sdm/ellison/template/catalog/product/list/related.phtml
    - app/design/adminhtml/sdm/ellison/template/system/cache/additional.phtml
    - skin/adminhtml/sdm/ellison/js/sdm_catalog.js (partially)


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.8.1: May 21, 2015
    - Lifecycle rework:
      - Renamed allow_sale to allow_cart
      - Added allow_checkout

v0.7.0: May 14, 2015
    - Added new attribute set for print catalogs

v0.6.1: April 01, 2015
    - Enforced a 1:1 currency rate.

v0.6.0: April 24, 2015
    - Euro store level currency implemented and works with catalog and cart
      promotions.
    - Removed price filter block on project tab.

v0.5.4: April 22, 2015
    - Created an custom price index table for store view currency (Euro), which
      fully works with catalog price comparisons.

v0.5.3: April 16, 2015
    - Rewrote Mage_Catalog_Model_Layer::prepareProductCollection to override
      prices with Euros.
    - Add note to accessories attribute

v0.5.2: April 09, 2015
    - Installed attribute 'price_euro' and 'special_price_euro' to store the
      Euro currency price.
    - Add note to button_display_logic attribute

v0.5.1: April 01, 2015
    - SDM_Catalog_Helper_Salelabel::getSaleLabel is able to distinguish type of
      catalog discount applied.

v0.5.0: March 18, 2015
    - Added adminhtml overrides to allow for clearing catalog image cache.

v0.4.0: March 11, 2015
    - Layered navigation filtering and counts fixed to work correctly.

v0.3.0: February 3, 2015
    - Added changes for backordering / lifecycle workflow

v0.2.0: February 10, 2015
    - Added ability to retrieve compatibilities.
    - Added ability to get media gallery and instruction images separately.
    - Added ability to add image label programmatically.
    - Added customized related products ("Customers Also Loved") where the list
      of products are from the admin and dynamically generated.
    - Added initial lifecycle logic and related attributes
      of products are from the admin and dynamically generated. Template file
      updated as well.
    - Added a method to retrieve related ideas (for simple products only).
    - Added a method to retrieve used products (i.e. associated simple products).
    - Added a method to retrieve accessories.

v0.1.0: February 9, 2015
    - Inital Release.
    - Life cycle
