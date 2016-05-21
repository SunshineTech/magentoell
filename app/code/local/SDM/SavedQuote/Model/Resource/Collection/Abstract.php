<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_SavedQuote_Model_Resource_Collection_Abstract class
 */
class SDM_SavedQuote_Model_Resource_Collection_Abstract
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Return a collection filtered with given parameters
     *
     * @param array $filters
     *
     * @return SDM_SavedQuote_Model_Resource_Collection_Abstract
     */
    public function setFilters($filters = array())
    {
        foreach ($filters as $attribute => $value) {
            $this->addFieldToFilter($attribute, $value);
        }

        return $this;

    }

    /**
     * Converts the collction to an array of objects
     *
     * @return array of objects
     */
    public function toArrayObjects()
    {
        $data = array();

        foreach ($this as $one) {
            $data[] = $one;
        }

        return $data;
    }
}
