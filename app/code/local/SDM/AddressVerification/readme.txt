/**
 * Separation Degrees One
 *
 * SDM's address verification extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_AddressVerification
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension is used to validate an address against a carrier's database. At
its release, only USPS address validation is implemented.

This extension works off of IWD_AddressVerification becuase that extension's
features could not be directly used. Code is ported, but it is refactored.

Extension Files:

  - app/etc/modules/SDM_AddressVerification.xml
  - app/code/local/SD/AddressVerification/*


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.1.0: January 07, 2015
  - Inital Release
