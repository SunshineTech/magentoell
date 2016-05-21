/**
 * Separation Degrees One
 *
 * Ellison's AX ERP integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Ax
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension implements the AX ERP integration for exchanging data and updating
Magento orders and inventory at its initial release.

Initial release features include order XML export for AX, inventory (quanitity
and life cycle) update for Magento, and order status update for Magento.

Extension Files:

    - app/etc/modules/SDM_Ax.xml
    - app/code/local/SD/Ax/*


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.3.0: July 20, 2015
    - Added ability to run the ERP integration processed from the system
      configuration.

v0.2.0: May 11, 2015
    - Preliminary inventory update finished. May need to be optimized.
    - Preliminary order export finished. Missing some delivery, payment, etc.
      details.

v0.1.0: February 27, 2015
    - Inital Release.
    - Installed custom order statuses and associated states.

@what works:
- Order status updates complete
- Inventory complete but still need to be optimized
- Order export mostly done except for some @todos

@ask madhavi: (asked some of these via email)
- invoice_account: what is this again?
- tax_amount: "shipping amount? Not tax?
- request_id: is this required?
- cybersource_merchant_ref_num: web order ID? is this the order number? Looks like 554cc05f02f39926d000004c in one of the sample files. Need to know for US and UK.
- delivery_zone, delivery_mode, delivery_term: how to get this?

Important Notes:
I ran did a mock run without actually updating data using inventory_upload_010515_222715.xml
on a catalog migrated from db-dump_04_07_15_1709.tgz, and it did the following updates.
- 5968 Purchase hold updates
- 705 life cycle updates