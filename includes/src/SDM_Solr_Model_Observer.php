<?php
/**
 * Separation Degrees Media
 *
 * Modifications to the IntegerNet_Solr module
 *
 * @category  SDM
 * @package   SDM_Solr
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2016 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Solr_Model_Result class
 */
class SDM_Solr_Model_Observer
{

    /**
     * Cache of taxonomy data
     * 
     * @var array
     */
    protected $_taxonomyCache = array(
        'tag_category'                => array(),
        'tag_subcategory'             => array(),
        'tag_product_line'            => array(),
        'tag_subproduct_line'         => array(),
        'tag_theme'                   => array(),
        'tag_subtheme'                => array(),
        'tag_curriculum'              => array(),
        'tag_subcurriculum'           => array(),
        'tag_designer'                => array(),
        'tag_artist'                  => array(),
        'tag_machine_compatibility'   => array(),
        'tag_material_compatibility'  => array(),
        'tag_special'                 => array(),
    );

    /**
     * Call the solr server and specify a custom type, and don't store the results anywhere
     * 
     * @param  $type
     * @return null
     */
    public function beforeSolrRequest($event)
    {
        $transport = $event->getTransport();
        // Add type and price filters to fq param
        $params = $transport->getData('params');
        $params['fq'] = $this->_processFqParam($params['fq']);
        $transport->setData('params', $params);

        // Remove date filter and add "OR" between each part of query string
        $queryText = $transport->getData('query_text');
        $queryText = $this->_processQueryText($queryText);
        $transport->setData('query_text', $queryText);
    }

    /**
     * Adds the type_t and corrected price field to the SOLR index
     * 
     * @param  $event
     * @return null
     */
    public function addProductDataToIndex($event)
    {
        $product = $event->getProduct();
        $productData = $event->getProductData();

        // Add type data (product or project)
        $productData->setData(
            'type_t',
            $product->getTypeId() === 'simple'
                ? SDM_Catalog_Helper_Data::PRODUCT_CODE
                : SDM_Catalog_Helper_Data::IDEA_CODE
        );

        // Special override for UK Euro store view to index `price_euro` field
        // instead of `price` field
        if (Mage::app()->getStore()->getId() == SDM_Core_Helper_Data::STORE_ID_UK_EU) {
            $productData->setData(
                'price_f',
                (float)$product->getData('price_euro')
            );
        }

        // Remove disabled taxonomy
        $this->_removeDisabledTaxonomy($productData);
    }

    /**
     * Adds the price and type filter to the string you pass.
     * Before doing so, it checks for those existing filters and strips them.
     *
     * @param  string $string
     * @return string
     */
    protected function _processFqParam($string)
    {
        // Always check for an existing price_f or type filters and strip them out
        $tempString = explode("AND", $string);
        foreach ($tempString as $key => $value) {
            if (strpos($value, 'type_t:pr') !== false || strpos($value, 'price_f:[') !== false) {
                unset($tempString[$key]);
            }
        }
        $string = implode("AND", $tempString);

        // Add type parameter
        if (isset($_SESSION['solr_type_override']) && !empty($_SESSION['solr_type_override'])) {
            $type = $_SESSION['solr_type_override'];
        } else {
            $type = Mage::helper('sdm_catalog')->getCatalogType();
        }
        $string = isset($string) ? $string : "";
        $string .= " AND type_t:" . ($type === 'project' ? 'project' : 'product');

        // Add price filter (and force the type to be products since projects cant have prices)
        $priceParams = $this->_getPriceParams();
        if (!empty($priceParams)) {
            $string .= " AND (".implode(" OR ", $priceParams).") AND type_t:product";
        }
        return $string;
    }

    /**
     * Removes the display_start_date filter (which we don't need) and adds
     * OR between all filters
     *
     * @param  string $queryText
     * @return string
     */
    protected function _processQueryText($queryText)
    {
        $queryText = preg_replace('/([a-zA-Z_]*:\")/',"\n".'${1}', $queryText);
        $queryTexts = array_map('trim', array_filter(explode("\n", $queryText)));
        foreach($queryTexts as $key => $queryText) {
            if (strpos($queryText, 'display_start_date_t:') === 0) {
                unset($queryTexts[$key]);
            }
        }
        $queryText = implode(" OR ", $queryTexts);
        return $queryText;
    }

    /**
     * Checks for price filter param and if found creates filter query
     * 
     * @return string
     */
    protected function _getPriceParams()
    {
        $price = Mage::app()->getRequest()->getParam('price');
        if (empty($price)) {
            return false;
        }

        $params = array();
        $priceRanges = explode(",", $price);
        foreach($priceRanges as $priceRange) {
            $price = explode("-", $priceRange);
            if (count($price) !== 2) {
                continue;
            }
            $min = empty($price[0]) ? 0.00 : ((float)$price[0])+0.01;
            $max = (float)$price[1];
            if ($min <= 0 && $max > 0) {
                $params[] = "price_f:[* TO ".number_format($max, 2)."]";
            } else if ($max > $min && $min >= 0) {
                $params[] = "price_f:[".number_format($min, 2)." TO ".number_format($max, 2)."]";
            } else if ($min > 0 && empty($max)) {
                $params[] = "price_f:[".number_format($min, 2)." TO *]";
            } 
        }
        return $params;
    }

    /**
     * Runs through all the taxonomy attributes for this product and removes
     * any assignments not enabled for the current site.
     *
     * Since taxonomy data is saved globally, but only enabled per website,
     * this process is REQUIRED. Disable this at your own risk.
     *
     * @param  object $productData
     * @return null
     */
    protected function _removeDisabledTaxonomy($productData)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        foreach($this->_taxonomyCache as $taxCode => $taxCahe) {
            if ($productData->hasData($taxCode."_facet")) {
                $assignedIds = $productData->getData($taxCode."_facet");
                $assignedMvIds = $productData->getData($taxCode."_t_mv");
                foreach($assignedIds as $idKey => $id) {
                    // Have we cached if this taxonomy id is allowed on this site?
                    if (!isset($this->_taxonomyCache[$taxCode][$id][$websiteId])) {
                        //var_dump("loading $taxCode - $id for $websiteId");
                        $tax = Mage::getModel('taxonomy/item')
                            ->load($id);
                        $this->_taxonomyCache[$taxCode][$id][$websiteId] = 
                            $tax->isActive();
                    }
                    // Can we allow this taxonomy ID on this site?
                    if (!$this->_taxonomyCache[$taxCode][$id][$websiteId]) {
                        //Nope - so remove it
                        unset($assignedIds[$idKey]);
                        unset($assignedMvIds[$idKey]);
                        $productData->setData($taxCode."_facet", $assignedIds);
                        $productData->setData($taxCode."_t_mv", $assignedMvIds);
                    }
                }
            }
        }
    }
}