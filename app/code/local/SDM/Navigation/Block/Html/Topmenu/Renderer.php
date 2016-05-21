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
 * SDM_Navigation_Block_Html_Topmenu_Renderer class
 */
class SDM_Navigation_Block_Html_Topmenu_Renderer
    extends Mage_Page_Block_Html_Topmenu_Renderer
{
    /**
     * Get product collection from catalog category model
     *
     * @param Varien_Data_Tree_Node $child
     *
     * @return Mage_Catalog_Model_Resource_Product_Collecttion
     */
    public function getCurrentTabFeatureProducts($child)
    {
        // Determine the category ID using the node
        $categoryId = (int)str_replace('category-node-', '', $child->getId());

        $tabCategory = Mage::getModel('catalog/category')->load($categoryId);

        $featuredCategory = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToFilter('level', $tabCategory->getLevel() + 1)
            ->addAttributeToFilter('path', array('like' => $tabCategory->getPath() . '%'))
            ->addAttributeToFilter('name', 'Featured Products')
            ->getFirstItem();

        if ($featuredCategory->getId()) {
            $productCollection = $featuredCategory->getProductCollection()
                ->applyRequiredAttributes()
                ->addAttributeToFilter('status', 1)
                ->addAttributeToFilter('visibility', 4)
                ->setPage(0, 3);
            $productCollection->getSelect()->orderRand('e.entity_id');
        } else {
            return null;
        }

        return $productCollection;
    }
}
