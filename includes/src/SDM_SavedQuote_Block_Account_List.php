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
 * SDM_SavedQuote_Block_Account_List class
 */
class SDM_SavedQuote_Block_Account_List extends Mage_Core_Block_Template
{
    /**
     * Columns displayed in the customer account tab
     * @var array
     */
    protected $_accountDisplayAttributes = array(
        'increment_id',
        'grand_total',
        'name',
        'expires_at',
        'created_at',
        'is_active',
        'order_id'
    );

    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sdm/savedquote/account/list.phtml');

        $savedQuotes = Mage::getResourceModel('savedquote/savedquote_collection')
            ->addFieldToSelect($this->_accountDisplayAttributes)
            ->addFieldToFilter(
                'customer_id',
                Mage::getSingleton('customer/session')->getCustomer()->getId()
            )
            ->addFieldToFilter(
                'is_active',
                array('neq' => SDM_SavedQuote_Helper_Data::PENDING_FLAG)
            )
            ->setOrder('created_at', 'desc');

        $this->setSavedQuotes($savedQuotes);

        Mage::app()->getFrontController()->getAction()->getLayout()
            ->getBlock('root')
            ->setHeaderTitle(Mage::helper('savedquote')->__('My Saved Quotes'));
    }

    /**
     * Prepare layout
     *
     * @return SDM_SavedQuote_Block_Account_List
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'savedquote.quote.list.pager')
            ->setCollection($this->getSavedQuotes());
        $this->setChild('pager', $pager);
        $this->getSavedQuotes()->load();

        return $this;
    }

    /**
     * Get the pager HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get the view URL. Takes user to the order page if it has been converted.
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @return string
     */
    public function getViewUrl($quote)
    {
        if (!$quote->getIsActive() && $quote->getOrderId()) {
            return $this->getUrl('sales/order/view', array('order_id' => $quote->getOrderId()));
        } else {
            return $this->getUrl('savedquote/quote/view', array('id' => $quote->getId()));
        }
    }

    /**
     * Get the view URL. Takes user to the order page if it has been converted.
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @return string
     */
    public function getReorderUrl($quote)
    {
        return $this->getUrl('savedquote/quote/reorder', array('id' => $quote->getId()));
    }

    /**
     * Get the delete URL
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @return string
     */
    public function getDeleteUrl($quote)
    {
        return $this->getUrl('savedquote/quote/delete', array('id' => $quote->getId()));
    }

    /**
     * Get the back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Returns true if this is a preorder listing page (ie. is this in ERUS?)
     *
     * @return boolean
     */
    public function isPreorderListing()
    {
        return Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE);
    }
}
