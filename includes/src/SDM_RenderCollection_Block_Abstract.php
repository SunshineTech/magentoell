<?php
/**
 * Separation Degrees Media
 *
 * Collection Rendering Widget
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RenderCollection
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_RenderCollection_Block_Abstract class
 */
class SDM_RenderCollection_Block_Abstract
    extends Mage_Core_Block_Template
{

    /**
     * The current collection for this carousel
     *
     * @var null
     */
    protected $_collection = null;

    /**
     * The current collection type, which controls the template we display
     *
     * @var string
     */
    protected $_collectionType = null;

    /**
     * The path to the template directory for this block
     *
     * @var string
     */
    protected $_templatePath = '';

    /**
     * Add final settings to our block
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        // Gets the template name
        $template = $this->_templatePath. DS .$this->_collectionType.'.phtml';

        // Get collection
        $collection = $this->getCollection();

        // Get product list
        $this->setTemplate($template)
            ->setCollection($collection);

        // Special logic to check for print catalog rendering
        if ($collection instanceof Mage_Catalog_Model_Resource_Collection_Abstract) {
            if ($collection->count() && $collection->getFirstItem()->isPrintCatalog()) {
                $this->setTemplate($this->_templatePath. DS .'print-catalog.phtml');
            }
        }

        return $this;
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  array                      $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if (!$product->getTypeInstance(true)->hasRequiredOptions($product)) {
            return $this->helper('checkout/cart')->getAddUrl($product, $additional);
        }
        $additional = array_merge(
            $additional,
            array(Mage_Core_Model_Url::FORM_KEY => $this->_getSingletonModel('core/session')->getFormKey())
        );
        if (!isset($additional['_escape'])) {
            $additional['_escape'] = true;
        }
        if (!isset($additional['_query'])) {
            $additional['_query'] = array();
        }
        $additional['_query']['options'] = 'cart';
        return $this->getProductUrl($product, $additional);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  array                      $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = array())
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }
        return '#';
    }

    /**
     * Price html
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    public function getPriceHtml($product)
    {
        $productBlock = $this->getLayout()->createBlock('catalog/product_price');
        return $productBlock->getPriceHtml($product);
    }

    /**
     * Sets a collection to this renderer
     *
     * @param object $collection
     * @param string $type
     *
     * @return SDM_RenderCollection_Block_Abstract
     */
    public function setCollection($collection, $type = 'product')
    {
        $this->_collection = $collection;
        $this->_collectionType = $type;
        $this->_checkDiscountTypeApplied();
        return $this;
    }

    /**
     * Sets a search string to this carousel
     *
     * @param string $string
     *
     * @return SDM_RenderCollection_Block_Abstract
     */
    public function setSearchString($string)
    {
        $this->_collectionType = 'product';
        $this->setData('search_string', $string);
        return $this;
    }

    /**
     * Sets a set of SKUs to this carousel
     *
     * @param string|array $skus
     *
     * @return SDM_RenderCollection_Block_Abstract
     */
    public function setSkus($skus)
    {
        if (!is_array($skus)) {
            $skus = explode(',', $skus);
        }
        $this->_collectionType = 'product';
        $this->setData('skus', array_map('trim', $skus));
        return $this;
    }

    /**
     * Returns the collection for this block
     *
     * @return Mage_Catalog_Product_Resource_Collection
     */
    public function getCollection()
    {
        if ($this->_collection === null) {
            if ($this->getSkus() !== null) {
                $this->_collection = $this->_getCollectionFromSkus();
            } elseif ($this->getSearchString() !== null) {
                $this->_collection = $this->_getSearchCollection();
            }
            $this->_checkDiscountTypeApplied();
        }
        return $this->_collection;
    }

    /**
     * Checks if we have a product collection; if we do, then add "discount type applied"
     *
     * @return SDM_RenderCollection_Block_Abstract
     */
    protected function _checkDiscountTypeApplied()
    {
        if ($this->_collection instanceof Mage_Catalog_Model_Resource_Product_Collection) {
            $this->_collection->applyRequiredAttributes();
        }
        return $this;
    }

    /**
     * Converts a set of SKUs into a product colleciton
     *
     * @return Mage_Catalog_Product_Resource_Collection
     */
    protected function _getCollectionFromSkus()
    {
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', array('in' => $this->getSkus()));

        $collection->getSelect()
            ->order('FIELD(e.sku, "'.implode('","', $this->getSkus()).'") ASC');

        // Add visibility filter
        $visibility = Mage::getModel('catalog/product_visibility');
        $visibility->addVisibleInCatalogFilterToCollection($collection);

        return $collection;
    }

    /**
     * Converts a search string into a product collection
     *
     * @return Mage_Catalog_Product_Resource_Collection
     */
    protected function _getSearchCollection()
    {
        // Parse out the chunk of the URL we're interested in
        $searchString = $this->getData('search_string');
        $searchString = explode('#', $searchString);
        $searchString = reset($searchString);
        $searchString = explode('?', $searchString);
        $searchString = end($searchString);
        $searchString = explode('&', $searchString);

        // Start a basic product collection
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addStoreFilter();

        // Apply filters to collection
        $type = 'simple';
        $order = '';
        $dir = '';
        foreach ($searchString as $string) {
            // Explode our search string around the equal sign
            $search = explode('=', $string);
            if (count($search) !== 2) {
                continue;
            }

            // Check if this filter should be ignored, or if
            // should be used in one of our search variables
            $ignored = false;
            switch ($search[0]) {
                case 'type':
                    $type = $search[1] === 'project' ? 'grouped' : 'simple';
                    $ignored = true;
                    break;
                case 'order':
                    $order = $search[1];
                    $ignored = true;
                    break;
                case 'dir':
                    $dir = $search[1];
                    $ignored = true;
                    break;
                case 'p':
                case 'q':
                case 'crumb':
                case 'price':
                case 'mode':
                case 'limit':
                case 'home':
                    $ignored = true;
            }

            if (!$ignored) {
                if (strpos($search[0], 'tag_') !== false) {
                    // Add taxonomy filter
                    Mage::helper('taxonomy')
                        ->addTaxonomyFilter($products, $search[0], explode('-', $search[1]));
                } elseif ($search[0] == 'cat') {
                    // Add category filter
                    $category = Mage::getModel('catalog/category')->load($search[1]);
                    $products->addCategoryFilter($category);
                } else {
                    // Add attribute filter
                    $products->addAttributeToFilter($search[0], array('eq' => array($search[1])));
                }
            }
        }

        // Add visibility filter
        $visibility = Mage::getModel('catalog/product_visibility');
        $visibility->addVisibleInCatalogFilterToCollection($products);

        // Add type ID filter
        $products->addAttributeToFilter('type_id', array('eq' => array($type)));

        // Add order and direction, if not empty
        if (!empty($order)) {
            $dir = !empty($dir) ? $dir : 'asc';
            $products->setOrder($order, $dir);
        }

        // Set page size and return
        return $products->setPageSize(20);
    }
}
