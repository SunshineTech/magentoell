/**
 * Separation Degrees Media
 *
 * SDM's core extension
 *
 * @category  SDM
 * @package   SDM_Core
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

--------------------------------------------------------------------------------
DESCRIPTION:
--------------------------------------------------------------------------------

This extension is the core extension from which all other SDM extensions extends.
It includes the main system configuration tab menu as well as some common
functionality shared by other SDM extensions.

Rewrites Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source::_prepareMultiselectIndex()
in order to allow the index process to pick up the SDM_Taxonomy's custom source
models and work with layered navigation. Note: see v0.2.0 release notes.

Extension Files:

  - app/etc/modules/SDM_Core.xml
  - app/code/local/SD/Core/*


--------------------------------------------------------------------------------
RELEASE NOTES:
--------------------------------------------------------------------------------

v0.3.6: June 24, 2015
  - Updated order of displayed layered navigation attributes again. See comment
    on ELSN-270.

v0.3.0: May 29, 2015
  - Changed 'description' to be 'objective'
  - Renamed 'short_description' to 'description'

v0.2.9: May 27, 2015
  - Added wysiwyg to idea detail attributes
  - Removed some unused attributes
  - Sorted our layered nav attributes properly

v0.2.3: May 19, 2015
  - Added cookie notice configuration changes

v0.2.1: May 12, 2015
  - Added install script to disable two unneeded social logins

v0.2.0: January 20, 2015
  - Rewrite to integrate SDM_Taxonomy's custom source models.
    (Note: this has been moved to SDM_Taxonomy)

v0.1.0: January 07, 2015
  - Inital Release
