<?php
/**
 * Separation Degrees One
 *
 * Ellison's navigation links
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Navigation
 * @author    Separation Degrees <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Navigation_Helper_Data class
 */
class SDM_Navigation_Helper_Data extends SDM_Core_Helper_Data
{

    const XML_PATH_NAV_LINK_CAT_ID = 'navigation/general/catalog_category_id';

    /**
     * Category URL key of the "Catalog" category
     *
     * @var str
     */
    protected $_categoryUrlPrefix = null;

    /**
     * Website-specific top navigation menu items are not available in the
     * Ellison database. They are defined manually here.
     *
     * @var array
     */
    protected $_topNavMenu = array(
        'szus' => array(
            0 => array('name' => 'Project Gallery', 'link' => 'not_available_yet'),
            1 => array('name' => 'Shop', 'link' => 'not_available_yet'),
            2 => array('name' => 'eclips', 'link' => 'not_available_yet'),
            3 => array('name' => 'Quilting', 'link' => 'not_available_yet'),
            4 => array('name' => 'Gift Cards', 'link' => 'not_available_yet'),
            5 => array('name' => 'Community', 'link' => 'not_available_yet'),
            6 => array('name' => 'Clearance', 'link' => 'not_available_yet'),
        ),
        'szuk' => array(
            0 => array('name' => 'Products', 'link' => 'not_available_yet'),
            1 => array('name' => 'Qiulting', 'link' => 'not_available_yet'),
            2 => array('name' => 'Projects', 'link' => 'not_available_yet'),
            3 => array('name' => 'Promotions', 'link' => 'not_available_yet'),
            4 => array('name' => 'Education', 'link' => 'not_available_yet'),
            5 => array('name' => 'Community', 'link' => 'not_available_yet'),
            6 => array('name' => 'Support', 'link' => 'not_available_yet'),
        ),
        'erus' => array(
            0 => array('name' => 'Craft Products', 'link' => 'not_available_yet'),
            1 => array('name' => 'Craft Projects', 'link' => 'not_available_yet'),
            2 => array('name' => 'Education Products', 'link' => 'not_available_yet'),
            3 => array('name' => 'Education Projects', 'link' => 'not_available_yet'),
            4 => array('name' => 'Community', 'link' => 'not_available_yet'),
            5 => array('name' => 'Support', 'link' => 'not_available_yet'),
        ),
        'eeus' => array(
            0 => array('name' => 'Lessons', 'link' => 'not_available_yet'),
            1 => array('name' => 'Products', 'link' => 'not_available_yet'),
            2 => array('name' => 'Electronic Cutting', 'link' => 'not_available_yet'),
            3 => array('name' => 'Gift Cards', 'link' => 'not_available_yet'),
            4 => array('name' => 'Specials', 'link' => 'not_available_yet'),
            5 => array('name' => 'Support', 'link' => 'not_available_yet'),
        ),
    );

    /**
     * Returns the category URL prefix
     *
     * @return str
     */
    public function getCatalogCategoryUrl()
    {
        if (!isset($this->_categoryUrlPrefix)) {
            $this->_categoryUrlPrefix = Mage::getStoreConfig(self::XML_PATH_NAV_LINK_CAT_ID);
        }

        return $this->_categoryUrlPrefix;
    }

    /**
     * Return the manually defined top navigation menu items. Migration script
     * uses this function.
     *
     * @return array
     */
    public function getTopNavMenu()
    {
        return $this->_topNavMenu;
    }
}
