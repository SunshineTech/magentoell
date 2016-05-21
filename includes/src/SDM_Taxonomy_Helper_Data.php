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
 * SDM_Taxonomy_Helper_Data class
 */
class SDM_Taxonomy_Helper_Data extends SDM_Core_Helper_Data
{
    const DISCOUNT_TYPE_PERCENT_CODE = 'percent';
    const DISCOUNT_TYPE_FIXED_CODE = 'fixed';
    const DISCOUNT_TYPE_ABSOLUTE_CODE = 'absolute';

    const DISCOUNT_TYPE_PERCENT_LABEL = 'Percent';
    const DISCOUNT_TYPE_FIXED_LABEL = 'Fixed';
    const DISCOUNT_TYPE_ABSOLUTE_LABEL = 'Absolute';

    /**
     * Allowed image file types
     *
     * @var array
     */
    protected $_allowedImageFileTypes = array('jpg', 'jpeg', 'gif', 'png');

    /**
     * Taxonomy valid dates and website assignment by entity IDs
     *
     * @var array
     */
    protected $_taxonomyItemData = null;

    /**
     * Current time in Y-m-d format
     *
     * @var str
     */
    protected $_now = null;

    /**
     * An array of stored attributes for filtering
     *
     * @var array
     */
    protected $_storedAttributes = array();

    /**
     * Current time in Y-m-d format
     *
     * @var array
     */
    protected $_storeIdsToWebsiteIds = null;

    /**
     * An array of taxonomy attribute IDs
     *
     * @var array
     */
    protected $_taxonomyAttributeIds = null;

    /**
     * Create variables to be used for indexing
     *
     * @return void
     */
    public function initForIndexing()
    {
        $this->_initDataForIndexing();
    }

    /**
     * Returns the lowest of all active promotional prices
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $websiteId
     *
     * @return double
     */
    public function getPromoPrice($product, $websiteId)
    {
        $prices = array();
        $basePrice = $product->getPrice();

        // Check all active promotions
        $promos = $this->getActivePromotions($websiteId);
        // Check for the given products
        foreach ($promos as $promo) {
            $priceData = $promo->getProducts($product); // Returns an array
            if (empty($priceData)) {
                continue;
            }
            $priceData = reset($priceData);

            $prices[] = $this->calculatePromoPrice(
                $basePrice,
                $priceData['discount_type'],
                $priceData['discount_value']
            );
        }

        if (empty($prices)) {
            // Note: SDM_Taxonomy is not dependent on SDM_CustomerDiscount
            return 999999; // @see SDM_CustomerDiscount_Helper_Price::VERY_BIG_NUMBER
        }

        return min($prices);
    }

    /**
     * Returns all of the active promotions
     *
     * @param int $websiteId
     *
     * @return array of SDM_Taxonomy_Model_Item
     */
    public function getActivePromotions($websiteId)
    {
        $promos = array();
        $collection = Mage::getResourceModel('taxonomy/item_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('type', SDM_Taxonomy_Model_Attribute_Source_Special::CODE);

        foreach ($collection as $promo) {
            if ($promo->isActive($websiteId)) {
                $promos[] = $promo;
            }
        }

        return $promos;
    }

    /**
     * Returns the calculated discounted price.
     *
     * @param double $basePrice
     * @param str    $type
     * @param double $value
     *
     * @return double
     */
    public function calculatePromoPrice($basePrice, $type, $value)
    {
        if ($type == self::DISCOUNT_TYPE_PERCENT_CODE) {
            $price = $basePrice * (100 - $value)/100;
        } elseif ($type == self::DISCOUNT_TYPE_FIXED_CODE) {
            $price = $value;
        } elseif ($type == self::DISCOUNT_TYPE_ABSOLUTE_CODE) {
            $price = $basePrice - $value;
        } else {
            $price = 999999;    // Should not happen
        }

        return round($price, 2);
    }

    /**
     * Adds a taxonomy filter to a collection
     *
     * @param mixed $collection
     * @param int   $attributeCode
     * @param mixed $value
     *
     * @return void
     */
    public function addTaxonomyFilter($collection, $attributeCode, $value)
    {
        $grouping = $collection->getSelect()->getPart(Zend_Db_Select::GROUP);
        if (empty($grouping)) {
            $collection->getSelect()->group('entity_id');
        }
        $attribute  = $this->_getAttribute($attributeCode);
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        ;
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $value = is_array($value) ? $value : array($value);
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            $connection->quoteInto("{$tableAlias}.value IN (?)", $value)
        );

        $storeId = Mage::app()->getStore()->getId();
        $tableName = $this->getTableName('catalog/product_index_eav');

        $collection->getSelect()->join(
            array($tableAlias => $tableName),
            implode(' AND ', $conditions),
            array()
        );
    }

