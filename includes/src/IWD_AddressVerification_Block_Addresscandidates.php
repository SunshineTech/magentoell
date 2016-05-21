<?php
class IWD_AddressVerification_Block_Addresscandidates extends Mage_Core_Block_Template
{
    public function getBillingValidationResults()
    {
    	$checkout	= Mage::getSingleton('addressverification/verification');
    	return $checkout->getCheckout()->getBillingValidationResults();
    }

    public function getShippingValidationResults()
    {
    	$checkout	= Mage::getSingleton('addressverification/verification');
    	return $checkout->getCheckout()->getShippingValidationResults();
    }
    public function getShowValidationResults()
    {
    	$checkout	= Mage::getSingleton('addressverification/verification');
    	return $checkout->getCheckout()->getShowValidationResults();
    }
    public function setShowValidationResults($val)
    {
    	$checkout	= Mage::getSingleton('addressverification/verification');
    	return $checkout->getCheckout()->setShowValidationResults($val);
    }
    public function getValdationResultsMode()
    {
    	$checkout	= Mage::getSingleton('addressverification/verification');
    	return $checkout->getCheckout()->getValidationResultsMode('save_order');
    }
    public function showAddressType(){
    	return Mage::getStoreConfig('addressverification/ups_address_verification/show_address_type');
    }
}
