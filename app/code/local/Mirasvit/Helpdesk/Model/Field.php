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
 * @method Mirasvit_Helpdesk_Model_Resource_Field_Collection|Mirasvit_Helpdesk_Model_Field[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Field load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Field setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Field setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Field getResource()
 * @method bool getIsNew()
 * @method Mirasvit_Helpdesk_Model_Field setIsNew(bool $flag)
 * @method bool getIsRequiredStaff()
 * @method Mirasvit_Helpdesk_Model_Field setIsRequiredStaff(bool $flag)
 * @method bool getIsRequiredCustomer()
 * @method Mirasvit_Helpdesk_Model_Field setIsRequiredCustomer(bool $flag)
 * @method string getType()
 * @method Mirasvit_Helpdesk_Model_Field setType(string $param)
 * @method string getName()
 * @method Mirasvit_Helpdesk_Model_Field setName(string $param)
 * @method string getCode()
 * @method Mirasvit_Helpdesk_Model_Field setCode(string $param)
 */
class Mirasvit_Helpdesk_Model_Field extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/field');
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

    public function getDescription()
    {
        return Mage::helper('helpdesk/storeview')->getStoreViewValue($this, 'description');
    }

    public function setDescription($value)
    {
        Mage::helper('helpdesk/storeview')->setStoreViewValue($this, 'description', $value);

        return $this;
    }

    public function setValues($value)
    {
        Mage::helper('helpdesk/storeview')->setStoreViewValue($this, 'values', $value);

        return $this;
    }

    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }

        if (isset($data['description']) && strpos($data['description'], 'a:') !== 0) {
            $this->setDescription($data['description']);
            unset($data['description']);
        }

        if (isset($data['values']) && strpos($data['values'], 'a:') !== 0) {
            $this->setValues($data['values']);
            unset($data['values']);
        }

        return parent::addData($data);
    }
    /************************/

    public function getValues($emptyOption = false)
    {
        $values = Mage::helper('helpdesk/storeview')->getStoreViewValue($this, 'values');
        $arr = explode("\n", $values);
        $values = array();
        foreach ($arr as $value) {
            $value = explode('|', $value);
            if (count($value) >= 2) {
                $values[trim($value[0])] = trim($value[1]);
            }
        }
        if ($emptyOption) {
            $res = array();
            $res[] = array('value' => '', 'label' => Mage::helper('helpdesk')->__('-- Please Select --'));
            foreach ($values as $index => $value) {
                $res[] = array(
                   'value' => $index,
                   'label' => $value,
                );
            }
            $values = $res;
        }

        return $values;
    }

    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setIsNew(true);
        }

        return parent::_beforeSave();
    }

    protected function _afterSaveCommit()
    {
        parent::_afterSaveCommit();

        if ($this->getIsNew()) {
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $tableName = $resource->getTableName('helpdesk/ticket');
            $query = "ALTER TABLE `{$tableName}` ADD `{$this->getCode()}` TEXT";
            $writeConnection->query($query);
            $writeConnection->resetDdlCache();
        }
    }

    protected function _beforeDelete()
    {
        $field = Mage::getModel('helpdesk/field')->load($this->getId());
        $this->setDbCode($field->getCode()); //unused???

        return parent::_beforeDelete();
    }

    protected function _afterDeleteCommit()
    {
        parent::_afterDeleteCommit();

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $tableName = $resource->getTableName('helpdesk/ticket');
        $query = "ALTER TABLE `{$tableName}` DROP `{$this->getDbCode()}`";
        $writeConnection->query($query);
        $writeConnection->resetDdlCache();
    }

    public function getGridType()
    {
        switch ($this->getType()) {
            case 'date':
                $type = 'date';
                break;
            case 'select':
            case 'checkbox':
                $type = 'options';
                break;
            default:
                $type = 'text';
                break;
        }

        return $type;
    }

    public function getGridOptions()
    {
        if ($this->getType() == 'checkbox') {
            return array(
                0 => Mage::helper('helpdesk')->__('No'),
                1 => Mage::helper('helpdesk')->__('Yes'),
                );
        }

        return $this->getValues();
    }
}
