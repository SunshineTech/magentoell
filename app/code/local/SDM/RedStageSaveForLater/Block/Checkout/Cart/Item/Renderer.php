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
 * SDM_RedStageSaveForLater_Block_Checkout_Cart_Item_Renderer class
 */
class SDM_RedStageSaveForLater_Block_Checkout_Cart_Item_Renderer
    extends Redstage_SaveForLater_Block_Checkout_Cart_Item_Renderer
{
    /**
     * Copy url
     *
     * @return string
     */
    public function getCopyToCartUrl()
    {
        return $this->getUrl(
            'saveforlater/index/copy',
            array(
                'item' => $this->getSaveForLaterItem()->getId(),
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl(),
                'form_key' => Mage::getSingleton('core/session')->getFormKey()
            )
        );
    }

    /**
     * Move url
     *
     * @return string
     */
    public function getMoveToCartUrl()
    {
        return $this->getUrl(
            'saveforlater/index/move',
            array(
                'item' => $this->getSaveForLaterItem()->getId(),
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl(),
                'form_key' => Mage::getSingleton('core/session')->getFormKey()
            )
        );
    }

    /**
     * Delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            'saveforlater/index/delete',
            array(
                'item' => $this->getSaveForLaterItem()->getId(),
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl(),
                'form_key' => Mage::getSingleton('core/session')->getFormKey()
            )
        );
    }
}
