/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension implements the saved quote feature for the Ellison's education
site. A "saved quote" preserves the shopping cart's prices, including item
prices, discounts, andshipping rate at the time of saving. Tax is recalculated
at the time of purchase.

Note:
1. Full functionality is tested only with simple and grouped products. If other
   product types are introduced, make sure the item conversions between saved
   quote, quote, and order work properly. Addtionally, check that
   SDM_Shipping_Model_Carrier_Savedquote::collectRates() identifies saved quotes
   properly.

2. Inchoo_LoginAsCustomer.xml (v1.0.0.0.0.0) must be installed with this extension,
   which provides the admin to convert an saved quote into an order.

Extension Files:

  - app/etc/modules/SDM_SavedQuote.xml
  - app/code/local/SD/SavedQuote/*
  - app/design/frontend/base/default/template/sdm/savedquote/*


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.3.0 Juen 04, 2015
  - Fixed saved quote checkout.
  - Saved quote checkout update a saved quote with associated order ID and
    converted date.

v0.2.0 March 26, 2015
  - Combined "shipping address" and "create new quote" forms so it's all
    captured through single form submission. Also added frontend JS validation
    to form.
  - Saved quotes no longer have a custom checkout
  - Added ability to checkout with saved quotes through onepage checkout

v0.1.0: xx xx, 2015
  - Inital Release
