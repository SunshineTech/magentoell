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
 * SDM_Designer_Block_Page_Tabs class
 */
class SDM_Designer_Block_Page_Tabs extends Mage_Core_Block_Template
{
    /**
     * Designer page tabs
     * @var array
     */
    protected $_designerTabs = null;

    /**
     * Gets an array of tabs for the designer page
     *
     * @return $tabs
     */
    public function getDesignerTabs()
    {
        if ($this->_designerTabs === null) {
            $tabs = array();
            $this->_designerTabs = array();

            // Get products
            $productsByCategory = Mage::helper('sdm_designer')
                ->getDesignerProductsByCategory($this->getDesigner());
            $categoryAttribute = Mage::helper('sdm_designer')->getAttribute('tag_category');
            $attSource = $categoryAttribute->getSource();
            foreach ($productsByCategory as $category => $products) {
                if ($products->count()) {
                    $catName = $attSource->getOptionText($category);
                    $tabs[] = array(
                        'name' => $catName,
                        'data' => $products,
                        'id' => trim(strtolower($catName)).'-products',
                        'type' => 'product'
                    );
                }
            }

            // Get projects
            $tabs[] = array(
                'name' => Mage::helper('sdm_catalog')->getProjectLabel('Project').'s',
                'data' => Mage::helper('sdm_designer')->getDesignerProjects($this->getDesigner()),
                'id' => 'products-tab',
                'type' => 'product'
            );

            // Get videos
            $tabs[] = array(
                'name' => 'Videos',
                'data' => Mage::helper('sdm_designer')->getDesignerVideos($this->getDesigner()),
                'id'   => 'videos-tab',
                'type' => 'video'
            );

            // Get articles
            $tabs[] = array(
                'name' => 'Articles',
                'data' => Mage::helper('sdm_designer')->getDesignerArticles($this->getDesigner()),
                'id' => 'articles-tab',
                'type' => 'article'
            );
            
            // Remove empty tabs, while generating blocks for the others
            foreach ($tabs as $key => $tabData) {
                if (empty($tabData['data'])) {
                    unset($tabs[$key]);
                } elseif (is_array($tabData['data']) ? !count($tabData['data']) : !$tabData['data']->count()) {
                    unset($tabs[$key]);
                } else {
                    $tabs[$key]['id'] = str_replace(" ", '-', $tabs[$key]['id']);
                    $tabs[$key]['block'] = $this->_makeTabContentBlock($tabData, $tabs[$key]['type']);
                }
            }

            $this->_designerTabs = $tabs;
        }
        return $this->_designerTabs;
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

    /**
     * Renders a tab as a carousel
     *
     * @param  array  $tabData
     * @param  string $type
     * @return $combined
     */
    protected function _makeTabContentBlock($tabData, $type = 'product')
    {
        $block = Mage::helper('rendercollection')
            ->initNewCarousel($tabData['data'], $type);

        return $block;
    }
}
