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
 * SDM_Catalog_Model_Resource_Product class
 */
class SDM_Catalog_Model_Resource_Product extends Mage_Catalog_Model_Resource_Product
{
    /**
     * Returns the compatible machines and accessory products
     *
     * @param SDM_Catalog_Model_Product $product
     * @param boolean                   $getCollection
     *
     * @return array
     */
    public function getCompatibleProducts($product, $getCollection = true)
    {
        if (strtolower($this->getAttributeText($product, 'product_type')) != 'die') {
            return;
        }

        $productLineId = $product->getCompatibilityProductLine();
        if (!$productLineId) {
            return;
        }

        $adapter = $this->_getReadAdapter();
        $websiteId = Mage::app()->getWebsite()->getId();

        // Get the product line ID
        $select = $adapter->select()
            ->from($this->getTable('compatibility/productline'), 'productline_id')
            ->where('productline_id = ?', $productLineId)
            ->limit(1);
        $result = $adapter->fetchCol($select);
        $id = reset($result);

        // Using the ID, find the compatibilities
        $select = $adapter->select()
            // Will need to add additional columns as more data is added (e.g. machine image)
            ->from(
                array('main_table' => $this->getTable('compatibility/compatibility')),
                array('main_table.*', 'p.name AS machine_name', 'p.image_link', 'p.image_page_link')
            )
            ->joinInner(
                array('p' => $this->getTable('compatibility/productline')),
                'main_table.machine_productline_id = p.productline_id',
                ''
            )
            ->where('main_table.die_productline_id = ?', $id)
            ->where('p.website_ids LIKE ?', "%$websiteId%")
            ->order('main_table.position ASC')
            ->order('main_table.id ASC'); // display position
        // Mage::log($select->__toString());

        $result = $adapter->fetchAll($select);

        // Add product collection in addition to delimited SKUs
        if ($getCollection) {
            $visibility = Mage::getModel('catalog/product_visibility');
            foreach ($result as $i => $one) {
                $result[$i]['collection'] = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect(
                        array('sku','small_image', 'name', 'price', 'visibility', 'button_display_logic')
                    )
                    ->applyRequiredAttributes()
                    ->addAttributeToFilter(
                        'sku',
                        array('IN' => explode(',', $one['associated_products']))
                    );
                $visibility->addVisibleInCatalogFilterToCollection($result[$i]['collection']);
            }
        }

        if (!empty($result)) {
            return $result;
        }
    }

    /**
     * Finds the attribute options text given an ID
     *
     * @param mixed $product
     * @param mixed $attributeCode
     *
     * @return mixed
     */
    public function getAttributeText($product, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);

        return $attribute->getSource()->getOptionText($product->getData($attributeCode));
    }
}
