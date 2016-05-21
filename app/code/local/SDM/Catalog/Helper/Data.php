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
 * SDM_Catalog_Helper_Data class
 */
class SDM_Catalog_Helper_Data extends Mage_Catalog_Helper_Data
{
    const XML_PATH_PRODUCT_LIMIT = 'sdm_catalog/general/limit';

    /**
     * Codes for products and products
     */
    const IDEA_CODE    = 'project';    // "Idea" is synonymous to "project"
    const PRODUCT_CODE = 'product';

    /**
     * Product/project facet IDs for SOLR
     */
    const IDEA_FACET_ID    = 1;         // "Idea" is synonymous to "project"
    const PRODUCT_FACET_ID = 0;

    /**
     * Attribute codes. Must be 'price_xyz' and 'special_price_xyz'.
     */
    const EURO_PRICE_ATTRIBUTE_CODE         = 'price_euro'; // xyz = euro
    const EURO_SPECIAL_PRICE_ATTRIBUTE_CODE = 'special_price_euro';

    /**
     * Code for business logic. Must match the custom attribute code's postfix,
     * 'xyz'.
     */
    const EURO_CODE = 'euro';

    /**
     * Code strings for the types of catalog discounts applied
     */
    const DISCOUNT_TYPE_APPLIED_CODE_PROMO         = 'promo';
    const DISCOUNT_TYPE_APPLIED_CODE_SPECIAL_PRICE = 'special_price';

    /**
     * Stores an array of all product SKUs by Product ID
     *
     * @var null
     */
    protected $_allSkusById = null;

    /**
     * Returns the maximum number of related products
     *
     * @return int
     */
    public function getProductLimitNumber()
    {
        return Mage::getStoreConfig(self::XML_PATH_PRODUCT_LIMIT);
    }

    /**
     * Gets the current product type filter based off the URL parameter
     *
     * @return string
     */
    public function getCatalogType()
    {
        if (Mage::app()->getRequest()->getParam('type') === self::IDEA_CODE) {
            return self::IDEA_CODE;
        } else {
            return self::PRODUCT_CODE;   // Returns this by default
        }
    }

    /**
     * Gets the catalog type name for product/project/lesson
     *
     * @param  string $type
     * @return string
     */
    public function getCatalogTypeName($type = null)
    {
        $type = trim(strtolower(empty($type) ? (string)$this->getCatalogType() : (string)$type));

        if ($type === self::IDEA_CODE) {
            return $this->getProjectLabel("Project");
        } else {
            return 'Product';
        }
    }

    /**
     * Returns the label for projects based off website
     * @param  string $type
     * @return string
     */
    public function getProjectLabel($type)
    {
        $type = trim(strtolower($type));
        $site = Mage::app()->getWebsite()->getCode();
        switch ($site) {
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED:
                return "Lesson";
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK:
                return "Idea";
            default:
                return "Project";
        }
    }

    /**
     * Returns the label for "view details" based off website code
     * Used in lifecycle button logic caching, so a website code must be specified
     *
     * @param  string $websiteCode
     * @return string
     */
    public function getIdeaViewDetailsText($websiteCode)
    {
        switch ($websiteCode) {
            case SDM_Core_Helper_Data::WEBSITE_CODE_ED:
                return "View Lesson";
            case SDM_Core_Helper_Data::WEBSITE_CODE_UK:
                return "View Idea";
            default:
                return "View Project";
        }
    }

    /**
     * Returns 'project' or 'lesson' based on the current site and product brand
     *
     * @param  object $product
     * @param  bool   $lowercase Do you want return value capitlized or not?
     * @return string
     */
    public function getProjectLabelFromProduct($product = null, $lowercase = false)
    {
        $brand = null;
        if (!empty($product) && $product->getId()) {
            $brand = $product->getBrand();
            $brand = empty($brand) ? null : strtolower(trim($brand));
        }

        $site = Mage::app()->getWebsite()->getCode();
        switch ($site) {
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE:
                $name = $brand === 'ellison' ? "Lesson" : "Project";
                return $lowercase ? strtolower($name) : $name;
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED:
                return $lowercase ? "lesson" : "Lesson";
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK:
                return $lowercase ? "idea" : "Idea";
            default:
                return $lowercase ? "project" : "Project";
        }
    }

