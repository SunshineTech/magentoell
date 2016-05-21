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
 * SDM_Catalog_Model_Product class
 */
class SDM_Catalog_Model_Product extends Mage_Catalog_Model_Product
{
    /**
     * Is the product in stock, or are backorders allowed
     *
     * @var bool
     */
    protected $_backorderStatus = null;

    /**
     * Purchase logic
     *
     * @var array
     */
    protected $_lifecycleLogic = null;

    /**
     * If this product new?
     *
     * @var bool
     */
    protected $_isNewProduct = null;

    /**
     * Holds references to accessory collections
     *
     * @var array
     */
    protected $_accessories = null;

    /**
     * Stores the featured accessories
     *
     * @var array
     */
    protected $_featuredAccessories = null;


    /**
     * Is this product a print catalog?
     *
     * @var bool
     */
    protected $_isPrintCatalog = null;

    /**
     * Get product price throught type instance
     *
     * Rewiritten to return Euro price when appropriate
     *
     * @return decimal
     */
    public function getPrice()
    {
        if ($this->_calculatePrice || !$this->getData('price')) {
            return $this->getPriceModel()->getPrice($this);
        } else {
            if ((int)Mage::app()->getStore()->getId() === SDM_Core_Helper_Data::STORE_ID_UK_EU) {
                return $this->getData('price_euro');
            } else {
                return $this->getData('price');
            }
        }
    }

    /**
     * Checks if a product is new
     *
     * @return boolean
     */
    public function isNewProduct()
    {
        if ($this->_isNewProduct === null) {
            $now = strtotime(date('Y-m-d', time()));
            $to = $this->getData('news_to_date');
            $from = $this->getData('news_from_date');

            if (empty($to) && empty($from)) {
                $this->_isNewProduct = false;
            } elseif (empty($to)) {
                $this->_isNewProduct = $now >= strtotime($from);
            } elseif (empty($from)) {
                $this->_isNewProduct = $now <= strtotime($to);
            } else {
                $this->_isNewProduct = $now >= strtotime($from) && $now <= strtotime($to);
            }
        }
        return $this->_isNewProduct;
    }

    /**
     * Returns if the product is a print catalog product
     *
     * @return boolean
     */
    public function isPrintCatalog()
    {
        if ($this->_isPrintCatalog === null) {
            $this->_isPrintCatalog = (int)$this->getData('attribute_set_id') === 11;
        }
        return $this->_isPrintCatalog;
    }

    /**
     * Checks the display_start_date and display_end_date variables
     * to see if we can display the product anywhere on the site
     *
     * @return boolean
     */
    public function isDisplayable()
    {
        if ($this->getData('is_displayable') === null) {
            // Default to displayable
            $this->setData('is_displayable', true);

            // Get current date
            $now = Mage::getModel('core/date')->timestamp(time());

            // Check start date
            $start = $this->getData('display_start_date');
            if (!empty($start) && strtotime($start) > $now) {
                $this->setData('is_displayable', false);
            }

            // Check end date
            $end = $this->getData('display_end_date');
            if (!empty($end) && strtotime($end) < $now) {
                $this->setData('is_displayable', false);
            }
        }
        return $this->getIsDisplayable();
    }

    /**
     * Checks if our purchase logic will allow us to sell the product.
     * This does not necessarily mean you can complete a checkout with the item.
     *
     * @return bool
     */
    public function isSalable()
    {
        // If print catalog, return true
        if ($this->isPrintCatalog()) {
            return true;
        }

        // If retail site, make sure logged in & approved
        if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if (!$customer->isApprovedRetailer()) {
                return false;
            }
        }

        if (!$this->getData('allow_cart_backorder') && $this->getStockItem()->getQty() <= 0) {
            return false;
        }

