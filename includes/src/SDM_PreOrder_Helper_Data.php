<?php
/**
 * Separation Degrees One
 *
 * Pre Order Module
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_PreOrder
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Pre-Order helper
 */
class SDM_PreOrder_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Determine if the current quote has pre order items
     *
     * @param Mage_Sales_Model_Quote|SDM_SavedQuote_Model_Savedquote|null $quote
     *
     * @return boolean
     */
    public function isQuotePreOrder($quote = false)
    {
        return Mage::helper('savedquote')->isQuotePreOrder($quote);
    }

    /**
     * Checks that pre-order is ready to be purchased
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @return boolean
     */
    public function canBePurchased(SDM_SavedQuote_Model_Savedquote $quote)
    {
        if ((int)$quote->getIsActive() !== SDM_SavedQuote_Helper_Data::PREORDER_APPROVED_FLAG) {
            return false;
        }

        foreach ($quote->getItemCollection() as $item) {
            if (!$item->canBePurchased()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Format date
     *
     * @param string $date
     *
     * @return string
     */
    public function getReadableDate($date)
    {
        return Mage::getSingleton('core/date')->gmtDate('F Y', $date);
    }

    /**
     * Split a pre-order saved quote in to individual ones by date
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @return SDM_PreOrder_Helper_Data
     */
    public function splitSavedQuote(SDM_SavedQuote_Model_Savedquote $quote)
    {
        if (!$this->isQuotePreOrder($quote)) {
            return $this;
        }
        $dates = $this->_getShippingDates($quote);
        foreach ($dates as $date) {
            $this->_duplicateQuote($quote, $date);
        }
        $this->_deleteQuote($quote);
        return $this;
    }

    /**
     * Approve a pre-order
     *
     * @param integer $id
     *
     * @return void
     */
    public function approve($id)
    {
        $quote = Mage::getModel('savedquote/savedquote')
            ->load($id);
        if ($quote->getId()) {
            $quote->setIsActive(SDM_SavedQuote_Helper_Data::PREORDER_APPROVED_FLAG)
                ->save();
            $this->_dispatchEmail($quote, 'approved');
        }
    }

    /**
     * Deny a pre-order
     *
     * @param integer $id
     *
     * @return void
     */
    public function deny($id)
    {
        $quote = Mage::getModel('savedquote/savedquote')
            ->load($id);
        if ($quote->getId()) {
            $quote->setIsActive(SDM_SavedQuote_Helper_Data::PREORDER_DENIED_FLAG)
                ->save();
            $this->_dispatchEmail($quote, 'denied');
        }
    }

    /**
     * Get all the shipping dates in this quote
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @return array
     */
    protected function _getShippingDates(SDM_SavedQuote_Model_Savedquote $quote)
    {
        $select = clone $quote->getItemCollection()->getSelect();
        $select->group('pre_order_shipping_dates')
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('pre_order_shipping_dates');
        $itemShipData = $quote->getItemCollection()->getConnection()->fetchCol($select);
        $dates = array();
        foreach ($itemShipData as $json) {
            $shipData = Mage::helper('core')->jsonDecode($json);
            $dates = array_merge($dates, array_keys($shipData));
        }
        return array_unique($dates);
    }

    /**
     * Create a new quote from parent for items shipping on this date
     *
     * @param SDM_SavedQuote_Model_Savedquote $parent
     * @param string                          $date
     *
     * @return void
     */
    protected function _duplicateQuote(SDM_SavedQuote_Model_Savedquote $parent, $date)
    {
        $quote = clone $parent;
        $coreDate = Mage::getSingleton('core/date');
        $expDate = Mage::app()->getLocale()->date($coreDate->timestamp());
        $expDate->add(6, Zend_Date::MONTH);
        $quote->setId(null)
            ->setPreOrderShippingDate($date)
            ->setIncrementId('P' . Mage::helper('savedquote')->getNextIncrementId())
            ->setName($this->__('Pre-Order (Shipping %s)', $this->getReadableDate($date)))
            ->setIsActive(SDM_SavedQuote_Helper_Data::PREORDER_PENDING_FLAG)
            ->setExpiresAt($coreDate->gmtDate(null, $expDate->getTimestamp()))
            ->save();
        $total = 0;
        $items = $parent->getItemCollection();
        foreach ($items as $parentItem) {
            $qty = $parentItem->getDateShipQty($date);
            if ($qty <= 0) {
                continue;
            }
            $item = clone $parentItem;
            $qtyRatio = $qty / $parentItem->getQty();
            $item->setId(null)
                ->setIsPreOrder(1)
                ->setSavedQuoteId($quote->getId())
                ->setPreOrderShippingDates('')
                ->setQty($qty)
                ->setTaxAmount($item->getTaxAmount() * $qtyRatio)
                ->setRowTotal($item->getRowTotal() * $qtyRatio)
                ->setDiscountAmount($item->getDiscountAmount() * $qtyRatio)
                ->save();
            $total += $item->getRowTotal();
        }
        $addresses = $parent->getAddressCollection();
        foreach ($addresses as $parentAddress) {
            $address = clone $parentAddress;
            $address->setId(null)
                ->setSavedQuoteId($quote->getId())
                ->save();
        }
        $quote->setSubtotal($total)
            ->setGrandTotal($total)
            ->save();
        $this->_dispatchEmail(Mage::getModel('savedquote/savedquote')->load($quote->getId()), 'created');
    }

    /**
     * Delete a quote
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @return void
     */
    protected function _deleteQuote(SDM_SavedQuote_Model_Savedquote $quote)
    {
        $quote->delete();
    }

    /**
     * Notify customer about decision of pre order
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     * @param string                          $type
     *
     * @return void
     */
    protected function _dispatchEmail(SDM_SavedQuote_Model_Savedquote $quote, $type)
    {
        Mage::getModel('core/email_template')
            ->setDesignConfig(array(
                'area'  => 'frontend',
                'store' => SDM_Core_Helper_Data::STORE_CODE_ER
            ))
            ->loadDefault('sdm_preorder_' . $type)
            ->setSenderName(Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME))
            ->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'))
            ->setStoreId(0)
            ->send(
                $quote->getCustomerEmail(),
                $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname(),
                array(
                    'quote'      => $quote,
                    'email_html' => $type == 'created' ? Mage::app()->getLayout()
                        ->createBlock('savedquote/email_detail')
                        ->setQuote($quote)
                        ->toHtml() : '',
                    'preorder_number' => $quote->getIncrementId(),
                    'preorder_link'   => Mage::app()->getStore(SDM_Core_Helper_Data::STORE_CODE_ER)
                        ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)
                        . 'savedquote/quote/view/id/' . $quote->getId(),
                    'ship_date' => Mage::helper('sdm_preorder')->getReadableDate(
                        $quote->getPreOrderShippingDate()
                    )
                )
            );
        ;
    }
}
