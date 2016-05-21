<?php
require_once 'AW/Onestepcheckout/controllers/AjaxController.php';
class IWD_AddressVerification_AjaxController extends AW_Onestepcheckout_AjaxController
{
	var $_cur_layout = null;
	
    protected function _getUpdatedLayout()
    {
    	$this->_initLayoutMessages('checkout/session');
        if ($this->_cur_layout === null)
        {
            $layout = $this->getLayout();
            $update = $layout->getUpdate();            
            $update->load('checkout_onepage_index');
            
            $layout->generateXml();
            $layout->generateBlocks();
            $this->_cur_layout = $layout;
        }

        return $this->_cur_layout;
    }

	protected function _getAddressCandidatesHtml()
    {
    	$layout	= $this->_getUpdatedLayout();
        return $layout->getBlock('checkout.addresscandidates')->toHtml();
    }
	
    public function getVerification()
    {
        return Mage::getSingleton('addressverification/verification');
    }

    
	/**
	 * save checkout address
	 */
	public function saveAddressAction()
	{
		if ($this->_expireAjax())
			return;
		
		// set results mode (need for javascript logic)
		$this->getVerification()->getCheckout()->setValidationResultsMode(false);

		$result = array(
				'success'     => true,
				'messages'    => array(),
				'blocks'      => array(),
				'grand_total' => ""
		);

		if (!$this->getRequest()->isPost())
		{
			$result['success'] = false;
			$result['messages'][] = $this->__('Please specify billing address information.');

			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
			return;
		}				
			
		if (!Mage::helper('addressverification')->isAddressVerificationEnabled())
		{
			parent::saveAddressAction();
			return;
		}

		$validation_enabled	= Mage::helper('addressverification')->getEnabledVerification();
		if(!$validation_enabled)
		{
			parent::saveAddressAction();
			return;
		}
		
		$this->getVerification()->setVerificationLib($validation_enabled);
		
		$allow_not_valid	= Mage::helper('addressverification')->allowNotValidAddress(); // if not valid addresses allowed for checkout

		// check billing address 
		$data = $this->getRequest()->getPost('billing', array());
		$customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
	
		if (isset($data['email'])) {
			$data['email'] = trim($data['email']);
			$this->getOnepage()->getQuote()->setCustomerEmail($data['email']);
		}
		if (isset($data['firstname'])) {
			$this->getOnepage()->getQuote()->setCustomerFirstname($data['firstname']);
		}
		if (isset($data['lastname'])) {
			$this->getOnepage()->getQuote()->setCustomerLastname($data['lastname']);
		}
		
		$usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;
		
		
		$bill_data = $data;
		$bill_addr_id = $customerAddressId;
		
		$billing_address_changed	= false;
		if($this->_checkChangedAddress($bill_data, 'Billing', $bill_addr_id, $validation_enabled))
		{
			$billing_address_changed	= true;
			$this->getVerification()->getCheckout()->setBillingWasValidated(false);
			// for save method
			$this->getVerification()->getCheckout()->setSaveBillingWasValidated(false);
		}
		
		// check shipping address
		$shipping_result = array();
		
		$shipping_address_changed	= false;
		$ship_data = array();
		$ship_addr_id = false;
			
		if ($usingCase === 0)
		{
			$ship_data = $this->getRequest()->getPost('shipping', array());
			$ship_addr_id = $this->getRequest()->getPost('shipping_address_id', false);
		
			$shipping_address_changed	= false;
			if($this->_checkChangedAddress($ship_data, 'Shipping', $ship_addr_id, $validation_enabled))
			{
				$shipping_address_changed	= true;
					
				$this->getVerification()->getCheckout()->setShippingWasValidated(false);
				// for save mothod
				$this->getVerification()->getCheckout()->setSaveShippingWasValidated(false);
			}
		}
		else
		{
			$this->getVerification()->getCheckout()->setShippingWasValidated(true);
			$this->getVerification()->getCheckout()->setShippingValidationResults(true);
		}

		$bill_was_validated	= $this->getVerification()->getCheckout()->getBillingWasValidated();
		$ship_was_validated	= $this->getVerification()->getCheckout()->getShippingWasValidated();
		
		if(!$billing_address_changed && !$shipping_address_changed){
			if($bill_was_validated && $ship_was_validated){
				parent::saveAddressAction();
				return;
			}
		}
		
		if(!$allow_not_valid){
			$bill_was_validated	= false;
			$ship_was_validated	= false;
		}

		if($bill_was_validated && $ship_was_validated)
		{
			parent::saveAddressAction();
			return;
		}

		// save addresses before validation
		$billing_result = Mage::helper('aw_onestepcheckout/address')->saveBilling($bill_data, $bill_addr_id);

		if(isset($billing_result['error']))
		{
			$result['success'] = false;
			if (is_array($billing_result['message'])) {
				$result['messages'] = $billing_result['message'];
			} else {
				$result['messages'][] = $billing_result['message'];
			}

			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
			return;
		}
		
		if ($usingCase === 0)
		{
			$shipping_result = Mage::helper('aw_onestepcheckout/address')->saveShipping($ship_data, $ship_addr_id);
			if(isset($shipping_result['error']))
			{
				$result['success'] = false;
				if (is_array($shipping_result['message'])) {
					$result['messages'] = $shipping_result['message'];
				} else {
					$result['messages'][] = $shipping_result['message'];
				}
					
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
				return;
			}
		}
			
		// run validation
		if(!$bill_was_validated)
		{
			$bill_validate	= $this->getVerification()->validate_address('Billing');
			if($bill_validate)
				$this->getVerification()->getCheckout()->setBillingWasValidated(true);
			else
				$this->getVerification()->getCheckout()->setBillingWasValidated(false);
		}
		
		if(!$ship_was_validated)
		{
			if ($usingCase === 0){
				$ship_validate	= $this->getVerification()->validate_address('Shipping');
				if($ship_validate)
					$this->getVerification()->getCheckout()->setShippingWasValidated(true);
				else
					$this->getVerification()->getCheckout()->setShippingWasValidated(false);
			}
			else
			{
				$this->getVerification()->getCheckout()->setShippingWasValidated(true);
//				$this->getVerification()->getCheckout()->setSaveShippingWasValidated(true);
			}
		}

		// prepare results to display
		$results_type = false;
		
		// check if exist validation errors
		if(isset($bill_validate) && is_array($bill_validate)
		&& isset($bill_validate['error']) && !empty($bill_validate['error']))
		{
			$results_type = 'billing';
		}

		// check if exist validation errors
		if(isset($ship_validate) && is_array($ship_validate)
		&& isset($ship_validate['error']) && !empty($ship_validate['error']))
		{
			if($results_type == 'billing')
				$results_type = 'both';
			else
				$results_type = 'shipping';
		}

		if(!empty($results_type))
		{
			$this->getVerification()->getCheckout()->setShowValidationResults($results_type);
			
			$result['blocks']['address_validation'] = $this->_getAddressCandidatesHtml();

			$this->getVerification()->getCheckout()->setShowValidationResults(false);

			// clear validation results
			$this->getVerification()->getCheckout()->setBillingValidationResults(false);
			$this->getVerification()->getCheckout()->setShippingValidationResults(false);
			
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
			return;
		}
		
		// clear validation results
		$this->getVerification()->getCheckout()->setBillingValidationResults(false);
		$this->getVerification()->getCheckout()->setShippingValidationResults(false);

		parent::saveAddressAction();
		return;
	}
	
