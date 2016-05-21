<?php
/**
 * Separation Degrees One
 *
 * Ellison's navigation links
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Navigation
 * @author    Separation Degrees <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Navigation_Model_Observer class
 */
class SDM_Navigation_Model_Observer extends Mage_Catalog_Model_Observer
{
    /**
     * Recursively adds categories to top menu
     *
     * Rewrite of Mage_Catalog_Model_Observer::_addCategoriesToMenu().
     * $categoryData's 'url' is changed.
     *
     * @param Varien_Data_Tree_Node_Collection|array $categories
     * @param Varien_Data_Tree_Node                  $parentCategoryNode
     * @param Mage_Page_Block_Html_Topmenu           $menuBlock
     * @param bool                                   $addTags
     *
     * @return void
     */
    protected function _addCategoriesToMenu($categories, $parentCategoryNode, $menuBlock, $addTags = false)
    {
        $categoryModel = Mage::getModel('catalog/category');
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();

            $categoryModel->setId($category->getId());
            if ($addTags) {
                $menuBlock->addModelTags($categoryModel);
            }

            $tree = $parentCategoryNode->getTree();

            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
                // Note: config.xml has list of EAV attributes to include
                'is_active' => $this->_isActiveMenuCategory($category),
                'open_in_new_tab' => $category->getOpenInNewTab()
            );

            // URL Customization; check first character to determine type of link
            $filteringParam = trim($category->getFilteringParameter());
            if (empty($filteringParam)) {
                $categoryData['url'] = Mage::getBaseUrl() . $category->getData('request_path');
            } elseif (strpos($filteringParam, 'http') !== (int)0) {
                // Internal link
                $categoryData['url'] = Mage::getBaseUrl() . $filteringParam;
            } else {
                // External link
                $categoryData['url'] = $filteringParam;
            }

            $categoryNode = new Varien_Data_Tree_Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            $flatHelper = Mage::helper('catalog/category_flat');
            if ($flatHelper->isEnabled() && $flatHelper->isBuilt(true)) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->_addCategoriesToMenu($subcategories, $categoryNode, $menuBlock, $addTags);
        }
    }
}
