<?php
/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Catalog_Model_Observer class
 */
class SDM_Catalog_Model_Observer
{

    protected $_lifecycleLock = null;

    /**
     * Check if a product page that's rendering is a print catalog and redirect
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function checkPrintCatalog($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->isPrintCatalog()) {
            Mage::app()->getResponse()->setRedirect('/print-catalogs');
        }
    }

    /**
     * Enforces a 1:1 exchange rate for everything/
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function enforceCurrency($observer)
    {
        $controllerAction = $observer->getEvent()->getControllerAction();
        $rates = $controllerAction->getRequest()->getPost('rate');

        foreach ($rates as $currency1 => &$currencies) {
            foreach ($currencies as $currency2 => &$rate) {
                if ($rate != 1) {
                    $rate = 1;
                }
            }
        }

        $controllerAction->getRequest()->setPost('rate', $rates);
    }

    /**
     * Runs on catalog_product_save_before
     *
     * This locks the product lifecycle from running until the entire catalog save
     * process is complete
     *
     * @return $this
     */
    public function lockLifecycleOnProductSave()
    {
        $this->_lifecycleLock = true;

        return $this;
    }

    /**
     * Runs on catalog_product_save_commit_after
     *
     * Unlocks the lifecycle process and runs it for the current product
     *
     * @param  Varien_Event_Observer $observer
     * @return $this
     */
    public function unlockAndRunLifecycleOnProductSave(Varien_Event_Observer $observer)
    {
        $this->_lifecycleLock = false;
        $product = $observer->getProduct();

        Mage::helper('sdm_catalog/lifecycle')
            ->applyLifecycleModifications($product->getId());

        return $this;
    }

    /**
     * Apply lifecycle modifications to product, if needed, on stock change
     *
     * @param  Varien_Event_Observer $observer
     * @return $this
     */
    public function applyLifecycleModificationsOnStockChange(Varien_Event_Observer $observer)
    {
        if (!$this->_canRunLifecycle(true)) {
            return $this;
        }

        $event = $observer->getEvent();
        $stockItem = $event->getItem();

        // Skip running if product is not simple
        $product = Mage::getModel('catalog/product')->load($stockItem->getProductId());
        if ($product->getTypeId() !== 'simple') {
            return $this;
        }

        Mage::helper('sdm_catalog/lifecycle')
            ->applyLifecycleModifications($stockItem->getProductId());

        return $this;
    }

