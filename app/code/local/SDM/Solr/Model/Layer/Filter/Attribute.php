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
 * Layer attribute filter
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class SDM_Solr_Model_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    /**
     * Get option text from frontend model by option id
     *
     * @param   int $optionId
     * @return  string|bool
     */
    protected function _getOptionText($optionId)
    {
        $text = $this->getAttributeModel()->getFrontend()->getOption($optionId);
        return is_array($text) ? $text["label"] : $text;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }
        $filters = explode(",", $filter);
        foreach($filters as $filter) {
            $text = $this->_getOptionText($filter);
            if ($filter && strlen($text)) {
                $this->_getResource()->applyFilterToCollection($this, $filter);
                $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
                $this->_items = array();
            }
        }
        return $this;
    }
}
