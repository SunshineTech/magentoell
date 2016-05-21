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


class Aitoc_Aitquantitymanager_Model_Mysql4_Product_Lowstock_Collection extends Mage_Reports_Model_Mysql4_Product_Lowstock_Collection
//class Mage_Reports_Model_Mysql4_Product_Lowstock_Collection extends Mage_Reports_Model_Mysql4_Product_Collection
{
	protected $_inventoryItemResource = null;
	protected $_inventoryItemJoined = false;
	protected $_inventoryItemTableAlias = 'lowstock_inventory_item';

        /**
         *         
         * @return Aitoc_Aitquantitymanager_Model_Mysql4_Product_Lowstock_Collection 
         */
        public function addToJoinFields($code, $field)
        {
            if (!isset($this->_joinFields[$code]))
            {
                $this->_joinFields[$code] = $field;   
            }

            return $this;
        }
	
	/**
	 * @return string
	 */
	protected function _getInventoryItemResource() 
	{
		if (is_null($this->_inventoryItemResource)) {
			$this->_inventoryItemResource = Mage::getResourceSingleton('cataloginventory/stock_item');
		}
		return $this->_inventoryItemResource;
    }

    /**
	 * @return string
	 */
	protected function _getInventoryItemTable() 
	{
		return $this->_getInventoryItemResource()->getMainTable();
    }

    /**
	 * @return string
	 */
	protected function _getInventoryItemIdField() 
	{
		return $this->_getInventoryItemResource()->getIdFieldName();
	}
	
	/**
	 * @return string
	 */
	protected function _getInventoryItemTableAlias() 
	{
		return $this->_inventoryItemTableAlias;
	}

	/**
	 * @param array|string $fields
	 * @return string
	 */
	protected function _processInventoryItemFields($fields) 
	{
		if (is_array($fields)) {
			$aliasArr = array();
			foreach ($fields as &$field) {
				if ( is_string($field) && strpos($field, '(') === false ) {
					$field = sprintf('%s.%s', $this->_getInventoryItemTableAlias(), $field);
				}   
			}
			unset($field);
			return $fields;
		}
		return sprintf('%s.%s', $this->_getInventoryItemTableAlias(), $fields);
	}
	
	/**
	 * Join cataloginventory_stock_item table for further
	 * stock_item values filters
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function joinInventoryItem($fields=array()) {
		if ( !$this->_inventoryItemJoined ) {
// start aitoc code
		    
            $iWebsiteId = 0;

            $controller = Mage::app()->getFrontController();
            
            if ($controller->getRequest()->getParam('website')) {
                $storeIds = Mage::app()->getWebsite($controller->getRequest()->getParam('website'))->getStoreIds();
                $iStoreId = array_pop($storeIds);
            } else if ($controller->getRequest()->getParam('group')) {
                $storeIds = Mage::app()->getGroup($controller->getRequest()->getParam('group'))->getStoreIds();
                $iStoreId = array_pop($storeIds);
            } else if ($controller->getRequest()->getParam('store')) {
                $iStoreId = (int)$controller->getRequest()->getParam('store');
            } else {
                $iStoreId = '';
            }

            if ($iStoreId)
            {
                $store = Mage::app()->getStore($iStoreId);
                $iWebsiteId = $store->getWebsiteId();
            }
            
            $websiteFilter = ' AND website_id = ' . $iWebsiteId;
            
		    if (!$iWebsiteId)
		    {
                $websiteFilter = '';
		    }
// finish aitoc code
			$this->getSelect()->join(
                array($this->_getInventoryItemTableAlias() => $this->_getInventoryItemTable()),
				sprintf('e.%s=%s.product_id',
					$this->getEntity()->getEntityIdField(),
					$this->_getInventoryItemTableAlias()
//				),
				).$websiteFilter , // fix for aitoc website
				array()
			);
			$this->_inventoryItemJoined = true;
		}
        if (is_string($fields)) {
            $fields = array($fields);
        }
        if (!empty($fields)) {
            $this->getSelect()->columns($this->_processInventoryItemFields($fields));
        }
		return $this;
	}
	
	/**
	 * @param array|string $typeFilter
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function filterByProductType($typeFilter)
	{
		if (!is_string($typeFilter) && !is_array($typeFilter)) {
			Mage::throwException(
				Mage::helper('catalog')->__('Wrong product type filter specified')
			);
		}
		$this->addAttributeToFilter('type_id', $typeFilter);
		return $this;
	}
	
	/**
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function filterByIsQtyProductTypes() 
	{
		$this->filterByProductType(
			array_keys(array_filter(Mage::helper('cataloginventory')->getIsQtyTypeIds()))
		);
		return $this;
	}
	
	/**
	 * @param int|null $storeId
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function useManageStockFilter($storeId=null)
	{
		$this->joinInventoryItem();
		$this->getSelect()->where(sprintf('IF(%s,%d,%s)=1', 
			$this->_processInventoryItemFields('use_config_manage_stock'), 
            (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK,$storeId), 
            $this->_processInventoryItemFields('manage_stock')));
        return $this;
	}
	
	/**
	 * @param int|null $storeId
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function useNotifyStockQtyFilter($storeId=null)
	{
#		$this->joinInventoryItem(array('qty'));
		$this->getSelect()->where(sprintf('qty < IF(%s,%d,%s)', 
			$this->_processInventoryItemFields('use_config_notify_stock_qty'), 
            (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_NOTIFY_STOCK_QTY,$storeId), 
            $this->_processInventoryItemFields('notify_stock_qty')));
        return $this;
	}
    
    public function setIdFieldName($fieldName)
    {
        $this->_setIdFieldName($fieldName);
    }

    /**
     * Get select count sql
     *
     * @return unknown
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        $resource = new Mage_Core_Model_Resource();
        $read = $resource->getConnection('core_read');
        $select = $read->select()
            ->from(array('table_ls' => new Zend_Db_Expr( '(' . $countSelect . ')')), '')
            ->columns("count(*)");

        return $select;
    }
}