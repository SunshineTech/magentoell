<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Model_Item class
 */
class SDM_Taxonomy_Model_Item extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('taxonomy/item');
    }

    /**
     * Returns the ID of the desired model
     *
     * @param str $type
     * @param str $code
     *
     * @return SDM_Taxonomy_Model_Item
     */
    public function getIdByCode($type, $code)
    {
        return $this->_getResource()->getIdByCode($type, $code);
    }

     /**
     * Returns the associated products. If an argument is passed in, it returns
     * just the relevant record
     *
     * @param Mage_Catalog_Model_Product|int $product
     *
     * @return array
     */
    public function getProducts($product = null)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = $product->getId();
        } else {
            $productId = $product;
        }

        $products = Mage::getResourceModel('taxonomy/item_product')->getProducts(
            $this->getId(),
            '*',
            $productId
        );

        return $products;
    }

    /**
     * Checks if taxonomy is active. Website assignment and date range must be
     * valid.
     *
     * @param int $websiteId
     *
     * @return bool
     */
    public function isActive($websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = (int)Mage::app()->getWebsite()->getId();
        }

        // Always available in admin
        if ($websiteId == 0) {
            return true;
        }

        // $now: server's local time
        $today = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $now = strtotime($today);   // Unix timestamp
        $pastDate = date(
            'Y-m-d',
            strtotime(Mage::getModel('core/date')->date('Y-m-d') . ' - 1 week')
        );
        $futureDate = date(
            'Y-m-d',
            strtotime(Mage::getModel('core/date')->date('Y-m-d') . ' + 1 week')
        );

        $dates = Mage::getResourceModel('taxonomy/item_date')
            ->getDates(
                $this->getId(),
                array('website_id', 'start_date', 'end_date'),
                $websiteId
            );

        if (empty($dates)) {
            return false;
        }
        $dates = reset($dates);

        // $dates: Timestamps set from the admin
        if (!$dates['start_date']) {
            $dates['start_date'] = $pastDate;
        }
        if (!$dates['end_date']) {
            $dates['end_date'] = $futureDate;
        }

        // Comparing local store time against timestamps set in the admin
        if ($now >= strtotime($dates['start_date'])
            && $now <= strtotime($dates['end_date'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Processing object before save data
     *
     * @return SDM_Taxonomy_Model_Item
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->getType() !== 'designer') {
            $this->setData('rich_description', '');
        }

        // Create and set a code only for new records
        $code = $this->getCode();
        if (!$code || empty($code)) {
            $this->setCode($this->_getHelper()->transformNameToCode($this->getName()));
        }

        return $this;
    }

    /**
     * Remove image file before a delete
     *
     * @return void
     */
    protected function _beforeDelete()
    {
        parent::_beforeDelete();

        $url = $this->getImageUrl();
        if (!empty($url)) {
            Mage::helper('compatibility')->removeFile(
                Mage::getBaseDir('media') . DS . $url
            );
        }
    }

    /**
     * Returns the taxonomy helper
     *
     * @return SDM_Taxonomy_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('taxonomy');
    }
}