    /**
     * Gets attribute model from code
     *
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    protected function _getAttribute($code)
    {
        if (!isset($this->_storedAttributes[$code])) {
            $this->_storedAttributes[$code] = Mage::getModel('eav/entity_attribute')
                ->loadByCode(
                    Mage_Catalog_Model_Product::ENTITY, $code
                );
        }
        return $this->_storedAttributes[$code];
    }

    /**
     * Remove all child records associated with the parent ID
     *
     * @param int $id
     *
     * @return void
     */
    public function deleteChildRecords($id)
    {
        $collection = Mage::getModel('taxonomy/item_date')->getCollection()
            ->addFieldToFilter('taxonomy_id', $id);

        foreach ($collection as $tag) {
            $tag->delete();
        }
    }

    /**
     * Convert a taxonomy collection into an array of label/values for
     * use as attribute option source arrays.
     *
     * @param SDM_Taxonomy_Model_Resource_Item_Collection $collection
     *
     * @return array
     */
    public function convertCollectionToOptions(
        SDM_Taxonomy_Model_Resource_Item_Collection $collection
    ) {
        $options = array();
        foreach ($collection as $item) {
            $options[] = array(
                'value' => $item->getId(),
                'label' => $item->getName()
            );
        }
        return $options;
    }

    /**
     * Returns all of the attributes options
     *
     * @param str $code
     *
     * @return array
     */
    public function getDataToIndex($code)
    {
        $data = array();
        // $optionValues is a nested array with index being attribute ID.
        // Inner array is taxonomy IDs
        $optionValues = Mage::getResourceModel('taxonomy/item')->getOptions($code);
        // Mage::log($code);Mage::log($optionValues);

        if (empty($optionValues)) {
            return array();
        }

        $attributeId = key($optionValues);
        $optionIds = reset($optionValues);
        // print_r($attributeId . PHP_EOL);
        // print_r($optionIds);

        foreach ($optionIds as $ids) {
            $ids = explode(',', trim($ids));  // Make string (from _varchar) into array
            foreach ($ids as $id) {
                $data[$id] = true;
            }
        }

        return array($attributeId => $data);
    }

    /**
     * Return the taxonomy attribute ID array
     *
     * @return array
     */
    public function getTaxonomyAttributeIds()
    {
        if (!isset($this->_taxonomyAttributeIds)) {
            $this->initForIndexing();
        }

        return $this->_taxonomyAttributeIds;
    }

    /**
     * Checks if taxonomy item (using ID) is enabled for the given store
     * (checks at the website level) and that today is within the display dates.
     *
     * @param int   $id  sdm_taxonomy.entity ID (not the attribute ID)
     * @param array $row Catalog table's _varchar entry (only need store ID at v0.4.5)
     *
     * @return bool
     */
    public function validateTaxonomyItem($id, $row)
    {
        // Delegated to initForIndexing()
        // Initialize some reference data to be used repeatedly
        // if ($this->_storeIdsToWebsiteIds === null || $this->_taxonomyItemData === null
        //     || $this->_now === null
        // ) {
        //     $this->_initDataForIndexing();
        // }

        // Check website
        $websiteId = $this->_storeIdsToWebsiteIds[$row['store_id']];
        if (isset($this->_taxonomyItemData[$id][$websiteId])) {
            $start = $this->stringToTime($this->_taxonomyItemData[$id][$websiteId]['start_date']);
            $end   = $this->stringToTime($this->_taxonomyItemData[$id][$websiteId]['end_date']);
            // Check date range
            if ($this->_now >= $start && $this->_now <= $end) {
                return true;
            }
        }

        return false;
    }

