<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Model_Item_Date class
 */
class SDM_Taxonomy_Model_Item_Date extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('taxonomy/item_date');
    }

    /**
     * Manipulate data before saving.
     *
     * @return SDM_Taxonomy_Model_Item_Date
     */
    protected function _beforeSave()
    {
        // Force beginning and ending H:i:s times
        if ($start = $this->getStartDate()) {
            $start = date('Y-m-d', strtotime($start));
            $this->setStartDate($this->getStartDate() . ' 00:00:00');
        }
        if ($end = $this->getEndDate()) {
            $end = date('Y-m-d', strtotime($end));
            $this->setEndDate($this->getEndDate() . ' 23:59:59');
        }

        return $this;
    }
}
