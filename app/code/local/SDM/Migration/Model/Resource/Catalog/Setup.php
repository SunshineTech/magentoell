<?php
/**
 * Separation Degrees Media
 *
 * Functions for the installation scripts
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Migration
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2014 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Migration_Model_Resource_Catalog_Setup class
 */
class SDM_Migration_Model_Resource_Catalog_Setup extends Mage_Catalog_Model_Resource_Setup
{
    /**
     * Create an attribute group. Group ID is not returned natively
     * from addAttributeGroup(). General group has sort order 1.
     *
     * @param str $groupName
     * @param int $setId     ID of attribute set to which the group appends
     * @param int $sortOrder
     *
     * @return int
     */
    public function createAttributeGroup($groupName, $setId, $sortOrder = 1)
    {
        $this->addAttributeGroup(
            $this->getCatalogEntityTypeId(),
            $setId,
            $groupName,
            $sortOrder
        );
    }

    /**
     * Create attribute an set.
     *
     * @param str $attSetName
     *
     * @return int
     */
    public function createAttributeSet($attSetName)
    {
        $attributeSetId = $this->getThisAttributeSetId($attSetName);

        if (isset($attributeSetId)) {
            return $attributeSetId;
        }

        $defaultSetId = $this->getThisAttributeSetId(
            'Default'
        );

        $attributeSet = Mage::getModel('eav/entity_attribute_set')
            ->setEntityTypeId($this->getCatalogEntityTypeId())
            ->setAttributeSetName($attSetName);

        if ($attributeSet->validate()) {
            $attributeSet->save();  // save the new attribute first
            $attributeSet->initFromSkeleton($defaultSetId)->save(); // fill in group atts

            return $attributeSet->getId();
        }
    }

    /**
     * Returns the attribute set ID. Rewrite of parent's method because it doesn't
     * work on non-existent attribute sets.
     *
     * @param str $name
     * @param str $type
     *
     * @return int
     */
    public function getThisAttributeSetId($name, $type = Mage_Catalog_Model_Product::ENTITY)
    {
        // if (is_numeric($name)) {
        //     return $name;   // it's actually already the set ID
        // }

        $attributeSet = $this->getAttributeSet(
            $this->getEntityTypeId($type),
            $name
        );

        if (isset($attributeSet['attribute_set_id'])) {
            return $attributeSet['attribute_set_id'];
        }
    }

    /**
     * Returns the 'catalog/product' numeric entity type ID
     *
     * @return int
     */
    public function getCatalogEntityTypeId()
    {
        $entity = $this->getEntityType(Mage_Catalog_Model_Product::ENTITY);
        return $entity['entity_type_id'];
    }

    /**
     * Returns a collection of "Catalog" categories
     *
     * @return Mage_Catalog_Model_Category_Resource_Collection
     */
    public static function getAllCatalogCategories()
    {
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('level', 2)
            ->addAttributeToFilter('name', 'Catalog');

        return $collection;
    }

    /**
     * Returns a collection of all categories except root and "Catalog"
     *
     * @return Mage_Catalog_Model_Category_Resource_Collection
     */
    public static function getAllNonBaseCategories()
    {
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter(
                'level',
                array('gt' => 1)
            )
            ->addAttributeToFilter(
                'name',
                array('neq' => 'Catalog')
            );
        // Mage::log($collection->getSelect()->__toString());

        return $collection;
    }

    // public function out($str)
    // {
    //     if (isset($str)) {
    //         echo '[NULL]<br />';
    //         return;
    //     }
    //     echo $str . '<br />';
    // }
}
