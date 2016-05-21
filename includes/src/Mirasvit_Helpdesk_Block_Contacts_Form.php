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



class Mirasvit_Helpdesk_Block_Contacts_Form extends Mage_Core_Block_Template
{
    public function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    public function getContactUsIsActive()
    {
        return $this->getConfig()->getGeneralContactUsIsActive();
    }

    public function getFormAction()
    {
        return Mage::getUrl('helpdesk/form/post');
    }

    public function getFrontendIsAllowPriority()
    {
        return $this->getConfig()->getFrontendIsAllowPriority();
    }

    public function getFrontendIsAllowDepartment()
    {
        return $this->getConfig()->getFrontendIsAllowDepartment();
    }

    public function getPriorityCollection()
    {
        return Mage::getModel('helpdesk/priority')->getCollection()
                ->setOrder('sort_order', 'asc');
    }

    public function getDepartmentCollection()
    {
        return Mage::getModel('helpdesk/department')->getCollection()
                ->addFieldToFilter('is_active', true)
                ->setOrder('sort_order', 'asc');
    }

    public function getCustomFields()
    {
        $collection = Mage::helper('helpdesk/field')->getContactFormCollection();

        return $collection;
    }

    public function getInputHtml($field)
    {
        return Mage::helper('helpdesk/field')->getInputHtml($field);
    }
}
