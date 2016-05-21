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
 * SDM_Solr_Block_Result_Layer_Filter class
 */
class SDM_Solr_Block_Result_Layer_Filter extends IntegerNet_Solr_Block_Result_Layer_Filter
{
    /**
     * @return Varien_Object[]
     * @throws Mage_Core_Exception
     */
    protected function _getAttributeFilterItems()
    {
        $items = array();
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeCodeFacetName = $attributeCode . '_facet';
        if (isset($this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName})) {

            $attributeFacets = (array)$this->_getSolrResult()->facet_counts->facet_fields->{$attributeCodeFacetName};

            foreach ($attributeFacets as $optionId => $optionCount) {
                if (!$optionCount && !$this->_isSelected($attributeCode, $optionId)) {
                    continue;
                }
                /** @var Mage_Catalog_Model_Category $currentCategory */
                $currentCategory = $this->_getCurrentCategory();
                if ($currentCategory) {
                    $removedFilterAttributeCodes = $currentCategory->getData('solr_remove_filters');
                    if (is_array($removedFilterAttributeCodes) && in_array($attributeCode, $removedFilterAttributeCodes)) {
                        continue;
                    }
                }
                $item = new Varien_Object();
                $item->setCount($optionCount);
                $text = $this->getAttribute()->getSource()->getOptionText($optionId);
                if (!is_string($text)) {
                    continue;
                }
                $item->setLabel($this->_getCheckboxHtml($attributeCode, $optionId) . ' ' . $text);
                $item->setUrl($this->_getUrl($optionId));
                $item->setIsChecked($this->_isSelected($attributeCode, $optionId));
                $item->setType('attribute');
                $item->setOptionId($optionId);
                
                Mage::dispatchEvent('integernet_solr_filter_item_create', array(
                    'item' => $item,
                    'solr_result' => $this->_getSolrResult(),
                    'type' => 'attribute',
                    'entity_id' => $optionId,
                ));

                $items[] = $item;
            }

            // Sort grade level labels
            if ($attributeCode === 'grade_level') {
                $sortedItems = array();
                foreach($items as $item) {
                    $label = trim($item->getLabel()) == 'Pre-K-K' ? '0' : $item->getLabel();
                    $sortedItems[$label] = $item;
                }
                ksort($sortedItems);
                $items = $sortedItems;
            }
        }

        return $items;
    }

    /**
     * Adds the new catalog crumb hash to the option URL
     * 
     * @param  $optionId
     * @return string
     */
    protected function _getUrl($optionId)
    {
        $url = parent::_getUrl($optionId);
        $crumbHash = Mage::getSingleton('sdm_catalogcrumb/crumb')->getHash();
        $urlParts = explode('?', $url);
        $queryString = array();
        if (count($urlParts) === 2) {
            $params = explode("&", $urlParts[1]);
            foreach($params as $key => $paramString) {
                $paramPieces = explode('=', $paramString);
                if (isset($paramPieces[0]) && $paramPieces[0] === 'crumb') {
                    unset($params[$key]);
                }
            }
            $params[] = "crumb=".$crumbHash;
            $urlParts[1] = implode('&', $params);
        } else {
            return $url."?crumb=".$crumbHash;
        }
        return implode('?', $urlParts);
    }
}
