<?php
/**
 * Separation Degrees Media
 *
 * Install required attributes
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Migration
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2014 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Migration_Helper_Data class
 */
class SDM_Migration_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * Name of the category to which all products are assigned.
     */
    const CATALOG_CATEGORY_NAME = 'Catalog';

    /**
     * Website root category codes and names
     */
    const WEBSITE_ROOT_CATEGORY_NAME_US = 'Sizzix US';
    const WEBSITE_ROOT_CATEGORY_NAME_UK = 'Sizzix UK';
    const WEBSITE_ROOT_CATEGORY_NAME_RE = 'Ellison Retailer';
    const WEBSITE_ROOT_CATEGORY_NAME_ED = 'Ellison Education';

    /**
     * Converts the Ellison URL parameters (Ruby on Rail) to Magento's search
     * URL.
     *
     * Note this is not yet completed as of v0.6.0.
     *
     * @param str $urlKey
     *
     * @return str
     */
    public function ellisonUrlParamsToMagento($urlKey)
    {
        return $urlKey;
    }

    /**
     * Transform the Magento attribute code into CamelCase with first letter
     * capitalized.
     *
     * @param  str $att
     * @return str
     */
    public function transformAttributeName($att)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $att)));
    }

    /**
     * Returns the specified category
     *
     * @param str $name
     * @param int $level
     * @param itn $parentId
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory($name, $level, $parentId)
    {
        $category = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('name', $name)
            ->addAttributeToFilter('parent_id', $parentId)
            ->addAttributeToFilter('level', $level)
            ->getFirstItem();

        return $category;
    }

    /**
     * Creates a category, if it doesn't exist. Update it otherwise.
     *
     * @param str $name
     * @param int $level
     * @param int $parentId
     * @param str $parentPath
     *
     * @return Mage_Catalog_Model_Category
     */
    public function updateCategory($name, $level, $parentId, $parentPath = null)
    {
        $category = $this->getCategory($name, $level, $parentId);

        if ($category->getId()) {
            $category->setName($name)->save();

        } else {
            $category = Mage::getModel('catalog/category')
                ->setName($name)
                ->setDisplayMode('PRODUCTS')
                ->setIsActive(1)
                ->setIsAnchor(1)
                ->setPath($parentPath)
                ->save();
        }

        return $category;
    }

    /**
     * Returns the catalog category collection
     *
     * @param str|array $toSelect
     * @param mixed     $parentPath
     *
     * @return Mage_Catalog_Model_Category_Resource_Collection
     */
    public function getCatetoryCollection($toSelect = '*', $parentPath = null)
    {
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect($toSelect)
            ->addAttributeToFilter('name', self::CATALOG_CATEGORY_NAME);

        if (isset($parentPath)) {
            $collection->addAttributeToFilter('path', $parentPath);
        }

        return $collection;
    }

    /**
     * Rturns the attribute set model
     *
     * @param int|str $id
     *
     * @return Mage_Eav_Model_Entity_Attribute_Set
     */
    public function getAttributeSet($id)
    {
        if (!is_numeric($id) && is_string($id)) { // Assume name is passed in
            $set = Mage::getModel('eav/entity_attribute_set')
                ->getCollection()
                ->addFieldToFilter('attribute_set_name', $id)
                ->getFirstItem();

            return $set;
        }
        if (!is_numeric($id)) { // Assume name is passed in
            Mage::throwException('Provided attribute set ID is not an integer');
        }

        return Mage::getModel('eav/entity_attribute_set')->load($id);
        ;
    }

    /**
     * Cleans the given string. There are some encoding issues that can't seem
     * to be solved with one method.
     *
     * @param str $str
     *
     * @return str
     */
    public function cleanStr($str)
    {
        return $this->removeExtendedAscii($str);
    }

    /**
     * Sizzix imploded arrays have weird delimiting. Clean up the pipes and replace
     * them with commas.
     *
     * @param str $str
     * @param str $delimiter
     * @param str $delimiter2
     *
     * @return str Comma-delimited
     */
    public function cleanDelimitedString($str, $delimiter = '|', $delimiter2 = ',')
    {
        return str_replace($delimiter, $delimiter2, trim($str, $delimiter));
    }

    /**
     * Returns the website codes
     *
     * @return array
     */
    public function getWebsiteCodes()
    {
        return array(
            SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_US => self::WEBSITE_ROOT_CATEGORY_NAME_US,
            SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK => self::WEBSITE_ROOT_CATEGORY_NAME_UK,
            SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE => self::WEBSITE_ROOT_CATEGORY_NAME_RE,
            SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED => self::WEBSITE_ROOT_CATEGORY_NAME_ED,
        );
    }

    /**
     * Website mapping
     *
     * @return array
     */
    public function websiteMapping()
    {
        $mapping = array();

        foreach ($this->getEllisonSystemCodes() as $websiteCode => $systemCode) {
            $q = "SELECT website_id FROM core_website WHERE code = '$websiteCode'";
            $mapping[$systemCode] = $this->getConn()->fetchOne($q);
        }

        return $mapping;
    }

    /**
     * Store mapping
     *
     * @return array
     */
    public function storeMapping()
    {
        $mapping = array();

        foreach ($this->getEllisonSystemCodes() as $websiteCode => $systemCode) {
            $q = "SELECT store_id FROM core_store WHERE code LIKE '$websiteCode%' LIMIT 1";
            $mapping[$systemCode] = $this->getConn()->fetchOne($q);
        }

        return $mapping;
    }

    /**
     * Get db
     *
     * @param string $type
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getConn($type = 'core_read')
    {
        return Mage::getSingleton('core/resource')->getConnection($type);
    }
}
