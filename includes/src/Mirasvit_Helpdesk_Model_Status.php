<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.1.3
 * @build     1578
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */



/**
 * @method Mirasvit_Helpdesk_Model_Resource_Status_Collection|Mirasvit_Helpdesk_Model_Status[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Status load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Status setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Status setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Status getResource()
 */
class Mirasvit_Helpdesk_Model_Status extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/status');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    public function getName()
    {
        return Mage::helper('helpdesk/storeview')->getStoreViewValue($this, 'name');
    }

    public function setName($value)
    {
        Mage::helper('helpdesk/storeview')->setStoreViewValue($this, 'name', $value);

        return $this;
    }

    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }

        return parent::addData($data);
    }
    /************************/

    public function loadByCode($code)
    {
        $collection = $this->getCollection()
        ->addFieldToFilter('code', $code);
        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        } else {
            return false;
        }
    }

    public function __toString()
    {
        return $this->getName();
    }
}
