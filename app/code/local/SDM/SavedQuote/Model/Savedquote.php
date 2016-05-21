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
 * Saved quote model
 */
class SDM_SavedQuote_Model_Savedquote extends Mage_Core_Model_Abstract
{
    const OBJECT_KEY_ITEMS = 'items';
    const OBJECT_KEY_ADDRESSES = 'addresses';

    protected $_items = null;

    protected $_itemCollection = null;

    // protected $_addressCollection = null;

    protected $_shippingAddress = null;

    protected $_billingAddress = null;

    protected $_quoteNumberPrefix = 'Q';

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('savedquote/savedquote');
    }

    /**
     * Returns the shipping address
     *
     * @return SDM_SavedQuote_Model_Savedquote_Address
     */
    public function getShippingAddress()
    {
        if (isset($this->_shippingAddress)) {
            return $this->_shippingAddress;
        }

        $this->_shippingAddress = $this->_getAddress(
            Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING
        );

        return $this->_shippingAddress;
    }

    /**
     * Returns the billing address
     *
     * @return SDM_SavedQuote_Model_Savedquote_Address
     */
    public function getBillingAddress()
    {
        if (isset($this->_billingAddress)) {
            return $this->_billingAddress;
        }

        $this->_billingAddress = $this->_getAddress(
            Mage_Customer_Model_Address_Abstract::TYPE_BILLING
        );

        return $this->_billingAddress;
    }

    /**
     * Returns the address given the type
     *
     * @param str $type
     *
     * @return SDM_SavedQuote_Model_Savedquote_Address
     */
    protected function _getAddress($type)
    {
        $address = clone $this->getAddressCollection();
        $address->setFilters(
                array(
                    'address_type' => $type
                )
            );
        // Mage::log($address->getSelect()->__toString());
        return $address->getFirstItem();
    }

    /**
     * Retrieve address collection. This should not use a singleton pattern.
     *
     * @return Mage_Customer_Model_Entity_Address_Collection
     */
    public function getAddressCollection()
    {
        return Mage::getResourceModel('savedquote/savedquote_address_collection')
            ->addFieldToFilter('saved_quote_id', $this->getId());
    }

    /**
     * Retrieve not loaded address collection
     *
     * @return Mage_Customer_Model_Entity_Address_Collection
     */
    public function getItemCollection()
    {
        if ($this->_itemCollection !== null) {
            return $this->_itemCollection;
        }

        $this->_itemCollection = Mage::getResourceModel('savedquote/savedquote_item_collection')
            ->addFieldToFilter('saved_quote_id', $this->getId());

        return $this->_itemCollection;
    }

    /**
     * Returns the totals
     *
     * @param string $code
     * @param string $label
     *
     * @return array
     */
    public function getTotals($code = null, $label = null)
    {
        $totals = array();

        $subtotal = $this->getSubtotal();
        $discount = $this->getDiscount();
        $shipping = $this->getShippingCost();
        $sdmShippingSurcharge = $this->getSdmShippingSurcharge();
        $tax =  $this->getTaxAmount();
        $grandTotal = $this->getGrandTotal();

        if ($subtotal) {
            $totals['subtotal'] = array(
                'code'  => 'subtotal',
                'label' => 'Subtotal',
                'value' => $subtotal,
                'render' => true
            );
        }
        if ($discount) {
            $totals['discount'] = array(
                'code'  => 'discount',
                'label' => 'Discount',
                'value' => $discount,
                'render' => false
            );
        }
        if ($shipping) {
            $totals['shipping'] = array(
                'code'  => 'shipping',
                'label' => 'Shipping & Handling (' .  $this->getShippingMethod() . ')',
                'value' => $shipping,
                'render' => true
            );
        }
        if ((float)$sdmShippingSurcharge > 0) {
            $totals['shipping_surcharge'] = array(
                'code'  => 'shipping_surcharge',
                'label' => 'Shipping & Handling Surcharge',
                'value' => $sdmShippingSurcharge,
                'render' => true
            );
        }
        if ($tax) {
            $totals['tax'] = array(
                'code'  => 'tax',
                'label' => 'Tax',
                'value' => $tax,
                'render' => true
            );
        }
        if ($grandTotal) {
            $totals['grand_total'] = array(
                'code'  => 'grand_total',
                'label' => 'Grand Total',
                'value' => $grandTotal,
                'render' => true
            );
        }

        if ($code && isset($totals[$code])) {
            if ($label) {
                $totals[$code]['label'] = $label;
            }
            return $totals[$code];
        } elseif ($code) {
            return array();
        }

        return $totals;
    }

    /**
     * Automatically updatd some update before saving
     *
     * @return SDM_SavedQuote_Model_Savedquote
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        // Inactive quotes cannot be modified
        // Mage::log($this->getOrigData('is_active'));
        if ($this->getOrigData('is_active') == SDM_SavedQuote_Helper_Data::INACTIVE_FLAG
            && $this->getIsActive() == SDM_SavedQuote_Helper_Data::INACTIVE_FLAG
        ) {
            Mage::throwException('Inactive saved quotes cannot be modified');
        }

        if (!$this->getId()) {  // New object
            $this->_refreshCreatedAt();
        } else {
            // If existing record is converted permanently, update final dates and assign quote #
            if ($this->getIsActive() == SDM_SavedQuote_Helper_Data::ACTIVE_FLAG
                && is_null($this->getExpiresAt())
            ) {
                $this->_setIncrementId();
                $this->_refreshCreatedAt();
                $this->_refreshExpiresAt();
            }
        }

        if ($this->_hasModelChanged()) {    // Data changed
            $this->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
        }

        return $this;
    }

    /**
     * Set an saved quote/pre-order number
     *
     * @return void
     */
    protected function _setIncrementId()
    {
        $newId = $this->_getHelper()->getNextIncrementId();
        $id = $this->_quoteNumberPrefix . (string)$newId;
        $this->setIncrementId($id);
    }

    /**
     * Refreshes created at date
     *
     * @return void
     */
    protected function _refreshCreatedAt()
    {
        $this->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
    }

    /**
     * Refreshes expiration date
     *
     * @return void
     */
    protected function _refreshExpiresAt()
    {
        $this->setExpiresAt(
            date(
                'Y-m-d H:i:s',
                strtotime(
                    Mage::getSingleton('core/date')->gmtDate() . '+'
                        . Mage::helper('savedquote')->validDays() . ' days'
                )
            )
        );
    }

    /**
     * Save associated objects to their respective tables
     *
     * @return SDM_SavedQuote_Model_Savedquote
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        // Save items and addresses

        return $this;
    }

    /**
     * Get the specified helper
     *
     * @param string $name
     *
     * @return SDM_Core_Helper_Data
     */
    protected function _getHelper($name = '')
    {
        if (empty($name)) {
            $name = 'savedquote';
        } else {
            $name = "savedquote/$name";
        }

        return Mage::helper($name);
    }
}
