<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Block_Slider class
 */
class SDM_Banner_Block_Slider
    extends Mage_Core_Block_Template
{
    /**
     * The current layout handles
     *
     * @return array
     */
    public function getLayoutHandles()
    {
        return Mage::app()->getLayout()->getUpdate()->getHandles();
    }

    /**
     * Gets this store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        return Mage::app()->getStore()->getWebsiteId();
    }

    /**
     * Load collection
     *
     * @return SDM_Banner_Model_Resource_Slider_Collection
     */
    public function getSliderCollection()
    {
        if (Mage::getStoreConfig('sdm_banner/general/enabled')) {
            // Current Store ID
            $currentStore = $this->getStoreId();

            // Current Page Handle
            $currentHandle = $this->getLayoutHandles();

            $sliderCollection = Mage::getModel('slider/slider')
                ->getCollection()
                ->addFieldToSelect('slider_id')
                ->addFieldToSelect('title')
                ->addFieldToSelect('sliderimage')
                ->addFieldToSelect('bannerurl')
                ->addFieldToSelect('mobileimage')
                ->addFieldToFilter('status', 1)
                ->setPageSize(Mage::getStoreConfig('sdm_banner/general/limit'));

            if ($sliderCollection) {
                // Get SDM_banner_page
                $pageTable = Mage::getSingleton('core/resource')->getTableName('slider/pages');

                // Get SDM_banner_layout
                $layoutTable = Mage::getSingleton('core/resource')->getTableName('slider/layouts');

                // Get SDM_banner_store
                $storeTable = Mage::getSingleton('core/resource')->getTableName('slider/stores');

                // Join SDM_banner tables
                $sliderCollection->getSelect()
                    ->joinLeft(
                        array('sbp' => $pageTable),
                        'main_table.slider_id = sbp.slider_id',
                        array()
                    )
                    ->joinLeft(
                        array('sbl' => $layoutTable),
                        'sbp.layout_id = sbl.layout_id',
                        array()
                    )
                    ->joinLeft(
                        array('sbs' => $storeTable),
                        'sbp.slider_id = sbs.slider_id',
                        array()
                    )
                    ->columns(array('sbl.layout_handle','sbs.store_id'));

                // Check by Store ID
                $sliderCollection->addFieldToFilter('store_id', array('eq'=> $currentStore));

                // // Check by Page Handler
                $sliderCollection->addFieldToFilter('layout_handle', array('in' => $currentHandle));
                $sliderCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));

                return $sliderCollection;
            }
        }
    }
}
