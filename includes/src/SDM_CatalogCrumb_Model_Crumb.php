<?php
/**
 * Separation Degrees One
 *
 * Custom breadcrumb functionality for Ellison's catalog
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogCrumb
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CatalogCrumb_Model_Crumb class
 */
class SDM_CatalogCrumb_Model_Crumb
    extends Mage_Core_Model_Abstract
{

    // Has the crumb been initialized?
    protected $_crumbInitialized = false;

    // Array of URL parameters
    protected $_params = null;

    // Array of filterable attributes
    protected $_filterableAttributes = null;

    // Number of filters currently active
    protected $_filterCount = null;

    // Special filter labels
    protected $_specialFilters = array(
        'q'         => array( 'label' => 'Search' ),
        'price'     => array( 'label' => 'Price' ),
        'cat'       => array( 'label' => '' )
    );

    // Filters we allow in addition to tag_*
    protected $_filterList = array(
        'q', 'price', 'cat', 'brand'
    );

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sdm_catalogcrumb/crumb');
        $this->_initializeCrumb();
    }

    /**
     * Runs the necessary logic to load the current crumb hash and compare it
     * to the current page filters to find if filters have been added or removed
     * from the crumb trail. If so, they are removed from their place in the
     * trail or added to the end of the trail. Then, a new crumb trail hash is
     * generated with this data. If this hash already exists, nothing happens,
     * but if it doesn't, a new one is created and saved to the database.
     *
     * Essentially, once this runs, we have done all the necessary crumb
     * processing for the page load.
     *
     * Note: To avoid repeating this initialization process, the crumb model
     * shoudl always be loaded as a singleton, eg:
     *
     *     Mage::getSingleton('sdm_catalogcrumb/crumb');
     *
     * @return this
     */
    protected function _initializeCrumb()
    {
        if ($this->_initializeCrumb) {
            return $this;
        }

        $params = $this->_getParams();

        // Load the current crumb if we have one
        if (isset($params['crumb'])) {
            $this->load($params['crumb'], 'hash');
            unset($params['crumb']);
        }

        // Remove irrelevant params
        unset($params['p']);
        unset($params['type']);
        unset($params['no_cache']);
        unset($params['alt_type']);
        unset($params['skip_alt_check']);
        unset($params['___SID']);

        // Get hash filter
        $hashFilters = $this->getFilters();
        if (empty($hashFilters)) {
            $hashFilters = array();
        } else {
            $hashFilters = Mage::helper('core')->jsonDecode($hashFilters);
        }

        // Array of any filters which were missing from $hashFilters
        $newFilters = array();

        // Figure out what's new with our hash filters
        foreach ($params as $filterCode => $param) {
            foreach ($param as $optionId) {
                if (!isset($hashFilters[$filterCode."|".$optionId])) {
                    $filterName = $this->getFilterName($filterCode);
                    $newFilters[$filterCode."|".$optionId] = (empty($filterName) ? '' : $filterName.": ")
                        . $this->getFilterOptionName($filterCode, $optionId);
                }
            }
        }

        // Array of missing filters
        $missingFilters = array();

        // Figure out what's missing with our hash filters
        foreach ($hashFilters as $filterKey => $fitlerLabel) {
            $exploded = explode('|', $filterKey);
            if (!isset($params[$exploded[0]]) || !in_array($exploded[1], $params[$exploded[0]])) {
                $missingFilters[] = $filterKey;
            }
        }

        // Were there any changes between our current page crumbs and the filters used?
        if (count($missingFilters) || count($newFilters)) {
            // Remove missing filters
            foreach ($missingFilters as $missing) {
                unset($hashFilters[$missing]);
            }

            foreach ($newFilters as $newString => $newLabel) {
                $hashFilters[$newString] = $newLabel;
            }

            $this->_filterCount = count($hashFilters);

            $hashFilters = Mage::helper('core')
                ->jsonEncode($hashFilters);

            // Current page's hash
            $currentHash = md5($hashFilters);

            // Check if this hash exists already...
            $this->load($currentHash, 'hash');

            // If this hash exists, load it, otherwise create and save it
            if ($this->getData('hash') !== $currentHash) {
                $this->setData('id', null);
                $this->setData('hash', $currentHash);
                $this->setData('filters', $hashFilters);
                $this->save();
            }
        }

        $this->_crumbInitialized = true;

        return $this;
    }

    /**
     * Adds the crumb hash to the state URL
     *
     * @param array $state
     *
     * @return array
     */
    public function addCrumbToState($state)
    {
        if (isset($state[1])) {
            // Remove existing crumb parameter
            $state[1] = preg_replace('/([?|&]?crumb=[^&?\s\'"]*)/', '', $state[1]);

            // Add "fresh" crumb parameter
            $state[1] .= ($state[1] ? "&" : "") . "crumb=" . $this->getHash();
        }

        return $state;
    }

    /**
     * Gets the current page parameters, with concatenated attribute values being
     * split into arrays
     *
     * @return array
     */
    protected function _getParams()
    {
        if ($this->_params === null) {
            $this->_params = array();
            $attributes = $this->getFilterableAttributes();
            foreach ($attributes as $key => $attributeData) {
                $value = $attributeData['value'];

                // Ensure value is a string
                if (!is_string($value)) {
                    continue;
                }
                // Ensure it's a valid filter, or starts with 'tag_'
                if (strpos($key, 'tag_') !== false || in_array($key, $this->_filterList)) {
                    $this->_params[$key] = $key == 'price' ? array($value) : explode(',', $value);
                }
            }
        }
        return $this->_params;
    }

    /**
     * Gets the label of a filter option.
     *
     * Special conditions provided for search and price filters.
     *
     * @param  string     $filterCode
     * @param  int|string $optionId
     * @return string
     */
    public function getFilterOptionName($filterCode, $optionId)
    {
        if ($filterCode === 'price') {
            $range = explode('-', $optionId);

            // To price
            $optionName = Mage::helper('core')->currency((float)$range[0], true, false);

            // From price
            $optionName .= empty($range[1])
                ? " and above"
                : " - " . Mage::helper('core')->currency((float)$range[1], true, false);

            return $optionName;
        } elseif ($filterCode === 'cat') {
            $optionName = Mage::getModel('catalog/category')->load($optionId);
            if ($optionName) {
                $optionName = $optionName->getName();
            } else {
                $optionName = "Category Id: ".$optionId;
            }
            return strtolower($optionName) === "catalog" ? "" : $optionName;
        } elseif ($filterCode === 'q') {
            return "\"".htmlentities($optionId)."\"";
        } else {
            $attributes = $this->getFilterableAttributes();
            return isset($attributes[$filterCode]['options'][$optionId])
                ? $attributes[$filterCode]['options'][$optionId]
                : $optionId;
        }
    }

    /**
     * Gets the name for a filterable attribute
     *
     * @param  string $filterCode
     * @return string
     */
    public function getFilterName($filterCode)
    {
        $attributes = $this->getFilterableAttributes();
        return isset($attributes[$filterCode]['label'])
            ? $attributes[$filterCode]['label']
            : $filterCode;
    }

    /**
     * Returns an array of all filterable attributes along with their options and current value
     *
     * Note: This is an expensive operation and caching isn't tremendously
     * helpful because $_filterableAttributes needs to be re-created at every
     * page request.
     *
     * @return array
     */
    public function getFilterableAttributes()
    {
        if ($this->_filterableAttributes === null) {
            $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
            $attributeCollection->addFieldToFilter('is_filterable', true);
            foreach ($attributeCollection as $attribute) {
                $options = array();
                foreach ($attribute->getSource()->getAllOptions(false) as $optionData) {
                    $options[$optionData['value']] = $optionData['label'];
                }
                $this->_filterableAttributes[$attribute->getAttributeCode()] = array(
                    'label' => $attribute->getFrontendLabel(),
                    'options' => $options,
                );
            }
            $this->_filterableAttributes = array_merge($this->_filterableAttributes, $this->_specialFilters);

            // Add current values to filtertable attributes
            foreach($this->_filterableAttributes as $key => $data) {
                $this->_filterableAttributes[$key]['value'] = Mage::app()->getRequest()->getParam($key);
            }

        }
        return $this->_filterableAttributes;
    }

    /**
     * Returns the trail of crumbs used to build the breadcrumb HTML
     *
     * @return array
     */
    public function getCrumbTrail()
    {
        $trail = Mage::helper('core')->jsonDecode($this->getData('filters'));
        return is_array($trail) ?  array_filter($trail) : $trail;
    }

    /**
     * Returns the number of filters currently active
     *
     * @return int
     */
    public function getFilterCount()
    {
        return $this->_filterCount;
    }

    /**
     * Searches through the breadcrumbs in order of newest to oldest.
     * Returns the first crumb that has an image or description available.
     * Only crumbs for taxonomy attributes or categories are returned.
     *
     * @return bool|SDM_Taxonomy_Model_Item
     */
    public function getLastCrumb()
    {
        if (!$this->hasLastCrumb()) {
            $this->setLastCrumb(false);
            $this->setLastCrumbType(false);
            $trail = $this->getCrumbTrail();
            if (!empty($trail)) {
                $reverseTrail = array_reverse($trail);
                foreach ($reverseTrail as $filterString => $filterLabel) {
                    $filterParts = explode('|', $filterString);
                    if ($filterParts[0] === 'cat') {
                        // Filter by category
                        $category = Mage::getModel('catalog/category')->load($filterParts[1]);
                        // Check we've loaded a category
                        if ($category->getId()) {
                            // Only return category if has image or description
                            if ($category->getDescription() || $category->getImageUrl()) {
                                $this->setLastCrumbType('category');
                                $this->setLastCrumb($category);
                                break;
                            }
                        }
                    } elseif (array_key_exists($filterParts[0], $this->getFilterableAttributes())) {
                        // Filter by taxonomy
                        $taxonomy = Mage::getModel('taxonomy/item')
                            ->load($filterParts[1]);
                        // Check we loaded a taxonomy, and that it's the right type
                        if ("tag_" . $taxonomy->getType() === $filterParts[0] && $taxonomy->getId()) {
                            // Only return taxonomy if has image or description
                            if ($taxonomy->getDescription() || $taxonomy->getImageUrl()) {
                                $this->setLastCrumbType('taxonomy');
                                $this->setLastCrumb($taxonomy);
                                break;
                            }
                        }
                    }
                }
            }
        }
        return parent::getLastCrumb();
    }

    /**
     * If we are filtering by a category, return it here so we can retrive
     * the category specific information
     *
     * @return Mage_Catalog_Model_Category|bool
     */
    public function getLastCrumbType()
    {
        if (!$this->hasLastCrumbType()) {
            $this->getLastCrumb();
        }
        return parent::getLastCrumbType();
    }

    /**
     * Get the base url segment of each
     *
     * @return string
     */
    public function getCrumbBaseUrl()
    {
        return Mage::getStoreConfig('navigation/general/catalog_category_id');
    }
}
