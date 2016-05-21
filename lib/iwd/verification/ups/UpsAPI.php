<?php
abstract class UpsAPI {
	/**
	 * Status code for a failed request
	 * 
	 * @var integer
	 */
	const RESPONSE_STATUS_CODE_FAIL = 0;
	
	/**
	 * Status code for a successful request
	 * 
	 * @var integer
	 */
	const RESPONSE_STATUS_CODE_PASS = 1;
	
	/**
	 * Access key provided by UPS
	 * 
	 * @access protected
	 * @var string
	 */
	protected $access_key;
	
	/**
	 * Developer key provided by UPS
	 * 
	 * @access protected
	 * @var string
	 */
	protected $developer_key;
	
	/**
	 * Password used to access UPS Systems
	 * 
	 * @access protected
	 * @var string
	 */
	protected $password;
	
	/**
	 * Response from the server as XML
	 * 
	 * @access protected
	 * @var DOMDocument
	 */
	protected $response;
	
	/**
	 * Response from the server as an array
	 * 
	 * @access protected
	 * @var array
	 */
	protected $response_array;
	
	/**
	 * Root Node for the repsonse XML
	 * 
	 * @access protected
	 * @var DOMNode
	 */
	protected $root_node;
	
	/**
	 * UPS Server to send Request to
	 * 
	 * @access protected
	 * @var string
	 */
	protected $server;
	
	/**
	 * Username used to access UPS Systems
	 * 
	 * @access protected
	 * @var string
	 */
	protected $username;
	
	/**
	 * xpath object for the response XML
	 * 
	 * @access protected
	 * @var DOMXPath
	 */
	protected $xpath;
	
	protected $results_errors;
	
	/**
	 * Sets up the API Object
	 * 
	 * @access public
	 */
	public function __construct() {
		/** Set the Keys on the Object **/
		$this->access_key = $GLOBALS['ups_api']['access_key'];
		$this->developer_key = $GLOBALS['ups_api']['developer_key'];
		
		
		/** Set the username and password on the Object **/
		$this->password = $GLOBALS['ups_api']['password'];
		$this->username = $GLOBALS['ups_api']['username'];
	} // end funciton __construct()
	
	/**
	 * Builds the XML used to make the request
	 * 
	 * If $customer_context is an array it should be in the format:
	 * $customer_context = array('Element' => 'Value');
	 * 
	 * @access public
	 * @param array|string $cutomer_context customer data
	 * @return string $return_value request XML
	 */
	public function buildRequest($customer_context = null) {
		// create the access request element
		$access_dom = new DOMDocument('1.0');
		$access_element = $access_dom->appendChild(
			new DOMElement('AccessRequest'));
		$access_element->setAttributeNode(new DOMAttr('xml:lang', 'en-US'));
		
		// create the child elements
		$access_element->appendChild(
			new DOMElement('AccessLicenseNumber', $this->access_key));
		$access_element->appendChild(
			new DOMElement('UserId', $this->username));
		$access_element->appendChild(
			new DOMElement('Password', $this->password));
		
		return $access_dom->saveXML();
	} // end function buildRequest()
	
	/**
	 * Returns the error message(s) from the response
	 * 
	 * @return array
	 */
	public function getError() {
		// iterate over the error messages
		$errors = $this->xpath->query('Response/Error', $this->root_node);
		$return_value = array();
		foreach ($errors as $error) {
			$return_value[] = array(
				'severity' => $this->xpath->query('ErrorSeverity', $error)
					->item(0)->nodeValue,
				'code' => $this->xpath->query('ErrorCode', $error)
					->item(0)->nodeValue,
				'description' => $this->xpath->query('ErrorDescription', $error)
					->item(0)->nodeValue,
				'location' => $this->xpath
					->query('ErrorLocation/ErrorLocationElementName', $error)
					->item(0)->nodeValue,
			); // end $return_value
		} // end for each error message
		
		return $return_value;
	} // end function getError()
	
	/**
	 * Checks to see if a repsonse is an error
	 * 
	 * @access public
	 * @return boolean 
	 */
	public function isError() {
		// check to see if the request failed
		$status = $this->xpath->query('Response/ResponseStatusCode',
			$this->root_node);
		if ($status->item(0)->nodeValue == self::RESPONSE_STATUS_CODE_FAIL) {
			return true;
		} // end if the request failed
		
		return false;
	} // end function isError
	
