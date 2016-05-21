/**
 * Separation Degrees One
 *
 * Ellison's Mage_Customer customizations
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension implements customizations to Mage_Customer module. Customization
include default retailer customer group that require approval.

Extension Files:

    - app/etc/modules/SDM_Customer.xml
    - app/code/local/SD/Customer/*


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.3.7: October 20, 2015
    - Password reset request goes through a custom customer validation.

v0.3.1: June 26, 2015
    - Installed a customer EAV attribute for purchase order usage.

v0.3.0: June 25, 2015
    - Implemented ERUS mininum order quantity enforcement.

v0.2.2: May 26, 2015
    - Added AX invoice ID to customer.

v0.2.1: April 16, 2015
    - Added a isLoggedIn() check for getCustomerGroupId() for getting customer
      group ID of a customer whose session has been expired. Group ID is not
      cleared when session expires.

v0.2.0: March 11, 2015
    - Added customer group sorting position, min. qty. override flag.
    - Updated grid and form with new attributes.

v0.1.0: March 02, 2015
    - Initial Release.
    - Assigns new retailer website customers to the default "Pending" customer
      group.
