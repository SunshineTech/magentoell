<?php
/**
 * Separation Degrees One
 *
 * Handles designer page and designer article rendering
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Designer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Designer_Helper_Data class
 */
class SDM_Designer_Helper_Data
    extends SDM_Core_Helper_Data
{
    /**
     * Gets the designer taxonomy item by code
     *
     * @return SDM_Taxonomy_Model_Taxonomy_Item
     */
    public function getCurrentDesigner()
    {
        if (Mage::registry('sdm_designer') === null) {
            $designerCode = Mage::app()->getRequest()->getParam('id');
            $designerTaxonomy = Mage::getModel('taxonomy/item')
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('code', $designerCode)
                ->addFieldToFilter('type', 'designer');
            $designerTaxonomy = $designerTaxonomy->getFirstItem();

            if ($designerTaxonomy->getId() && $designerTaxonomy->getType() === 'designer') {
                // We have a designer on this page
                Mage::register('sdm_designer', $designerTaxonomy);
            } elseif (Mage::getSingleton('cms/page')->getType() === 'designer') {
                $designerTaxonomy = Mage::getModel('taxonomy/item')
                    ->load(Mage::getSingleton('cms/page')->getTaxonomyId());
                if ($designerTaxonomy->getId() && $designerTaxonomy->getType() === 'designer') {
                    // We have a designer on this page
                    Mage::register('sdm_designer', $designerTaxonomy);
                } else {
                    // We don't have a designer on this page
                    Mage::register('sdm_designer', false);
                }
            }

            // If it's not active, unset it
            if (!$designerTaxonomy->isActive()) {
                if (Mage::registry('sdm_designer') !== null) {
                    Mage::unregister('sdm_designer');
                }
                Mage::register('sdm_designer', false);
            }

        }
        return Mage::registry('sdm_designer');
    }

    /**
     * Gets designer product collection
     *
     * @param  SDM_Taxonomy_Model_Item $designer
     * @param  string                  $type
     * @param  integer                 $limit
     * @return $collection
     */
    public function getDesignerProducts($designer = null, $type = 'simple', $limit = 12)
    {
        if (empty($designer)) {
            $designer = $this->getCurrentDesigner();
        }
        $designerId = $designer->getId();
        if (empty($designerId)) {
            return array();
        }

        $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*');

        if ($limit) {
            $collection->setPage(0, (int)$limit);
        }

        if (!empty($type)) {
            $collection->addAttributeToFilter('type_id', $type);
        }

        // Add designer filter
        Mage::helper('taxonomy')->addTaxonomyFilter(
            $collection,
            'tag_designer',
            $designerId
        );

        // Add visibility filter
        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($collection);

        return $collection;
    }

    /**
     * Gets all the products of a designer, in a multidimensional array split
     * by each category the designer has products in
     *
     * @param  SDM_Taxonomy_Model_Item $designer
     * @return array
     */
    public function getDesignerProductsByCategory($designer = null)
    {
        if (empty($designer)) {
            $designer = $this->getCurrentDesigner();
        }
        $designerId = $designer->getId();
        if (empty($designerId)) {
            return array();
        }

        // Get category IDs this designer has products in by checking index table
        $storeId = Mage::app()->getStore()->getId();
        $indexTable = $this->getTableName('catalog/product_index_eav');
        $connection = $this->getConn();
        $select = $connection->select();
        $select->from($indexTable. ' AS designer_idx', array(
                'category_idx.value AS category_id'
            ))
            ->joinLeft(
                array('category_idx' => $indexTable),
                'designer_idx.entity_id=category_idx.entity_id',
                array()
            )
            ->where('designer_idx.store_id = ' . $storeId)
            ->where('designer_idx.attribute_id = 136')
            ->where('designer_idx.value = ' . $designerId)
            ->where('category_idx.store_id = ' . $storeId)
            ->where('category_idx.attribute_id = 134')
            ->group('category_id');
        $categoryResult = $this->getConn()->fetchCol($select);

        $categorized = array();
        foreach ($categoryResult as $categoryId) {
            // Get product collection
            $categorized[$categoryId] = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', 'simple')
                ->setPage(0, 24);

            // Add visibility filter
            Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInCatalogFilterToCollection($categorized[$categoryId]);

            // Add designer filter
            Mage::helper('taxonomy')->addTaxonomyFilter(
                $categorized[$categoryId],
                'tag_designer',
                $designerId
            );

            // Add category filter
            Mage::helper('taxonomy')->addTaxonomyFilter(
                $categorized[$categoryId],
                'tag_category',
                $categoryId
            );
        }

        return $categorized;
    }

    /**
     * Gets all grouped products from this designer
     *
     * @param  SDM_Taxonomy_Model_Item $designer
     * @return Mage_Catalog_Model_Product_Resource_Collection
     */
    public function getDesignerProjects($designer)
    {
        if (empty($designer)) {
            $designer = $this->getCurrentDesigner();
        }
        $designerId = $designer->getId();
        if (empty($designerId)) {
            return array();
        }

        $products = $this->_getDesignerProductCollection($designerId, 'grouped');

        return $products;
    }

    /**
     * Gets all simple products from this designer
     *
     * @param  int    $designerId
     * @param  string $typeId
     * @return Mage_Catalog_Model_Product_Resource_Collection
     */
    public function _getDesignerProductCollection($designerId, $typeId)
    {
        // Create product collection
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', $typeId)
            ->addAttributeToSort('created_at', 'desc')
            ->setPage(0, 24);

        Mage::helper('taxonomy')
            ->addTaxonomyFilter($products, 'tag_designer', $designerId);

        // Mage::log($products->getSelect()->__toString());die();

        // Add visibility filter
        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($products);

        return $products;
    }

    /**
     * Gets all simple products from this designer
     *
     * @param  SDM_Taxonomy_Model_Item $designer
     * @return array|SDM_YoutubeFeed_Model_Resource_Video_Collection
     */
    public function getDesignerVideos($designer)
    {
        if (empty($designer)) {
            $designer = $this->getCurrentDesigner();
        }
        if (!$designer->getId()) {
            return array();
        }

        return Mage::getModel('sdm_youtubefeed/video')
            ->getCollection()
            ->addFieldToFilter('status', SDM_YoutubeFeed_Model_Video::STATUS_ENABLED)
            ->addDesignerToFilter($designer)
            ->setOrder('published_at', Zend_Db_Select::SQL_DESC);
    }

    /**
     * Gets all simple products from this designer
     *
     * @param  SDM_Taxonomy_Model_Item $designer
     * @return $articles
     */
    public function getDesignerArticles($designer)
    {
        if (empty($designer)) {
            $designer = $this->getCurrentDesigner();
        }
        $designerId = $designer->getId();
        if (empty($designerId)) {
            return array();
        }

        $articles = Mage::getModel('cms/page')
            ->getCollection()
            ->addFieldToFilter('type', 'designer')
            ->addFieldToFilter('taxonomy_id', $designerId);

        return $articles;
    }

    /**
     * Gets attribute model from code
     *
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public function getAttribute($code)
    {
        if (!isset($this->_storedAttributes[$code])) {
            $this->_storedAttributes[$code] = Mage::getModel('eav/entity_attribute')
                ->loadByCode(
                    Mage_Catalog_Model_Product::ENTITY, $code
                );
        }
        return $this->_storedAttributes[$code];
    }
}
