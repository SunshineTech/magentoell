<?php
/**
 * Separation Degrees One
 *
 * Valutec Giftcard Integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Valutec
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Used for total rendering in email
 */
class SDM_Valutec_Block_Sales_Order_Giftcard
    extends Mage_Core_Block_Template
{
    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Retreive gift cards applied to current order
     *
     * @return array|boolean
     */
    public function getGiftCards()
    {
        $source = $this->getSource();
        if (!($source instanceof Mage_Sales_Model_Order)) {
            return false;
        }
        return Mage::helper('sdm_valutec')->getGiftcard($this->getOrder());
    }

    /**
     * Initialize giftcard order total
     *
     * @return Enterprise_GiftCardAccount_Block_Sales_Order_Giftcards
     */
    public function initTotals()
    {
        $total = new Varien_Object(array(
            'code'       => $this->getNameInLayout(),
            'block_name' => $this->getNameInLayout(),
            'area'       => $this->getArea()
        ));
        $this->getParentBlock()->addTotalBefore($total, array('grand_total'));
        return $this;
    }

    /**
     * Get label properties
     *
     * @return mixed
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get value properties
     *
     * @return mixed
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
