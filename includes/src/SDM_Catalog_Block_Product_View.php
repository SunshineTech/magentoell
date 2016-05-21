<?php
/**
 * Separation Degrees Media
 *
 * Product View
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

require_once 'SDM/HTML/simple_html_dom.php';

/**
 * Renders the product view page
 */
class SDM_Catalog_Block_Product_View extends Mage_Catalog_Block_Product_View
{
    protected $_designerCollection = null;
    protected $_designerProductsCollection = null;
    protected $_taxonomyArtistString = null;
    protected $_taxonomyThemeString = null;
    protected $_taxonomyCurriculumString = null;
    protected $_taxonomyGradeLevelString = null;
    protected $_taxonomyProductLine = null;

    /**
     * Get the current open product on display
     *
     * @return SDM_Catalog_Model_Product
     */
    public function getCurrentProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Returns HTML of the child detailed_content blocks, excluding ones that
     * are empty
     *
     * @return array
     */
    public function getTabContents()
    {
        $emptyAliases = array();
        $productType = strtolower($this->getProduct()->getAttributeText('product_type'));
        $contents = $this->getChildGroup('detailed_content', 'getChildHtml');

        // Get contents first to identify empty ones
        foreach ($contents as $alias => $html) {
            $html = trim($html);
            if (empty($html)) {
                unset($contents[$alias]);
                $emptyAliases[] = $alias;
            }
        }

        // Load the tabs and remove the ones with empty contents
        $tabs = $this->getTabs();
        foreach ($tabs as $alias => $html) {
            // Hide sizzix/ellison101 on gift card pages
            if ($productType === 'gift_card' && $alias === 'tab.sizzix101') {
                unset($tabs[$alias]);
                continue;
            }
            // Check if alias is empty
            foreach ($emptyAliases as $empty) {
                if (strpos($alias, $empty) !== false) {
                    unset($tabs[$alias]);
                    continue;
                }
            }
        }

        return array(
            'tabs' => $tabs,
            'contents' => $contents
        );
    }

    /**
     * Check for Project/Lesson and Sizzi101/Ellison101 labeling on PDP tabs
     *
     * @return array
     */
    public function getTabs()
    {
        foreach ($this->getSortedChildBlocks() as $block) {
            $alias = $block->getBlockAlias();
            if ($alias === 'tab.projects') {
                // Override title for projects tab
                $block->setTitleOverride(
                    Mage::helper('sdm_catalog')->getProjectLabelFromProduct($this->getCurrentProduct()) . 's'
                );
            } elseif ($alias === 'tab.sizzix101') {
                // Override title for sizzix101
                $block->setTitleOverride(
                    Mage::helper('sdm_catalog')->getSizzix101Name($this->getCurrentProduct())
                );
            }
        }
        return $this->getChildGroup('detailed_tab', 'getChildHtml');
    }

    /**
     * Returns the product instruction illustration images of the compatibility
     * product line.
     *
     * @return array
     */
    public function getProductInsturctionImages()
    {
        $images = array();
        $product = $this->getCurrentProduct();
        $id = trim($product->getCompatibilityProductLine());

        if ($id) {
            $productLine = Mage::getModel('compatibility/productline')->load($id);
            if (!$productLine->getRichDescription()) {
                return $images;
            }
            $html = str_get_html($productLine->getRichDescription());

            $i = 0;
            foreach ($html->find('img') as $element) {
                $matches = array();
                preg_match_all(
                    "/{{media url=(\"|\')([a-z\-_0-9\/\:\.]*)(\"|\')}}/i",
                    $element->src,
                    $matches
                );
                $imageLink = isset($matches[2]) ? reset($matches[2]) : '';

                $images[$i]['title'] = trim($element->title);
                $images[$i]['alt'] = trim($element->alt);
                $images[$i]['link'] = trim($imageLink);
                $i++;
            }
        }

        return $images;
    }