    /**
     * Initialize the taxonomy data for validating dates
     *
     * Note: If the taxonomy item is missing any websites, add website data with
     * invalid dates. If any dates are missing for an assigned website, assign
     * dates that passes validation.
     *
     * @return void
     */
    protected function _initDataForIndexing()
    {
        $websites = array();
        $websiteOptions = Mage::getSingleton('adminhtml/system_store')
            ->getWebsiteValuesForForm(false, true);
        unset($websiteOptions[0]);  // Remove Admin store
        foreach ($websiteOptions as $option) {
            $websites[$option['value']] = $option['value'];
        }

        $pastDate = date(
            'Y-m-d',
            $this->stringToTime() - 60 * 60 * 24 * 7
        );
        $futureDate = date(
            'Y-m-d',
            $this->stringToTime() + 60 * 60 * 24 * 7
        );

        $data = Mage::getResourceModel('taxonomy/item')
            ->getAllData(array('entity_id'), true);

        foreach ($data as $row) {
            $this->_taxonomyItemData[$row['entity_id']][$row['website_id']]['start_date']
                = $row['start_date'];
            $this->_taxonomyItemData[$row['entity_id']][$row['website_id']]['end_date']
                = $row['end_date'];
        }

        // Fill in missing website and time data. Filler date data allows time
        // validation to pass.
        foreach ($this->_taxonomyItemData as $tagId => $item) {
            // $unassignedWebsites = $websites;
            foreach ($item as $websiteId => $dates) {
                // unset($unassignedWebsites[$websiteId]);
                // Set dates such that it will pass date range validation if empty
                if (!$dates['start_date']) {
                    $this->_taxonomyItemData[$tagId][$websiteId]['start_date'] = $pastDate;
                }
                if (!$dates['end_date']) {
                    $this->_taxonomyItemData[$tagId][$websiteId]['end_date'] = $futureDate;
                }
            }
        }

        // Find taxonomy attribute IDs
        $attributes = $this->getTypes();
        foreach ($attributes as $code => $name) {
            $id = $this->getAttributeId("tag_$code");
            if ($id) {
                $this->_taxonomyAttributeIds[$id] = $id;
            }
        }

        $this->_now = $this->stringToTime();
        $this->_storeIdsToWebsiteIds = Mage::helper('sdm_core')->getStoreIdsToWebsiteIds();
    }

    /**
     * Returns the attribute ID of the code
     *
     * @param str $code
     *
     * @return int
     */
    public function getAttributeId($code)
    {
        $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $code = $eavAttribute->getIdByCode('catalog_product', $code);

        return $code;
    }

    /**
     * Returns all the possible Taxonomy types
     *
     * @param string $option
     *
     * @return array
     */
    public function getTypes($option = 'all')
    {
        $types = array(
            SDM_Taxonomy_Model_Attribute_Source_Category::CODE              => $this->__('Category'),
            SDM_Taxonomy_Model_Attribute_Source_Subcategory::CODE           => $this->__('Sub Category'),
            SDM_Taxonomy_Model_Attribute_Source_Theme::CODE                 => $this->__('Theme'),
            SDM_Taxonomy_Model_Attribute_Source_Subtheme::CODE              => $this->__('Sub Theme'),
            SDM_Taxonomy_Model_Attribute_Source_Curriculum::CODE            => $this->__('Curriculum'),
            SDM_Taxonomy_Model_Attribute_Source_Subcurriculum::CODE         => $this->__('Sub Curriculum'),
            SDM_Taxonomy_Model_Attribute_Source_Productline::CODE           => $this->__('Product Line'),
            SDM_Taxonomy_Model_Attribute_Source_Subproductline::CODE        => $this->__('Sub Product Line'),
            SDM_Taxonomy_Model_Attribute_Source_Artist::CODE                => $this->__('Artist'),
            SDM_Taxonomy_Model_Attribute_Source_Designer::CODE              => $this->__('Designer'),
            SDM_Taxonomy_Model_Attribute_Source_Machinecompatibility::CODE  => $this->__('Machine Compatibility'),
            SDM_Taxonomy_Model_Attribute_Source_Materialcompatibility::CODE => $this->__('Material Compatibility'),
            SDM_Taxonomy_Model_Attribute_Source_Event::CODE                 => $this->__('Event'),
            SDM_Taxonomy_Model_Attribute_Source_Special::CODE               => $this->__('Special'),
        );

        if ($option == 'all') {
            $types[SDM_Taxonomy_Model_Attribute_Source_Discountcategory::CODE] = $this->__('Discount Category');
        }

        return $types;
    }

