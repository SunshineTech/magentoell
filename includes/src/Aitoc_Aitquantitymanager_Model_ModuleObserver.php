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
class Aitoc_Aitquantitymanager_Model_ModuleObserver
{
    /* @var $oDb Varien_Db_Adapter_Pdo_Mysql */
    protected $_oDb = null;

    /**
     * @return bool
     */
    public function onAitocModuleLoad()
    {
        if (Mage::registry('aitoc_inventory_loaded'))
        {
            return false;
        }

        $this->_oDb = Mage::getModel('sales_entity/order')->getReadConnection();

        // check default website
        $this->_checkAitocWebsite();

        $bConvertCompleted = $this->_getAitStockSetting('inventory_convert_completed');

        if (!$bConvertCompleted)
        {
            $this->_copyToAitocTable();
        }

        Mage::register('aitoc_inventory_loaded', true);
    }

    protected function _copyToAitocTable()
    {

        $iDefaultWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId();

        $iOldWebsiteId = $this->_getAitStockSetting('default_website_id');

        $sTableAitocItem   = $this->_getTable('aitquantitymanager/stock_item');
        $sTableMagentoItem = $this->_getDefaultTable($sTableAitocItem);

        $sTableAitocStatus   = $this->_getTable('aitquantitymanager/stock_status');
        $sTableMagentoStatus = $this->_getDefaultTable($sTableAitocStatus);

        if ($iOldWebsiteId)
        {
            $this->_updateDefaultWebsiteId($iOldWebsiteId, $iDefaultWebsiteId);
        }

        // delete temp connection with default website to products
        $this->_deleteWebsite('website_id', $iDefaultWebsiteId);

        // insert temp connection with default website to products
        $this->_insertTempConnection($iDefaultWebsiteId);

        // insert inventory items
        $stock_status_changed_auto = $this->_getStockStatusChangedSql();

        $sSql = "
            INSERT into `" . $sTableAitocItem . "`
            (SELECT
            null as 'item_id',
            `p`.`website_id`,
            `main_table`.`product_id`,
            `main_table`.`stock_id`,
            `main_table`.`qty`,
            `main_table`.`min_qty`,
            `main_table`.`use_config_min_qty`,
            `main_table`.`is_qty_decimal`,
            `main_table`.`backorders`,
            `main_table`.`use_config_backorders`,
            `main_table`.`min_sale_qty`,
            `main_table`.`use_config_min_sale_qty`,
            `main_table`.`max_sale_qty`,
            `main_table`.`use_config_max_sale_qty`,
            `main_table`.`is_in_stock`,
            `main_table`.`low_stock_date`,
            `main_table`.`notify_stock_qty`,
            `main_table`.`use_config_notify_stock_qty`,
            `main_table`.`manage_stock`,
            `main_table`.`use_config_manage_stock`,
            1 as 'use_default_website_stock',".
                    $stock_status_changed_auto.
                    "
            FROM `" . $sTableMagentoItem . "` AS `main_table`
            INNER JOIN `" . $this->_getTable('catalog/product_website') . "` AS `p` ON main_table.product_id=p.product_id
            LEFT JOIN `" . $sTableAitocItem . "` AS `ait_item` ON ait_item.product_id=p.product_id AND ait_item.website_id = p.website_id

            WHERE (ait_item.website_id IS NULL))
                ";
        $this->_oDb->query($sSql);

        // insert inventory status
        $sSql = "
            INSERT into `" . $sTableAitocStatus . "`
            (SELECT
            `main_table`.`product_id`,
            `p`.`website_id`,
            `main_table`.`stock_id`,
            `main_table`.`qty`,
            `main_table`.`stock_status`

            FROM `" . $sTableMagentoStatus . "` AS `main_table`
            INNER JOIN `" . $this->_getTable('catalog/product_website') . "` AS `p` ON (main_table.product_id=p.product_id)
            LEFT JOIN `" . $sTableAitocStatus . "` AS `ait_status` ON ait_status.product_id=p.product_id AND ait_status.website_id = p.website_id

            WHERE (ait_status.website_id IS NULL AND main_table.website_id = 1))";

        $this->_oDb->query($sSql);

        // delete temp connection with default website to products
        $this->_deleteWebsite('website_id',$iDefaultWebsiteId);
        // delete old default website id
        $this->_deleteStockSetting('default_website_id');
        // set settings
        $this->_insertStockSetting('inventory_convert_completed', 1);
    }

