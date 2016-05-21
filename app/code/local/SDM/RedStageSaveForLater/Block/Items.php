<?php
/**
 * Separation Degrees One
 *
 * Fixes for RedStage_SaveForLater
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RedstageSaveForLater
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_RedStageSaveForLater_Block_Items class
 */
class SDM_RedStageSaveForLater_Block_Items
    extends Redstage_SaveForLater_Block_Items
{
    /**
     * Only render if active
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!Mage::getStoreConfig('saveforlater/settings/active')) {
                return '';
        }
        return parent::_toHtml();
    }

    /**
     * Get saved items
     *
     * @return Redstage_SaveForLater_Model_Mysql4_Item_Collection
     */
    public function getItems()
    {
        $items = Mage::getResourceModel('saveforlater/item_collection');

        if (Mage::getSingleton('customer/session')->getCustomer()
            && Mage::getSingleton('checkout/session')->getQuote()->getId()
        ) {
            $items->getSelect()
                ->where("
					( quote_id = ". Mage::getSingleton('checkout/session')->getQuote()->getId() ." )
					". (
                        Mage::getSingleton('customer/session')->getCustomer()
                            ? 'OR ( customer_id = \''
                                . Mage::getSingleton('customer/session')->getCustomer()->getId() .'\')'
                            : ''
                    ) ."
				");
        } else {
            $items->getSelect()
                ->where("1 = 2");
        }

        // Join saved for later to product flat so we can get visibility column
        Mage::helper('sdm_catalog')
            ->joinToProductFlat($items, '`main_table`.`product_id`', 'visibility');

        // Add visibility filter
        $items->addFieldToFilter(
            'visibility',
            array('in', Mage::getModel('sdm_catalog/product_visibility')->getVisibleInSiteIds())
        );

        return $items;
    }

    /**
     * Render item
     *
     * @param Redstage_SaveForLater_Model_Item $saveforlater_item
     *
     * @return string
     */
    public function getItemHtml($saveforlater_item)
    {
        $product = Mage::getModel('catalog/product')
            ->load($saveforlater_item->getProductId());

        $_item = Mage::getModel('sales/quote_item')
            ->setQuote(Mage::getSingleton('checkout/cart')->getQuote())
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setProduct($product);

        $_item->addOption(new Varien_Object(array(
            'product' => $product,
            'product_id' => $product->getId(),
            'code' => 'info_buyRequest',
            'value' => $saveforlater_item->getBuyRequest()
        )));

        /* FOR CUSTOM OPTIONS */
        $buyRequest = unserialize($saveforlater_item->getBuyRequest());
        $buyRequest['options'] = isset($buyRequest['options']) ? $buyRequest['options'] : array();
        $_item->addOption(new Varien_Object(array(
            'product' => $product,
            'product_id' => $product->getId(),
            'code' => 'option_ids',
            'value' => implode(',', array_keys($buyRequest['options']))
        )));
        foreach ($buyRequest['options'] as $option_id => $option_value) {
            $_item->addOption(new Varien_Object(array(
                'product' => $product,
                'product_id' => $product->getId(),
                'code' => 'option_'. $option_id,
                'value' => $option_value
            )));
        }
        /* END FOR CUSTOM OPTIONS */

        /* FOR CONFIGURABLE PRODUCTS */
        $buyRequest['super_attribute'] = isset($buyRequest['super_attribute'])
            ? $buyRequest['super_attribute']
            : array();
        $_item->addOption(new Varien_Object(array(
            'product' => $product,
            'product_id' => $product->getId(),
            'code' => 'attributes',
            'value' => serialize($buyRequest['super_attribute'])
        )));
        /* END FOR CONFIGURABLE PRODUCTS */

        /* FOR BUNDLED PRODUCTS */
        $options = isset($buyRequest['bundle_option']) ? $buyRequest['bundle_option'] : array();
        $qtys = isset($buyRequest['bundle_option_qty']) ? $buyRequest['bundle_option_qty'] : array();
        if (is_array($options)) {
            /*$options = array_filter($options, 'intval');
			foreach ($options as $_optionId => $_selections) {
				if (empty($_selections)) {
					unset($options[$_optionId]);
				}
			}*/
            $optionIds = array_keys($options);
        } else {
            $optionIds = array();
        }
        $selectionIds = array();
        foreach ($options as $optionId => $selectionId) {
            if (!is_array($selectionId)) {
                if ($selectionId != '') {
                    $selectionIds[] = (int)$selectionId;
                }
            } else {
                foreach ($selectionId as $id) {
                    if ($id != '') {
                        $selectionIds[] = (int)$id;
                    }
                }
            }
        }
        $_item->addOption(new Varien_Object(array(
            'product' => $product,
            'product_id' => $product->getId(),
            'code' => 'bundle_option_ids',
            'value' => serialize(array_map('intval', $optionIds))
        )));
        $_item->addOption(new Varien_Object(array(
            'product' => $product,
            'product_id' => $product->getId(),
            'code' => 'bundle_selection_ids',
            'value' => serialize($selectionIds)
        )));

        /*$stream = fopen( Mage::getBaseDir() .'/tmp/tmp.txt', 'a+' );
		fwrite( $stream, "selectionIds: ". print_r( $selectionIds, 1 ) ."\n" );
		fwrite( $stream, "qtys: ". print_r( $qtys, 1 ) ."\n" );
		fclose( $stream );*/

        $i = 1;
        foreach ($selectionIds as $selection_id) {
            $_item->addOption(new Varien_Object(array(
                'product' => $product,
                'product_id' => $product->getId(),
                'code' => 'selection_qty_'. $selection_id,
                'value' => $qtys[ $i ]
            )));
            $i++;
        }
        /* END FOR BUNDLED PRODUCTS */

        $renderer = $this->getLayout()->createBlock('saveforlater/checkout_cart_item_renderer')
            ->setTemplate('redstage_saveforlater/cart/item/default.phtml')
            ->setSaveForLaterItem($saveforlater_item)
            ->setItem($_item);

        return $renderer->toHtml();
    }
}