        // Check simple products for "allow_cart", and then fallback to default logic
        if ($this->getTypeId() === 'simple') {
            return $this->getData('allow_cart') ? parent::isSalable() : false;
        } else {
            return parent::isSalable();
        }
    }

    /**
     * Checks if our purchase logic will allow us to sell the product
     *
     * @return bool
     */
    public function isSaleable()
    {
        return $this->isSalable();
    }

    /**
     * Checks if our purchase logic will allow us to sell the product
     *
     * @return bool
     */
    public function isAvailable()
    {
        // If print catalog, return true
        if ($this->isPrintCatalog()) {
            return true;
        }

        if ($this->getTypeId() === 'simple') {
            return $this->getData('allow_cart') ? parent::isAvailable() : false;
        } else {
            return parent::isAvailable();
        }
    }

    /**
     * Check if we can preorder the propduct
     *
     * @return boolean
     */
    public function isPreorderable()
    {
        return (bool)$this->getData('allow_preorder');
    }

    /**
     * Check if we can add the product to a quote
     *
     * @return boolean
     */
    public function isQuotable()
    {
        return (bool)$this->getData('allow_quote');
    }

    /**
     * Check if we can backorder the product (in checkout)
     *
     * @return boolean
     */
    public function isBackorderable()
    {
        return (bool)$this->getData('allow_checkout_backorder');
    }

    /**
     * Unserializes the button display logic field
     *
     * @return array
     */
    public function getParsedButtonDisplayLogic()
    {
        if (!$this->hasParsedButtonDisplayLogic()) {
            // Start with no override
            $this->setButtonLogicRetailerOverride(false);

            // Is button display logic missing? Then run lifecycle logic and get it
            $logic = $this->getButtonDisplayLogic();
            $index = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
            if (Mage::getStoreConfig('sdm_lifecycle/options/enabled') && $index->getMode() === 'real_time') {
                if (empty($logic)) {
                    Mage::helper('sdm_catalog/lifecycle')
                        ->applyLifecycleModifications($this->getId());
                    $product = Mage::getModel('catalog/product')->load($this->getId());
                    $logic = $product->getData('button_display_logic');
                    $this->setButtonDisplayLogic($logic);
                }
            }

            // Special logic for unapproved retailers
            if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)) {
                $session = Mage::getSingleton('customer/session');
                if (!$session->isLoggedIn() || !$session->getCustomer()->isApprovedRetailer()) {
                    $logic = array(
                        'type' => 'text',
                        'value' => ''
                    );
                    $this->setButtonLogicRetailerOverride(true);
                    $this->setParsedButtonDisplayLogic($logic);
                    return $logic;
                }
            }

            // Save parsed logic
            $parsedLogic = empty($logic) ? array() : unserialize($logic);
            $this->setParsedButtonDisplayLogic($parsedLogic);
        }

        // Return data
        return parent::getParsedButtonDisplayLogic();
    }

    /**
     * Returns true if we're on the retailer site as an unapproved retailer.
     * We can use this to know if the button logic we see is real, or "overridden"
     * due to this condition.
     *
     * @return bool
     */
    public function getButtonLogicRetailerOverride()
    {
        if ($this->hasButtonLogicRetailerOverride()) {
            $this->getParsedButtonDisplayLogic();
        }

        return parent::getButtonLogicRetailerOverride();
    }

    /**
     * What type of button are we showing
     *
     * @return string
     */
    public function getButtonType()
    {
        $logic = $this->getParsedButtonDisplayLogic();
        return isset($logic['type']) ? $logic['type'] : 'text';
    }

    /**
     * What type of button are we showing
     *
     * @return string
     */
    public function getButtonValue()
    {
        $logic = $this->getParsedButtonDisplayLogic();
        if (!empty($logic)) {
            return isset($logic['value']) ? $logic['value'] : '';
        }
        return '* Button Display Logic Missing *';
    }

    /**
     * Can we view button on PDP?
     *
     * @return bool
     */
    public function getButtonVisiblePDP()
    {
        $logic = $this->getParsedButtonDisplayLogic();
        return isset($logic['visible_pdp']) ? (bool)$logic['visible_pdp'] : true;
    }

    /**
     * Can we view button listing?
     *
     * @return bool
     */
    public function getButtonVisibleListing()
    {
        $logic = $this->getParsedButtonDisplayLogic();
        return isset($logic['visible_listing']) ? (bool)$logic['visible_listing'] : true;
    }

    /**
     * Logic to return avialalbility message only if product is not orderable;
     * otherwise, we never want to return it no matter what
     *
     * @return string
     */
    public function getAvailabilityMessage()
    {
        $notOrderable = (int)$this->getData('is_orderable') === 0;
        return $notOrderable ? $this->getData('availability_message') : '';
    }

    /**
     * Retrieve collection related product
     * Add Ellison's custom visibility to collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function getRelatedProductCollection()
    {
        $collection = parent::getRelatedProductCollection();

        if (Mage::app()->getWebsite()->getCode() != Mage_Core_Model_Store::ADMIN_CODE) {
            $visibility = Mage::getModel('catalog/product_visibility');
            $visibility->addVisibleInCatalogFilterToCollection($collection);
        }

        return $collection;
    }

    /**
     * Returns the related products. A related product is of the same taxonomy
     * type of the given product. Product line tag must match as well. Furthermore,
     * they are generated differently depending on the taxonomy and the website.
     *
     * @see Mage_Catalog_Model_Product::getRelatedProducts()
     *
     * @return array of SDM_Catalog_Model_Products
     */
    public function getRelatedProducts()
    {
        if (!$this->hasData('related_products')) {
            $products = array();
            $websiteCode = Mage::app()->getWebsite()->getCode();
            $maxAllowedN = Mage::helper('sdm_catalog')->getProductLimitNumber();

            if ($websiteCode === Mage_Core_Model_Store::ADMIN_CODE) {
                return parent::getRelatedProducts();
            }

            /**
             * Statically related products. Add these first, so they appear first
             * in the frontend in the position assigned.
             */
            // @var Mage_Catalog_Model_Resource_Product_Link_Product_Collection
            $staticRelatedProducts = $this->getRelatedProductCollection();
            // $this->_applyRequiredAttributes($staticRelatedProducts);  // Assigned in admin; deprecated
            $staticRelatedProducts->applyRequiredAttributes();
            $staticRelatedProducts->setPage(1, $maxAllowedN);

            foreach ($staticRelatedProducts as $product) {
                $products[] = $product;
            }

            // If we have enough products, return them
            if (count($products) >= $maxAllowedN) {
                return $products;
            }

            // Set limit to remaining required number of products
            $this->setSqlLimit($maxAllowedN - count($products));

            /**
             * Dynamically related products. These are shuffled.
             */
            if ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_US
                || $websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK
            ) {
                if ($this->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {    // Products
                    $collection = $this->getProductCollection()->addSubthemeTagFilter($this);
                    if ($collection && (int)$collection->getSize() === 0) {
                        $collection = $this->getProductCollection()->addSubcategoryTagFilter($this);
                    }
                } else {    // Ideas
                    $collection = $this->getProductCollection()->addThemeTagFilter($this);
                }

            } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED) {
                if ($this->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {    // Products
                    $collection = $this->getProductCollection()->addSubcurriculumTagFilter($this);
                    if ($collection && (int)$collection->getSize() === 0) {
                        $collection = $this->getProductCollection()->addCategoryTagFilter($this);
                    }
                } else {    // Ideas
                    $collection = $this->getProductCollection()->addSubcurriculumTagFilter($this);
                }

            } elseif ($websiteCode === SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE) {
                if ($this->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {    // Products
                    // If brand = sizzix, use SZUS logic. If brand = ellison, use EEUS logic.
                    if (strtolower($this->getAttributeText('brand')) != 'sizzix') {
                        $collection = $this->getProductCollection()->addSubthemeTagFilter($this);
                        if ($collection && (int)$collection->getSize() === 0) {
                            $collection = $this->getProductCollection()->addSubcategoryTagFilter($this);
                        }
                    } else {    // Use this by default
                        $collection = $this->getProductCollection()->addSubcurriculumTagFilter($this);
                        if ($collection && (int)$collection->getSize() === 0) {
                            $collection = $this->getProductCollection()->addCategoryTagFilter($this);
                        }
                    }
                } else {    // Ideas
                    // If brand = sizzix, use SZUS logic. If brand = ellison, use EEUS logic.
                    if (strtolower($this->getAttributeText('brand')) != 'sizzix') {
                        $collection = $this->getProductCollection()->addThemeTagFilter($this);
                    } else {    // Use this by default
                        $collection = $this->getProductCollection()->addSubcurriculumTagFilter($this);
                    }
                }

                // Admin scope
            } else {
                // Covered already previously
            }

            // Ensure proper visibility
            $visibility = Mage::getModel('catalog/product_visibility');
            $visibility->addVisibleInCatalogFilterToCollection($collection);

            if (isset($collection) && $collection) {
                foreach ($collection as $product) {
                    $products[] = $product;
                    if (count($products) >= $maxAllowedN) { // double check
                        break;
                    }
                }
            }
            $this->setData('related_products', $products);
        }

        return $this->getData('related_products');
    }

    /**
     * Returns the product collection without any filters
     *
     * @return SDM_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        return Mage::getResourceModel('catalog/product_collection');
    }

    /**
     * Returns child products. Only works for grouped products for now, as there
     * are no configurable or bundle products in the catalog.
     *
     * @see Mage_Catalog_Model_Product_Type_Grouped::getAssociatedProducts()
     *
     * @return array Array of SDM_Catalog_Model_Product
     */
    public function getChildProducts()
    {
        if ($this->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            return $this->getTypeInstance(true)->getAssociatedProducts($this);
        }
    }

    /**
     * Alias method for getChildProducts()
     *
     * @return mixed
     */
    public function getUsedProducts()
    {
        return $this->getChildProducts();
    }

    /**
     * Return the parent products
     *
     * @return array of SDM_Catalog_Model_Product
     */
    public function getAssociatedParents()
    {
        $products = array();
        $childId = $this->getId();
        $parentIds = Mage::getResourceSingleton('catalog/product_link')
            ->getParentIdsByChild(
                $childId,
                Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED
            );

        $collection = $this->getProductCollection()->applyRequiredAttributes()
            ->addAttributeToFilter(
                'entity_id',
                array('in' => $parentIds)
            );

        $visibility = Mage::getModel('catalog/product_visibility');
        $visibility->addVisibleInCatalogFilterToCollection($collection);

        $collection->getSelect()->order('e.created_at DESC');

        foreach ($collection as $product) {
            $products[] = $product;
        }
        return $products;
    }

    /**
     * Get parent html
     *
     * @return string
     */
    public function getAssociatedParentsHtml()
    {
        $productCollection = $this->getAssociatedParents();

        // If only one, no need to display bottom block
        if (count($productCollection) <= 1) {
            return array();
        }

        if (!empty($productCollection)) {
            return Mage::helper('rendercollection')
                ->initNewListing($productCollection, 'product')
                ->toHtml();
        }
    }

    /**
     * Returns the accessories.
     *
     * @return array of SDM_Catalog_Model_Product
     */
    public function getAccessories()
    {
        if ($this->_accessories === null) {
            $products = array();
            $accessories = str_replace(' ', '', trim($this->getRelatedAccessories()));
            $accessories = str_replace('|', ',', $accessories);
            $skus = explode(',', $accessories);
            $skus = array_unique(array_map('trim', array_filter($skus)));

            $collection = $this->getProductCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter(
                    'sku',
                    array('in' => $skus)
                );

            $visibility = Mage::getModel('catalog/product_visibility');
            $visibility->addVisibleInCatalogFilterToCollection($collection);

            foreach ($collection as $product) {
                $products[$product->getSku()] = $product;
            }

            // Sort products in the order they were listed
            $sortedProducts = array();
            foreach ($skus as $sku) {
                if (isset($products[$sku])) {
                    $sortedProducts[$sku] = $products[$sku];
                }
            }

            $this->_accessories = $sortedProducts;
        }

        return $this->_accessories;
    }

    /**
     * Returns the accessories.
     *
     * @return array of SDM_Catalog_Model_Product
     */
    public function getFeaturedAccessories()
    {
        if ($this->_featuredAccessories === null) {
            $products = array();
            $accessories = str_replace(' ', '', trim($this->getRelatedAccessories()));

            // Explode by pipe
            $skus = array_map('trim', explode('|', $accessories));
            $skus = array_filter($skus);
            $beforePipe = explode(',', reset($skus));

            // Get all accessories, and filter out the ones we should show as featured
            $accessories = $this->getAccessories();
            $featuredAccessories = array();

            // Continue if we have valid accessories
            if (count($accessories)) {
                // First, try with the beforePipes skus
                foreach ($beforePipe as $sku) {
                    if (isset($accessories[$sku])) {
                        $featuredAccessories[$sku] = $accessories[$sku];
                    }
                }

                // If nothing, grab the first accessory
                if (empty($featuredAccessories)) {
                    $firstAccessory = reset($accessories);
                    $featuredAccessories[$firstAccessory->getSku()] = $firstAccessory;
                }
            }

            $this->_featuredAccessories = $featuredAccessories;
        }

        return $this->_featuredAccessories;
    }

    /**
     * Returns the compatible machines and associated products
     *
     * @param bool $getCollection Determines whether to include product collection
     *
     * @return array Could contain 'collection' element which will contain a collection
     */
    public function getCompatibleProducts($getCollection = true)
    {
        if (!$this->hasCompatabilities()) {
            $this->setCompatibilities(
                $this->_getResource()->getCompatibleProducts($this, $getCollection)
            );
        }

        return $this->getCompatibilities();
    }

    /**
     * Returns a collection of
     * @return [type] [description]
     */
    public function getCompatibleMaterials()
    {
        if (!$this->hasCompatibleMaterial()) {
            $materials = array();
            $matComp = $this->getTagMaterialCompatibility();
            $compatibleMatIds = explode(',', trim($matComp));

            $collection = Mage::getModel('taxonomy/item')->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter(
                    'entity_id',
                    array('in' => $compatibleMatIds)
                )
                ->setOrder('name', 'ASC');

            foreach ($collection as $material) {
                $materials[] = $material;
            }

            $this->setCompatibleMaterial($materials);
        }

        return $this->getCompatibleMaterial();
    }

    /**
     * Add image to media gallery. Modified to update image label.
     *
     * @param string       $file           file path of image in file system
     * @param string|array $mediaAttribute code of attribute with type 'media_image', leave blank if image should be
     *                                     only in gallery leave blank if image should be only in gallery leave blank if
     *                                     image should be only in gallery
     * @param boolean      $move           if true, it will move source file
     * @param boolean      $exclude        mark image as disabled in product page view
     * @param string|null  $label
     *
     * @return Mage_Catalog_Model_Product
     */
    public function addImageToMediaGallery(
        $file,
        $mediaAttribute = null,
        $move = false,
        $exclude = true,
        $label = null
    ) {
        $attributes = $this->getTypeInstance(true)->getSetAttributes($this);
        if (!isset($attributes['media_gallery'])) {
            return $this;
        }
        $mediaGalleryAttribute = $attributes['media_gallery'];
        /* @var $mediaGalleryAttribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $mediaGalleryAttribute->getBackend()
            ->addImage($this, $file, $mediaAttribute, $move, $exclude, $label); // only modification
        return $this;
    }

    /**
     * Retrive media gallery images, except for instruciton images. Instruction
     * image data is set here as well.
     *
     * @return Varien_Data_Collection
     */
    public function getMediaGalleryImages()
    {
        if (!$this->getData('media_gallery_images')) {
            $this->_getMediaGalleryImages();
        }

        return $this->getData('media_gallery_images');
    }

    /**
     * Retrieve the instruction images. Sets it if not set.
     *
     * @return Varien_Data_Collection
     */
    public function getInstructionImages()
    {
        if (!$this->getData('instruction_image')) {
            $this->_getMediaGalleryImages();
        }
        return $this->getData('instruction_image');
    }

    /**
     * Set the product gallery and instruction images
     *
     * @return void
     */
    protected function _getMediaGalleryImages()
    {
        if (!$this->hasData('media_gallery_images') && is_array($this->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            $instructionImages = new Varien_Data_Collection();
            foreach ($this->getMediaGallery('images') as $image) {
                if ($image['disabled']) {
                    continue;
                }

                $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $this->getMediaConfig()->getMediaPath($image['file']);

                // Separate images into product and insturction images
                if (strpos(strtolower($image['label']), 'instruction:') !== false) {
                    $image['instruction_label'] = explode('instruction:', $image['label']);
                    $image['instruction_label'] = end($image['instruction_label']);
                    $instructionImages->addItem(new Varien_Object($image));
                } else {
                    $images->addItem(new Varien_Object($image));
                }
            }
            $this->setData('media_gallery_images', $images);
            $this->setData('instruction_image', $instructionImages);
        }
    }

    /**
     * Depreacted method in favor of
     * SDM_Catalog_Model_Resource_Product_Link_Product_Collection::applyRequiredAttributes.
     *
     * Applies attributes required for rendering for any product collection
     *
     * Note: this should really be defined in Mage_Catalog_Model_Resource_Product_Link_Product_Collection.
     * However, in order to avoid rewriting that class, this filter is defined here.
     *
     * @param mixed $collection
     *
     * @see SDM_Catalog_Model_Resource_Product_Collection::_applyRequiredAttributes()
     * @see Mage_Catalog_Block_Product_List_Related::_prepareData()
     *
     * @return Any product collection
     */
    protected function _applyRequiredAttributes($collection)
    {
        $collection->addMinimalPrice()
            ->addFinalPrice()
            // ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addUrlRewrite()
            ->setPositionOrder()
            ->addStoreFilter();
    }
}
