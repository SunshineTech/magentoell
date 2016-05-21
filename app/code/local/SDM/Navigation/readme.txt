/**
 * Separation Degrees One
 *
 * Ellison's navigation links
 *
 * @category  SDM
 * @package   SDM_Navigation
 * @author    Separation Degrees <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension implements a custom navigation links. The category
has an additional attribute called "filtering parameter", which is appended to
the end of the "catalog" category URL in order to display a filtered list of
product as desired similar to the non-Magento Ellison site.

The "catalog" category is the single category that contains all of the
products for a given website. This category is omitted in the navigation menu
and is used for the purpose of filtering and layered navigation.

Added Products Block under top navigation.

Extension Files:

    - app/etc/modules/SDM_Navigation.xml
    - app/code/local/SD/Navigation/*
    - app/design/frontend/sdm/ellison/template/page/html/topmenu/renderer.phtml

--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.1.1: February xx, 2015
  - Added ability to open navigation links in an external window.
    @todo: This needs to be reimplemented somehow...


v0.1.0: February 10, 2015
  - Inital Release
