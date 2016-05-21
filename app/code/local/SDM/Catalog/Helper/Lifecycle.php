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
 * SDM_Catalog_Helper_Lifecycle class
 */
class SDM_Catalog_Helper_Lifecycle
    extends Mage_Core_Helper_Data
{
    /**
     * Attribute Set ID mapping
     */
    const ATTRIBUTE_SET_PRODUCTS = 9;
    const ATTRIBUTE_SET_IDEAS = 10;

    /**
     * Cache of attribute data which we'll be updating directly
     *
     * @var array
     */
    protected $_directAttributeCache = array(
        "visibility"  => array(
            "id"      => "102",
            "table"   => "catalog_product_entity_int",
            "quick"   => false
        ),
        "allow_cart_backorder" => array(
            "id"      => "190",
            "table"   => "catalog_product_entity_int",
            "quick"   => true
        ),
        "allow_checkout_backorder" => array(
            "id"      => "218",
            "table"   => "catalog_product_entity_int",
            "quick"   => true
        ),
        "allow_preorder" => array(
            "id"      => "188",
            "table"   => "catalog_product_entity_int",
            "quick"   => true
        ),
        "allow_quote" => array(
            "id"      => "189",
            "table"   => "catalog_product_entity_int",
            "quick"   => true
        ),
        "allow_cart"  => array(
            "id"      => "196",
            "table"   => "catalog_product_entity_int",
            "quick"   => true
        ),
        "allow_checkout" => array(
            "id"      => "209",
            "table"   => "catalog_product_entity_int",
            "quick"   => true
        ),
        "button_display_logic" => array(
            "id"      => "201",
            "table"   => "catalog_product_entity_text",
            "quick"   => true
        ),
        "price" => array(
            "id"      => "75",
            "table"   => "catalog_product_entity_decimal",
            "quick"   => false
        ),
        "weight" => array(
            "id"      => "80",
            "table"   => "catalog_product_entity_decimal",
            "quick"   => true
        )
    );

    /**
     * Modifies critical product values based off a product's lifecycle state.
     * These changes are applied directly to a saved product on the database.
     * Its primary purpose is to run after product saving or after inventory updates.
     *
     * @param  SDM_Catalog_Model_Product $product
     * @param  mixed                     $forcedStoreId
     * @return $this
     */
    public function applyLifecycleModifications($product, $forcedStoreId = null)
    {
        $productId = null;
        if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = $product->getId();
        } elseif (is_int($product) || is_numeric($product)) {
            $productId = (int)$product;
        }

        $this->_applyLifecycleModifications($productId, $forcedStoreId);
        return $this;
    }

    /**
     * Public function to apply lifecycle logic to a product. Optionally provide a
     * forced store id; otherwise, lifecycle logic will be applied to all stores.
     *
     * @param  int $productId
     * @param  int $forcedStoreId
     * @return $this
     */
    protected function _applyLifecycleModifications($productId, $forcedStoreId = null)
    {
        if (empty($productId)) {
            return false;
        }

        // Get websites product belongs to
        $productWebsites = Mage::getResourceModel('catalog/product_website')
            ->getWebsites(array($productId));
        if (empty($productWebsites)) {
            return $this;
        }
        $productWebsites = $productWebsites[$productId];

        // Get websites in store
        $storeWebsites = Mage::app()->getWebsites();

        foreach ($storeWebsites as $websiteId => $website) {
            // Is this product in this website?
            if (!in_array($websiteId, $productWebsites)) {
                continue;
            }

            // Then apply modifications to each store view
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $storeId => $store) {
                    if ($forcedStoreId === null || (int)$forcedStoreId == (int)$storeId) {
                        $this->_applyLifecycleModificationsByStore(
                            $productId,
                            $storeId,
                            $website
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Protected function to apply lifecycle modifications to a product.
     *
     * @param  int                     $productId
     * @param  int                     $storeId
     * @param  Mage_Core_Model_Website $website
     * @return $this
     */
    protected function _applyLifecycleModificationsByStore($productId, $storeId, $website)
    {
        if (empty($productId) || empty($storeId) || empty($website)) {
            return false;
        }

        $product = $this->_getProduct($productId, $storeId);

        // Start by loading stock product
        $stockItem = Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($product, $website->getId());

        // Is this a print catalog?
        if ($product->isPrintCatalog()) {
            // Do print-catalog specific save functions and exit
            return $this->_processPrintCatalog($product, $stockItem, $storeId, $website->getCode());
        }

        // Get our decision variables
        $productValues = $this->_getProductValuesByTypeId($product, $stockItem);
        if (empty($productValues)) {
            return $this;
        }

        // Get our resulting changes by mapping out our current decision state
        $mapping = Mage::helper('sdm_catalog/lifecycle_mapping');
        $mappedValues = $mapping->getMappedValues($productValues, $website->getCode());

        // Save the changes to the product
        $this->_runDirectProductLifecycleUpdate(
            $product,
            $mappedValues,
            $storeId
        );

        return $this;
    }

    /**
     * Returns the necessary product values based on the product attribute set id
     *
     * @param  Mage_Catalog_Model_Product             $product
     * @param  Mage_CatalogInventory_Model_Stock_Item $stockItem
     * @return array
     */
    protected function _getProductValuesByTypeId($product, $stockItem)
    {
        switch ($product->getAttributeSetId()) {
            case self::ATTRIBUTE_SET_PRODUCTS:
                return array(
                    'type'                  => 'product',
                    'price'                 => $product->getPrice(),
                    'lifecycle'             => trim(strtolower($product->getAttributeText('life_cycle'))),
                    'is_orderable'          => (bool)$product->getData('is_orderable'),
                    'is_preorderable'       => (bool)$product->getData('is_preorderable'),
                    'is_backorderable'      => (bool)$product->getData('is_backorderable'),
                    'is_displayable'        => $product->isDisplayable(),
                    'availability_message'  => $product->getData('availability_message'),
                    'has_stock'             => $stockItem->getQty() > 0 && $stockItem->getIsInStock()
                );
            case self::ATTRIBUTE_SET_IDEAS:
                return array(
                    'type'                  => 'idea',
                    'is_displayable'        => $product->isDisplayable()
                );
            default:
                return array();
        }
    }

    /**
     * Process print catalog, doesn't require any logic from
     * SDM_Catalog_Helper_Lifecycle_Mapping
     *
     * @param  mixed $product
     * @param  mixed $stockItem
     * @param  mixed $storeId
     * @param  mixed $websiteCode
     * @return SDM_Catalog_Helper_Lifecycle
     */
    protected function _processPrintCatalog($product, $stockItem, $storeId, $websiteCode)
    {
        $values = array(
            'visibility'  => 2,   // Catalog only visibility
            'price'       => 0,
            'weight'      => 1
        );

        // Add button display logic
        $isUk = $websiteCode == 'sizzix_uk';
        $values['button_display_logic'] = serialize(array(
            'type'              => 'add-to-cart',
            'value'             => $isUk ? 'Add To Basket' : 'Add To Cart',
            'visible_listing'   => true,
            'visible_pdp'       => true
        ));

        // Run mass attribute update on product
        $this->_runDirectProductLifecycleUpdate(
            $product,
            $values,
            $storeId
        );

        return $this;
    }

    /**
     * Returns the product. Must be from EAV tables, not flat.
     *
     * @param  int $productId
     * @param  int $storeId
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct($productId, $storeId)
    {
        return Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->load($productId);
    }

    /**
     * Update lifecycle field directly to the eav and flat tables.
     *
     * This bypasses Magento's indexing when updating lifecycle attributes,
     * while still keeping the index up to date. This is not a recommended practice,
     * and is only being used here for performance reasons.
     *
     * @param  int   $product
     * @param  array $values
     * @param  int   $storeId
     * @return SDM_Catalog_Helper_Lifecycle
     */
    protected function _runDirectProductLifecycleUpdate($product, $values, $storeId)
    {
        // Do we need to do a slow update?
        $requiresSlowUpdate = false;

        // Queue of queries to run for fast update
        $fastQueries = array();

        // Core write method
        $coreWrite = Mage::getSingleton('core/resource')
            ->getConnection('core_write');

        // Update eav tables
        foreach ($values as $attributeCode => $value) {
            // We should never get here, because this means we are updating
            // an attribute that's not in the _directAttributeCache array.
            // But, in case someone changes the lifecycle script and adds new
            // attributes, I'm leaving this check here.
            if (!isset($this->_directAttributeCache[$attributeCode])) {
                Mage::throwException(
                    "Error: attempting to update a lifecycle attribute that was not found " .
                    "in the _directAttributeCache array. Skipping lifecycle update."
                );
                return false;
            }
            $attribute = $this->_directAttributeCache[$attributeCode];

            // Format $comparisonValue for comparison purposes
            $compareValue = $product->getData($attributeCode);
            if ($attribute["table"] === "catalog_product_entity_int") {
                $compareValue = (int)$compareValue;
            } elseif ($attribute["table"] === "catalog_product_entity_text") {
                $compareValue = (string)$compareValue;
            } else {
                // Don't trust comparisons from other types
                $compareValue = false;
            }

            // Check if value has changed before attempting to update
            if ($compareValue === false || $compareValue !== $value) {
                // Check how we update this attribute
                if ($attribute["quick"]) {
                    // Run quick update, direct to the database
                    $fastQueries[] = $this->_buildFastQuery(
                        $coreWrite,             // Write adapter
                        $attribute["table"],    // Table name
                        $product->getId(),      // Entity ID
                        $attribute["id"],       // Attribute ID
                        $storeId,               // Store ID
                        $value                  // Value
                    );
                } else {
                    $requiresSlowUpdate = true;
                    break;
                }
            }
        }

        // What type of update do we need to run for this product?
        if ($requiresSlowUpdate) {
            // Run a slow update
            Mage::getModel("catalog/product_action")
                ->updateAttributes(
                    array($product->getId()),
                    $values,
                    $storeId
                );
        } elseif (count($fastQueries)) {
            // Run direct (fast) updates to EAV tables
            foreach ($fastQueries as $query) {
                $coreWrite->query($query);
            }

            // Update flat tables since we updated EAV
            $coreWrite->update(
                "catalog_product_flat_" . $storeId,
                $values,
                "`entity_id` = {$product->getId()} "
            );
        }

        return $this;
    }

    /**
     * Build the sql query used for fast udpates
     *
     * @param  Magento_Db_Adapter_Pdo_Mysql $adapter
     * @param  string                       $table
     * @param  int                          $entityId
     * @param  int                          $attributeId
     * @param  int                          $storeId
     * @param  mixed                        $value
     * @return string
     */
    protected function _buildFastQuery($adapter, $table, $entityId, $attributeId, $storeId, $value)
    {
        $query = "INSERT INTO ".$adapter->quoteIdentifier($table)." ";

        $query .= "(`entity_id`, `attribute_id`, `store_id`, `value`) ";

        $query .= $adapter->quoteInto(
            "VALUES (?) ",
            array(
                $entityId,
                $attributeId,
                $storeId,
                $value
            )
        );

        $query .= "ON DUPLICATE KEY UPDATE ";

        $query .= $adapter->quoteInto(
            "`entity_id` = ?, ",
            $entityId
        );

        $query .= $adapter->quoteInto(
            "`attribute_id` = ?, ",
            $attributeId
        );

        $query .= $adapter->quoteInto(
            "`store_id` = ?, ",
            $storeId
        );

        $query .= $adapter->quoteInto(
            "`value` = ?",
            $value
        );

        return $query;
    }
}
