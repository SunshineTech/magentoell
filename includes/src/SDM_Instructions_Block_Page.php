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
 * SDM_Instructions_Block_Page class
 */
class SDM_Instructions_Block_Page extends Mage_Core_Block_Template
{
    const DEFAULT_LIMIT = 15;
    const PARAM_PAGE    = 'p';
    const PARAM_LIMIT   = 'limit';

    protected $_productTypes = null;
    protected $_collections = null;
    protected $_activeCollection = null;

    /**
     * Get's the current active instruction type
     *
     * @return string
     */
    public function getActiveType()
    {
        if (!$this->hasActiveType()) {
            $activeType = Mage::app()->getRequest()->getParam('product_type');
            $this->setActiveType(empty($activeType) ? 'accessory' : $activeType);
        }
        return parent::getActiveType();
    }

    /**
     * Gets an array of instructions blocks, grouped by product type
     *
     * @return array
     */
    public function getAllInstructionData()
    {
        if ($this->_collections === null) {
            $collections = array();
            $visibility = Mage::getModel('catalog/product_visibility');
            $options = $this->_getProductTypes();
            $activeType = $this->getActiveType();
            $q = $this->_getSearchQuery();
            $hasSearch = isset($q) && !empty($q);

            foreach ($options as $productType) {
                $typeCode = trim(strtolower(str_replace(' ', '-', $productType['label'])));
                $typeCode = str_replace('_', '-', $typeCode);

                $collection = Mage::getModel('catalog/product')->getCollection();
                $collection->addAttributeToSelect('name');
                $collection->addAttributeToSelect('sku');
                $collection->addAttributeToSelect('instruction_file');
                if ($hasSearch) {
                    $visibility->addVisibleInSiteFilterToCollection($collection);
                } else {
                    $visibility->addVisibleInCatalogFilterToCollection($collection);
                }


                $collection->addAttributeToFilter('type_id', array('eq' => 'simple'));
                $collection->addAttributeToFilter('instruction_file', array('notnull' => true));
                $collection->addAttributeToFilter('instruction_file', array('neq' => ''));
                $collection->addAttributeToFilter('product_type', array('eq' => $productType['value']));

                if (!$collection->count()) {
                    continue;
                }

                $collections[$typeCode] = array(
                    'label'         => $productType['label'],
                    'collection'    => $collection,
                    'code'          => $typeCode,
                    'active'        => $activeType === $typeCode
                );

                if ($activeType === $typeCode) {
                    $this->_activeCollection = $typeCode;
                }

                // Order by most recent products
                $collection->getSelect()->reset(Zend_Db_Select::ORDER);
                $collection->getSelect()->order('e.entity_id desc');
            }

            if (empty($collections)) {
                $this->_collections = false;
                return false;
            }

            // Set active collection if not set yet
            if ($this->_activeCollection === null) {
                $firstCollection = reset($collections);
                $this->_activeCollection = $firstCollection['code'];
                $firstCollection['active'] = true;
            }

            // Add pagination
            $collections[$this->_activeCollection]['collection']->clear();
            $collections[$this->_activeCollection]['collection']->setPageSize($this->getCurrentLimit())
                ->setCurPage($this->getRequest()->getParam(self::PARAM_PAGE, false)
                    ? $this->getRequest()->getParam(self::PARAM_PAGE)
                    : 1);

            // Add search query
            $collection = $collections[$this->_activeCollection]['collection'];
            if ($hasSearch) {
                $collection
                    ->getSelect()
                    ->joinLeft(
                        array('fulltextsearch' => 'catalogsearch_fulltext'),
                        'e.entity_id=fulltextsearch.product_id',
                        'data_index'
                    )
                    ->where('fulltextsearch.data_index LIKE "%'.addslashes($q).'%"')
                    ->group('e.entity_id');
                $collection->clear();
            }

            $this->_collections = $collections;
        }
        return $this->_collections;
    }

    /**
     * Gets data for active instructions
     *
     * @return mixed
     */
    public function getActiveInstructionsData()
    {
        if ($this->_activeCollection === null) {
            $this->getAllInstructionData();
        }

        if ($this->_collections === false) {
            return false;
        }

        $activeBlock =  $this->_collections[$this->_activeCollection];

        if (!isset($activeBlock['block'])) {
            $activeBlock['block'] = Mage::helper('rendercollection')
                ->initNewListing(
                    $activeBlock['collection'],
                    'instructions',
                    $this->getToolbarOptions()
                );
        }

        return $activeBlock;
    }

    /**
     * Returns all possible product types
     *
     * @return array
     */
    protected function _getProductTypes()
    {
        if ($this->_productTypes === null) {
            $attribute = Mage::getSingleton('eav/config')
                ->getAttribute('catalog_product', 'product_type');
            $this->_productTypes = $attribute->getSource()->getAllOptions(false);
        }
        return $this->_productTypes;
    }

    /**
     * Get escaped search query
     *
     * @return string
     */
    public function getEscapedSearchQuery()
    {
        return htmlentities($this->_getSearchQuery());
    }

    /**
     * List of settings for the toolbar/pager
     *
     * @return Varien_Object
     */
    public function getToolbarOptions()
    {
        return new Varien_Object(array(
            'pager_options' => array(
                'limit'           => $this->getCurrentLimit(),
                'page_var_name'   => self::PARAM_PAGE,
                'limit_var_name'  => self::PARAM_LIMIT,
                'available_limit' => array(
                    15 => 15,
                    30 => 30,
                    45 => 45,
                )
            )
        ));
    }

    /**
     * Current limit
     *
     * @return integer
     */
    public function getCurrentLimit()
    {
        return $this->getRequest()->getParam(self::PARAM_LIMIT, false)
            ? $this->getRequest()->getParam(self::PARAM_LIMIT)
            : self::DEFAULT_LIMIT;
    }

    /**
     * Get search query
     *
     * @return string
     */
    protected function _getSearchQuery()
    {
        return Mage::app()->getRequest()->getParam('search');
    }
}
