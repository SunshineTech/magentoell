<?php
/**
 * Separation Degrees One
 *
 * Ellison's AX ERP integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Ax
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Ax_Helper_Product class
 */
class SDM_Ax_Helper_Product extends SDM_Ax_Helper_Data
{
    const XML_ATTRIBUTE_US_QTY = 'onhand_qty_wh1';
    const XML_ATTRIBUTE_UK_QTY = 'onhand_qty_uk';

    /**
     * Life cycle attribute mapping bettwen option values and IDs
     *
     * @var array
     */
    protected $_lifeCycleMap = array();

    /**
     * Purchase hold attribute mapping bettwen option values and IDs
     *
     * @var array
     */
    protected $_purchaseHoldMap = array();

    /**
     * UK website ID
     *
     * @var array
     */
    protected $_ukWebsiteId = null;

    /**
     * Aitoc website ID (default inventory website ID)
     *
     * @var array
     */
    protected $aitocWebsiteId = null;

    /**
     * UK website ID
     *
     * @var array
     */
    protected $_ukStoreId = null;

    /**
     * Nested array of website IDs for inventory update
     *
     * @var array
     */
    protected $_websiteIdsForInventory = null;

    /**
     * Attributes needed when loading the product object using loadByAttribute()
     *
     * @var array
     */
    protected $_productAttributes = array('life_cycle', 'purchase_hold');

    /**
     * Updates the product inventory and life cycle
     *
     * @return bool
     */
    public function updateProducts()
    {
        if (!$this->isEnabled()) {
            $this->log('Products cannot be updated when AX ERP Extension is disabled');
            return;
        } else {
            $this->log('*******************************************');
            $this->log('>>> START: Beginning product updates');
        }

        $this->_init();
        // $this->_resetGlobalInventoryUsageFlag(); // Should not need to.

        // Get all of XML files
        $filePath = $this->getImportPath('inventory');
        $files = glob(Mage::getBaseDir() . DS . $filePath . DS . '*.xml');
        $filesToArchive = array();
        $lcN = 0;       // Updated count for lifecycle
        $qtyUsN = 0;    // For US inventory
        $qtyUkN = 0;    // For UK inventory
        $phN = 0;       // For Purchase hold
        $N = 0;     // Total count
        $dom = new DOMDocument();

        // Update all products if applicable
        foreach ($files as $i => $file) {
            $this->log('Processing: ' . basename($file));

            // Process only inventory files
            $fileLowerCase = strtolower(basename($file));
            if (strpos($fileLowerCase, 'inventory_upload_') === false) {
                continue;
            }

            // Read XML file
            $dom->load($file);

            $items = $dom->getElementsByTagName('item');  // Get all 'order' nodes
            foreach ($items as $itemNode) {
                $this->_updateProduct($itemNode, $lcN, $qtyUsN, $qtyUkN, $phN);
                $N++;
            }
            $filesToArchive[] = $file;
        }

        // Archive/move files
        $result = $this->archiveFiles($filesToArchive, 'inventory');
        if (!$result) {
            return false;
        }

        $this->log('');
        $this->log("$lcN/$N Life cycles updated.");
        $this->log("$phN/$N Purchase holds updated.");
        $this->log("$qtyUsN/$N US qtys updated.");
        $this->log("$qtyUkN/$N UK qtys updated.");
        $this->log(">>> END");
        $this->log('*******************************************');
        $this->log('');

        return true;
    }

    /**
     * Update life cycle, inventory, etc.
     *
     * @param DOMElement $itemNode
     * @param int        $lcN
     * @param int        $qtyUsN
     * @param int        $qtyUkN
     * @param int        $phN
     *
     * @return bool
     */
    protected function _updateProduct($itemNode, &$lcN, &$qtyUsN, &$qtyUkN, &$phN)
    {
        $sku = $itemNode->getAttribute('number');
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku, $this->_productAttributes);
        if (!$product || !$product->getId()) {
            // $this->log("SKU '$sku' not found. Skipped.");
            return;
        }

        /**
         * Mage_CatalogInventory_Model_Stock_Item updates
         */
        if ($this->_updateInventory($product, $itemNode, 'US')) {
            $qtyUsN++;
        }
        // UK inventory no longer updated via AX per ELSN-720
        /*if ($this->_updateInventory($product, $itemNode, 'UK')) {
            $qtyUkN++;
        }*/

        /**
         * Mage_Catalog_Model_Product updates only if data has changed
         */
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            $this->_updateLifeCycle($product, $itemNode);
            // $product->setUpdateRequired(true);   // For testing

