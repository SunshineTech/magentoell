<?php
/**
 * Separation Degrees One
 *
 * Custom breadcrumb functionality for Ellison's catalog
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogCrumb
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CatalogCrumb_Model_Resource_Crumb class
 */
class SDM_CatalogCrumb_Model_Resource_Crumb
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize table and PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sdm_catalogcrumb/crumb', 'id');
        $this->_storeTableName = 'sdm_catalogcrumb/crumb';
    }
}
