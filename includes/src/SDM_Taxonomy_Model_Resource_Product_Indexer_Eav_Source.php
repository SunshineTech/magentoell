<?php
/**
 * Separation Degrees Media
 *
 * Rewrite to allow custom source models of varchar-multiselect to be indexed
 * and shown on the layered navigation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Model_Resource_Product_Indexer_Eav_Source class
 */
class SDM_Taxonomy_Model_Resource_Product_Indexer_Eav_Source
    extends Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
{
    /**
     * Prepare data index for indexable multiply select attributes.
     *
     * Injects custom source model data to be indexed.
     *
     * @param array $entityIds   The entity IDS limitation
     * @param int   $attributeId The attribute ID limitation
     *
     * @return SDM_Core_Model_Resource_Product_Indexer_Eav_Source
     */
    protected function _prepareMultiselectIndex($entityIds = null, $attributeId = null)
    {
        $adapter    = $this->_getWriteAdapter();
        $helper = Mage::helper('taxonomy');
        $helper->initForIndexing(); // Required to index taxonomy
        $taxAttIds = $helper->getTaxonomyAttributeIds();

        // prepare multiselect attributes
        if (is_null($attributeId)) {
            $attrIds    = $this->_getIndexableAttributes(true);
        } else {
            $attrIds    = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }

        // load attribute options
        $options = array();
        $select  = $adapter->select()
            ->from($this->getTable('eav/attribute_option'), array('attribute_id', 'option_id'))
            ->where('attribute_id IN(?)', $attrIds);
        $query = $select->query();
        while ($row = $query->fetch()) {
            $options[$row['attribute_id']][$row['option_id']] = true;
        }

        // Correct taxonomy's options
        $this->_addCustomSourceModelOptions($options);

        // prepare get multiselect values query
        $productValueExpression = $adapter->getCheckSql('pvs.value_id > 0', 'pvs.value', 'pvd.value');
        $select = $adapter->select()
            ->from(
                array('pvd' => $this->getValueTable('catalog/product', 'varchar')),
                array('entity_id', 'attribute_id')
            )
            ->join(
                array('cs' => $this->getTable('core/store')),
                '',
                array('store_id')
            )
            ->joinLeft(
                array('pvs' => $this->getValueTable('catalog/product', 'varchar')),
                'pvs.entity_id = pvd.entity_id AND pvs.attribute_id = pvd.attribute_id'
                    . ' AND pvs.store_id=cs.store_id',
                array('value' => $productValueExpression)
            )
            ->where('pvd.store_id=?',
                $adapter->getIfNullSql('pvs.store_id', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            )
            ->where('cs.store_id!=?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->where('pvd.attribute_id IN(?)', $attrIds);

        $statusCond = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'pvd.entity_id', 'cs.store_id', $statusCond);

        if (!is_null($entityIds)) {
            $select->where('pvd.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('pvd.entity_id'),
            'website_field' => new Zend_Db_Expr('cs.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $i     = 0;
        $data  = array();
        $query = $select->query();
        while ($row = $query->fetch()) {
            $values = explode(',', $row['value']);
            $values = array_unique($values);    // Unfortunately, Ellison has duplicate tag assigsments,
                                                // which cause duplicate entries and constraint errors,
                                                // in the source (mongoDB) database.

            foreach ($values as $valueId) {
                // All options inluded at this point (i.e. taxonomy not validated)
                if (isset($options[$row['attribute_id']][$valueId])) {
                    $isTaxonomyAttribute = isset($taxAttIds[$row['attribute_id']]);
                    $isWithinDate = $helper->validateTaxonomyItem($valueId, $row);

                    // Important: Only for taxonomy attributes
                    // Check if date is within range and store/website is enabled
                    if (!$isTaxonomyAttribute || $isWithinDate) {
                        $data[] = array(
                            $row['entity_id'],
                            $row['attribute_id'],
                            $row['store_id'],
                            $valueId
                        );

                        $i ++;
                        if ($i % 10000 == 0) {
                            $this->_saveIndexData($data);
                            $data = array();
                        }
                    }
                }
            }
        }

        $this->_saveIndexData($data);
        unset($options);
        unset($data);

        return $this;
    }

    /**
     * Add the custom source model options to the options array.
     *
     * @param array $options
     *
     * @return void
     */
    protected function _addCustomSourceModelOptions(&$options)
    {
        // Get all custom source models
        $sourceModels = Mage::helper('taxonomy')->getTypes('index');
        if (empty($sourceModels)) {
            return;
        }

        // Retrieve all options regardless of the products' status
        foreach ($sourceModels as $code => $name) {
            $tags = Mage::helper('taxonomy')->getDataToIndex($code);

            // These native Magento attribute options for the taxonomy must be
            // removed and replaced with correct data as they were not saved
            // correctly using the custom source models.
            if (isset($options[key($tags)])) {
                unset($options[key($tags)]);
            }

            $options += $tags;
        }
    }
}
