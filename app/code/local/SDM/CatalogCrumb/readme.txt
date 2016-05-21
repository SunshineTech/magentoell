/**
 * Separation Degrees One
 *
 * Custom breadcrumb functionality for Ellison's catalog
 *
 * @category  SDM
 * @package   SDM_CatalogCrumb
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

Custom breadcrumb functionality for Ellison's catalog.

Also allows for the last selected taxonomy item's image and description to
be pulled in to the top of the catalog page.

It accomplishe this by appending a crumb URL variable with a hash that is tied
to a series of attributes/filters saved in a specific order.

If the crumb is loaded and there are extra filters on the page, a new crumb
is generated with the new filters added to the end of the list.

If the crumb is loaded and there are extra filters on the page, a new crumb
is generated with the filters removed from their place in the list.

Extension Files:

    - app/etc/modules/SDM_CatalogCrumb.xml
    - app/code/local/SD/CatalogCrumb/*


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.2.0: March 13, 2015
    - Added auto increment to table.

v0.1.0: March 13, 2015
    - Inital Release.
