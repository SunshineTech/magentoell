<?php
/**
 * Separation Degrees Media
 *
 * Implements the product compatibility functionality.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Compatibility
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Compatibility_Model_Source_Productline class
 */
class SDM_Compatibility_Model_Source_Productline
    extends Mage_Eav_Model_Entity_Attribute_Source_Table
    // extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $collection = Mage::getModel('compatibility/productline')
            ->getCollection();

        $options = array_merge( // Add an empty selection
            array(array('value' => null, 'label' => '')),
            $this->_toArray($collection)
        );

        return $options;
    }
    /**
     * Get all options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Convert a taxonomy collection into an array of label/values for
     * use as attribute option source arrays.
     *
     * @param SDM_Taxonomy_Model_Resource_Item_Collection $collection
     *
     * @return array
     */
    public function _toArray($collection)
    {
        $options = array();

        foreach ($collection as $item) {
            $options[] = array(
                'value' => $item->getId(),
                'label' => $item->getName()
            );
        }
        return $options;
    }

    /**
     * Return the options array
     *
     * @return array
     */
    public function getAllNameOptions()
    {
        $collection = Mage::getModel('compatibility/productline')
            ->getCollection()
            ->addFieldToSelect(array('productline_id', 'name'));

        return $this->_toNameArray($collection);
    }

    /**
     * Processes the collection and returns an associative array with keys
     * being the proruct line ID and values being name
     *
     * @param SDM_Compatibility_Model_Resource_Productline $collection
     *
     * @return array
     */
    protected function _toNameArray($collection)
    {
        $options = array();

        foreach ($collection as $productLine) {
            $options[$productLine->getId()] = $productLine->getName();
        }

        return $options;
    }
}
