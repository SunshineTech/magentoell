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
 * SDM_SavedQuote_Block_Totals class
 */
class SDM_SavedQuote_Block_Totals extends Mage_Core_Block_Template
{
    /**
     * Totals
     *
     * @var array
     */
    protected $_totals = null;

    /**
     * Returns an array that contains the totals
     *
     * @param int $id
     *
     * @return array
     */
    public function getTotals($id = null)
    {
        if ($this->_totals) {
            return $this->_totals;
        }

        $totals = array();

        if ($id) {
            $quote = Mage::getModel('savedquote/savedquote')->load($id);
        } else {    // Otherwise, get it from cache
            $quote = Mage::registry('saved_quote');
        }

        // Subtotal and grand total are always available
        $totals['grand_total'] = array(
            'value' => $quote->getGrandTotal(),
            'label' => 'Grand Total',
            'render' => false
        );
        $totals['subtotal'] = array(
            'value' => $quote->getSubtotal(),
            'label' => 'Subtotal',
            'render' => true
        );
        if ($quote->getDiscount()) {
            $totals['discount'] = array(
                'value' => $quote->getDiscount(),
                'label' => 'Discount',
                'render' => false
            );
        }
        if ($quote->getTaxAmount()) {
            $totals['tax_amount'] = array(
                'value' => $quote->getTaxAmount(),
                'label' => 'Tax',
                'render' => true
            );
        }
        if ($quote->getShippingCost()) {
            $totals['shipping'] = array(
                'value' => $quote->getShippingCost(),
                'label' => 'Shipping & Handling (' . $quote->getShippingMethod() . ')',
                'render' => true
            );
        }
        if ($quote->getSdmShippingSurcharge() > 0) {
            $totals['shipping_surchage'] = array(
                'value' => $quote->getSdmShippingSurcharge(),
                'label' => 'Shipping & Handling Surcharge',
                'render' => true
            );
        }

        $this->_totals = $totals;

        return $this->_totals;
    }

    /**
     * Render totals html for specific totals
     *
     * @param string $code
     * @param int    $colspan
     * @param string $label
     *
     * @return string
     */
    public function renderTotals($code = null, $colspan = 1, $label = null)
    {
        $html = '';
        $totals = $this->getTotals();

        // If code is passed in, render just that value
        if (!is_null($code)) {
            if ($label && isset($totals[$code])) {
                $totals[$code]['label'] = $label;
            } elseif (!isset($totals[$code])) {
                return '';
            }
            return $this->renderTotal($totals[$code], $code, $colspan);
        }

        // Otherwise, render all totals
        foreach ($totals as $code => $value) {
            if (!$value['render']) {
                continue;
            }
            $html .= $this->renderTotal($totals[$code], $code, $colspan);
        }
        return $html;
    }

    /**
     * Renders the HTML for the totals.
     *
     * Note that this does not use a render and simply builds the HTML.
     *
     * @param array $data
     * @param str   $code
     * @param int   $colspan
     *
     * @return str
     */
    public function renderTotal($data, $code, $colspan = 1)
    {
        $label = htmlentities($data['label']);
        $price = Mage::helper('core')->currency($data['value'], true, false);

        return "<tr>
            <td style=\"\" class=\"a-right\" colspan=\"$colspan\">
                $label
            </td>
            <td style=\"\" class=\"a-right\">
                <span class=\"price\">$price</span>
            </td>
        </tr>";

    }
}
