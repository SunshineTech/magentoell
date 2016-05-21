/**
 * Separation Degrees One
 *
 * Checkout-related customization
 *
 * @category  SDM
 * @package   SDM_Checkout
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension, at its initial release, rewrites Mage_Checkout_CartController in
order to dispatch the even "checkout_cart_product_add_before". It fires at a
convenient location where the cart object contains the updated quote items. It
fires once per add-to-cart or quote update and requires no quote save.

Event 'checkout_cart_product_add_before' can be fired, but it is not used at the
initial release.

Additionally, it implementes a minimum quantity requirement for the Ellison
Retailer site. Min. Qty. is defined per product and certain customer groups
ignore the requirement if they're configured to override it.

Extension Files:

    - app/etc/modules/SDM_Checkout.xml
    - app/code/local/SDM/Checkout/*

    // This file is installed as part of Aitoc_Aitquantitymanager. It uses a
    // non-standard Magento practice for template creation and placement.
    // It has been directly edited to include a note in the admin.
    - var/ait_patch/design/adminhtml/base/default/template/aitcommonfiles/design
      --adminhtml--default--default--template--catalog--product--tab--inventory.phtml


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.3.1.: July 23, 2015
    - Changed "keep shopping" URL to the catalog category page.

v0.3.0: June 26, 2015
    - Purchase order payment method is usable only if the customer account allows
      it on ERUS.
    - Purchase order can be uploaded when paying with a purchase order from My Account.

v0.2.2: June 25, 2015
    - Updated cart and onepage controllers to accommodate SDM_Customer's v0.3.0
      updates.

v0.2.1: Match 12, 2015
    - Implemented customer group min. qty. override.

v0.2.0:
    - Added ajax cart functionality

v0.1.2:
    - Cart controller rewritten to dispatch an event for updating shopping cart
      once.

v0.1.1: February 20, 2014
    - Added a custom minimum quantuty attribute and implemented requirement
      enforcement.

v0.1.0: February 19, 2014
    - Inital Release.
    - Added event "checkout_cart_product_add_before".
