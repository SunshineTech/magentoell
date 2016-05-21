<?php
/**
 * Separation Degrees One
 *
 * Mage_Catalog-related customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Catalog_Model_Resource_Product_Collection class
 */
class SDM_Catalog_Model_Resource_Product_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection
{

    /**
     * Have we added required attributes to this collection?
     *
     * @var boolean
     */
    protected $_hasRequiredAttributes = false;

    /**
     * Shortcut for getting this collection's SQL query
     *
     * @return $this
     */
    public function logSql()
    {
        Mage::log($this->getSelect()->__toString());
        return $this;
    }

    /**
     * Removes the price filter if the collection is for the given type of
     * products
     *
     * Important:
     * Price condition may be price_index/e.min_price or cp.final_price
     *
     * @param str $type
     *
     * @see SDM_Catalog_Model_Resource_Layer_Filter_Price
     *
     * @return void
     */
    public function removePriceFilter($type = Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
    {
        $needUpdate = false;
        $select = $this->getSelect();
        $wheres = $select->getPart(Zend_Db_Select::WHERE);

        foreach ($wheres as $where) {
            if ((strpos($where, 'type_id') !== false)
                && (strpos($where, $type) !== false)
            ) {
                $needUpdate = true;
            }
        }

        // Remove all price conditions
        if ($needUpdate) {
            $newWheres = array();

            foreach ($wheres as $where) {
                if ((strpos($where, '.min_price') === false)
                    && (strpos($where, '.final_price') === false)
                ) {
                    $newWheres[] = $where;
                }
            }
            $select->setPart(Zend_Db_Select::WHERE, $newWheres);
        }
    }

    /**
     * Add a left-join to make available the type of discount applied to the
     * catalog to the product collection on the frontend
     *
     * Helper function being called supports adding discount type applied to
     * both product and generic collections. By specifying e.entity_id as
     * the $joinOn parameter, we are telling the helper to join this to
     * a product collection.
     *
     * @see SDM_Catalog_Helper_Data::addDiscountTypeAppliedToCollection()
     *
     * @return $this
     */
    public function addDiscountTypeAppliedTags()
    {
        Mage::helper('sdm_catalog')
            ->addDiscountTypeAppliedToCollection($this, 'e.entity_id');

        return $this;
    }

    /**
     * Add the Euro price to the collection if on the UK Euro store.
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function addEuroPrices()
    {
        $store = Mage::app()->getStore();
        $storeId = $store->getId();

        if ($store->getCode() == SDM_Core_Helper_Data::STORE_CODE_UK_EU) {
            $this->_overridePrices(SDM_Catalog_Helper_Data::EURO_CODE);
        }

        return $this;
    }

    /**
     * Overrides the price and special_price with the specified currency prices.
     *
     * 'minimal_price' added to prevent strikethrough prices from being displayed.
     *
     * @param str $currency Currency string that is part of the attribute
     *
     * @return void
     */
    public function _overridePrices($currency = null)
    {
        if (!$currency) {
            return;
        }

        $currency = strtolower($currency);

        // Left-join allows the original price and special_price fields to be used
        // in case the custom prices are not indexed
        $select = $this->getSelect()
            ->joinLeft(
                array('cp' => $this->getTable('sdm_catalog/index_custom_price')),
                'cp.entity_id = e.entity_id',
                array('price', 'final_price', 'final_price AS minimal_price')
            );
    }

    /**
     * Checks if the collection/select needs to have the type_id filter applied
     * and applies or updates it, if neccessary.
     *
     * @param bool $type Pass false if not available
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function addTypeIdFilter($type = false)
    {
        $whereUpdated = false;
        $noTypeIdFilter = true;
        if ($type === false) {
            // Get it from the URL
            $type = Mage::helper('sdm_catalog')->getCatalogFilterType();
        }

        if ($this instanceof Zend_Db_Select) {
            $select = $this;
        } else {
            $select = $this->getSelect();
        }

        // Get the WHERE clause
        $wheres = $select->getPart(Zend_Db_Select::WHERE);
        if (empty($wheres)) {
            $select->where("e.type_id = '$type'");
            return $this;
        }

        foreach ($wheres as $i => $where) {
            if (strpos($where, 'type_id') !== false) {
                $noTypeIdFilter = false;
                // Not known if it's a simple or grouped filter; check both.
                if (strpos($where, Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) !== false
                    && Mage_Catalog_Model_Product_Type::TYPE_SIMPLE != $type
                ) {
                    $wheres[$i] = str_replace(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $type, $where);
                    $whereUpdated = true;
                    break;
                }
                if (strpos($where, Mage_Catalog_Model_Product_Type::TYPE_GROUPED) !== false
                    && Mage_Catalog_Model_Product_Type::TYPE_GROUPED != $type
                ) {
                    $wheres[$i] = str_replace(Mage_Catalog_Model_Product_Type::TYPE_GROUPED, $type, $where);
                    $whereUpdated = true;
                    break;
                }
            }
        }

        if ($whereUpdated) {    // Need to replace whentire WHERE clause
            $select->setPart(Zend_Db_Select::WHERE, $wheres);
            // $select->reset(Zend_Db_Select::WHERE);  // Remove first
            // $select->where(implode(' ', $wheres));  // Re-add them
        } elseif ($noTypeIdFilter) {    // Just need to add addtional condition
            $select->where("e.type_id = '$type'");
        }

        return $this;
    }

    /**
     * Set basic parameters for the taxonomy-filtered collection
     *
     * @param SDM_Catalog_Model_Product $product
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function addBaseTaxonomyFilter($product)
    {
        $idStr = $product->getTagProductLine();

        $multiOrs = array();
        $ids = explode(',', $idStr);
        foreach ($ids as $id) {
            $multiOrs[] = array('attribute' => 'concat_tag_product_line', 'like' => "%,$id,%");
        }

        $this->addAttributeToFilter('type_id', $product->getTypeId())
            ->addAttributeToFilter(
                'entity_id', array('neq' => $product->getId())
            );
        $this->applyRequiredAttributes();

        if ($id) {  // Product line filter applies to all searches
            $this->addExpressionAttributeToSelect(
                    'concat_tag_product_line',
                    "CONCAT(',',{{tag_product_line}},',')",
                    array('tag_product_line' => 'tag_product_line')
                )
                ->addAttributeToFilter($multiOrs);
        }

        return $this;
    }

    /**
     * Add a specific taxonomy filter
     *
     * @param SDM_Catalog_Model_Product $product
     * @param str                       $code
     *
     * @see Mage_Catalog_Block_Product_List_Related::_prepareData()
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection|bool
     */
    protected function _addTaxonomyTagFilter($product, $code)
    {
        // $limit = Mage::helper('sdm_catalog')->getProductLimitNumber();
        $key = "tag_$code";
        $idStr = trim($product->getData($key));

        if (is_null($idStr)) {
            return false; // Don't return $this as addBaseTaxonomyFilter() will return results
        }

        $multiOrs = array();
        $ids = explode(',', $idStr);
        foreach ($ids as $id) {
            $multiOrs[] = array('attribute' => 'concat_tag_attribute', 'like' => "%,$id,%");
        }

        $collection = $this->addBaseTaxonomyFilter($product)
            ->addExpressionAttributeToSelect(
                'concat_tag_attribute',
                "CONCAT(',',{{" . $key . "}},',')",
                array($key => $key)
            )
            ->addAttributeToFilter($multiOrs)
            ->addStoreFilter()
            ->setPage(1, $product->getSqlLimit()); // First page, limit number
        $collection->getSelect()->orderRand('e.entity_id'); // randomize selection by product ID

        return $collection;
    }

    /**
     * Get the custom Ellison collection
     *
     * @param SDM_Catalog_Model_Product $product
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function addSubcategoryTagFilter($product)
    {
        return $this->_addTaxonomyTagFilter(
            $product,
            SDM_Taxonomy_Model_Attribute_Source_Subcategory::CODE
        );
    }

    /**
     * Add a theme tag filter
     *
     * @param SDM_Catalog_Model_Product $product
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function addThemeTagFilter($product)
    {
        return $this->_addTaxonomyTagFilter(
            $product,
            SDM_Taxonomy_Model_Attribute_Source_Theme::CODE
        );
    }

    /**
     * Add a subtheme tag filter
     *
     * @param SDM_Catalog_Model_Product $product
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function addSubthemeTagFilter($product)
    {
        return $this->_addTaxonomyTagFilter(
            $product,
            SDM_Taxonomy_Model_Attribute_Source_Subtheme::CODE
        );
    }

    /**
     * Add a subcurriculum tag filter
     *
     * @param SDM_Catalog_Model_Product $product
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function addSubcurriculumTagFilter($product)
    {
        return $this->_addTaxonomyTagFilter(
            $product,
            SDM_Taxonomy_Model_Attribute_Source_Subcurriculum::CODE
        );
    }

    /**
     * Add a category tag filter
     *
     * @param SDM_Catalog_Model_Product $product
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function addCategoryTagFilter($product)
    {
        return $this->_addTaxonomyTagFilter(
            $product,
            SDM_Taxonomy_Model_Attribute_Source_Category::CODE
        );
    }

    /**
     * Applies attributes required for rendering
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function applyRequiredAttributes()
    {
        if (!$this->_hasRequiredAttributes) {
            $this->_hasRequiredAttributes = true;

            return $this->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addUrlRewrite()
                ->addDiscountTypeAppliedTags()
                ->addEuroPrices();
        }
        return $this;
    }
}
