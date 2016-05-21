<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_RetailerApplication_Block_Account_Application_View_Group class
 */
class SDM_RetailerApplication_Block_Account_Application_View_Group extends Mage_Core_Block_Template
{
    /**
     * Initializes the fields for this group
     *
     * @param  string $fieldMapping
     * @return $this
     */
    public function initFields($fieldMapping)
    {
        $fields = array();
        foreach ($fieldMapping as $key => $mapping) {
            $fields[] = Mage::app()
                ->getLayout()
                ->createBlock('retailerapplication/account_application_view_group_field')
                ->setTemplate('sdm/retailerapplication/account/application/view/group/' . $mapping['type'] . '.phtml')
                ->setValues(isset($mapping['values']) ? $mapping['values'] : '')
                ->setName(isset($mapping['name']) ? $mapping['name'] : '')
                ->setAllowNoVal(isset($mapping['noval']) ? $mapping['noval'] : false)
                ->setNoValMessage(isset($mapping['nvmsg']) ? $mapping['nvmsg'] : '')
                ->setId($key)
                ->setNote(isset($mapping['note']) ? $mapping['note'] : '');
        }
        $this->setFields($fields);
        return $this;
    }
}
