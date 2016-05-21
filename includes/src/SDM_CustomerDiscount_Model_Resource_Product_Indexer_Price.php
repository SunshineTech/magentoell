<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

// Ultimately extends Mage_Catalog_Model_Resource_Product_Indexer_Price
/**
 * SDM_CustomerDiscount_Model_Resource_Product_Indexer_Price class
 */
class SDM_CustomerDiscount_Model_Resource_Product_Indexer_Price
    extends Aitoc_Aitquantitymanager_Model_Mysql4_FrontCatalogResourceEavMysql4ProductIndexerPrice
{
    /**
     * Retailer customer group discount percentages
     *
     * @var array
     */
    protected $_retailerGroupDiscounts = array();

    /**
     * Promotional prices
     *
     * @var array
     */
    protected $_promoPrices = array();

    /**
     * Applied discount types
     *
     * @var array
     */
    protected $_appliedDiscountTypes = array();

    /**
     * Product prices (regular, special)
     *
     * @var array
     */
    protected $_productPrices = array();

    /**
     * Custom final prices
     *
     * @var array
     */
    protected $_productCustomFinalPrices = array();

    /**
     * Negotiated product products (not used)
     *
     * @var array
     */
    // protected $_negotiatedProductPrices = array();

    /**
     * Rebuild all index data. Update Retailer website's prices.
     *
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->beginTransaction();
        try {
            $this->clearTemporaryIndexTable();
            $this->_prepareWebsiteDateTable();
            $this->_prepareTierPriceIndex();
            $this->_prepareGroupPriceIndex();

            $indexers = $this->getTypeIndexers();
            foreach ($indexers as $indexer) {
                /**
                 * @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Price_Interface
                */
                $indexer->reindexAll();
            }

            /**
             * Rewrite begins
             *
             * Prior to syncing data to the real table, update compared prices,
             * custom prices, etc.
             */
            $this->_updateCustomPrices();

            // Must be done after all custom prices are indexed
            $this->_updateFinalPrices();
            /**
             * Rewrite ends
             */

            $this->syncData();
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Update custom prices
     *
     * This method indexes only Euro prices as of v0.5.4. It should be updated
     * as required.
     *
     * @return void
     */
    protected function _updateCustomPrices()
    {
        $this->clearCustomIndexTable();
        $this->_indexEuroPrices();
    }

    /**
     * Indexes the Euro prices
     *
     * @return void
     */
    protected function _indexEuroPrices()
    {
        $website = Mage::helper('sdm_core')->getWebsiteByCode(SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK);
        $store = Mage::helper('sdm_core')->getStoreByCode(SDM_Core_Helper_Data::STORE_CODE_UK_EU);
        $storeId = $store->getId();
        $prices = $this->_getProductPrices(
            SDM_Catalog_Helper_Data::EURO_CODE,
            $website->getId(),
            $storeId
        );

        // Get All indexed product IDs for the UK website
        $productIds = $this->_getRelevantProductIds($storeId);

        try {
            $i = 0; // Don't depend on the array returned above
            $query = '';

            foreach ($productIds as $id) {
                if (!isset($prices[$id]['price'])) {
                    continue;
                }

                if (isset($prices[$id]['special_price'])) {
                    $finalPrice = $prices[$id]['special_price'];
                } else {
                    $finalPrice = $prices[$id]['price'];
                }

                if ($i % 2000 === 0) {
                    $this->_updateCustomTable($query);
                    $query = "INSERT INTO `{$this->getCustomIndexTableName()}` VALUES ($id,$storeId,{$prices[$id]['price']},$finalPrice),";
                } else {
                    $query .= "($id,$storeId,{$prices[$id]['price']},$finalPrice),";
                }
                $this->_productCustomFinalPrices[$id][$storeId] = $finalPrice;
                $i++;
            }
            $this->_updateCustomTable($query);

        } catch (Exception $e) {
            Mage::throwException("Unable to index Euro prices. {$e->getMessage()}");
        }
    }

    /**
     * Runs the SQL query if not empty
     *
     * @param str $query
     *
     * @return void
     */
    protected function _updateCustomTable($query)
    {
        $query = trim($query);
        if (empty($query)) {
            return;
        }

        $query = rtrim($query, ',');
        $query .= ';';
        $this->_getWriteAdapter()->query($query);
    }

    /**
     * Returns the corresponding product price data with special prices, if
     * they are active (i.e. within data range).
     *
     * @param str $code
     * @param int $websiteId
     * @param int $storeId
     *
     * @return array
     */
    protected function _getProductPrices($code, $websiteId, $storeId)
    {
        // Get the relevant collection
        $prices = array();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId($storeId)
            ->addAttributeToSelect(
                array(
                    'entity_id', "price_$code", "special_price_$code",
                    'special_from_date', 'special_to_date', 'status'
                )
            )
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        // Put data into a friendly array
        $today = strtotime(Mage::getModel('core/date')->date('Y-m-d'));
        $pastDate = strtotime(Mage::getModel('core/date')->date('Y-m-d') . ' - 1 week'); // Always smaller than $today
        $futureDate = strtotime(Mage::getModel('core/date')->date('Y-m-d') . ' + 1 week');

        foreach ($collection as $product) {
            $id = $product->getId();

            if (!(double)$product->getData("price_$code")) {
                continue;
            }
            $prices[$id]['price'] = $product->getData("price_$code");

            if ($product->getData("special_price_$code") != null) {
                if ($product->getData('special_from_date') == null) {
                    $from = $pastDate;
                } else {
                    $from = strtotime($product->getData('special_from_date'));
                }

                if ($product->getData('special_to_date') == null) {
                    $to = $futureDate;
                } else {
                    $to = strtotime($product->getData('special_to_date'));
                }

                if ($from <= $today && $today <= $to) {
                    $prices[$id]['special_price'] = $product->getData("special_price_$code");
                } else {
                    // $prices[$id]['special_price'] = null;
                }
            }
        }

        return $prices;
    }

    /**
     * Return the product IDs that are active and assigned to the given store ID
     *
     * @param int $storeId
     *
     * @return array
     */
    protected function _getRelevantProductIds($storeId)
    {
        $productIds = array();
        $collection = Mage::getModel("catalog/product")->getCollection()
            ->setStoreId($storeId)
            ->addAttributeToSelect(array('status', 'type_id'))
            ->addAttributeToFilter(
                'status',
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED
            )
            ->addAttributeToFilter(
                'type_id',
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
            );

        foreach ($collection as $product) {
            $productIds[] = $product->getId();
        }

        return $productIds;
    }

    /**
     * Clears the custom price index table
     *
     * @return void
     */
    public function clearCustomIndexTable()
    {
        $customIndexTable = $this->getCustomIndexTableName();
        $this->_getWriteAdapter()->delete($customIndexTable);
    }

    /**
     * Returns the custom price index table's name
     *
     * @return str
     */
    protected function getCustomIndexTableName()
    {
        return $this->getTable('sdm_catalog/index_custom_price');
    }

    /**
     * Update the temporary index price index table with the lowest catalog
     * prices, which are derived from various sources.
     *
     * Note: When indexing, Mage_Catalog_Model_Resource_Product_Indexer_Price_Default::_prepareFinalPriceData
     * is used. It queries the database in such a way to get the data for the
     * index table at once. This is the reason price updates are done after
     * they are already written to the temporary table.
     *
     * @return void
     */
    protected function _updateFinalPrices()
    {
        $newPrices = array();
        $newCustomPrices = array();
        $allWebsiteIds = $this->_getAllWebsites();
        $retailerWebsiteId = $this->_getRetailerWebsiteId();
        $allOtherWebsiteIds = $allWebsiteIds;
        unset($allOtherWebsiteIds[$retailerWebsiteId]);
        $websiteIdsToStoreIds = Mage::helper('sdm_core')->websiteIdsToStoreIds();
        $storeCodes = Mage::helper('sdm_core')->getAssociativeEllisonStoreCodes();

        /**
         * Cache pricing data required for indexing. Must be done in this order.
         */
        $this->_initRetailerGroupDiscounts();
        // $this->_initNegotiatedProductPrices();   // Cannot be indexed
        $this->_initProductPrices();
        $this->_initPromoPrices();

        $allIndexedFinalPrices = $this->_getAllIndexedFinalPrices($this->getIdxTable());
        // Mage::log('Product Prices'); Mage::log($this->_productPrices);
        // Mage::log('Custom Final'); Mage::log($this->_productCustomFinalPrices);
        // Mage::log('Promo Prices'); Mage::log($this->_promoPrices);
        // Mage::log($websiteIdsToStoreIds);
        // Mage::log($storeCodes);
        // Mage::log($allIndexedFinalPrices);
        // Mage::log($this->_promoPrices);

        foreach ($allIndexedFinalPrices as $productId => $groupPrices) {
            if (!isset($this->_productPrices[$productId])) {
                continue;
            }

            // For each of the group prices, compute all possible catalog prices
            // and save the lowest one.
            // Mirroring SDM_CustomerDiscount_Model_Observer::applyPriceComparison
            foreach ($groupPrices as $customerGroupId => $finalPrices) {
                /**
                 * All website get their promo, special, and final prices compared
                 */
                foreach ($finalPrices as $websiteId => $finalPrice) {
                    /**
                     * Prepare prices independent of website and required for indexing
                     */
                    foreach ($websiteIdsToStoreIds[$websiteId] as $storeId) {
                        // Custom prices have their own final prices
                        if (isset($this->_productCustomFinalPrices[$productId][$storeId])) {
                            $finalPrice = $this->_productCustomFinalPrices[$productId][$storeId];
                        }

                        // MSRP/Regular and special price
                        $regularPrice = $this->_productPrices[$productId][$storeId]['price'];
                        $specialPrice = $this->_productPrices[$productId][$storeId]['special_price'];
                        if (!$specialPrice) {
                            $specialPrice = SDM_CustomerDiscount_Helper_Price::VERY_BIG_NUMBER;
                        }

                        // Retailer discount price
                        $discountCatId = $this->_productPrices[$productId][$storeId]['discount_catgory_id'];
                        $retailerDiscountPrice = SDM_CustomerDiscount_Helper_Price::VERY_BIG_NUMBER;
                        if (isset($this->_retailerGroupDiscounts[$customerGroupId][$discountCatId])
                            && $this->_retailerGroupDiscounts[$customerGroupId][$discountCatId] > 0
                        ) {
                            $discountPercentage = $this->_retailerGroupDiscounts[$customerGroupId][$discountCatId];
                            $retailerDiscountPrice = $regularPrice * (100-$discountPercentage)/100;
                        }

                        // Promotional price
                        $promoPrice = SDM_CustomerDiscount_Helper_Price::VERY_BIG_NUMBER;
                        if (isset($this->_promoPrices[$productId][$storeId])) {
                            $promoPrice = $this->_promoPrices[$productId][$storeId];
                        }

                        $lowestPrice = min(
                            $finalPrice,    // Incorporates regular and special prices
                            // $specialPrice,  // Need to include?
                            $promoPrice
                        );

                        // Retailer website has more price to compare
                        if ($websiteId == $retailerWebsiteId) {
                            $lowestPrice = min($lowestPrice, $retailerDiscountPrice);
                        }

                        /**
                         * Mark to index if lower price is available
                         *
                         * Important:
                         * Natively indexed prices are per website ID. Custom
                         * prices are per store ID.
                         */
                        if ($lowestPrice <= $finalPrice
                            && $storeCodes[$storeId] != SDM_Core_Helper_Data::STORE_CODE_UK_EU
                        ) {
                            $newPrices[$productId][$customerGroupId][$websiteId] = $lowestPrice;

                        } elseif ($lowestPrice <= $finalPrice
                            && $storeCodes[$storeId] == SDM_Core_Helper_Data::STORE_CODE_UK_EU
                        ) {
                            $newCustomPrices[$productId][$storeId] = $lowestPrice;
                        }

                        // Save applied discount flags to index as well
                        // Important: Must be by store ID
                        if ($lowestPrice == $specialPrice && $finalPrice == $specialPrice) {
                            $this->_appliedDiscountTypes[$productId][$storeId]
                                = SDM_Catalog_Helper_Data::DISCOUNT_TYPE_APPLIED_CODE_SPECIAL_PRICE;
                        } elseif ($lowestPrice == $promoPrice && $finalPrice > $promoPrice) {
                            $this->_appliedDiscountTypes[$productId][$storeId]
                                = SDM_Catalog_Helper_Data::DISCOUNT_TYPE_APPLIED_CODE_PROMO;
                        }

                        // Debugging code
                        // if (in_array($productId, array(44087, 44088))
                        //     && $storeId == 1
                        //     && $customerGroupId === 0
                        // ) {
                        //     Mage::log("Product ID: $productId");
                        //     Mage::log("Lowest: $$lowestPrice");
                        //     Mage::log("Special: $$specialPrice");
                        //     Mage::log("Promo: $$promoPrice");
                        //     Mage::log("Final: $$finalPrice");
                        //     Mage::log('');
                        // }
                    }
                }   // End of website loop
            }
        }
        // Mage::log('Product Prices | '); Mage::log($this->_productPrices);Mage::log($newPrices);
        // Mage::log('New Prices'); Mage::log($newPrices);
        // Mage::log('New Custom Prices'); Mage::log($newCustomPrices);
        // Mage::log($this->_appliedDiscountTypes);

        // Update temporary index table
        $this->_updateIndexTable($newPrices);
        $this->_updateCustomIndexTable($newCustomPrices);

        // Update the applied discount type index table
        $this->_updateAppliedDiscountTypeIndexTable();

        // Clear out cache variables
        unset($this->_productPrices);
        unset($this->_retailerGroupDiscounts);
        unset($this->_promoPrices);
    }

    /**
     * Update index table
     *
     * @return void
     */
    protected function _updateAppliedDiscountTypeIndexTable()
    {
        // Mage::log($this->_appliedDiscountTypes);
        $tableName = Mage::getSingleton('core/resource')
            ->getTableName('customerdiscount/applied_discount');

        // Remove all data first
        // Note: TRUNCATE cannot be used in a transaction because it cannot be rolled back.
        //       Auto-increment cannot be reset, either.
        $this->_getWriteAdapter()->delete($tableName);

        // Insert all records, a chunk at a time
        $i = 1; // Explicitly assign row ID
        $chunks = array_chunk($this->_appliedDiscountTypes, 5000, true);
        foreach ($chunks as $chunk) {
            $sql = "INSERT INTO `$tableName` (id,store_id, product_id, type) VALUES ";
            foreach ($chunk as $productId => $products) {
                foreach ($products as $storeId => $type) {
                    $sql .= "($i,$storeId,$productId,'$type'),";
                    $i++;
                }
            }
            $sql = rtrim($sql, ',');

            $this->_getWriteAdapter()->query($sql);
        }
    }

    /**
     * Update the temporary index table before it is synced
     *
     * @param array $priceData
     *
     * @return void
     */
    protected function _updateIndexTable($priceData)
    {
        $write = $this->_getWriteAdapter();
        $tempIndexTable = $this->getIdxTable();

        foreach ($priceData as $productId => $groupPrices) {
            foreach ($groupPrices as $groupId => $webPrice) {
                foreach ($webPrice as $websiteId => $price) {
                    try {
                        $where = "entity_id = '$productId' "
                            . "AND customer_group_id = '$groupId' "
                            . "AND website_id = '$websiteId' "
                            . "AND tax_class_id = '2'";
                        $bind = array(
                            'final_price' => round($price, 2)
                        );
                        $write->update($tempIndexTable, $bind, $where);

                    } catch (Exception $e) {
                        Mage::helper('customerdiscount')->log(
                            "Failed to update indexed price at Product ID/Group ID/Website ID: "
                                . "$productId/$groupId/$websiteId",
                            Zend_Log::ERR
                        );
                    }
                }
            }
        }
    }

    /**
     * Update the custom price index table. There is no temporary table.
     *
     * @param array $priceData
     *
     * @return void
     */
    protected function _updateCustomIndexTable($priceData)
    {
        $write = $this->_getWriteAdapter();
        $indexTable = $this->getTable('sdm_catalog/index_custom_price');

        foreach ($priceData as $productId => $storePrices) {
            foreach ($storePrices as $storeId => $price) {
                try {
                    $where = "entity_id = '$productId' "
                        . "AND store_id = '$storeId'";
                    $bind = array(
                        'final_price' => round($price, 2)
                    );
                    $write->update($indexTable, $bind, $where);

                } catch (Exception $e) {
                    Mage::helper('customerdiscount')->log(
                        "Failed to update indexed custom price at Product ID/Store ID: "
                            . "$productId/$storeId",
                        Zend_Log::ERR
                    );
                }

            }
        }
    }

    /**
     * Initialize the retailer group discount percentages
     *
     * @return void
     */
    protected function _initRetailerGroupDiscounts()
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                $this->getTable('customerdiscount/discountgroup'),
                array('category_id', 'customer_group_id', 'amount')
            );
        $results = $this->_getReadAdapter()->fetchAll($select);

        foreach ($results as $row) {
            $this->_retailerGroupDiscounts[$row['customer_group_id']][$row['category_id']]
                = $row['amount'];
        }
        // Mage::log($this->_retailerGroupDiscounts);
    }

    /**
     * Init product prices
     *
     * @return void
     */
    protected function _initProductPrices()
    {
        $stores = Mage::app()->getStores();
        $storeCodes = Mage::helper('sdm_core')->getAssociativeEllisonStoreCodes();

        // Iterating through all of the stores and assigning their prices to by
        // websites is fine because prices are at the website level.
        foreach ($stores as $store) {
            $storeId = $store->getId();
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(
                    array(
                        'status',
                        'type_id', 'price', 'tag_discount_category', 'special_price',
                        'price_euro', 'special_price_euro'
                    )
                )
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->addAttributeToFilter(
                    'type_id',
                    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                )
                ->setStore($store);

            // Save prices per product and website
            foreach ($collection as $product) {
                // MSRP required to calculate Ellison's retailer discounted price
                if ($storeCodes[$store->getId()] == SDM_Core_Helper_Data::STORE_CODE_UK_EU) {
                    $this->_productPrices[$product->getId()][$storeId]['price'] = $product->getPriceEuro();
                    $this->_productPrices[$product->getId()][$storeId]['special_price'] = $product->getSpecialPriceEuro();
                } else {
                    $this->_productPrices[$product->getId()][$storeId]['price'] = $product->getPrice();
                    $this->_productPrices[$product->getId()][$storeId]['special_price'] = $product->getSpecialPrice();

                    // Debugging code
                    // if ($storeId == 1 && $product->getSku() == '656430') {
                    //     echo "Store ID: $storeId" . PHP_EOL;
                    //     print_r($product->debug());
                    //     echo PHP_EOL;
                    // }
                }
                $this->_productPrices[$product->getId()][$storeId]['discount_catgory_id'] = (int)$product->getTagDiscountCategory();
            }
            // if ($storeId == 1) {
            //     Mage::log("Store ID: $storeId");
            //     Mage::log($collection->getSelect()->__toString());
            //     Mage::log($this->_productPrices);
            // }
        }
    }

    /**
     * Initializes all of the active promotions' prices
     *
     * @return void
     */
    protected function _initPromoPrices()
    {
        // $allWebsiteIds = $this->_getAllWebsites();
        $websiteIdsToStoreIds = Mage::helper('sdm_core')->websiteIdsToStoreIds();

        // Iterate through all websites since promotion can be applied to any
        // set of websites
        foreach ($websiteIdsToStoreIds as $websiteId => $storeIds) {
            // Get all active promotions per on website, not store
            $promos = Mage::helper('taxonomy')->getActivePromotions($websiteId);
            foreach ($promos as $promo) {
                $products = $promo->getProducts();
                foreach ($products as $product) {
                    // Calculate all promo prices
                    foreach ($storeIds as $storeId) {
                        if (!isset($this->_productPrices[$product['product_id']])) {
                            // echo "Product ID {$product['product_id']} not assigned to Store $storeId". PHP_EOL;
                            continue;
                        }

                        $basePrice = $this->_productPrices[$product['product_id']][$storeId]['price'];

                        // Cache all promotional prices and save the lowest if a product
                        // appears in more than one promotion
                        if (isset($this->_promoPrices[$product['product_id']][$storeId])) {
                            $this->_promoPrices[$product['product_id']][$storeId]
                                = min(
                                    $this->_promoPrices[$product['product_id']][$storeId],
                                    Mage::helper('taxonomy')->calculatePromoPrice(
                                        $basePrice,
                                        $product['discount_type'],
                                        $product['discount_value']
                                    )
                                );
                        } else {
                            $this->_promoPrices[$product['product_id']][$storeId]
                                = Mage::helper('taxonomy')->calculatePromoPrice(
                                    $basePrice,
                                    $product['discount_type'],
                                    $product['discount_value']
                                );
                        }
                    } // End of store IDs
                } // End of promo products
            } // End of promos
        } // End of website IDs

        // Mage::log('Promo Prices'); Mage::log($this->_promoPrices);
    }

    /**
     * Retrieves all of the indexed (natively by Magento) final prices. As price
     * is set at the website level, all of the different website-level final
     * prices must be obtained.
     *
     * Note that SZUS/EEUS, SZUK, and ERUS have different prices, but all of the
     * prices are retrieved since they all need to be updated/re-indexed anyway.
     *
     * @param str $table
     *
     * @return array
     */
    protected function _getAllIndexedFinalPrices($table)
    {
        $prices = array();

        // Tax class ID is hard-coded with 2 for 'Taxable Goods'
        // @see Mage/Tax/data/tax_setup/data-install-1.6.0.0.php
        $select = $this->_getReadAdapter()->select()
            ->from(
                $table,
                array(
                    'entity_id',
                    'customer_group_id',
                    'final_price',
                    'website_id',
                )
            )
            ->where('tax_class_id = ?', 2)  // Tax class is hard-coded in Magento
            ->where('final_price IS NOT NULL');

        $results = $this->_getReadAdapter()->fetchAll($select);
        foreach ($results as $row) {
            $prices[$row['entity_id']][$row['customer_group_id']][$row['website_id']] = $row['final_price'];
        }

        return $prices;
    }

    /**
     * Returns the retailer website ID
     *
     * @return int
     */
    protected function _getRetailerWebsiteId()
    {
        return Mage::getModel('core/website')
            ->load(
                SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE,
                'code'
            )
            ->getId();
    }

    /**
     * Returns all websites
     *
     * @return array
     */
    protected function _getAllWebsites()
    {
        $ids = array();
        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            $ids[$website->getId()] = $website->getId();
        }

        return $ids;
    }
}
