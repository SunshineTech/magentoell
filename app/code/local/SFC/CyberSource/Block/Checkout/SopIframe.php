<?php
/**
 * StoreFront CyberSource Tokenized Payment Extension for Magento
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of StoreFront Consulting, Inc.
 *
 * @category  SFC
 * @package   SFC_CyberSource
 * @author    Garth Brantley <garth@storefrontconsulting.com>
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 * @license   http://www.storefrontconsulting.com/media/downloads/ExtensionLicense.pdf StoreFront Consulting Commercial License
 * @link      http://www.storefrontconsulting.com/cybersource-saved-credit-cards-extension-for-magento/
 *
 */

class SFC_CyberSource_Block_Checkout_SopIframe extends Mage_Checkout_Block_Onepage_Abstract
{

    private $jsonContent = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sfc_cybersource/checkout/sop_iframe.phtml');
        // Set default content as empty array
        $this->jsonContent = json_encode(array());
    }

    public function setJson($jsonContent)
    {
        $this->jsonContent = $jsonContent;
    }

    public function getJson()
    {
        return $this->jsonContent;
    }

}