	/**
	 * Send a request to the UPS Server using xmlrpc
	 * 
	 * @access public
	 * @params string $request_xml XML request from the child objects
	 * buildRequest() method
	 * @params boool $return_raw_xml whether or not to return the raw XML from
	 * the request
	 * 
	 * @todo remove array creation after switching over to xpath
	 */
	public function sendRequest($request_xml) {
		
		$this->response_array = array();
		$this->results_errors = array();
		
		$response = false;
		
		$f_open = ini_get('allow_url_fopen');
		if(!empty($f_open) && $f_open != 'Off')
		{
			// create the context stream and make the request
			$context = stream_context_create(array(
					'http' => array(
							'method' => 'POST',
							'header' => 'Content-Type: text/xml',
							'content' => $request_xml,
					),
			));
			$response = file_get_contents($this->server, false, $context);
		}
		else // use cURL
		{
			// init cURL
			$ch = curl_init();
		
			$CURL_OPTS = array(
					CURLOPT_CONNECTTIMEOUT => 60,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT        => 60,
					CURLOPT_FRESH_CONNECT  => 1,
					CURLOPT_USERAGENT      => 'ups-php',
					CURLOPT_FOLLOWLOCATION => true,
			);
			
			$opts = $CURL_OPTS;
			$opts[CURLOPT_POSTFIELDS] = $request_xml;
			$opts[CURLOPT_URL] = $this->server;
			$opts[CURLOPT_SSL_VERIFYPEER] 	= 0;
			$opts[CURLOPT_SSL_VERIFYHOST]	= 0;
				
			// set options
			curl_setopt_array($ch, $opts);
		
			// execute
			$curl_response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($httpCode == 200)
			{
				$hlength  = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
				$response = substr($curl_response, $hlength);
			}
				
			// close
			curl_close($ch);
		}
		
		if(empty($response))
			return $this->response_array;

		// parse results
		$this->response_array = XML2Array::createArray($response);
		
		if(isset($this->response_array['AddressValidationResponse']))
			$this->response_array = $this->response_array['AddressValidationResponse'];
		else
			$this->response_array = array();
		
		// check if errors
		$this->checkResultsErrors();

		return $this->response_array;
	}
	
	protected function checkResultsErrors(){
		$this->results_errors = array();
		
		$errors = array();
		$response = $this->response_array;
		if(isset($response['Response'])){
			$response = $response['Response'];
			if(!isset($response['AddressKeyFormat'])){
				if(isset($response['Error'])){
					if(isset($response['Error']['ErrorDescription']))
						$errors[] = $response['Error']['ErrorDescription'];
				}
			}
		}
		
		$this->results_errors = $errors;
	}
	
	public function getResultsErrors(){
		return $this->results_errors;
	}
	
	/**
	 * Builds the Request element
	 * 
	 * @access protected
	 * @param DOMElement $dom_element
	 * @param string $action
	 * @param string $option
	 * @param string|array $customer_context
	 * @return DOMElement
	 */
	protected function buildRequest_RequestElement(&$dom_element, $action,
		$option = null, $customer_context = null) {
		// create the child element
		$request = $dom_element->appendChild(
			new DOMElement('Request'));
		
		// create the children of the Request element
		$transaction_element = $request->appendChild(
			new DOMElement('TransactionReference'));
		$request->appendChild(
			new DOMElement('RequestAction', $action));
		
		// check to see if an option was passed in
		if (!empty($option)) {
			$request->appendChild(
				new DOMElement('RequestOption', $option));
		} // end if an option was passed in
		
		// create the children of the TransactionReference element
		$transaction_element->appendChild(
			new DOMElement('XpciVersion', '1.0'));
		
		// check if we have customer data to include
		if (!empty($customer_context)) {
			// check to see if the customer context is an array
			if (is_array($customer_context)) {
				$customer_element = $transaction_element->appendChild(
					new DOMElement('CustomerContext'));

				// iterate over the array of customer data
				foreach ($customer_context as $element => $value) {
					$customer_element->appendChild(
						new DOMElement($element, $value));
				} // end for each customer data
			} // end if the customer data is an array
			else {
				$transaction_element->appendChild(
					new DOMElement('CustomerContext', $customer_context));
			} // end if the customer data is a string
		} // end if we have customer data to include
		
		return $request;
	} // end function buildRequest_RequestElement()
	
	/**
	 * Returns the name of the servies response root node
	 * 
	 * @access protected
	 * @return string
	 * 
	 * @todo remove after phps self scope has been fixed
	 */
	protected abstract function getRootNodeName();
} // end class UpsAPI
