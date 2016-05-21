<?php

class Redstage_SaveForLater_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer {

	public function getCopyToCartUrl(){
		return $this->getUrl(
			'saveforlater/index/copy',
			array(
				'item' => $this->getSaveForLaterItem()->getId(),
				Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl()
			)
		);
	}

	public function getMoveToCartUrl(){
		return $this->getUrl(
			'saveforlater/index/move',
			array(
				'item' => $this->getSaveForLaterItem()->getId(),
				Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl()
			)
		);
	}

	public function getDeleteUrl(){
		return $this->getUrl(
			'saveforlater/index/delete',
			array(
				'item' => $this->getSaveForLaterItem()->getId(),
				Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl()
			)
		);
	}

	public function getOptionList(){
		if( ( $this->getItem()->getProduct()->getTypeId() == 'configurable' ) || ( $this->getItem()->getProduct()->getTypeId() == 'grouped' ) ){
			$helper = Mage::helper('catalog/product_configuration');
			$options = $helper->getConfigurableOptions($this->getItem());
			return $options;
		} else if( $this->getItem()->getProduct()->getTypeId() == 'bundle' ){
			$helper = Mage::helper('bundle/catalog_product_configuration');
			$options = $helper->getOptions($this->getItem());
			return $options;
		} else {
			return parent::getOptionList();
		}
	}

}
