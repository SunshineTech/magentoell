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

$base = Mage::getModuleDir('controllers', 'Mage_Catalog');
require_once $base . DS . "CategoryController.php";

/**
 * SDM_Catalog_CategoryController class
 */
class SDM_Catalog_CategoryController
    extends Mage_Catalog_CategoryController
{

    // Url parameters to allow dashes in
    protected $_allowDashes = array('q', 'price');

    protected function _construct()
    {
        if ($url = $this->_checkParamDashes()) {
            return $this->_redirectUrl($url);
        }
        return parent::_construct();
    }

    /**
     * Look for any params that are separated by dashes, and convert to commas.
     * These are legacy as we use commas now for SOLR search.
     * Example: 50-70     should be changed to    50,70
     * 
     * @return mixed
     */
    protected function _checkParamDashes()
    {
        $hasDashes = false;
        $url = Mage::helper('core/url')->getCurrentUrl();
        $urlParts = explode('?', $url);
        if (count($urlParts) === 2) {
            $params = explode("&", $urlParts[1]);
            foreach($params as $key => $paramString) {
                $paramPieces = explode('=', $paramString);
                $paramValue = isset($paramPieces[1]) ? $paramPieces[1] : "";
                if (in_array($paramPieces[0], $this->_allowDashes)) {
                    continue;
                }
                if (count(explode("-", $paramValue)) > 1) {
                    $hasDashes = true;
                    $params[$key] = $paramPieces[0]."=".implode(",", explode("-", $paramValue));
                }
            }
            $urlParts[1] = implode('&', $params);
            $url = implode('?', $urlParts);
        }
        return $hasDashes ? $url : false;
    }

    /**
     * Initialize requested category object using 'cat' param before
     * falling back to using 'id' param
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCatagory()
    {
        Mage::dispatchEvent('catalog_controller_category_init_before', array('controller_action' => $this));
        $categoryId = (int) $this->getRequest()->getParam('cat', false);
        if (!$categoryId) {
            return parent::_initCatagory();
        }

        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);

        // Force the sidebar to show
        $category->setIsAnchor(1);

        if (!Mage::helper('catalog/category')->canShow($category)) {
            return false;
        }
        Mage::getSingleton('catalog/session')->setLastVisitedCategoryId($category->getId());
        Mage::register('current_category', $category);
        Mage::register('current_entity_key', $category->getPath());

        try {
            Mage::dispatchEvent(
                'catalog_controller_category_init_after',
                array(
                    'category' => $category,
                    'controller_action' => $this
                )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $category;
    }

    /**
     * Category view action
     *
     * @return void
     */
    public function viewAction()
    {
        if ($category = $this->_initCatagory()) {
            // If there is a filtering parameter, redirect to it
            $redirectUrl = $category->getFilteringRedirectUrl();
            if (!empty($redirectUrl)) {
                $this->_redirectUrl($redirectUrl);
                return $this;
            }

            // Get URL path
            $url = Mage::helper('core/url')->getCurrentUrl();
            $url = explode('?', str_replace(Mage::getBaseUrl(), '', $url));
            $url = reset($url);
            $urlPath = explode('/', $url);
            $baseCatalogPath = Mage::getStoreConfig('navigation/general/catalog_category_id');

            // Get child products
            $childProducts = $category->getProductCollection();
            $childProducts->getSelect()
                ->columns('cat_index.is_parent AS is_parent')
                ->where('is_parent="1"');
            $childProductsCount = $childProducts->count();

            if (empty($childProductsCount) && $urlPath[0] !== $baseCatalogPath) {
                // Check if we should show a hook page
                $childCategories = $category->getChildrenCategories();

                // If our children are columns, then grab the children of the columns and
                // make it into a single collection
                $columnChildCategories = array();
                foreach ($childCategories as $child) {
                    if (strpos(trim(strtolower($child->getName())), "column ") !== false) {
                        $columnChildCategories[] = $child->getChildren();
                    }
                }
                
                if (count($columnChildCategories)) {
                    $columnChildCategories = implode(',', $columnChildCategories);
                    $childCategories = Mage::getModel('catalog/category')
                        ->getCollection()
                        ->addAttributeToFilter('entity_id', array('in' => explode(',', $columnChildCategories)))
                        ->addAttributeToSelect('*');
                }

                if (count($childCategories)) {
                    $this->_showHookPage($category, $childCategories);
                } else {
                    $this->_redirectUrl(Mage::getBaseUrl() . $baseCatalogPath);
                }
                return $this;
            } elseif (!empty($childProductsCount) && $urlPath[0] !== $baseCatalogPath) {
                $categoryIdPath = "?cat=".$category->getId();
                $this->_redirectUrl(Mage::getBaseUrl() . $baseCatalogPath.$categoryIdPath);
            }

            $design = Mage::getSingleton('catalog/design');
            $settings = $design->getDesignSettings($category);

            // apply custom design
            if ($settings->getCustomDesign()) {
                $design->applyCustomDesign($settings->getCustomDesign());
            }

            Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            if (!$category->hasChildren()) {
                $update->addHandle('catalog_category_layered_nochildren');
            }

            $this->addActionLayoutHandles();
            $update->addHandle($category->getLayoutUpdateHandle());
            $update->addHandle('CATEGORY_' . $category->getId());
            $this->loadLayoutUpdates();

            // apply custom layout update once layout is loaded
            if ($layoutUpdates = $settings->getLayoutUpdates()) {
                if (is_array($layoutUpdates)) {
                    foreach ($layoutUpdates as $layoutUpdate) {
                        $update->addUpdate($layoutUpdate);
                    }
                }
            }

            $this->generateLayoutXml()->generateLayoutBlocks();
            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $this->getLayout()->helper('page/layout')->applyTemplate($settings->getPageLayout());
            }

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('categorypath-' . $category->getUrlPath())
                    ->addBodyClass('category-' . $category->getUrlKey());
            }

            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }

    /**
     * Render this page as a hook page
     *
     * @param  Mage_Core_Model_Abstract $category
     * @param  mixed                    $childCategories
     * @return $this
     */
    protected function _showHookPage($category, $childCategories)
    {
        $design = Mage::getSingleton('catalog/design');
        $settings = $design->getDesignSettings($category);

        // apply custom design
        if ($settings->getCustomDesign()) {
            $design->applyCustomDesign($settings->getCustomDesign());
        }

        Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $update->addHandle('hookpage');

        $this->addActionLayoutHandles();
        $update->addHandle($category->getLayoutUpdateHandle());
        $update->addHandle('CATEGORY_' . $category->getId());
        $update->addHandle('HOOKPAGE_' . $category->getId());
        $this->loadLayoutUpdates();

        // apply custom layout update once layout is loaded
        if ($layoutUpdates = $settings->getLayoutUpdates()) {
            if (is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }
        }

        $this->generateLayoutXml()->generateLayoutBlocks();

        $this->getLayout()->helper('page/layout')->applyTemplate('one_column');

        if ($root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('categorypath-' . $category->getUrlPath())
                ->addBodyClass('category-' . $category->getUrlKey());
        }

        // Create hookpage block
        $hookpage = $this->getLayout()->createBlock(
            'Mage_Catalog_Block_Category_View',
            'hookpage',
            array('template' => 'catalog/category/view/hookpage.phtml')
        );
        $this->getLayout()->getBlock('category.products')->append($hookpage);

        // Check if there is a parent category for this hookpage category
        $parent = $category->getParentCategory();
        if ($parent->getId()) {
            if (strpos(trim(strtolower($parent->getName())), "column ") !== false) {
                $parent = $parent->getParentCategory();
            }
            $this->getLayout()->getBlock('category.products')->setHookPageParent($parent);
        }

        // Sort child categories
        $sortedCategories = array();
        foreach ($childCategories as $childCat) {
            $urlPath = $childCat->getUrlPath();
            $sortKey = (int)$childCat->getPosition();
            if (strpos($urlPath, '/column-') !== false) {
                $matches = array();
                preg_match("/\/column-(\d)\//", $urlPath, $matches);
                if (isset($matches[1])) {
                    $sortKey += $matches[1] * 100000;
                }
            }
            while (isset($sortedCategories[$sortKey])) {
                // Make sure this position isn't already taken
                $sortKey++;
            }
            $sortedCategories[$sortKey] = $childCat;
        }
        ksort($sortedCategories);

        // Set children to hookpage
        $this->getLayout()->getBlock('hookpage')->setHookCategories($sortedCategories);
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();

    }
}
