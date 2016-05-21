<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_MWISHLIST
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

class Itoris_MWishlist_Controller_Admin_Controller extends Mage_Adminhtml_Controller_Action {

	public function preDispatch() {
		$result = parent::preDispatch();
		$this->getDataHelper()->tryRegister();

		if (!Itoris_Installer_Client::isAdminRegistered($this->getDataHelper()->getAlias())) {
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
			Mage::getSingleton('adminhtml/session')->addError('Component not registered!');
			$this->loadLayout();
			$register = $this->getLayout()->createBlock('core/template');
			$this->getLayout()->getBlock('content')->unsetChildren();
			$this->getLayout()->getBlock('left')->unsetChildren();
			$register->setTemplate('itoris/mwishlist/index/index/register.phtml');
			$this->getLayout()->getBlock('content')->append($register);
			$this->renderLayout();
		}
		return $result;
	}

	/**
	 * @return Itoris_Mwishlist_Helper_Data
	 */
	public function getDataHelper() {
		return Mage::helper('itoris_mwishlist');
	}
}
?>