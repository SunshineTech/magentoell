<?php
/**
 * Separation Degrees One
 *
 * Handles designer page and designer article rendering
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Designer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Designer_Model_Adminhtml_System_Config_Source_Designer class
 */
class SDM_Designer_Model_Adminhtml_System_Config_Source_Designer
{
    /**
     * System config options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = Mage::getResourceModel('taxonomy/item_collection')
            ->addFieldToFilter('type', 'designer')
            ->setOrder('name', Zend_Db_Select::SQL_ASC);
        $options = array();
        foreach ($collection as $item) {
            $options[] = array(
                'value' => $item->getCode(),
                'label' => $item->getName()
            );
        }
        return $options;
    }
}
