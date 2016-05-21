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
 * SDM_Catalog_Model_Resource_Layer_Filter_Price class
 */
class SDM_Catalog_Model_Resource_Layer_Filter_Price
    extends IntegerNet_Solr_Model_Resource_Catalog_Layer_Filter_Price
{
    /**
     * Retrieve array with products counts per price range
     *
     * Rewritten to add the type_id and switch out e.min_price with custom prices.
     * Note that to replace min_price, cp.final_price is used under the assumption
     * that minimium and final prices are the same.
     *
     * @param  Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param  int                                   $range
     * @return array
     */
    public function getCount($filter, $range)
    {
        $select = $this->_getSelect($filter);

        $priceExpression = $this->_getFullPriceExpression($filter, $select);

        /**
         * Check and set correct variable values to prevent SQL-injections
         */
        $range = floatval($range);
        if ($range == 0) {
            $range = 1;
        }

        // The multi-selectable filter adds to the count incorrectly if 'COUNT(*)' is used
        $countExpr = new Zend_Db_Expr('COUNT(DISTINCT(pe.entity_id))');
        $rangeExpr = new Zend_Db_Expr("FLOOR(({$priceExpression}) / {$range}) + 1");
        $rangeOrderExpr = new Zend_Db_Expr("FLOOR(({$priceExpression}) / {$range}) + 1 ASC");

        $select->columns(array(
            'range' => $rangeExpr,
            'count' => $countExpr
        ));
        $select->group($rangeExpr)->order($rangeOrderExpr);

        /**
         * Majority of rewrite begins
         */
        // Join into the product entity table so we can filter by type_id
        $select->join(
            array('pe' => 'catalog_product_entity'),
            "pe.entity_id = e.entity_id",
            ''
        );

        // WHERE clause may already have type_id filtered from
        // SDM_Catalog_Model_Category::getProductCollection
        $this->_addTypeIdFilter($select);

        // Join custom prices, and modify the min_price select to display the
        // custom price ranges, if necessary
        $store = Mage::app()->getStore();
        if ($store->getCode() == SDM_Core_Helper_Data::STORE_CODE_UK_EU) {
            // Modify the "select column" clause to use Euro price
            $columns = $select->getPart(Zend_Db_Select::COLUMNS);
            $newColumns = Mage::helper('sdm_core/zend')->reconstructColumns($columns);
            $newColumns = str_replace('(e.min_price)', '(cp.final_price)', $newColumns);
            $newColumns = str_replace('range', '`range`', $newColumns);
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->columns($newColumns);

            // Modify the WHERE clause to use Euro price
            $wheres = $select->getPart(Zend_Db_Select::WHERE);
            $isReplaced = Mage::helper('sdm_core/zend')->replaceWhereSegment(
                'e.min_price',
                'cp.final_price',
                $wheres
            );
            $select->setPart(Zend_Db_Select::WHERE, $wheres);

            // Modify the GROUP BY clause to use Euro price
            $group = $select->getPart(Zend_Db_Select::GROUP);
            $newGroup = Mage::helper('sdm_core/zend')->reconstructClause($group);
            $newGroup = str_replace('e.min_price', 'cp.final_price', $newGroup);
            $select->reset(Zend_Db_Select::GROUP);
            $select->group($newGroup);

            // Modify the ORDER BY clause to use Euro price
            $order = $select->getPart(Zend_Db_Select::ORDER);
            $newOrder = Mage::helper('sdm_core/zend')->reconstructClause($order);
            $newOrder = str_replace('e.min_price', 'cp.final_price', $newOrder);
            $select->reset(Zend_Db_Select::ORDER);
            $select->order($newOrder);
        }
        // Mage::log($select->getPart(Zend_Db_Select::WHERE));
        // Mage::log($select->__toString());
        /**
         * Rewrite ends
         */

        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Adds a type_id filter or modifies the WHERE clause so the proper alias
     * is used for the filer.
     *
     * @param Zend_Db_Select $select
     *
     * @return void
     */
    protected function _addTypeIdFilter($select)
    {
        $wheres = $select->getPart(Zend_Db_Select::WHERE);

        $isReplaced = Mage::helper('sdm_core/zend')->replaceWhereSegment(
            'e.type_id',
            'pe.type_id',
            $wheres
        );

        if ($isReplaced) {
            $select->setPart(Zend_Db_Select::WHERE, $wheres);
            // $select->reset(Zend_Db_Select::WHERE);
            // $select->where(implode(' ', $wheres));
        } else {
            $select->where("pe.type_id = '" . $this->getCatalogFilterType() . "'");
        }
    }

    /**
     * Gets the current product type filter based off the URL parameter
     *
     * @return string
     */
    public function getCatalogFilterType()
    {
        return Mage::helper('sdm_catalog')->getCatalogFilterType();
    }
}
