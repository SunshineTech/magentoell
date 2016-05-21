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
 * Saved quote detail page in admin
 */
class SDM_SavedQuote_Block_Adminhtml_Savedquote_View
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Set up variables and buttons
     */
    public function __construct()
    {
        parent::__construct();
        $quote = $this->getQuote();
        $isPreorder = Mage::helper('sdm_preorder')->isQuotePreOrder($quote);

        if ($quote->getCouponCodes()) {
            $couponCodeUsageStr =  " with coupon code '{$quote->getCouponCodes()}'";
        } else {
            $couponCodeUsageStr = '';
        }

        $this->_objectId = 'id';
        $this->_blockGroup = 'savedquote';
        $this->_controller = 'adminhtml_savedquote';
        $this->_headerText = Mage::helper('sales')->__(
            ($isPreorder ? 'Pre-Order' : 'Saved Quote' ) . ' #%s %s',
            (string)$quote->getIncrementId(),
            $couponCodeUsageStr
        );

        $this->_removeButton('reset');
        $this->_removeButton('delete');

        $customer = Mage::getModel('customer/customer')->load(Mage::registry('saved_quote')->getCustomerId());
        Mage::register('current_customer', $customer);

        $customerId = $customer->getId();
        $permKey = md5($customerId . $customer->getPasswordHash());
        $this->_addButton('customer_login', array(
            'label'   => Mage::helper('amperm')->__('Log In as Customer'),
            'onclick' => 'window.open(\'' . Mage::helper('adminhtml')->getUrl('adminhtml/ampermlogin/login', array('customer_id' => $customerId, 'perm_key' => $permKey)).'\', \'customer\');',
            'class'   => 'back',
        ), 0, 1);

        if ($isPreorder
            && $quote->getIsActive() == SDM_SavedQuote_Helper_Data::PREORDER_PENDING_FLAG
        ) {
            $this->_addButton('preoorder_deny', array(
                'label'   => Mage::helper('adminhtml')->__('Cancel Pre-Order'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        '*/*/preorderDeny',
                        array('id' => $quote->getId())
                    ) . '\')',
            ));
            $this->_addButton('preoorder_approve', array(
                'label'   => Mage::helper('adminhtml')->__('Approve Pre-Order'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        '*/*/preorderApprove',
                        array('id' => $quote->getId())
                    ) . '\')',
            ));
        } elseif ($quote->getIsActive() == SDM_SavedQuote_Helper_Data::ACTIVE_FLAG) {
            $this->_addButton('savedquote_deny', array(
                'label'   => Mage::helper('adminhtml')->__('Cancel Saved Quote'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        '*/*/savedQuoteCancel',
                        array('id' => $quote->getId())
                    ) . '\')',
            ));
        }
    }

    /**
     * Returns the URL for the edit form
     *
     * @param  object $quote
     * @return string
     */
    public function getFormEditUrl($quote)
    {
        return $this->getUrl(
            '*/*/edit',
            array('id' => $quote->getId())
        );
    }

    /**
     * Returns the customer's first and last name
     *
     * @return str
     */
    public function getCustomerName()
    {
        return trim($this->getQuote()->getCustomerFirstname()) . ' ' .
            trim($this->getQuote()->getCustomerLastname());
    }

    /**
     * Returns the customer for this quote
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (!$this->hasCustomer()) {
            $this->setCustomer(
                Mage::getModel('customer/customer')->load($this->getQuote()->getCustomerId())
            );
        }
        return parent::getCustomer();
    }

    /**
     * Return the status name
     *
     * @return str
     */
    public function getStatus()
    {
        $id = $this->getQuote()->getIsActive();

        return Mage::helper('savedquote')->getStateName($id);
    }

    /**
     * Returns the registered saved quote
     *
     * @return SDM_SavedQuote_Model_Savedquote
     */
    public function getQuote()
    {
        return Mage::registry('saved_quote');
    }

    /**
     * Get the converted order id
     *
     * @return string|boolean
     */
    public function getOrderIncrementId()
    {
        if (!$this->getQuote()->getOrderId()) {
            return false;
        }
        $collection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('entity_id', $this->getQuote()->getOrderId());
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('increment_id');
        return $collection->getFirstItem()->getIncrementId();
    }

    /**
     * Returns the stock quantity for a product for a particular website id
     *
     * @param  object $item
     * @param  int    $websiteId
     * @return int
     */
    public function getStockByWebsite($item, $websiteId)
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($item->getProduct(), $websiteId);
        return floor($stockItem->getQty());
    }
}