    /**
     * Returns the sku label depending off product type and site
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getSkuLabel($product = null)
    {
        // Return "Item #"" if no product provided or not grouped product
        if ($product === null || $product->getTypeId() !== 'grouped') {
            //Mage::log($product->getData());
            return "Item #";
        }

        $site = Mage::app()->getWebsite()->getCode();
        switch ($site) {
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED:
                return "Lesson #";
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK:
                return "Idea #";
            default:
                return "Project #";
        }
    }

    /**
     * Returns 'Sixxiz 101' or 'Ellison 101' based on the current site and product brand
     *
     * @param  object $product
     * @return string
     */
    public function getSizzix101Name($product = null)
    {
        $brand = null;
        if (!empty($product) && $product->getId()) {
            $brand = $product->getAttributeText('brand');
            $brand = empty($brand) ? null : strtolower(trim($brand));
        }

        $site = Mage::app()->getWebsite()->getCode();
        switch ($site) {
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE:
                return $brand === 'ellison' ? "Ellison 101" : "Sizzix 101";
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED:
                return "Ellison 101";
            default:
                return "Sizzix 101";
        }
    }

    /**
     * Returns 'Sixxiz 101' or 'Ellison 101' based on the current site and product brand
     *
     * @param  object $product
     * @param  string $suffix
     * @param  string $prefix
     * @return string
     */
    public function getSizzix101BlockName($product = null, $suffix = "media_box", $prefix = "pdp")
    {
        $brand = null;
        if (!empty($product) && $product->getId()) {
            $brand = $product->getAttributeText('brand');
            $brand = empty($brand) ? null : strtolower(trim($brand));
        }

        $site = Mage::app()->getWebsite()->getCode();
        switch ($site) {
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE:
                return $prefix . "_" . ($brand === 'ellison' ? "ellison" : "sizzix") . "101_" . $suffix;
            case SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED:
                return $prefix . "_ellison101_" . $suffix;
            default:
                return $prefix . "_sizzix101_" . $suffix;
        }
    }

    /**
     * Maps "project" (idea) to "grouped" and "product" to "simple"
     *
     * @param  boolean|string $type
     * @return string
     */
    public function getCatalogFilterType($type = false)
    {
        if ($type === false) {
            $type = $this->getCatalogType();
        }

        if ($type === self::IDEA_CODE) {
            return Mage_Catalog_Model_Product_Type::TYPE_GROUPED;
        } else {
            return Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
        }
        // $type = $type === false ? $this->getCatalogType()  : $type;
        // return $type === 'project' ? 'grouped' : 'simple';
    }

    /**
     * Returns the url for submitting multiple items via ajax
     *
     * @return string
     */
    public function getMultiSubmitAjaxCartUrl()
    {
        return Mage::getUrl(
            "checkout/cart/ajaxaddmultiple",
            array(
                'form_key' => Mage::getSingleton('core/session')->getFormKey(),
                '_forced_secure' => Mage::app()->getStore()->isCurrentlySecure()
            )
        );
    }

    /**
     * Joins a collection with the applied discount table so we
     * can tell what kind of discount is applied to the products.
     *
     * By default, this joins to product collections on e.entity_id. If you
     * want to join to a different type of collection, select another column
     * to join on which contains the product id.
     *
     * @param  Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param  string                                         $joinOn
     * @return $this
     */
    public function addDiscountTypeAppliedToCollection($collection, $joinOn = "e.entity_id")
    {
        $collection->getSelect()->joinLeft(
            array('dt' => Mage::getSingleton('core/resource')
                ->getTablename('customerdiscount/applied_discount')),
                $joinOn . " = dt.product_id AND dt.store_id = '"
                . Mage::app()->getStore()->getId() . "'",
            array('type AS discount_type_applied')
        );
        return $this;
    }

    /**
     * Adds applied discount to SOLR collection.
     *
     * @param  Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param  string                                         $joinOn
     * @return $this
     */
    public function addDiscountTypeAppliedToSolrCollection($ids, $collection)
    {
        $ids = implode(",", $ids);
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $results = $read->fetchAll("SELECT * FROM sdm_catalog_product_index_applied_discount WHERE product_id IN ($ids) AND store_id = " . Mage::app()->getStore()->getId());
        $discounts = array();
        foreach($results as $row) {
            $discounts[$row['product_id']] = $row['type'];
        }
        
        // Apply to collection
        foreach($collection as $product) {
            if (isset($discounts[$product->getId()])) {
                $product->setDiscountTypeApplied($discounts[$product->getId()]);
            }
        }

        return $collection;
    }

