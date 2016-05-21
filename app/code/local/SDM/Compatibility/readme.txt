/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension implements the product compatibility feature. A "compatibility" is
between a die and a machine that share the same "product line". A product line
is the product line taxonomy (e.g. tag) from the old non-Magento Ellison site.

In the Magento site, Ellison uses two separate "product lines". One is part
of the taxonomy, and the other is used in this extension for compatibility.
Ellison will manage them separately.

Note that "machine/material compatibility" is unrelated to product compatibility,
and they are managed independently of any taxonomy or compatibility.

Extension Files:

    - app/etc/modules/SDM_Compatibility.xml
    - app/code/local/SDM/Compatibility/*


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.2.3: June 12, 2015
    - Created a new field to accommodate extra images.

v0.2.1: February 5, 2015
    - Changed image column name

v0.2.0: February 2, 2015
    - Added product collection in SDM_Catalog_Model_Product::getCompatibility().

v0.1.0: Janeuary 27, 2015
    - Inital Release.
