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
 * SDM_Designer_Block_Article_Trending class
 */
class SDM_Designer_Block_Article_Trending extends Mage_Core_Block_Template
{
    /**
     * Gets designer trending articles and products collection combined as an array
     *
     * @param  SDM_Taxonomy_Model_Item $designer
     * @return $combined
     */
    public function getDesignerTrendingCollection($designer = null)
    {
        if (empty($designer)) {
            $designer = $this->getDesigner();
        }

        $productCollection = Mage::helper('sdm_designer')->getDesignerProducts($designer, false, 8);
        $productCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));

        return $productCollection;

        // $articleCollection = Mage::helper('sdm_designer')->getDesignerArticles($designer, 4);
        // $articleCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));

        // $combined = array_merge((array)$productCollection, (array)$articleCollection);
        // shuffle($combined);

        // return $combined;
    }

    /**
     * Gets a listing block with a designer's trending collection
     * Used on designer article pages
     *
     * @return $videos
     */
    public function getTrendingProductsBlock()
    {
        $collection = $this->getDesignerTrendingCollection();

        $block = Mage::helper('rendercollection')
            ->initNewCarousel($collection);

        return $block;
    }

    /**
     * Gets the current designer
     *
     * @return SDM_Taxonomy_Model_Item
     */
    public function getDesigner()
    {
        return Mage::helper('sdm_designer')->getCurrentDesigner();
    }
}
