/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

Banner ads slider can be dynamically place on multiple pages.
Currently pages are being inject in upgrade-1.0.1-1.0.2.php manually.
In the future client could dynamically add a new page from the admin (by requests)

Currently available page are:
- Homepage
- Category Page
- Product Page
- Calendar Page (currently this handler is calendar_index_index if different just manually change it in the database)
- and Shopping Cart Page.

Banner will always shown on the top before any main content.

Extension Files:

    - app/etc/modules/SDM_Banner.xml
    - app/code/local/SD/Banner/*
    - app/design/adminhtml/sdm/ellison/layout/banner.xml
    - app/design/frontend/base/default/template/sdm/banner/slider.phtml


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v1.0.2: March 31, 2015
    - Added 3 new tables(layout, pages and stores)
    - Inject record to table on _afterSave()
    - See SDM_Banner_Model_Resource_Slider
    - AddUpdate() layout from sdm_banner_layouts in the observer.
    - Check for store id && current layout handles.
    - Change slideimage to mobileimage on 770px break point.

v1.0.1: March 30, 2015
    - Added mobileimage column into sdm_banner

v1.0.0: February 27, 2015
    - Inital Release.
