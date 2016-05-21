<?php

require_once(dirname(__FILE__) . '/abstract_migrate.php');

class SDM_Shell_MigrateWishlist extends SDM_Shell_AbstractMigrate
{
    const CUT_OFF_DATE = '2015-01-01';

    protected $_logFile = 'wishlist_migration.log';

    /**
     * Magento read connection
     */
    protected $_dbcR = null;

    protected $_websiteMapping = array(
        'szus' => 1,
        'szuk' => 3,
        'erus' => 4,
        'eeus' => 5
    );
    protected $_storeMapping = array(
        'szus' => 1,
        'szuk' => 7,
        'erus' => 5,
        'eeus' => 6
    );

    public function run()
    {
        ini_set('memory_limit', '4000M');

        // $this->deleteAllFiles('log');
        $this->_init();

        // Clear all quotes
        $this->log('Removing all wishlists...');
        $this->_deleteAllWishlists();

        // Save test saved quote
        $this->log('Migrating wishlists...');
        $this->_processWishlists();
    }

    /**
     * Wrapper to migrated saved quotes
     */
    protected function _processWishlists()
    {
        $wislistIds = $this->_getValidEllisonWishlistIds();
        $i = 1;
        $N = count($wislistIds);

        foreach ($wislistIds as $one) {
            $list = $this->_getEllisonWishlist($one->id);

            // Check it has any products; SLQ query eliminates these cases
            $productIds = trim($list->product_ids);
            if (!$productIds) {
                $this->log("No products in list ID {$list->id}");
                continue;
            }

            // Check for a valid customer
            if (isset($this->_websiteMapping[$list->system])) {
                $customer = $this->_getCustomer($list->user_id, $this->_websiteMapping[$list->system]);
                // $customer = Mage::getModel('customer/customer')->load(1);    // Test only

                if (!$customer || !$customer->getId()) {
                    $this->log("Unable to find customer ID '{$list->user_id}'");
                    continue;
                }
            } else {
                $this->log("Unable to find website ID for system '{$list->system}'");
                continue;
            }

            // Add product to wishlist
            if ($this->_createList($list, $customer)) {
                $this->log("$i/$N: {$list->id} | {$list->name} saved");
            } else {
                $this->log("$i/$N: {$list->id} | {$list->name} failed");
            }
            $i++;
        }
    }

    /**
     * Creates a new wishlist
     *
     * @param stdClass $data Ellison List data
     */
    protected function _createList($data, $customer)
    {
        // Type of list depends on the 'name' of the Ellison list
        $name = trim($data->name);
        if ($name === 'Saved Items - To Buy Later') {
            return $this->_createSaveForLater($data, $customer);

        } elseif ($name === 'Items I own') {
            // These are just ordered item and a separate lists are not implemented
            // at launch
            return false;

        } elseif ($name === 'Untitled List') {
            // These go into the 'Main' wishlist
            return $this->_createMainList($data, $customer, 'Main');

        } else {
            // Other ones are custom wishlists
            return $this->_createMainList($data, $customer, $name);
        }

        return true;
    }

    protected function _createMainList($data, $customer, $name)
    {
        $productIds = $this->_getProductIds($data->product_ids);
        if (!$productIds) {
            $this->log("No products found for list ID {$data->id}");
            return false;
        }
        $storeId = $this->_storeMapping[$data->system];
        // $storeId = $this->_websiteMapping[$data->system];

        // Create Itoris "Main" list
        // @see Itoris_MWishlist_Model_Mwishlistnames::setName
        $q = "INSERT INTO `itoris_mwishlists` (`multiwishlist_name`, `multiwishlist_customer_id`, `multiwishlist_editable`, `multiwishlist_is_main`)
            VALUES (\"$name\", {$customer->getId()}, 1, 1)";
        try {
            $this->_dbcR->query($q);
        } catch (Exception $e) {
            $this->log('Query failed: ' . $q);
            return false;
        }
        $imWishlistId = (int)$this->_dbcR->fetchOne("SELECT LAST_INSERT_ID()");

        // Magento wishlist (only one per customer)
        try {
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer);
            if (!$wishlist->getId()) {
                $wishlist = Mage::getModel('wishlist/wishlist');
                $wishlist->setCustomerId($customer->getId())
                    ->setUpdatedAt($data->updated_at)
                    ->save();
            }

        } catch (Exception $e) {
            $this->log(
                "Unable to create wishlist for customer {$customer->getId()} | {$customer->getEmail()}"
                    . " Error: {$e->getMessage()}"
            );
            return false;
        }
        $wishlistId = $wishlist->getId();

        foreach ($productIds as $productId) {
            $productId = $productId['entity_id'];
            // $product = Mage::getModel('catalog/product')->load($productId);
            // if ($product->isInStock()) {
            //     echo 'Salable ' .PHP_EOL;
            // } else {
            //     echo 'Not salable ' .PHP_EOL;
            // }
            $requestParams = array(
                'product' => $productId,
                'imw' => $imWishlistId,
                'store_id' => $storeId,
            );
            $code = serialize($requestParams);

            // Save for wishlist_item and wishlist_item_option
            $q2 = "INSERT INTO `wishlist_item` (`wishlist_id`, `product_id`, `store_id`, `added_at`, `qty`)
                VALUES ($wishlistId, $productId, $storeId, '$data->updated_at', 1)";
            try {
                $this->_dbcR->query($q2);
                $itemId = $this->_dbcR->fetchOne("SELECT LAST_INSERT_ID()");
            } catch (Exception $e) {
                return $this->_failedWishlist($customer, $e, $wishlistId, $imWishlistId, $q2);
            }

            $q3 = "INSERT INTO `wishlist_item_option` (`wishlist_item_id`, `product_id`, `code`, `value`)
                VALUES ($itemId, $productId, 'info_buyRequest', '$code')";
            try {
                $this->_dbcR->query($q3);
            } catch (Exception $e) {
                return $this->_failedWishlist($customer, $e, $wishlistId, $imWishlistId, $q3);
            }

            // Save for itoris_mwishlist_settings
            $q4 = "INSERT INTO `itoris_mwishlist_items` (`item_id`, `multiwishlist_id`)
                VALUES ($itemId, $imWishlistId)";
            try {
                $this->_dbcR->query($q4);
            } catch (Exception $e) {
                return $this->_failedWishlist($customer, $e, $wishlistId, $imWishlistId, $q4);
            }

            // Below method is extremely slow because addNewItem()
            // // Add items
            // $requestParams = array(
            //     'product' => $productId,
            //     'imw' => $imWishlistId,
            //     'store_id' => $storeId,
            //     // 'form_key' => ''
            // );
            // $buyRequest = new Varien_Object($requestParams);
            // $result = $wishlist->addNewItem($productId, $buyRequest)
            //     ->save();
        }

        return true;
    }