    /**
     * Removes all products associated with the taxonomy item ID
     *
     * @param int $tagId
     *
     * @return void
     */
    public function removeAllSpecialProducts($tagId)
    {
        $collection = Mage::getModel('taxonomy/item_product')->getCollection()
            ->addFieldToFilter('taxonomy_id', $tagId);

        foreach ($collection as $product) {
            $product->delete();
        }
    }

    /**
     * Returns the record ID of the given taxonomy
     *
     * @param str $type
     * @param str $code
     *
     * @return int
     */
    public function getTaxonomyId($type, $code)
    {
        $id = Mage::getResourceModel('taxonomy/item')->getIdByCode($type, $code);

        return $id;
    }

    /**
     * Returns the media directory path of the extension
     *
     * @param bool $full To return full path
     *
     * @return str
     */
    public function getMediaDirectoryPath($full = false)
    {
        if ($full) {
            return Mage::getBaseDir('media') . DS . 'taxonomy';
        } else {
            return 'taxonomy';
        }
    }

    /**
     * Returns the allowed image file types
     *
     * @return array
     */
    public function getAllowedImageFileTyes()
    {
        return $this->_allowedImageFileTypes;
    }

    /**
     * Transforms the posted data to be more friendly for processing
     *
     * @param array $data
     *
     * @return array
     */
    public function transformSpecialProductData($data)
    {
        $newData = array();

        if (isset($data['sku'][0])) {
            foreach ($data['sku'] as $i => $one) {
                // Ensure values are assigned
                if (!isset($data['sku'][$i])) {
                    continue;
                }
                $data['discount_type'][$i] = isset($data['discount_type'][$i]) ? $data['discount_type'][$i] : false;
                $data['discount_value'][$i] = isset($data['discount_value'][$i]) ? $data['discount_value'][$i] : false;

                // Set new data
                $newData[$i]['sku'] = $data['sku'][$i];
                $newData[$i]['discount_type'] = $data['discount_type'][$i];
                $newData[$i]['discount_value'] = $data['discount_value'][$i];
            }
        }

        return $newData;
    }

    /**
     * Convert a readable date to a timestamp
     *
     * @param string|null $input
     *
     * @return integer
     */
    public function stringToTime($input = null)
    {
        return $input ? strtotime($input) : Mage::getSingleton('core/date')->timestamp();
    }

    /**
     * Import CSV
     *
     * @param string $file
     *
     * @return array
     */
    public function importCsv($file)
    {
        $keys = array('sku', 'discount_type', 'discount_value');
        $results = array();
        if (($handle = fopen($file['tmp_name'], "r")) !== false) {
            $keys = fgetcsv($handle, 1000, ",");
            if (array_diff($keys, array('sku', 'discount_type', 'discount_value'))) {
                return false;
            }
            $keyRow = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                for ($keyColumn = 0; $keyColumn < count($data); $keyColumn++) {
                    if ((count($data)==1) && (!$data[$keyColumn])) {
                        continue;
                    }
                    $results[$keyRow][$keys[$keyColumn]] = $data[$keyColumn];
                }
                $keyRow++;
            }
        }

        $results = array_values($results);
        fclose($handle);
        
        return $results;
    }
}
