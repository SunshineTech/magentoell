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
 * SDM_Catalog_Model_Resource_Layer_Filter_Decimal class
 */
class SDM_Catalog_Model_Resource_Layer_Filter_Decimal
    extends Mage_Catalog_Model_Resource_Layer_Filter_Decimal
{
    /**
     * Retrieve array with products counts per range
     *
     * @param  Mage_Catalog_Model_Layer_Filter_Decimal $filter
     * @param  int                                     $range
     * @return array
     */
    public function getCount($filter, $range)
    {
        $select     = $this->_getSelect($filter);
        $adapter    = $this->_getReadAdapter();

        $countExpr  = new Zend_Db_Expr("COUNT(*)");
        $rangeExpr  = new Zend_Db_Expr("FLOOR(decimal_index.value / {$range}) + 1");

        $select->columns(array(
            'decimal_range' => $rangeExpr,
            'count' => $countExpr
        ));
        $select->group($rangeExpr);

        // Filter by type_id
        $select->where('`e`.`type_id` = "'.$this->getCatalogFilterType().'"');

        return $adapter->fetchPairs($select);
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

    /**
     * Note sure what it does. From AdjustWare_Nav_Model_Mysql4_Catalog_Layer_Filter_Decimal
     *
     * @param Mage_Catalog_Model_Layer_Filter_Decimal $filter
     *
     * @return SDM_Catalog_Model_Resource_Layer_Filter_Decimal
     */
    public function applyPriceRange($filter)
    {
        return $this;
    }
}