    protected function _insertStockSetting($name, $value)
    {
        $fields = array();
        $fields['code']= $name;
        $fields['value']= $value;
        $this->_oDb->insert($this->_getTable('aitquantitymanager/stock_settings'), $fields);
    }

    protected function _deleteStockSetting($code)
    {
        $condition = array($this->_oDb->quoteInto('code=?', $code));
        $this->_oDb->delete($this->_getTable('aitquantitymanager/stock_settings'), $condition);
    }

    protected function _updateDefaultWebsiteId($iOldWebsiteId, $iDefaultWebsiteId)
    {
        $sTableAitocItem   = $this->_getTable('aitquantitymanager/stock_item');
        $sTableAitocStatus   = $this->_getTable('aitquantitymanager/stock_status');
        // update default website_id
        $fields = array('website_id' => $iDefaultWebsiteId);

        $where = $this->_oDb->quoteInto('website_id =?', $iOldWebsiteId);
        $this->_oDb->update($sTableAitocItem, $fields, $where);
        $this->_oDb->update($sTableAitocStatus, $fields, $where);
    }

    protected function _deleteWebsite($field, $id)
    {
        // delete temp connection with default website to products
        $condition = array($this->_oDb->quoteInto($field.'=?', $id));
        $this->_oDb->delete($this->_getTable('catalog/product_website'), $condition);
    }

    protected function _insertTempConnection($iDefaultWebsiteId)
    {
        // insert temp connection with default website to products
        $sSql = "
    INSERT into `" . $this->_getTable('catalog/product_website') . "` (`product_id`, `website_id`)

    (SELECT
    `main_table`.`entity_id`,
    " . $iDefaultWebsiteId . " as `website_id`
    FROM `" . $this->_getTable('catalog/product') . "` AS `main_table` WHERE `main_table`.`entity_id` > 0)
                ";
        $this->_oDb->query($sSql);
    }

    protected function _getStockStatusChangedSql($isDisable = false)
    {
        $sqlArray = array();
        if (version_compare(Mage::getVersion(), '1.6.0.0', 'ge'))
        {
            $sqlArray[] = '`main_table`.`stock_status_changed_auto` as `stock_status_changed_automatically`';
            $sqlArray[] = $isDisable?'`main_table`.`use_config_enable_qty_increments`':'`main_table`.`use_config_enable_qty_inc` as `use_config_enable_qty_increments`';
            $sqlArray[] = $isDisable?'`main_table`.`stock_status_changed_auto` as `stock_status_changed_automatically`':'`main_table`.`use_config_qty_increments`';
        }
        else
        {
            $sqlArray[] = '`main_table`.`stock_status_changed_automatically`';
            if (version_compare(Mage::getVersion(), '1.4.1.0', 'ge'))
            {
                $sqlArray[] = '`main_table`.`use_config_enable_qty_increments`';
                $sqlArray[] = '`main_table`.`use_config_qty_increments`';
            }
        }

        if (version_compare(Mage::getVersion(), '1.4.1.0', 'ge'))
        {
            $sqlArray[] = '`main_table`.`qty_increments`';
            $sqlArray[] = '`main_table`.`enable_qty_increments`';
        }

        if(version_compare(Mage::getVersion(), '1.7.0.0', 'ge'))
        {
            $sqlArray[]= '`main_table`.`is_decimal_divided`';
        }

        return implode(", ", $sqlArray);
    }