    /**
     * Join a non-product collection to the product flat table
     *
     * @param object $collection
     * @param string $joinOn
     * @param array  $selectColumns
     *
     * @return void
     */
    public function joinToProductFlat(
        $collection,
        $joinOn = 'main_table.product_id',
        $selectColumns = array()
    ) {
        // Add table name to columsn we're selecting
        $selectColumns = is_array($selectColumns) ? $selectColumns : array($selectColumns);
        foreach ($selectColumns as $key => $value) {
            $selectColumns[$key] = "product_flat_table.".$value;
        }

        // Add join to collection select
        $collection->getSelect()
            ->joinLeft(
                array('product_flat_table' => 'catalog_product_flat_'
                    . Mage::app()->getStore()->getId()
                ),
                $joinOn . '=product_flat_table.entity_id',
                $selectColumns
            );
    }

    /**
     * We commonly have to filter hidden products from showing up in secondary product
     * collections, ie. collections that are related to products but are not actualy
     * product collection. For example, wishlist items or followed items.
     *
     * This method joins one of these collections to the product flat table and filters
     * out products by catalog visibility
     *
     * @param object $collection
     * @param string $joinOn
     * @param bool   $allowNull
     *
     * @return void
     */
    public function addVisibleInCatalogToGenericCollection(
        $collection,
        $joinOn = 'main_table.product_id',
        $allowNull = false
    ) {
        // Join table to product flat
        $this->joinToProductFlat($collection, $joinOn, 'visibility');

        // Add filter for visibility
        $visibilityIds = Mage::getModel('catalog/product_visibility')
            ->getVisibleInCatalogIds();
        $collection->getSelect()
            ->where(
                'product_flat_table.visibility IN (?) '.
                ($allowNull ? 'OR product_flat_table.visibility IS NULL' : ''),
                $visibilityIds
            );
    }

    /**
     * We commonly have to filter hidden products from showing up in secondary product
     * collections, ie. collections that are related to products but are not actualy
     * product collection. For example, wishlist items or followed items.
     *
     * This method joins one of these collections to the product flat table and filters
     * out products by site visibility
     *
     * @param object $collection
     * @param string $joinOn
     * @param bool   $allowNull
     *
     * @return void
     */
    public function addVisibleInSiteToGenericCollection(
        $collection,
        $joinOn = 'main_table.product_id',
        $allowNull = false
    ) {
        // Join table to product flat
        $this->joinToProductFlat($collection, $joinOn, 'visibility');

        // Add filter for visibility
        $visibilityIds = Mage::getModel('catalog/product_visibility')
            ->getVisibleInSiteIds();
        $collection->getSelect()
            ->where('product_flat_table.visibility IN (?)'.
                ($allowNull ? ' OR product_flat_table.visibility IS NULL' : ''),
                $visibilityIds
            );
    }

    /**
     * User for various admin grids
     *
     * @return array
     */
    public function getLifecycleOptions()
    {
        $attribute = Mage::getModel('eav/config')->getAttribute(
            'catalog_product',
            'life_cycle'
        );
        
        $options = array();
        foreach ($attribute->getSource()->getAllOptions(true, true) as $instance) {
            $options[$instance['value']] = $instance['label'];
        }

        return $options;
    }

    /**
     * Gets all SKUs by ID
     *
     * @return array
     */
    public function getAllSkusById()
    {
        if ($this->_allSkusById === null) {
            $sql = "SELECT `entity_id` AS 'product_id', `sku` FROM `catalog_product_entity`";
            $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
            $results = $readConnection->fetchAll($sql);
            $this->_allSkusById = array();
            foreach ($results as $product) {
                $this->_allSkusById[$product["product_id"]] = $product["sku"];
            }
        }
        return $this->_allSkusById;
    }

    /**
     * Get SKU by Product ID
     *
     * @param  int $id
     * @return string
     */
    public function getSkuById($id)
    {
        if ($this->_allSkusById === null) {
            $this->getAllSkusById();
        }
        $id = (int)$id;
        if (!isset($this->_allSkusById[$id])) {
            $product = Mage::getModel('catalog/product')->load($id);
            $this->_allSkusById[$id] = $product->getSku();
        }
        return $this->_allSkusById[$id];
    }
}