            if ($product->getUpdateRequired() === true) {
                $result = $this->updateAttributes(  // Need this updated before applyLifecycleModifications()
                    array($product->getId()),
                    array('life_cycle' => $product->getLifeCycle())
                );
                Mage::helper('sdm_catalog/lifecycle')
                    ->applyLifecycleModifications($product->getId());

                if ($result) {
                    $this->log('Updated life_cycle: SKU '.$product->getSku());  // verbose
                    $lcN++;
                }
            }
        }

        $this->_updatePurchaseHold($product, $itemNode);
        // $product->setUpdateRequired(true);   // For testing
        if ($product->getUpdateRequired() === true) {
            $result = $this->updateAttributes(
                array($product->getId()),
                array('purchase_hold' => $product->getPurchaseHold())
            );

            if ($result) {
                $this->log('Updated purhcase_hold: SKU '.$product->getSku());   // verbose
                $phN++;
            }
        }
    }

    /**
     * Updates product's life cycle from AX
     *
     * @param Mage_Catalog_Model_Product $product
     * @param DOMElement                 $itemNode
     *
     * @return void
     */
    protected function _updateLifeCycle($product, $itemNode)
    {
        $product->setUpdateRequired(false); // Reset
        $lifeCycleStr = trim($itemNode->getAttribute('life_cycle'));
        if (empty($lifeCycleStr)) {
            return;
        }

        $lifeCycleId = $this->_lifeCycleMap[$lifeCycleStr];
        if (!$lifeCycleId) {
            $this->log(
                "Invalid life cycle value encountered from AX: $lifeCycleStr"
            );
            return;
        }

        // Save only if updated
        if ((int)$product->getLifeCycle() !== (int)$lifeCycleId) {
            $product->setLifeCycle($lifeCycleId);
            $product->setUpdateRequired(true);
        }
    }

    /**
     * Updates product's purchase hold flag from AX
     *
     * @param Mage_Catalog_Model_Product $product
     * @param DOMElement                 $itemNode
     *
     * @return void
     */
    protected function _updatePurchaseHold($product, $itemNode)
    {
        $product->setUpdateRequired(false);
        $puchaseHoldStr = $itemNode->getAttribute('purchasehold');

        if (!$puchaseHoldStr) { // Don't update anything is missing
            $product->setUpdateRequired(false);
        } else {
            $puchaseHoldId = $this->_purchaseHoldMap[$puchaseHoldStr];
            if ((int)$product->getPurchaseHold() !== (int)$puchaseHoldId) {
                $product->setPurchaseHold($puchaseHoldId);
                $product->setUpdateRequired(true);
            }
        }
    }

    /**
     * Update global inventory.
     *
     * Note: UK no longer updates inventory via AX per ELSN-720.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param DOMElement                 $itemNode
     * @param str                        $location
     *
     * @return boolean
     */
    protected function _updateInventory($product, $itemNode, $location)
    {
        if ($location === 'US') {
            $qty = $itemNode->getAttribute(self::XML_ATTRIBUTE_US_QTY);
            $stockItem = $this->_getStockItem($product);
        } elseif ($location === 'UK') {
            return false;   // 'false' prevents successfull UK update counter
            $qty = $itemNode->getAttribute(self::XML_ATTRIBUTE_UK_QTY);
            $stockItem = $this->_getStockItem($product, $this->_ukWebsiteId);
        } else {
            $this->log("Unknown warehouse location code: $location");
            return false;
        }
        $qty = (string)trim($qty);  // Prepare it for comparison

        // Check some conditions
        // Note: Integer-comparison cannot be made because empty() evaluates to true
        if (!$stockItem->getId()) {
            // $this->log("! Unable to find $location stock item data for SKU {$product->getSku()}");
            return false;
        } elseif (empty($qty) && $qty !== '0') {
            $this->log("Invalid $location qty for SKU {$product->getSku()}");
            return false;
        }

        $oldQty = (int)$stockItem->getQty();
        $qty = (int)$qty;
        if ($oldQty !== $qty) {
            try {
                $this->_updateStockItemQty($stockItem, $qty, $location);

                // Update LC to reflect correct availability
                if (($oldQty === 0 && $qty !== 0) || ($oldQty !== 0 && $qty === 0)) {
                    Mage::helper('sdm_catalog/lifecycle')
                        ->applyLifecycleModifications($stockItem->getProductId());
                    // Additionally takes store ID
                }

                $this->log("Updated $location qty SKU: {$product->getSku()}");  // verbose

                return true;

            } catch (Exception $e) {
                $this->log(
                    "Unable to update $location qty for SKU {$product->getSku()}"
                        . ' Error: ' . $e->getMessage()
                );
            }

        } else {
            // No need to log
            // $this->log("No qty change for $location SKU: {$product->getSku()}");
        }

        return false;
    }

    /**
     * Return the approprirate stock item object
     *
     * @param Mage_Catalog_Model_Producr $product
     * @param int                        $websiteId
     *
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    protected function _getStockItem($product, $websiteId = null)
    {
        $stockItem =Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($product->getId(), $websiteId);

        return $stockItem;
    }

    /**
     * Update qty
     *
     * @param Varien_Object $stockItem
     * @param int           $qty
     * @param str           $location
     *
     * @return void
     * @throws Mage_Core_Exception
     */
    protected function _updateStockItemQty($stockItem, $qty, $location)
    {
        $websiteIds = $this->_websiteIdsForInventory[$location];
        if ($location === 'UK') {
            $useDefaultStock = 0;
            // $websiteId = $this->_ukWebsiteId;
            $websiteIds = implode(',', $this->_websiteIdsForInventory['UK']);
            $sets = array(
                'use_default_website_stock' => 0,
                // 'website_id' => $this->_ukWebsiteId
            );
        } else {    // US/Default
            $useDefaultStock = 1;
            $websiteIds = $this->_websiteIdsForInventory['US'];
            $websiteIds[] = $this->_getAitocWebsiteId();
            $websiteIds = implode(',', $websiteIds);
            $sets = array(
                'use_default_website_stock' => 1,
                // 'website_id' => $this->_aitocWebsiteId
            );
        }
        $sets['qty'] = $qty;
        $where = "product_id = {$stockItem->getProductId()} AND stock_id = 1 AND website_id IN ($websiteIds)" . PHP_EOL;
        // print_r($this->getTableName('aitquantitymanager/stock_item'));print_r($sets);print_r($where.PHP_EOL);

        $result = $this->getConn('core_write')->update( // Returns number of rows affected
            $this->getTableName('aitquantitymanager/stock_item'),
            $sets,    // SET
            $where
        );

        if ($result == 0 || is_null($result)) {
            $this->log("Nothing updated for $location stock for ID {$stockItem->getProductId()}");
        }

        // Aitoc's extension makes stock item save extremely slow and resource-hungry
        // if ($location === 'US') {
        //     $stockItem->setData('qty', $qty);
        //     $stockItem->setUseDefaultWebsiteStock(1);
        // } elseif ($location === 'UK') {
        //     $stockItem->setData('qty', $qty);
        //     $stockItem->setStoreId($this->_ukStoreId);  // Simulate saving from admin
        // }

        // $stockItem->setCallingClass(    // Simulate saving from admin; note sure if needed
        //     'Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryObserver'
        // );
        // $stockItem->save();
    }


    /**
     * Initializes variables that need to be used repeatedly
     *
     * @return void
     */
    protected function _init()
    {
        // Life cycle
        $attribute = Mage::getModel('eav/config')
            ->getAttribute('catalog_product', 'life_cycle');
        $attributeId = $attribute->getAttributeId();
        $attributeOptions = $attribute->getSource()->getAllOptions(false);

        // Fill out mapping
        foreach ($attributeOptions as $option) {
            if (isset($option['label']) && !empty($option['label'])) {
                $this->_lifeCycleMap[$option['label']] = $option['value'];
            }
        }

        // Purchase hold
        $this->_purchaseHoldMap = array(
            'no' => '0',
            'yes' => '1'
        );

        // UK website ID
        $this->_setUkWebsiteId();
        $this->_setAitocWebsiteId();

        // UK store ID
        $stores = array();
        $collection = Mage::getModel('core/store')->getCollection();
        foreach ($collection as $store) {
            $stores[$store->getId()] = $store->getCode();
        }
        $stores = array_flip($stores);

        // Either of the UK stores can be assigned here
        $this->_ukStoreId = $stores[SDM_Core_Helper_Data::STORE_CODE_UK_BP];
        $this->_websiteIdsForInventory = $this->getWebsiteIdsForInventory();
    }

    /**
     * Manually update all aitoc_cataloginventory_stock_item.use_default_website_stock
     * values to 1, except for UK.
     *
     * @return void
     */
    protected function _resetGlobalInventoryUsageFlag()
    {
        $tableName = Mage::getSingleton('core/resource')->getTableName('aitquantitymanager/stock_item');
        $q = "UPDATE $tableName AS i
            SET i.`use_default_website_stock` = 1
            WHERE i.`website_id` != {$this->_getUkWebsiteId()}";
        $this->getConn('core_write')->query($q);
    }

    /**
     * Retrieve the UK website ID
     *
     * @return integer
     */
    protected function _getUkWebsiteId()
    {
        if (!$this->_ukWebsiteId) {
            $this->_setUkWebsiteId();
        }

        return $this->_ukWebsiteId;
    }

    /**
     * Retrieve the Aitoc/Default inventory website ID
     *
     * @return integer
     */
    protected function _getAitocWebsiteId()
    {
        if (!$this->_aitocWebsiteId) {
            $this->_setAitocWebsiteId();
        }

        return $this->_aitocWebsiteId;
    }

    /**
     * Sets the UK website ID variable
     *
     * @return void
     */
    protected function _setUkWebsiteId()
    {
        $websites = Mage::helper('sdm_core')->getAssociativeEllisonSystemCodes();
        $websites = array_flip($websites);
        $this->_ukWebsiteId = $websites[SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_UK];
    }

    /**
     * Sets the UK website ID variable
     *
     * @return void
     */
    protected function _setAitocWebsiteId()
    {
        $sql = "SELECT `website_id`
            FROM `{$this->getTableName('core/website')}`
            WHERE code = '" . SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_AITOC. "'";

        $this->_aitocWebsiteId = $this->getConn()->fetchOne($sql);
    }
}
