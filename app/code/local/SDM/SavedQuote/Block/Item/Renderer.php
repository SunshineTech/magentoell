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
 * Renderer for individual item on savedquote create page
 */
class SDM_SavedQuote_Block_Item_Renderer
    extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * Set item for render
     *
     * @param Mage_Sales_Model_Quote_Item $item
     *
     * @return SDM_SavedQuote_Block_Item_Renderer
     */
    public function setSavedQuoteItem(SDM_SavedQuote_Model_Savedquote_Item $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * Get item product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->getItem()->getProduct();
    }

    /**
     * For now, just getting pre-order info if applicable
     *
     * @return boolean|array
     */
    public function getProductOptions()
    {
        if (!$this->getItem()->getIsPreOrder()) {
            return false;
        }
        return array(
            array(
                'label' => 'Release Date',
                'value' => $this->getProductReleaseDate()
            )
        );
    }

    /**
     * Get the release date for this item
     *
     * @return string
     */
    public function getProductReleaseDate()
    {
        return Mage::getSingleton('core/date')->gmtDate('F Y', $this->getItem()->getPreOrderReleaseDate());
    }

    /**
     * Get available pre-order dates for this item
     *
     * @return string[]
     */
    public function getPreOrderOptions()
    {
        if (!$this->getItem()->getIsPreOrder()) {
            return $this->getDefaultPreOrderOptions();
        }
        $today = Mage::getSingleton('core/date')->date('Y-n-d');
        $release = $this->getItem()->getPreOrderReleaseDate();
        $todayD = new DateTime(Mage::getSingleton('core/date')->date('Y-n-d'));
        $releaseD = new DateTime($this->getItem()->getPreOrderReleaseDate());
        if ($releaseD < $todayD) {
            $date = $today;
        } else {
            $date = $release;
        }
        list($productMonth, $productYear) = explode(
            ',',
            Mage::getSingleton('core/date')->gmtDate('n,Y', $date)
        );
        return $this->_getPreOrderOptionFromRange($productMonth, $productYear);
    }

    /**
     * The default options (used for non-preorder items)
     *
     * @return string[]
     */
    public function getDefaultPreOrderOptions()
    {
        $quote = $this->helper('savedquote')->getQuote();
        $earliestPreOrderDate = false;
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->getIsPreOrder()) {
                continue;
            }
            if ($earliestPreOrderDate === false) {
                $earliestPreOrderDate = $item->getPreOrderReleaseDate();
                continue;
            }
            $curDate = new DateTime($earliestPreOrderDate);
            $newDate = new DateTime($item->getPreOrderReleaseDate());
            if ($newDate < $curDate) {
                $earliestPreOrderDate = $item->getPreOrderReleaseDate();
            }
        }
        if (!$this->hasDefaultPreOrderOptions()) {
            list($currentMonth, $currentYear) = explode(
                ',',
                Mage::getSingleton('core/date')->date('n,Y')
            );
            $this->setDefaultPreOrderOptions($this->_getPreOrderOptionFromRange($currentMonth, $currentYear));
        }
        return parent::getDefaultPreOrderOptions();
    }

    /**
     * Create an array of options with input month and year
     *
     * @param integer $startMonth
     * @param integer $startYear
     *
     * @return string[]
     */
    protected function _getPreOrderOptionFromRange($startMonth, $startYear)
    {
        $options = array();
        $months = range($startMonth, 12);
        foreach ($months as $month) {
            $options[$startYear . '-' . $month] = $this->__($this->getMonthNameFromNumber($month))
                . ' ' . $startYear;
        }
        $months   = range(1, 12);
        $lastYear = $startYear + 3;
        $years    = range($startYear + 1, $lastYear);
        foreach ($years as $year) {
            foreach ($months as $month) {
                if ($month >= $startMonth && $year == $lastYear) {
                    break 2;
                }
                $options[$year . '-' . $month] = $this->__($this->getMonthNameFromNumber($month)) . ' ' . $year;
            }
        }
        return $options;
    }

    /**
     * Get the readable month name from a number
     *
     * @param integer $number
     *
     * @return string
     */
    public function getMonthNameFromNumber($number)
    {
        $datetime = DateTime::createFromFormat('!m', $number);
        return $datetime->format('M');
    }
}