    /**
     * Returns the artist taxonomy items for this grouped product
     *
     * @return string
     */
    public function getTaxonomyArtistString()
    {
        if ($this->_taxonomyArtistString === null) {
            // Don't display on EEUS
            if ($this->helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_ED)) {
                $this->_taxonomyArtistString = '';
            } else {
                // Get Attribute tag_artist ids
                $artistTagIds = trim($this->getCurrentProduct()->getTagArtist());

                // Get Artist Taxonomy
                if (!empty($artistTagIds)) {
                    $artistCollection = Mage::getModel('taxonomy/item')
                        ->getCollection()
                        ->addFieldToFilter('type', 'artist')
                        ->addFieldToFilter('entity_id',
                            array('in' => explode(',', $artistTagIds))
                        );
                    $artistArray = array();
                    foreach ($artistCollection as $artist) {
                        if ($artist->isActive()) {
                            $artistArray[] = "<a href='/catalog?tag_artist="
                                . $artist->getId()."&type=project'>"
                                . $artist->getName() . "</a>";
                        }
                    }
                    $this->_taxonomyArtistString = implode(', ', $artistArray);
                } else {
                    $this->_taxonomyArtistString = '';
                }
            }
        }
        return $this->_taxonomyArtistString;
    }

    /**
     * Returns the theme taxonomy items for this grouped product
     *
     * @return string
     */
    public function getTaxonomyThemeString()
    {
        if ($this->_taxonomyThemeString === null) {
            // Don't display on EEUS
            if ($this->helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_ED)) {
                $this->_taxonomyThemeString = '';
            } else {
                // Get Attribute tag_theme ids
                $themeTagIds = trim($this->getCurrentProduct()->getTagTheme());

                // Get Theme Taxonomy
                if (!empty($themeTagIds)) {
                    $themeCollection = Mage::getModel('taxonomy/item')
                        ->getCollection()
                        ->addFieldToFilter('type', 'theme')
                        ->addFieldToFilter('entity_id',
                            array('in' => explode(',', $themeTagIds))
                        );
                    $themeArray = array();
                    foreach ($themeCollection as $theme) {
                        if ($theme->isActive()) {
                            $themeArray[] = "<a href='/catalog?tag_theme="
                                . $theme->getId() . "&type=project'>"
                                . $theme->getName() . "</a>";
                        }
                    }
                    $this->_taxonomyThemeString = implode(', ', $themeArray);
                } else {
                    $this->_taxonomyThemeString = '';
                }
            }
        }
        return $this->_taxonomyThemeString;
    }

    /**
     * Returns the curriculum taxonomy items for this grouped product
     *
     * @return string
     */
    public function getTaxonomyCurriculumString()
    {
        if ($this->_taxonomyCurriculumString === null) {
            // Don't display on SZUK or SZUS
            if ($this->helper('sdm_core')->isSite(
                SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_US,
                SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_UK
            )) {
                $this->_taxonomyCurriculumString = '';
            } else {
                // Get Attribute tag_curriculum ids
                $curriculumTagIds = trim($this->getCurrentProduct()->getTagCurriculum());

                // Get Curriculum Taxonomy
                if (!empty($curriculumTagIds)) {
                    $curriculumCollection = Mage::getModel('taxonomy/item')
                        ->getCollection()
                        ->addFieldToFilter('type', 'curriculum')
                        ->addFieldToFilter('entity_id',
                            array('in' => explode(',', $curriculumTagIds))
                        );

                    $curriculumArray = array();
                    foreach ($curriculumCollection as $curriculum) {
                        if ($curriculum->isActive()) {
                            $curriculumArray[] = "<a href='/catalog?tag_curriculum="
                                . $curriculum->getId() . "&type=project'>"
                                . $curriculum->getName()."</a>";
                        }
                    }
                    $this->_taxonomyCurriculumString = implode(', ', $curriculumArray);
                } else {
                    $this->_taxonomyCurriculumString = '';
                }
            }
        }
        return $this->_taxonomyCurriculumString;
    }

    /**
     * Returns the product's grade level if appropriate
     *
     * @return string
     */
    public function getTaxonomyGradeLevelString()
    {
        if ($this->_taxonomyGradeLevelString === null) {
            // Don't display on SZUK or SZUS
            if ($this->helper('sdm_core')->isSite(
                SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_US,
                SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_UK
            )) {
                $this->_taxonomyGradeLevelString = '';
            } else {
                // Get Attribute tag_curriculum ids
                $gradeLevelIds = $this->getCurrentProduct()->getGradeLevel();
                $gradeLevelIds = array_filter(array_map('trim', explode(',', $gradeLevelIds)));

                // Prepare attribtue labels
                $labels = array();
                $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'grade_level');
                foreach ($attribute->getSource()->getAllOptions(true, true) as $instance) {
                    $labels[$instance['value']] = $instance['label'];
                }

                // Get Curriculum Taxonomy
                if (!empty($gradeLevelIds)) {
                    $gradeLevelArray = array();
                    foreach ($gradeLevelIds as $gradeLevel) {
                        $gradeLevelArray[] = "<a href='/catalog?grade_level="
                            . $gradeLevel . "&type=project'>"
                             .$labels[$gradeLevel] . "</a>";
                    }
                    $this->_taxonomyGradeLevelString = implode(', ', $gradeLevelArray);
                } else {
                    $this->_taxonomyGradeLevelString = '';
                }
            }
        }
        return $this->_taxonomyGradeLevelString;
    }

    /**
     * Grab designer collection where designer tag id.
     *
     * @return array
     */
    public function getDesignerCollection()
    {
        if ($this->_designerCollection === null) {
            // Get Product
            $currentProduct = $this->getCurrentProduct();

            // Get Attribute tag_designer id
            $designerTagIds = trim($currentProduct->getTagDesigner());

            // Get Designer
            $designerArray = array();
            if (!empty($designerTagIds)) {
                $designerCollection = Mage::getModel('taxonomy/item')
                    ->getCollection()
                    ->addFieldToFilter('entity_id',
                        array('in' => explode(',', $designerTagIds))
                    );

                foreach ($designerCollection as $designer) {
                    if ($designer->isActive()) {
                        $designerArray[] = $designer;
                    }
                }
            }
            $this->_designerCollection = $designerArray;
        }
        return $this->_designerCollection;
    }

    /**
     * Get product by selected designers in taxonomy multiselect field
     *
     * @return $productCollection;
     */
    public function getDesignerProducts()
    {
        if ($this->_designerProductsCollection === null) {
            // Get Product
            $currentProduct = $this->getCurrentProduct();

            // Get Attribute tag_designer id
            $designerTagIdsArray = array();
            $designerCollection = $this->getDesignerCollection();
            foreach ($designerCollection as $designer) {
                $designerTagIdsArray[] = $designer->getId();
            }

            // Get Product Collection by selected attribute
            if (!empty($designerTagIdsArray)) {
                $productCollection = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('type_id', 'simple')
                    ->addAttributeToFilter('status',
                        array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                    )
                    ->addAttributeToFilter('entity_id',
                        array('neq' => $currentProduct->getId())
                    )
                    ->setPage(0, 8);

                Mage::helper('taxonomy')->addTaxonomyFilter(
                    $productCollection,
                    'tag_designer',
                    $designerTagIdsArray
                );
                Mage::getSingleton('catalog/product_visibility')
                    ->addVisibleInCatalogFilterToCollection($productCollection);

                $productCollection->getSelect()->orderRand('e.entity_id');

                $this->_designerProductsCollection = $productCollection;
            } else {
                $this->_designerProductsCollection = false;
            }
        }
        return $this->_designerProductsCollection;
    }

    /**
     * Generate designer products html using render collection
     *
     * @return string
     */
    public function getDesignerProductsHtml()
    {
        $productCollection = $this->getDesignerProducts();
        if (!empty($productCollection) && $productCollection->count()) {
            return Mage::helper('rendercollection')
                ->initNewListing($productCollection, 'product')
                ->toHtml();
        }
        return '';
    }

    /**
     * Generate machine compatibility html using render collection
     *
     * @param int $minCount
     *
     * @return string
     */
    public function getAccessoriesHtml($minCount = 0)
    {
        $accessoriesCollection = $this->getCurrentProduct()->getAccessories();
        if (!empty($accessoriesCollection) && count($accessoriesCollection) > $minCount) {
            return Mage::helper('rendercollection')
                ->initNewListing($accessoriesCollection, 'product')
                ->toHtml();
        }
        return '';
    }

    /**
     * Generate machine compatibility html using render collection
     *
     * @return string
     */
    public function getMachineCompatibilityImageHtml()
    {
        $productCollection = $this->getMachineCompatibilityImage();
        if (!empty($productCollection)) {
            return Mage::helper('rendercollection')
                ->initNewCarousel($productCollection, 'machine')
                ->toHtml();
        }
        return '';
    }

    /**
     * Get products that are assigned via machine compatibility association
     *
     * @return $compatibility
     */
    public function getCompatibleProducts()
    {
        return $this->getCurrentProduct()->getCompatibleProducts();
    }

    /**
     * Added _forced_secure check to submit URL
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  array                      $additional
     * @return string
     */
    public function getSubmitUrl($product, $additional = array())
    {
        $additional['_forced_secure'] = Mage::app()->getStore()->isCurrentlySecure();
        return parent::getSubmitUrl($product, $additional);
    }
}
