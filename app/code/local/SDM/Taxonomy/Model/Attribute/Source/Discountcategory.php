<?php
/**
 * Separation Degrees One
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Model_Attribute_Source_Discountcategory class
 */
class SDM_Taxonomy_Model_Attribute_Source_Discountcategory
    extends SDM_Taxonomy_Model_Attribute_Source_Abstract
{
    const CODE = 'discount_category';

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions()
    {
        $collection = Mage::getModel('taxonomy/item')
            ->getCollection()
            ->addFieldToFilter('type', $this->getCode());

        return array_merge( // Add an empty selection
            array(array('value' => null, 'label' => '')),
            Mage::helper('taxonomy')->convertCollectionToOptions($collection)
        );
    }
}
