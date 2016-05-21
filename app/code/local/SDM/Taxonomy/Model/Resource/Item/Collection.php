<?php
/**
 * Separation Degrees One
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Model_Resource_Item_Collection class
 */
class SDM_Taxonomy_Model_Resource_Item_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('taxonomy/item');
    }

    /**
     * Filter by type
     *
     * @param string $type
     *
     * @return SDM_Taxonomy_Model_Resource_Item_Collection
     */
    public function filterType($type)
    {
        $this->addFieldToFilter('type', array('eq' => $type));
        return $this;
    }

    /**
     * Filter by multiple types
     *
     * @param array $types
     *
     * @return SDM_Taxonomy_Model_Resource_Item_Collection
     */
    public function filterMultipleTypes($types)
    {
        $this->addFieldToFilter('type', array('in' => $types));
        return $this;
    }
}