    /**
     * Logs error, removes created wishlists
     */
    protected function _failedWishlist($customer, $e, $wishlistId, $imWishlistId, $q)
    {
        // Remove associated records
        $this->_dbcR->query("DELETE FROM `wishlist` WHERE wishlist_id = $wishlistId");
        $this->_dbcR->query("DELETE FROM `itoris_mwishlists` WHERE multiwishlist_id = $imWishlistId");
        $this->log("Failed to create wishlist for customer {$customer->getId()} | {$customer->getEmail()}");
        $this->log('Failed query: ' . $q);
        return false;
    }

    protected function _createSaveForLater($data, $customer)
    {
        $productIds = $this->_getProductIds($data->product_ids);
        if (!$productIds) {
            $this->log("No products found for list ID {$data->id}");
            return false;
        }

        foreach ($productIds as $id) {
            $product = Mage::getModel('catalog/product')->load($id);
            if (!$product->getId()) {
                $this->log("Product ID {$id} not found");
                continue;
            }
            $request = array(
                'qty' => 1,
                'original_qty' => 1,
                'product' => $product->getId()
            );

            $list = Mage::getModel('saveforlater/item')
                ->setCustomerId($customer->getId())
                ->setProductId($product->getId())
                ->setName($product->getName())
                ->setQty(1)
                ->setPrice($product->getFinalPrice())
                ->setBuyRequest(serialize($request))
                ->setDateSaved($data->created_at)
                ->save();
        }

        return true;
    }

    /**
     * Retrieves all wishlists that are still valid
     *
     * @return stdClass
     */
    protected function _getValidEllisonWishlistIds()
    {
        // Get the cut-off date ignoring the time of day
        $cutOffDate = date(self::CUT_OFF_DATE);

        $q = "SELECT id
            FROM lists
            WHERE updated_at >= '$cutOffDate' AND `active` = 1 AND product_ids != ''
                AND `name` != 'Items I own'"
            // . " AND user_id = '53289fd3fb60b50b430003b1'"
            ;
        $wishlistIds = $this->query($q);
        // echo $q.PHP_EOL; print_r($wishlistIds);

        return $wishlistIds;
    }

    /**
     * Deletes both the Magento, Itoris, and Redstage wishlists
     */
    protected function _deleteAllWishlists()
    {
        // Magento
        $this->getConn('core_write')->query("DELETE FROM wishlist");
        // $collection = Mage::getModel('wishlist/wishlist')->getCollection()
        //     ->addFieldToSelect(array('wishlist_id'));
        // foreach ($collection as $object) {
        //     $object->delete();
        //     $this->log('Wishlist entity ID: ' . $object->getId() . ' --> Deleted');
        // }

        // Itoris
        $this->getConn('core_write')->query("DELETE FROM itoris_mwishlists");

        // Redstage
        $this->getConn('core_write')->query("TRUNCATE saveforlater_item");
    }

    /**
     * Retrieves the Magento customer given the user_id in the ported MongoDB
     *
     * @param int $userId
     * @param int $websiteId
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer($userId, $websiteId)
    {
        $email = $this->_getEllisonCustomer($userId);

        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);

        return $customer;
    }

    protected function _getEllisonCustomer($userId)
    {
        $q = "SELECT * FROM users WHERE mongoid = '$userId'";
        $customer = $this->query($q);
        if (!$customer) {
            return;
        }

        $customer = reset($customer);

        return trim($customer->email);
    }

    protected function _getEllisonWishlist($id)
    {
        $list = $this->query("SELECT l.* FROM lists AS l WHERE l.`id` = $id LIMIT 1");
        if (!$list) {
            $this->log("No wishlist found for ID $id");
             return false;
        }

        return reset($list);
    }

    protected function _getProductIds($ids)
    {
        $skus = array();
        $ids = explode(',', $ids);
        foreach ($ids as &$id) {
            $id = "'$id'";
        }
        $ids = implode(',', $ids);

        $result = $this->query("SELECT item_num FROM products WHERE mongoid IN ($ids)");
        if (!$result) {
            $this->log("No wishlist found for ID $id");
            return false;
        }

        // Get product collection
        foreach ($result as $one) {
            $skus[] = "'{$one->item_num}'";
        }
        $skus = implode(',', $skus);

        $entityIds = $this->getConn()->fetchAll("SELECT entity_id FROM catalog_product_entity WHERE sku IN ($skus)");

        return $entityIds;
    }


    protected function _init()
    {
        $this->_initMongoDb();

        $this->_dbcR = $this->getConn('core_write');
    }
}

$shell = new SDM_Shell_MigrateWishlist();
$shell->run();