/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

Adds retailer application functionality to Ellison site.

On retailer side, an approved application is required to see prices and make a
purchase.

Administrators have the option to changes the status of an application by managing
the customer's profile in the admin panel. The admin can also leave a note that
is not shared with the user, if necessary.

The application collects stanard information, in addition to files and addresses.
The addresses are automatically saved to the account and set as default
shipping/billing addresses.

The application cannot be edited directly through the admin panel (yet), but can
be modified by using the "Login as User" feature.

--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.2.0: June xx, 2015
  - Business address of the retailer application is not viewable for the
    customer. It can be viewed and modified in the admin, but it cannot be removed.
  - Fax number cannot be removed once saved. It can only be edited.

v0.1.0: April 22, 2015
  - Inital Release