    /**
     * Observer function that checks product display dates and
     * changes lifecycle accordingly
     *
     * @return $this
     */
    public function checkDisplayDates()
    {
        $storeWebsites = Mage::app()->getWebsites();

        foreach ($storeWebsites as $websiteId => $website) {
            // Then apply modifications to each store view
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $storeId => $store) {
                    $this->_checkDisplayDates($store);
                }
            }
        }

        return $this;
    }

    /**
     * This does all the heavy lifting of finding products that need
     * to be enabled or hidden based off display dates
     *
     * @param  object $store
     * @return $this
     */
    protected function _checkDisplayDates($store)
    {
        $visibility = Mage::getModel("catalog/product_visibility");
        $resource = Mage::getSingleton('core/resource');

        // === ENABLE CRITERIA ===
        // Doesn't have a start date
        //      OR after start date
        // AND Doesn't have an end date
        //      OR before end date
        // AND Is disabled
        $shouldBeVisible = $this->_getProductCollectionForStore($store->getId())
            ->addAttributeToFilter(array(array(
                'attribute'      => 'display_start_date',
                array('null' => true)
            ),array(
                'attribute'      => 'display_start_date',
                'to'           => Mage::getModel('core/date')->gmtDate()
            )))
            ->addAttributeToFilter(array(array(
                'attribute'      => 'display_end_date',
                array('null' => true)
            ),array(
                'attribute'      => 'display_end_date',
                'from'             => Mage::getModel('core/date')->gmtDate()
            )))
            ->addAttributeToFilter(
                'visibility',
                array('eq'       => $visibility::VISIBILITY_NOT_VISIBLE)
            )
            ->setPageSize(200)
            ->setCurPage(1);
        $shouldBeVisible->getSelect()
            ->join(
                array('W' => $resource->getTableName('catalog/product_website')),
                '`W`.`product_id`=`e`.`entity_id` AND `W`.`website_id`=' . $store->getWebsiteId()
            );
        $shouldBeVisible = $shouldBeVisible->getAllIds();

        // === DISABLE CRITERIA ===
        // Has a start date
        // AND is before start date
        // AND is NOT disabled
        $shouldBeHidden1 = $this->_getProductCollectionForStore($store->getId())
            ->addAttributeToFilter(
                'display_start_date',
                array('notnull'  => true)
            )
            ->addAttributeToFilter(
                'display_start_date',
                array('from'       => Mage::getModel('core/date')->gmtDate())
            )
            ->addAttributeToFilter(
                'visibility',
                array('neq'      => $visibility::VISIBILITY_NOT_VISIBLE)
            )
            ->setPageSize(200)
            ->setCurPage(1);
        $shouldBeHidden1->getSelect()
            ->join(
                array('W' => $resource->getTableName('catalog/product_website')),
                '`W`.`product_id`=`e`.`entity_id` AND `W`.`website_id`=' . $store->getWebsiteId()
            );
        $shouldBeHidden1 = $shouldBeHidden1->getAllIds();

        // === DISABLE CRITERIA ===
        // Has an end date
        // AND is after end date
        // AND is NOT disabled
        $shouldBeHidden2 = $this->_getProductCollectionForStore($store->getId())
            ->addAttributeToFilter(
                'display_end_date',
                array('notnull'  => true)
            )
            ->addAttributeToFilter(
                'display_end_date',
                array('to'       => Mage::getModel('core/date')->gmtDate())
            )
            ->addAttributeToFilter(
                'visibility',
                array('neq'      => $visibility::VISIBILITY_NOT_VISIBLE)
            )
            ->setPageSize(200)
            ->setCurPage(1);
        $shouldBeHidden2->getSelect()
            ->join(
                array('W' => $resource->getTableName('catalog/product_website')),
                '`W`.`product_id`=`e`.`entity_id` AND `W`.`website_id`=' . $store->getWebsiteId()
            );
        $shouldBeHidden2 = $shouldBeHidden2->getAllIds();

        // Combine hidden
        $shouldBeHidden = array_unique(array_merge($shouldBeHidden1, $shouldBeHidden2));

        // Enable items that should be enabled
        foreach ($shouldBeVisible as $productId) {
            Mage::helper('sdm_catalog/lifecycle')
                ->applyLifecycleModifications($productId, $store->getId());

        }

        // Disable products that should be disabled
        if (count($shouldBeHidden)) {
            Mage::getModel("catalog/product_action")
                ->updateAttributes(
                    $shouldBeHidden,
                    array('visibility' => $visibility::VISIBILITY_NOT_VISIBLE),
                    $store->getId()
                );
        }

        return $this;
    }

    /**
     * Grabs a product collection for a specific store
     *
     * @param  object $store
     * @return $collection
     */
    protected function _getProductCollectionForStore($store)
    {
        $collection = Mage::getResourceModel('catalog/product_collection');

        // Change the store on the entity
        // This doesn't change it in the (already constructed) SQL query
        $collection->setStore($store);

        if (!$collection->isEnabledFlat()) {
            return $collection;
        }

        // Change the used table to the $store we want
        $select = $collection->getSelect();
        $from = $select->getPart('from');

        // Here, getFlatTableName() will pick up the store set above
        $from[$collection::MAIN_TABLE_ALIAS]['table']
            = $from[$collection::MAIN_TABLE_ALIAS]['tableName']
                = $collection->getEntity()->getFlatTableName();

        $select->setPart('from', $from);
        return $collection;
    }

    /**
     * Checks if product lifecycle is enabled in the system config
     *
     * @return boolean [description]
     */
    protected function _isLifecycleEnabled()
    {
        return (bool) Mage::getStoreConfig('sdm_lifecycle/options/enabled');
    }

    /**
     * Checks if lifecycle and enabled and if we have a lifecycle lock on or not
     *
     * @param  boolean $checkLock Should we check the lifecycle lock or not?
     * @return bool
     */
    protected function _canRunLifecycle($checkLock = true)
    {
        // If we should check the lock, make sure it wasn't set to false (null or false is OK)
        if ($checkLock && $this->_lifecycleLock === true) {
            return false;
        }

        // Is lifecycle enabled?
        return $this->_isLifecycleEnabled();
    }
}
