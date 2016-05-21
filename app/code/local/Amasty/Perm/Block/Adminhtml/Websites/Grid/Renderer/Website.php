<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Block_Adminhtml_Websites_Grid_Renderer_Website extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return '<a title="' . Mage::helper('amperm')->__('Login the `%s` Website', $row->getName()) . '"
            href="' . $this->getUrl('adminhtml/ampermlogin/login', array('website_id' => $row->getWebsiteId(),
                'customer_id' => $this->getRequest()->getParam('customer_id'),
                'perm_key' => $this->getRequest()->getParam('perm_key'))) . '">'
            . $row->getData($this->getColumn()->getIndex()) . '</a>';
    }
}
