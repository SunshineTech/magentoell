<?php
/**
 * Separation Degrees One
 *
 * Ellison's Teachers' Planning Calendar
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Calendar
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Abstract block
 */
class SDM_Calendar_Block_Abstract extends Mage_Core_Block_Template
{
    /**
     * Get an instance of core/date
     *
     * @return Mage_Core_Model_Date
     */
    public function getDateSingleton()
    {
        if (!$this->hasDateSingleton()) {
            $this->setDateSingleton(Mage::getSingleton('core/date'));
        }
        return parent::getDateSingleton();
    }

    /**
     * Convert date to readable string
     *
     * @param  string $input
     * @return string
     */
    protected function getReadableDate($input)
    {
        return $this->getDateSingleton()->date('n/d/Y', $this->getDateSingleton()->gmtTimestamp($input));
    }
}
