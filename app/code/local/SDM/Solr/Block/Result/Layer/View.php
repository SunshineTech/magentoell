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
 * SDM_Solr_Block_Result_Layer_View class
 */
class SDM_Solr_Block_Result_Layer_View extends IntegerNet_Solr_Block_Result_Layer_View
{

    protected $_fieldSortOrder = array(
        'brand',
        'tag_category',
        'tag_subcategory',
        'tag_product_line',
        'tag_subproduct_line',
        'tag_theme',
        'tag_subtheme',
        'tag_curriculum',
        'tag_subcurriculum',
        'grade_level',
        'tag_designer',
        'tag_artist',
        'tag_machine_compatibility',
        'tag_material_compatibility',
        'tag_special',
        'price'
    );

    protected $_parentChildAttributes = array(
        'tag_category'     => 'tag_subcategory',
        'tag_product_line' => 'tag_subproduct_line',
        'tag_theme'        => 'tag_subtheme',
        'tag_curriculum'   => 'tag_subcurriculum'
    );

    public function getFilters()
    {
        if (is_null($this->_filters)) {
            $this->_filters = array();
            $facetName = 'category';
            if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$facetName})) {

                $categoryFacets = (array)$this->_getSolrResult()->facet_counts->facet_fields->{$facetName};
                $categoryFilter = $this->_getCategoryFilter($categoryFacets);
                if ($categoryFilter->getHtml()) {
                    $this->_filters['cat'] = $categoryFilter;
                }
            }
            foreach (Mage::getSingleton('integernet_solr/bridge_attributeRepository')->getFilterableAttributes(Mage::app()->getStore()->getId(), false) as $attribute) {
                /** @var Mage_Catalog_Model_Entity_Attribute $attribute */

                /** @var Mage_Catalog_Model_Category $currentCategory */
                $currentCategory = $this->_getCurrentCategory();
                if ($currentCategory) {
                    $removedFilterAttributeCodes = $currentCategory->getData('solr_remove_filters');

                    if (is_array($removedFilterAttributeCodes) && in_array($attribute->getAttributeCode(), $removedFilterAttributeCodes)) {
                        continue;
                    }
                }

                $code = $attribute->getAttributeCode();
                $attributeCodeFacetName = $code . '_facet';
                if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName})) {

                    $attributeFacets = (array)$this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName};
                    $this->_filters[$code] = $this->_getFilter($attribute, $attributeFacets);
                }
                $attributeCodeFacetRangeName = Mage::helper('integernet_solr')->getFieldName($attribute);
                if (isset($this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName})) {

                    $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_intervals->{$attributeCodeFacetRangeName};
                    $this->_filters[$code] = $this->_getIntervalFilter($attribute, $attributeFacetData);
                } elseif (isset($this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName})) {

                    $attributeFacetData = (array)$this->_getSolrResult()->facet_counts->facet_ranges->{$attributeCodeFacetRangeName};
                    $this->_filters[$code] = $this->_getRangeFilter($attribute, $attributeFacetData);
                }
            }

            $this->_filters = $this->_processFilters($this->_filters);
        }
        return $this->_filters;
    }

    /**
     * Runs all of Ellison's custom logic to sort, rename, and show/hide relevant filters
     * 
     * @param  array $filters
     * @return array
     */
    protected function _processFilters($filters)
    {
        $processedFilters = array();

        // Sort based off _fieldSortOrder filters
        foreach($this->_fieldSortOrder as $key) {
            if (isset($filters[$key])) {
                $processedFilters[$key] = $filters[$key];
                unset($filters[$key]);
            }
        }

        // Add any remaining filters to the end
        foreach($filters as $key => $filter) {
            $processedFilters[$key] = $filters[$key];
        }

        // Add a flag on each filter to indicate if it has a value or not
        foreach($processedFilters as $key => $filter) {
            $value = Mage::app()->getRequest()->getparam($key);
            $processedFilters[$key]->setIsSelected(!empty($value));
        }

        // Check parent/child relations, remove children who's parents are not selected
        foreach($this->_parentChildAttributes as $parent => $child) {
            if (!$processedFilters[$parent]->getIsSelected()) {
                unset($processedFilters[$child]);
            }
        }

        // Hide "cat" and "tag_event" on all sites
        unset($processedFilters['cat']);
        unset($processedFilters['tag_event']);

        // Hide brand on all sites except ERUS
        if (!$this->helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)) {
            unset($processedFilters['brand']);
        }

        // Hide grade level and curriculum on SZUK and SZUS
        if ($this->helper('sdm_core')->isSite(
            SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_US,
            SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_UK
        )) {
            unset($processedFilters['grade_level']);
            unset($processedFilters['tag_curriculum']);
            unset($processedFilters['tag_subcurriculum']);
        }

        // Hide price and special filter on the projects tab
        if (Mage::helper('sdm_catalog')->getCatalogType() === SDM_Catalog_Helper_Data::IDEA_CODE) {
            unset($processedFilters['price']);
            unset($processedFilters['tag_special']);
        }

        // Force "Price" label to "Price" on all sites (part of our pricing magic for UK)
        if (isset($processedFilters['price'])) {
            $processedFilters['price']->setName("Price");
        }

        // Determine if the filter should be open or closed
        foreach($processedFilters as $key => $filter) {
            $processedFilters[$key]->setClosedFilter(
                $key !== 'tag_category' && !$filter->getIsSelected()
            );
        }

        return $processedFilters;
    }
}
