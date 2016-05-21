/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension implements the customer discount feature and its interfaces for
management. At release, it is specifically intended for the Ellison Retailer
website. The final price of a product is determined by various discounts or
pricings, which are compared at different places. They are retailer, volume, and
negotiated, promotional, and coupon discounts.

For more informaiton on the retailer price comparison, see ELSN-133 in JIRA and
the supplmentary file, logic.txt.

For the standard retailer pricing, pre-defined and fixed percentage discount is
available depending on the customer group and "discount category", which has been
translated into the taxonomy model in Magento. Data for this is established in
the migration.

Extension Files:

    - app/etc/modules/SDM_CustomerDiscount.xml
    - app/code/local/SDM/CustomerDiscount/*
    - app/design/adminhtml/sdm/ellison/template/customerdiscount/matrix.phtml
    - app/design/adminhtml/sdm/ellison/layout/customerdiscount.xml

    - app/code/local/Mexbs/Tieredcoupon/
    - app/etc/modules/Mexbs_Tieredcoupon.xml
    - app/design/adminhtml/default/default/layout/mexbs/tieredcoupon.xml


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.4.3: April 21, 2015
    - Removed foreign key constraints from sdm_catalog_product_index_applied_discount
      because the ON DELETE condition was incorrect and the constraints do not
      seem necessary.

v0.4.2: April 15, 2015
    - Replaced unique key to store_id and product_id.

v0.4.1: April 15, 2015
    - Replaced 'website_id' column with 'store_id'.

v0.4.0: April 08, 2015
    - Group (tiered) coupons implemented.

v0.3.1: April 06, 2015
    - All catalog prices are compared, and the lowest is displayed in both
      catalog list and product view pages.
    - Types of applied discounts can be distinguished.

v0.3.0: April 01, 2015
    - Ellison's promotion prices are used in the comparison.
    - Type of applied discount can not yet be discerned on catalog list and
      product view pages.

v0.2.0: March 18, 2015
    - Updated the price indexer to index retail customer prices.
    - Retailter pricing rendering on product view page and cart works.

v0.1.2: Match 11, 2015
    - Updated discount matrix to be sorted according to customer group position.

v0.1.1: February 27, 2014
    - Installed min_qty attribute. Min. qty. feature is implemented in
      SDM_Checkout.

v0.1.0: February 19, 2014
    - Inital Release.
    - Customer group discount data migrated.
    - Retailer discount matrix view created. For viewing only.


// Issues
- A coupon should still apply even if not all items qualify.

