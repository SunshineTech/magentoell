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
 * SDM_Catalog_Helper_Catalog_Product_Price_Indexer class
 */
class SDM_Catalog_Helper_Catalog_Product_Price_Indexer
    extends Innoexts_StorePricing_Helper_Catalog_Product_Price_Indexer
{
    /**
     * Get final price expression
     *
     * @param Varien_Db_Adapter_Interface $write
     * @param Zend_Db_Expr                $price
     * @param Zend_Db_Expr                $specialPrice
     * @param Zend_Db_Expr                $specialFrom
     * @param Zend_Db_Expr                $specialTo
     *
     * @return Zend_Db_Expr
     */
    public function getFinalPriceExpr($write, $price, $specialPrice, $specialFrom, $specialTo)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            $currentDate    = $write->getDatePartSql('cwd.website_date');
            if ($this->getVersionHelper()->isGe1700()) {
                $groupPrice     = $write->getCheckSql('gp.price IS NULL', "{$price}", 'gp.price');
            }
            $specialFromDate    = $write->getDatePartSql($specialFrom);
            $specialToDate      = $write->getDatePartSql($specialTo);
            $specialFromUse     = $write->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
            $specialToUse       = $write->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
            $specialFromHas     = $write->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
            $specialToHas       = $write->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");
            $finalPrice         = $write->getCheckSql("{$specialFromHas} > 0 AND {$specialToHas} > 0"
                . " AND {$specialPrice} < {$price}", $specialPrice, $price);
            if ($this->getVersionHelper()->isGe1700()) {
                $finalPrice         = $write->getCheckSql("{$groupPrice} < {$finalPrice}", $groupPrice, $finalPrice);
            }
        } else {
            $currentDate    = new Zend_Db_Expr('cwd.website_date');     // Fixed
            $finalPrice     = new Zend_Db_Expr("IF(IF({$specialFrom} IS NULL, 1, "
                . "IF(DATE({$specialFrom}) <= {$currentDate}, 1, 0)) > 0 AND IF({$specialTo} IS NULL, 1, "
                . "IF(DATE({$specialTo}) >= {$currentDate}, 1, 0)) > 0 AND {$specialPrice} < {$price}, "
                . "{$specialPrice}, {$price})");
        }
        return $finalPrice;
    }

    /**
     * Get bundle special price expression
     *
     * @param  Varien_Db_Adapter_Interface $write
     * @param  Zend_Db_Expr                $specialPrice
     * @param  Zend_Db_Expr                $specialFrom
     * @param  Zend_Db_Expr                $specialTo
     * @return Zend_Db_Expr
     */
    public function getBundleSpecialPriceExpr($write, $specialPrice, $specialFrom, $specialTo)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            $currentDate     = new Zend_Db_Expr('cwd.website_date');
        } else {
            $currentDate     = new Zend_Db_Expr('cwd.website_date');    // Fixed
        }
        if ($this->getVersionHelper()->isGe1600()) {
            $specialExpr    = $write->getCheckSql(
                $write->getCheckSql(
                    $specialFrom.' IS NULL', '1', $write->getCheckSql($specialFrom . ' <= ' . $currentDate, '1', '0')
                )." > 0 AND ".
                $write->getCheckSql(
                    $specialTo . ' IS NULL', '1',
                    $write->getCheckSql($specialTo . ' >= ' . $currentDate, '1', '0')
                ). " > 0 AND {$specialPrice} > 0 ", $specialPrice, '0'
            );
        } else {
            $specialExpr    = new Zend_Db_Expr("IF(IF({$specialFrom} IS NULL, 1, "
                . "IF({$specialFrom} <= {$currentDate}, 1, 0)) > 0 AND IF({$specialTo} IS NULL, 1, "
                . "IF({$specialTo} >= {$currentDate}, 1, 0)) > 0 AND {$specialPrice} > 0, $specialPrice, 0)");
        }
        return $specialExpr;
    }
}
