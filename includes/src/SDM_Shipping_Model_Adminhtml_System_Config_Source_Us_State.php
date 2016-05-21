<?php
/**
 * Separation Degrees One
 *
 * Ellison and Sizzix Shipping Rules
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Shipping
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * System config option of US states
 */
class SDM_Shipping_Model_Adminhtml_System_Config_Source_Us_State
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = Mage::getResourceModel('directory/region_collection')
            ->addFieldToFilter('country_id', SDM_Shipping_Model_Carrier_Table_Us::COUNTRY_ID_US)
            ->setOrder('default_name', Zend_Db_Select::SQL_ASC);
        $options = array();
        foreach ($collection as $region) {
            $options[] = array(
                'value' => $region->getCode(),
                'label' => $region->getDefaultName(),
            );
        }
        return $options;
    }
}
