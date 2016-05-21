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
 * Magento's payment object for Valutec giftcards
 */
class SDM_Valutec_Model_Payment_Method_Giftcard
    extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Identifier
     *
     * @var string
     */
    protected $_code = 'sdm_valutech_giftcard';

    /**
     * Renderer for this payment method form
     *
     * @var string
     */
    protected $_formBlockType = 'sdm_valutec/payment_method_form_giftcard';

    /**
     * Renderer for this payment method list item
     *
     * @var string
     */
    protected $_infoBlockType = 'sdm_valutec/payment_method_info_giftcard';

    /**
     * Check whether method is available
     *
     * @param  Mage_Sales_Model_Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $badTypes = Mage::helper('sdm_valutec')->getDisallowedProductTypes();
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            if (in_array(Mage::getModel('catalog/product')->load($item->getProductId())->getProductType(), $badTypes)) {
                return false;
            }
        }
        return parent::isAvailable($quote);
    }

    /**
     * Assign data to info model instance
     *
     * @param  mixed $data
     * @return SDM_Valutec_Model_Payment_Method_Giftcard
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $this->getInfoInstance()->setCardNumber($data->getCardNumber());
        $this->getInfoInstance()->setCardPin($data->getCardPin());
        return $this;
    }

    /**
     * Prevent this payment method from being used
     *
     * This isn't actually a payment method, just a placeholder for the
     * discount form.  You should never be able to checkout using the faux
     * giftcard payment method.  Instead use a credit card for the remaining
     * balance, or the "No Payment Method Required" payment method.
     *
     * Hopefully this is never seen since we're doing some voo-doo in the
     * frontend.
     *
     * @return void
     */
    public function validate()
    {
        // Generic BS message...
        Mage::throwException('Please use another payment method.');
    }
}
