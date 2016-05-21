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
 * SDM_Catalog_Model_Resource_Category_Flat class
 */
class SDM_Catalog_Model_Resource_Category_Flat extends Mage_Catalog_Model_Resource_Category_Flat
{
    /**
     * Load nodes by parent id
     * Added description, open_in_new_tab, and image to request
     *
     * @param  Mage_Catalog_Model_Category|int $parentNode
     * @param  integer                         $recursionLevel
     * @param  integer                         $storeId
     * @param  bool                            $onlyActive
     * @return Mage_Catalog_Model_Resource_Category_Flat
     */
    protected function _loadNodes($parentNode = null, $recursionLevel = 0, $storeId = 0, $onlyActive = true)
    {
        $_conn = $this->_getReadAdapter();
        $startLevel = 1;
        $parentPath = '';
        if ($parentNode instanceof Mage_Catalog_Model_Category) {
            $parentPath = $parentNode->getPath();
            $startLevel = $parentNode->getLevel();
        } elseif (is_numeric($parentNode)) {
            $selectParent = $_conn->select()
                ->from($this->getMainStoreTable($storeId))
                ->where('entity_id = ?', $parentNode)
                ->where('store_id = ?', $storeId);
            $parentNode = $_conn->fetchRow($selectParent);
            if ($parentNode) {
                $parentPath = $parentNode['path'];
                $startLevel = $parentNode['level'];
            }
        }
        $select = $_conn->select()
            ->from(
                array('main_table' => $this->getMainStoreTable($storeId)),
                array('entity_id',
                    new Zend_Db_Expr('main_table.' . $_conn->quoteIdentifier('name')),
                    new Zend_Db_Expr('main_table.' . $_conn->quoteIdentifier('path')),
                    new Zend_Db_Expr('main_table.' . $_conn->quoteIdentifier('description')),
                    new Zend_Db_Expr('main_table.' . $_conn->quoteIdentifier('image')),
                    new Zend_Db_Expr('main_table.' . $_conn->quoteIdentifier('open_in_new_tab')),
                    'is_active',
                    'is_anchor')
            )
            ->where('main_table.include_in_menu = ?', '1')
            ->order('main_table.position');

        if ($onlyActive) {
            $select->where('main_table.is_active = ?', '1');
        }

        /**
 * @var $urlRewrite Mage_Catalog_Helper_Category_Url_Rewrite_Interface
*/
        $urlRewrite = $this->_factory->getCategoryUrlRewriteHelper();
        $urlRewrite->joinTableToSelect($select, $storeId);

        if ($parentPath) {
            $select->where($_conn->quoteInto("main_table.path like ?", "$parentPath/%"));
        }
        if ($recursionLevel != 0) {
            $levelField = $_conn->quoteIdentifier('level');
            $select->where($levelField . ' <= ?', $startLevel + $recursionLevel);
        }

        $inactiveCategories = $this->getInactiveCategoryIds();

        if (!empty($inactiveCategories)) {
            $select->where('main_table.entity_id NOT IN (?)', $inactiveCategories);
        }

        // Allow extensions to modify select (e.g. add custom category attributes to select)
        Mage::dispatchEvent('catalog_category_flat_loadnodes_before', array('select' => $select));

        $arrNodes = $_conn->fetchAll($select);
        $nodes = array();
        foreach ($arrNodes as $node) {
            $node['id'] = $node['entity_id'];
            $node['open_in_new_tab'] = (int)$node['open_in_new_tab'];
            $nodes[$node['id']] = Mage::getModel('catalog/category')->setData($node);
        }

        return $nodes;
    }
}
