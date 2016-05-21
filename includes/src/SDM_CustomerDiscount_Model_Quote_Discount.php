<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * *****************************************************************************
 * IMPORTANT: this is a complete override of Mage_SalesRule_Model_Quote_Discount,
 * not a rewrite of a method, because collect() invokes parent::collect();
 * *****************************************************************************
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CustomerDiscount_Model_Quote_Discount class
 */
class SDM_CustomerDiscount_Model_Quote_Discount
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Discount calculation object
     *
     * @var Mage_SalesRule_Model_Validator
     */
    protected $_calculator;

    /**
     * Initialize discount collector
     */
    public function __construct()
    {
        $this->setCode('discount');
        $this->_calculator = Mage::getSingleton('salesrule/validator');
    }

    /**
     * Collect address discount amount
     *
     * Rewritten to fire the event, `sales_quote_address_discount_item_customerdiscount`
     *
     * @param  Mage_Sales_Model_Quote_Address $address
     * @return Mage_SalesRule_Model_Quote_Discount
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();
        $store = Mage::app()->getStore($quote->getStoreId());
        $this->_calculator->reset($address);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $eventArgs = array(
            'website_id'        => $store->getWebsiteId(),
            'customer_group_id' => $quote->getCustomerGroupId(),
            'coupon_code'       => $quote->getCouponCode(),
        );

        $this->_calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $this->_calculator->initTotals($items, $address);

        $address->setDiscountDescription(); // removed 'array()'
        $items = $this->_calculator->sortItemsByPriority($items);
        foreach ($items as $item) {
            if ($item->getNoDiscount()) {
                $item->setDiscountAmount(0);
                $item->setBaseDiscountAmount(0);
            } else {
                /**
                 * Child item discount we calculate for parent
                 */
                if ($item->getParentItemId()) {
                    continue;
                }

                $eventArgs['item'] = $item;
                Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $this->_calculator->process($child);
                        $eventArgs['item'] = $child;
                        Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);

                        $this->_aggregateItemDiscount($child);
                    }

                } else {
                    /**
                     * Rewrite begins
                     */
                    // Coupon discount always applies to regular price ("MSRP") for Ellison
                    $msrp = $item->getMsrp();   // 'price' attribute value from the catalog (not 'msrp' attribute)
                    $item->setDiscountCalculationPrice($msrp);
                    $item->setBaseDiscountCalculationPrice($msrp);
                    $item->setOriginalPrice($msrp);
                    $item->setBaseOriginalPrice($msrp);

                    $this->_calculator->process($item);

                    // Mage::log('--> Dispatching sales_quote_address_discount_item_customerdiscount');
                    Mage::dispatchEvent(
                        'sales_quote_address_discount_item_customerdiscount',
                        array(
                            'item' => $item,
                            // 'address' => $address
                        )
                    );

                    $this->_aggregateItemDiscount($item);
                    /**
                     * Rewrite ends
                     */
                }
            }
        }

        /**
         * process weee amount
         */
        if (Mage::helper('weee')->isEnabled() && Mage::helper('weee')->isDiscounted($store)) {
            $this->_calculator->processWeeeAmount($address, $items);
        }

        /**
         * Process shipping amount discount
         */
        $address->setShippingDiscountAmount(0);
        $address->setBaseShippingDiscountAmount(0);
        if ($address->getShippingAmount()) {
            $this->_calculator->processShippingAmount($address);
            $this->_addAmount(-$address->getShippingDiscountAmount());
            $this->_addBaseAmount(-$address->getBaseShippingDiscountAmount());
        }

        $this->_calculator->prepareDescription($address);
        return $this;
    }

    /**
     * Aggregate item discount information to address data and related properties
     *
     * @param  Mage_Sales_Model_Quote_Item_Abstract $item
     * @return Mage_SalesRule_Model_Quote_Discount
     */
    protected function _aggregateItemDiscount($item)
    {
        $this->_addAmount(-$item->getDiscountAmount());
        $this->_addBaseAmount(-$item->getBaseDiscountAmount());
        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param  Mage_Sales_Model_Quote_Address $address
     * @return Mage_SalesRule_Model_Quote_Discount
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getDiscountAmount();

        if ($amount != 0) {
            $description = $address->getDiscountDescription();
            if (strlen($description)) {
                $title = Mage::helper('sales')->__('Discount (%s)', $description);
            } else {
                $title = Mage::helper('sales')->__('Discount');
            }
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }
}
