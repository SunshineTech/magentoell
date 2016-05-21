<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Model_Attribute_Source_Abstract class
 */
abstract class SDM_Taxonomy_Model_Attribute_Source_Abstract
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get code
     *
     * @return string
     */
    abstract public function getCode();

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
        $collection->getSelect()->order('name', 'asc');

        return Mage::helper('taxonomy')->convertCollectionToOptions($collection);
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Retrieve Column(s) for Flat
     *
     * @see Mage_Eav_Model_Entity_Attribute_Source_Table::getFlatColumns
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $isMulti = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';

        if (Mage::helper('core')->useDbCompatibleMode()) {
            $columns[$attributeCode] = array(
                'type'      => $isMulti ? 'varchar(255)' : 'int',
                'unsigned'  => false,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            );
            if (!$isMulti) {
                $columns[$attributeCode . '_value'] = array(
                    'type'      => 'varchar(255)',
                    'unsigned'  => false,
                    'is_null'   => true,
                    'default'   => null,
                    'extra'     => null
                );
            }
        } else {
            $type = ($isMulti) ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_INTEGER;
            $columns[$attributeCode] = array(
                'type'      => $type,
                'length'    => $isMulti ? '255' : null,
                'unsigned'  => false,
                'nullable'   => true,
                'default'   => null,
                'extra'     => null,
                'comment'   => $attributeCode . ' column'
            );
            if (!$isMulti) {
                $columns[$attributeCode . '_value'] = array(
                    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length'    => 255,
                    'unsigned'  => false,
                    'nullable'  => true,
                    'default'   => null,
                    'extra'     => null,
                    'comment'   => $attributeCode . ' column'
                );
            }
        }

        return $columns;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     *
     * @see Mage_Eav_Model_Entity_Attribute_Source_Table::getFlatUpdateSelect
     *
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute_option')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
