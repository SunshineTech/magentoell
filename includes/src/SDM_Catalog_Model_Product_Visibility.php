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
 * SDM_Catalog_Model_Product_Visibility class
 */
class SDM_Catalog_Model_Product_Visibility
    extends Mage_Catalog_Model_Product_Visibility
{
    // const VISIBILITY_NOT_VISIBLE    = 1;
    // const VISIBILITY_IN_CATALOG     = 2;
    // const VISIBILITY_IN_SEARCH      = 3;
    // const VISIBILITY_BOTH           = 4;
    const VISIBILITY_LIMITED           = 5;    // Only visible on product detail page

    /**
     * Retrieve visible in site ids array
     *
     * @return array
     */
    public function getVisibleInSiteIds()
    {
        return array(
            self::VISIBILITY_IN_SEARCH,
            self::VISIBILITY_IN_CATALOG,
            self::VISIBILITY_BOTH,
            self::VISIBILITY_LIMITED
        );
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return array(
            self::VISIBILITY_NOT_VISIBLE => Mage::helper('catalog')->__('Not Visible Individually'),
            self::VISIBILITY_IN_CATALOG  => Mage::helper('catalog')->__('Catalog'),
            self::VISIBILITY_IN_SEARCH   => Mage::helper('catalog')->__('Search'),
            self::VISIBILITY_BOTH        => Mage::helper('catalog')->__('Catalog, Search'),
            self::VISIBILITY_LIMITED     => Mage::helper('catalog')->__('Limited Visibility')
        );
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public static function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, array('value'=>'', 'label'=>''));
        return $options;
    }

    /**
     * Retireve all options
     *
     * @return array
     */
    public static function getAllOptions()
    {
        $res = array();
        $res[] = array('value'=>'', 'label'=> Mage::helper('catalog')->__('-- Please Select --'));
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = array(
               'value' => $index,
               'label' => $value
            );
        }
        return $res;
    }

    /**
     * Retrieve option text
     *
     * @param  int $optionId
     * @return string
     */
    public static function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
