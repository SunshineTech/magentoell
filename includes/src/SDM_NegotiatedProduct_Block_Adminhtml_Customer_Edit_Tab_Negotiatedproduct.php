<?php
/**
 * Separation Degrees One
 *
 * Ellison's negotiated product prices
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_NegotiatedProduct
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_NegotiatedProduct_Block_Adminhtml_Customer_Edit_Tab_Negotiatedproduct class
 */
class SDM_NegotiatedProduct_Block_Adminhtml_Customer_Edit_Tab_Negotiatedproduct
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $this->setTemplate('customer/tab/negotiated_product.phtml');
    }

    /**
     * Get product collection
     *
     * @return SDM_NegotiatedProduct_Model_Resource_Negotiatedproduct_Collection
     */
    public function getProducts()
    {
        $customer = Mage::registry('current_customer');

        $collection = Mage::getResourceModel('negotiatedproduct/negotiatedproduct_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId());

        return $collection;
    }

    /**
     * Returns the delete URL
     *
     * @param int $id
     *
     * @return str
     */
    public function getDeleteLink($id)
    {
        $url = Mage::helper("adminhtml")->getUrl("adminhtml/negotiatedproduct/delete/id/$id");
        $html = '<a href="' . $url . '">Delete</a>';

        return $html;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Negotiated Products (ERUS only)');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Negotiated Products (ERUS only)');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'tags';
    }
}