	public function placeOrderAction()
	{
		if ($this->_expireAjax())
			return;
	
		// set results mode (need for javascript logic)
		$this->getVerification()->getCheckout()->setValidationResultsMode(false);
		
		$result = array(
				'success'  => true,
				'messages' => array(),
		);
		 
		if (!$this->getRequest()->isPost())
		{
			$result['success'] = false;
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
			return;
		}
	
		if (!Mage::helper('addressverification')->isAddressVerificationEnabled())
		{
			parent::placeOrderAction();
			return;
		}
		 
		$validation_enabled	= Mage::helper('addressverification')->getEnabledVerification();
		if(!$validation_enabled)
		{
			parent::placeOrderAction();
			return;
		}
		 
		$allow_not_valid	= Mage::helper('addressverification')->allowNotValidAddress(); // if not valid addresses allowed for checkout
		
		if($allow_not_valid)
		{
			$bill_was_validated	= $this->getVerification()->getCheckout()->getSaveBillingWasValidated();
			$ship_was_validated	= $this->getVerification()->getCheckout()->getSaveShippingWasValidated();
		}
		else
		{
			$bill_was_validated	= false;
			$ship_was_validated	= false;
		}
			
		if($bill_was_validated && $ship_was_validated)
		{
			parent::placeOrderAction();
			return;
		}
		////
		
		// save address
		$bill_data = $this->getRequest()->getPost('billing', array());
		$bill_addr_id = $this->getRequest()->getPost('billing_address_id', false);
		
		$usingCase = isset($bill_data['use_for_shipping']) ? (int) $bill_data['use_for_shipping'] : 0;
		
		$ship_data = array();
		$ship_addr_id = false;
			
		if ($usingCase === 0)
		{
			$ship_data = $this->getRequest()->getPost('shipping', array());
			$ship_addr_id = $this->getRequest()->getPost('shipping_address_id', false);
		}
		
		$billing_result = Mage::helper('aw_onestepcheckout/address')->saveBilling($bill_data, $bill_addr_id);

		if ($usingCase === 0)
		{
			$shipping_result = Mage::helper('aw_onestepcheckout/address')->saveShipping($ship_data, $ship_addr_id);
		}

		// check errors
		if (isset($shipping_result)) {
			$saveResult = array_merge($billing_result, $shipping_result);
		} else {
			$saveResult = $billing_result;
		}
		
		// check billing address
		$billing_address_changed	= false;
		if($this->_checkChangedAddress($bill_data, 'Billing', $bill_addr_id, $validation_enabled))
		{
			$billing_address_changed	= true;
			
			$this->getVerification()->getCheckout()->setSaveBillingWasValidated(false);
		}
		
		// check shipping address
		$shipping_address_changed	= false;
			
		if ($usingCase === 0)
		{
			if($this->_checkChangedAddress($ship_data, 'Shipping', $ship_addr_id, $validation_enabled))
			{
				$shipping_address_changed	= true;
				
				$this->getVerification()->getCheckout()->setSaveShippingWasValidated(false);
			}
		}
		else
		{
			$this->getVerification()->getCheckout()->setSaveShippingWasValidated(true);
			$this->getVerification()->getCheckout()->setShippingValidationResults(true);
		}
		
		$bill_was_validated	= $this->getVerification()->getCheckout()->getBillingWasValidated();
		$ship_was_validated	= $this->getVerification()->getCheckout()->getShippingWasValidated();
		
		
		$this->getVerification()->setVerificationLib($validation_enabled);
	
		// start validation
		$this->getVerification()->getCheckout()->setSaveBillingWasValidated(false);
		 
		if(!$this->getVerification()->getQuote()->isVirtual())
		{			
			if ($usingCase === 0)
				$this->getVerification()->getCheckout()->setSaveShippingWasValidated(false);
			else
				$this->getVerification()->getCheckout()->setSaveShippingWasValidated(true);
		}
		else
			$this->getVerification()->getCheckout()->setSaveShippingWasValidated(true);
		 
		/// Address Verification
		if($allow_not_valid)
		{
			$bill_was_validated	= $this->getVerification()->getCheckout()->getSaveBillingWasValidated();
			$ship_was_validated	= $this->getVerification()->getCheckout()->getSaveShippingWasValidated();
		}
		else
		{
			$bill_was_validated	= false;
			$ship_was_validated	= false;
		}

		if(!$bill_was_validated)
		{
			$bill_validate	= $this->getVerification()->validate_address('Billing');
			if($bill_validate)
				$this->getVerification()->getCheckout()->setSaveBillingWasValidated(true);
			else
				$this->getVerification()->getCheckout()->setSaveBillingWasValidated(false);
		}
	
		if(!$this->getVerification()->getQuote()->isVirtual())
		{
			if(!$ship_was_validated)
			{
				// check if shipping is the same as billing
				if ($usingCase === 0)
				{
					$ship_validate	= $this->getVerification()->validate_address('Shipping');
					if($ship_validate)
						$this->getVerification()->getCheckout()->setSaveShippingWasValidated(true);
					else
						$this->getVerification()->getCheckout()->setSaveShippingWasValidated(false);
				}
				else
					$this->getVerification()->getCheckout()->setSaveShippingWasValidated(true);
			}
		}

		// check if exist validation results for any address
		if((isset($bill_validate) && is_array($bill_validate)) || (isset($ship_validate) && is_array($ship_validate)))
		{
			if((isset($bill_validate) && isset($bill_validate['error']) && !empty($bill_validate['error'])) ||
			   (isset($ship_validate) && isset($ship_validate['error']) && !empty($ship_validate['error']))
			)
			{
				$result['success'] = false;
				unset($result['messages']);
				
				$error_type = '';
				if(isset($bill_validate) && isset($bill_validate['error']) && !empty($bill_validate['error']))
					$error_type = 'billing';
				if(isset($ship_validate) && isset($ship_validate['error']) && !empty($ship_validate['error']))
				{
					if($error_type == 'billing')
						$error_type = 'both';
					else
						$error_type = 'shipping';
				}
				 
				$this->getVerification()->getCheckout()->setShowValidationResults($error_type);
				
				// set results mode (need for javascript logic)
				$this->getVerification()->getCheckout()->setValidationResultsMode('save_order');

				$result['blocks']['checkout-address-validation-load'] = $this->_getAddressCandidatesHtml();
				 
				$this->getVerification()->getCheckout()->setShowValidationResults(false);
				 
				// clear validation results
				$this->getVerification()->getCheckout()->setBillingValidationResults(false);
				$this->getVerification()->getCheckout()->setShippingValidationResults(false);

				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
				return;
			}
		}

		// clear validation results
		$this->getVerification()->getCheckout()->setBillingValidationResults(false);
		$this->getVerification()->getCheckout()->setShippingValidationResults(false);
		//// End Address Verification
		 
		parent::placeOrderAction();
	}
	
	
	protected function _checkChangedAddress($data, $addr_type = 'Billing', $addr_id = false, $check_city_street = false)
	{
		$method	= "get{$addr_type}Address";
		$address = $this->getVerification()->getQuote()->{$method}();
	
		if(!$addr_id)
		{
			if(($address->getRegionId()	!= $data['region_id']) || ($address->getPostcode() != $data['postcode']) || ($address->getCountryId() != $data['country_id']))
				return true;
	
			// if need to compare street and city
			if($check_city_street)
			{
				// check street address
				$street1	= $address->getStreet();
				$street2	= $data['street'];
	
				if(is_array($street1))
				{
					if(is_array($street2))
					{
						if(trim(strtolower($street1[0])) != trim(strtolower($street2[0])))
						{
							return true;
						}
						if(isset($street1[1]))
						{
							if(isset($street2[1]))
							{
								if(trim(strtolower($street1[1])) != trim(strtolower($street2[1])))
									return true;
							}
							else
							{
								if(!empty($street1[1]))
									return true;
							}
						}
						else
						{
							if(isset($street2[1])){
								$s21	= trim($street2[1]);
								if(!empty($s21))
									return true;
							}
						}
					}
					else
					{
						if(trim(strtolower($street1[0])) != trim(strtolower($street2)))
							return true;
					}
				}
				else
				{
					if(is_array($street2))
					{
						if(trim(strtolower($street1)) != trim(strtolower($street2[0])))
							return true;
					}
					else
					{
						if(trim(strtolower($street1)) != trim(strtolower($street2)))
							return true;
					}
				}
	
				// check city
				$add_city	= $address->getCity();
				$add_city	= trim(strtolower($add_city));
				if( $add_city	!= trim(strtolower($data['city'])))
					return true;
			}
			 
			return false;
		}
		else{
			if($addr_id != $address->getCustomerAddressId())
				return true;
			else
				return false;
		}
	}
	
}