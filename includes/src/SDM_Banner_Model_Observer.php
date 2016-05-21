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
 * Banner observer
 */
class SDM_Banner_Model_Observer
{
    /**
     * Add the banner to the page if the layout handle is used
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function applyLayoutUpdateXml(Varien_Event_Observer $observer)
    {
        // Current Store ID
        $currentStore = Mage::app()->getStore()->getWebsiteId();

        // Current Page Handle
        $handleName = $observer->getEvent()->getAction()->getFullActionName();

        if ($handleName == 'sdm_calendar_event_list') {
            $calendar = Mage::registry('current_calendar');
            if ($calendar->getType() != 'grid') {
                return;
            }
        }

        $pageCollection = Mage::getModel('slider/pages')
            ->getCollection()
            ->addFieldToSelect('layout_id')
            ->addFieldToSelect('slider_id');

        // Get SDM_banner_layout
        $layoutTable = Mage::getSingleton('core/resource')->getTableName('slider/layouts');

        // Get SDM_banner_store
        $storeTable = Mage::getSingleton('core/resource')->getTableName('slider/stores');

        // Join SDM_banner tables
        $pageCollection->getSelect()
            ->joinLeft(
                array('sbl' => $layoutTable),
                'main_table.layout_id = sbl.layout_id',
                array()
            )
            ->joinLeft(
                array('sbs' => $storeTable),
                'main_table.slider_id = sbs.slider_id',
                array()
            )
            ->columns(array('sbs.store_id', 'sbl.layout_handle', 'sbl.layout_update_xml'));

        // Check by Store ID
        $pageCollection->addFieldToFilter('store_id', array('eq'=> $currentStore));

        // Check by Page Handler
        $pageCollection->addFieldToFilter('layout_handle', array('eq' => $handleName));
        //Mage::log($pageCollection->getSelect()->__toString());

        foreach ($pageCollection as $layout) {
            $layoutUpdate = $layout->getLayoutUpdateXml();
            $layoutHandle = $layout->getLayoutHandle();
            if ($handleName == $layoutHandle) {
                $layouts = $observer->getEvent()->getLayout();
                $layouts->getUpdate()->load();
                $layouts->getUpdate()->addHandle($layoutHandle);
                $layouts->getUpdate()->addUpdate($layoutUpdate);
                $layouts->generateXml();
            }
        }
    }
}
