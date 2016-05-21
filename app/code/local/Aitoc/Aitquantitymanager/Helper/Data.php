<?php
/**
 * Multi-Location Inventory
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitquantitymanager
 * @version      2.2.3
 * @license:     rJhV4acfvLy4sPgpe7MoLJnfOEhDVfWVuKRvbpcv30
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */


class Aitoc_Aitquantitymanager_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getHiddenWebsiteId()
    {
        $oModel = Mage::getModel('aitquantitymanager/mysql4_core_website');
        
        $iWebsiteId = $oModel->getIdByCode('aitoccode');
        
        return $iWebsiteId; 
    }
    
    public function getCataloginventoryStockTable()
    {
        $sOriginalAitocTable = 'aitoc_cataloginventory_stock_item'; 
        $sOriginalStockTable = 'cataloginventory_stock'; 
        
        $sCurrentAitocTable = Mage::getSingleton('core/resource')->getTableName('aitquantitymanager/stock_item');
        
        if ($sOriginalAitocTable == $sCurrentAitocTable)
        {
            $sCurrentStockTable = $sOriginalStockTable;
        }
        else 
        {
            $sPrefix = substr($sCurrentAitocTable, 0, strpos($sCurrentAitocTable, $sOriginalAitocTable));
            $sCurrentStockTable = $sPrefix . $sOriginalStockTable;
        }
        
        return $sCurrentStockTable; 
    }
    
    
}

?>