    public function onAitocModuleDisableBefore($observer)
    {
        if ('Aitoc_Aitquantitymanager' != $observer->getAitocmodulename())
        {
            return false;
        }

        // process database transformation
        $this->_oDb     = Mage::getModel('sales_entity/order')->getReadConnection();
        /* @var $oDb Varien_Db_Adapter_Pdo_Mysql */

        // insert inventory items
        $sTableAitocItem   = $this->_getTable('aitquantitymanager/stock_item');
        $sTableMagentoItem = $this->_getDefaultTable($sTableAitocItem);

        $sTableAitocStatus   = $this->_getTable('aitquantitymanager/stock_status');
        $sTableMagentoStatus = $this->_getDefaultTable($sTableAitocStatus);

        $this->_oDb->delete($sTableMagentoItem);

        $stock_status_changed_auto = $this->_getStockStatusChangedSql(true);
        $sSql = "
            INSERT into `" . $sTableMagentoItem . "`
            (SELECT
            null as `item_id`,
            `main_table`.`product_id`,
            `main_table`.`stock_id`,
            `main_table`.`qty`,
            `main_table`.`min_qty`,
            `main_table`.`use_config_min_qty`,
            `main_table`.`is_qty_decimal`,
            `main_table`.`backorders`,
            `main_table`.`use_config_backorders`,
            `main_table`.`min_sale_qty`,
            `main_table`.`use_config_min_sale_qty`,
            `main_table`.`max_sale_qty`,
            `main_table`.`use_config_max_sale_qty`,
            `main_table`.`is_in_stock`,
            `main_table`.`low_stock_date`,
            `main_table`.`notify_stock_qty`,
            `main_table`.`use_config_notify_stock_qty`,
            `main_table`.`manage_stock`,
            `main_table`.`use_config_manage_stock`,".
                        $stock_status_changed_auto.
                        "

            FROM `" . $sTableAitocItem . "` AS `main_table`
            WHERE (main_table.website_id = " . Mage::helper('aitquantitymanager')->getHiddenWebsiteId() . "))";
        $this->_oDb->query($sSql);
        // insert inventory status
        $this->_oDb->delete($sTableMagentoStatus);
        $sSql = "
            INSERT into `" . $sTableMagentoStatus . "`
            (SELECT
            `main_table`.`product_id`,
            `p`.`website_id`,
            `main_table`.`stock_id`,
            `main_table`.`qty`,
            `main_table`.`stock_status`

            FROM `" . $sTableAitocStatus . "` AS `main_table`
            INNER JOIN `" . $this->_getTable('catalog/product_website') . "` AS `p` ON main_table.product_id=p.product_id
            WHERE (main_table.website_id = " . Mage::helper('aitquantitymanager')->getHiddenWebsiteId() . "))";
        $this->_oDb->query($sSql);
        // set settings
        $this->_insertStockSetting('default_website_id', Mage::helper('aitquantitymanager')->getHiddenWebsiteId());
        $this->_deleteStockSetting('inventory_convert_completed');

        // delete default website

        $condition = array($this->_oDb->quoteInto('code=?', 'aitoccode'));
        $this->_oDb->delete($this->_getTable('core/website'), $condition);

        $process = Mage::getSingleton('index/indexer')->getProcessByCode('cataloginventory_stock');
        $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);

    }

    protected function _checkAitocWebsite()
    {
        $sAitocDefaultWebsite = 'aitoccode';
        $oSelect = $this->_oDb->select();
        /* @var $oSelect Varien_Db_Select */

        $oSelect->from(array('website' => $this->_getTable('core/website')), '*')
            ->where('website.code = "' . $sAitocDefaultWebsite .'"');

        if (!$this->_oDb->fetchOne($oSelect))
        {
            $fields = array();
            $fields['code']= $sAitocDefaultWebsite;
            $fields['name']= '';
            $fields['sort_order']= 0;
            $fields['default_group_id']= 0;
            $fields['is_default']= 0;
            $this->_oDb->insert($this->_getTable('core/website'), $fields);
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function _getTable($name)
    {
        return Mage::getSingleton('core/resource')->getTableName($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function _getAitStockSetting($name)
    {
        // check database transform
        $oSelect = $this->_oDb->select();
        /* @var $oSelect Varien_Db_Select */
        $oSelect->from(array('main' => $this->_getTable('aitquantitymanager/stock_settings')), array('value'))
            ->where('main.code = "'.$name.'"');

        return $this->_oDb->fetchOne($oSelect);
    }

    /**
     * @param $aitname
     * @return mixed
     */
    protected function _getDefaultTable($aitname)
    {
        if (strpos($aitname, 'aitoc_') !== false)
        {
            return str_replace('aitoc_', '', $aitname);
        }
        return $aitname;
    }
}