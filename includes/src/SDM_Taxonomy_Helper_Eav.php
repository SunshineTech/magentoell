<?php
/**
 * Separation Degrees One
 *
 * Helper to aid in manipulating EAVs
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Helper_Eav class
 */
class SDM_Taxonomy_Helper_Eav extends SDM_Core_Helper_Data
{
    /**
     * Directly updates EAV values. Limited to certain types of EAVs.
     *
     * This method does not accept dynamic values and requires explicit arguments
     * in order to avoid heavy resource usage in case it is used repeatedly.
     *
     * @param array $data
     *
     * @return void
     */
    public function updateSpecialEavTags($data)
    {
        $adapter = $this->getConn('core_write');
        $attribute = Mage::getModel('eav/entity_attribute')
            ->loadByCode('catalog_product', 'tag_special');
        $attributeId = $attribute->getId();
        $table = $attribute->getBackendTable();
        $storeId = 0;

        $select = $adapter->select()
            ->from($table, array('value_id', 'value'))
            ->limit(1);

        foreach ($data as $productId => $value) {
            // Get record
            $select->reset(Zend_Db_Select::WHERE);
            $select->where('entity_type_id = ?', 4) // Static
                ->where('attribute_id = ?', $attributeId)
                ->where('store_id = ?', $storeId)
                ->where('entity_id = ?', $productId);
            // Mage::log($select->__toString());

            $row = $adapter->fetchAll($select);
            $row = reset($row);

            // Record exists
            if (isset($row['value_id']) && is_numeric($row['value_id'])) {
                if ($row['value']) {
                    $values = explode(',', $row['value']);
                } else {
                    $values = array();
                }

                if (!in_array($value, $values)) {
                    $values[] = $value;
                }

                $values = implode(',', $values);
                if (empty($values)) {
                    $values = null;
                }

                $adapter->update(
                    $table,
                    array('value' => $values),
                    'value_id = ' . $row['value_id']
                );

                // Record does not exist
            } else {
                $adapter->insert(
                    $table,
                    array(
                        'entity_type_id' => 4,  // Static
                        'attribute_id' => $attributeId,
                        'store_id' => $storeId,
                        'entity_id' => $productId,
                        'value' => $value
                    )
                );
            }
        }
    }

    /**
     * Removes special tags from the EAV records
     *
     * @param int   $tagIdToRemove Taxonomy tag ID to remove from the EAV record
     * @param array $removedIds    Product IDs removed in the save
     *
     * @return void
     */
    public function removeSpecialEavTags($tagIdToRemove, $removedIds)
    {
        $adapter = $this->getConn('core_write');
        $attribute = Mage::getModel('eav/entity_attribute')
            ->loadByCode('catalog_product', 'tag_special');
        $attributeId = $attribute->getId();
        $table = $attribute->getBackendTable();
        $storeId = 0;
        $select = $adapter->select()
            ->from($table, array('value_id', 'value'))
            ->limit(1);

        // Retrieve each row and remove tag ID
        foreach ($removedIds as $removedId) {
            $select->reset(Zend_Db_Select::WHERE);
            $select->where('entity_type_id = ?', 4) // Static
                ->where('attribute_id = ?', $attributeId)
                ->where('store_id = ?', $storeId)
                ->where('entity_id = ?', $removedId);

            $row = $adapter->fetchAll($select);
            $row = reset($row);

            if (isset($row['value_id']) && is_numeric($row['value_id'])) {
                if ($row['value']) {
                    $values = explode(',', $row['value']);
                    $i = array_search($tagIdToRemove, $values); // False on no hit
                    if ($i !== false) {
                        unset($values[$i]);
                        $eavValue = implode(',', $values);

                        if (empty($eavValue)) {
                            $eavValue = null;
                        }
                        $adapter->update(
                            $table,
                            array('value' => $eavValue),
                            'value_id = ' . $row['value_id']
                        );
                    }
                }
            }
        }
    }
}
