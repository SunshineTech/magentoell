/**
 * Separation Degrees One
 *
 * Ellison's Mage_Sales customizations
 *
 * @category  SDM
 * @package   SDM_Sales
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension implements customizations to Mage_Sales module. At its initial
release, it added custom order statuses. States were not modified.

In order to accomodate SDM_Ax, where order items must have their regular prices/
MSRPs and discount amounts recorded,

Extension Files:

    - app/etc/modules/SDM_Sales.xml
    - app/code/local/SD/Sales/*


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.2.2: July 15, 2015
    - Subtotal fix for discounted saved quotes items when checking out while the
      products have had their prices changed.

v0.2.1: May 26, 2015
    - AX invoice ID for orders now saves to the customer. `sdm_sales_flat_order_ax`
      table dropped.

v0.2.0: May 12, 2015
    - Added a table to keep AX invoice ID for orders.

v0.1.1: March 02, 2015
    - Custom attribute "msrp" saved to quote and order item tables.

v0.1.0: February 27, 2015
    - Inital Release.
    - Installed custom order statuses and associated states.


