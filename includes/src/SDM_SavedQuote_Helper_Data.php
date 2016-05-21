<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * General helper function for Saved Quotes
 */
class SDM_SavedQuote_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * `is_active` flag values of the main saved quote table
     */
    const INACTIVE_FLAG = 0;
    const ACTIVE_FLAG = 1;
    const PENDING_FLAG = 2; // these are for business logic only
    const PREORDER_PENDING_FLAG = 3;
    const PREORDER_APPROVED_FLAG = 4;
    const PREORDER_DENIED_FLAG = 5;
    const QUOTE_CANCELED_FLAG = 6;

    /**
     * System configuration paths
     */
    const XML_PATH_IS_ENALBED = 'savedquote/general/enabled';
    const XML_PATH_LOGGING_ENABLED = 'savedquote/general/logging';
    const XML_PATH_LOGGING_FILE_NAME = 'savedquote/general/log_filename';
    const XML_PATH_VALID_DAYS = 'savedquote/general/valid_days';
    const XML_PATH_SYSTEM_INCREMENT_ID = 'savedquote/system/increment_id';

    /**
     * Save instances of saved quotes to reference as needed
     */
    public $_savedQuote = array();

    /**
     * Set some persistent variables
     */
    public function __construct()
    {
        $this->_logFile = Mage::getStoreConfig(self::XML_PATH_LOGGING_FILE_NAME);
    }

    /**
     * Checks if we have a saved quote session active
     *
     * @return boolean
     */
    public function isSavedQuoteSession()
    {
        $savedQuoteId = $this->getSavedQuoteNumber();
        return empty($savedQuoteId) ? false : true;
    }

    /**
     * Returns the active saved quote increment id
     *
     * @return string
     */
    public function getSavedQuoteNumber()
    {
        return $this->getQuote()->getSavedQuoteId();
    }

    /**
     * Re-writes the current quote's data with the data from the saved quote,
     * to ensure nothing from the original quote's shipping or item information
     * has changed
     *
     * @return $this
     */
    public function resetQuoteDataFromSavedQuote()
    {
        $savedQuote = $this->getSavedQuote();
        if (!empty($savedQuote)) {
            Mage::helper('savedquote/converter')
                ->addSavedQuoteToSession($savedQuote, true);
        }
        return $this;
    }

    /**
     * Save an existing saved quote address
     *
     * @param SDM_SavedQuote_Model_Savedquote_Address $data
     *
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function updateSavedQuoteAddress($data)
    {
        $street = implode("\n", array_slice($data['street'], 0, 2));

        if (isset($data['address_id']) && !empty($data['address_id'])) {
            $address = Mage::getModel('savedquote/savedquote_address')
                ->load($data['address_id']);
        } else {
            $address = Mage::getModel('savedquote/savedquote_address')
                ->setSavedQuoteId($data['saved_quote_id']);
        }

        $address->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setCompany($data['company'])
            ->setStreet($street)
            ->setCity($data['city'])
            // Don't update these since user has already estimated shipping
            //->setRegionId($data['region_id'])
            //->setPostcode($data['postcode'])
            ->setTelephone($data['telephone'])
            ->setFax($data['fax']);

        // Don't update these since user has already estimated shipping
        //$address->setCountryId($data['country_id'])
        //    ->setRegion($data['region']);

        $address->validate();

        // Save address to address book?
        if (isset($data['save_in_address_book'])) {
            $savedAddress = Mage::getModel('customer/address');
            $savedAddress->setData($address->getData());
            $savedAddress->setParentId($this->getCustomer()->getId());
            $savedAddress->setEntityTypeId(2);
            // Set as default?
            $defaultAddress = $this->getCustomer()->getDefaultShippingAddress();
            if (empty($defaultAddress)) {
                $savedAddress->setIsDefaultShipping(true);
            }
            $savedAddress->save();
        }

        try {
            $address->save();
        } catch (Exception $e) {
            $this->log($e->getMessage());
            Mage::throwException('Address failed to save');
        }
    }

    /**
     * Checks if a quote can be converted into an order
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @see    SDM_SavedQuote_Block_Account_Savedquote_View::aboutThisQuote()
     * @return bool
     */
    public function canBePurchased($quote)
    {
        if ((int)$quote->getIsActive() !== SDM_SavedQuote_Helper_Data::ACTIVE_FLAG) {
            return false;
        }

        if (!$this->checkExpiration($quote)) {
            return false;
        }

        foreach ($quote->getItemCollection() as $item) {
            if (!$item->canBePurchased()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cancel a saved quote
     *
     * @param integer $id
     *
     * @return void
     */
    public function cancel($id)
    {
        $quote = Mage::getModel('savedquote/savedquote')
            ->load($id);
        if ($quote->getId()) {
            $quote->setIsActive(self::QUOTE_CANCELED_FLAG)
                ->save();
        }
    }

    /**
     * Checks if the expiration date has passed
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @return bool
     */
    public function checkExpiration($quote)
    {
        $now = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $expiration = $quote->getExpiresAt();
        // Mage::log("Comparing now to exp: $now vs. $expiration");

        if (strtotime($expiration) < strtotime($now)) {
            return false;
        }

        return true;
    }

    /**
     * Converts a Magento quote to a saved quote.
     *
     * Note that the returned object does not contain item and address objects.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return SDM_SavedQuote_Model_Savedquote
     * @throws Mage_Core_Exception
     */
    public function saveNewPendingSavedQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->clearAllPendingSavedQuotes();

        // Save the quote

        $savedQuote = Mage::helper('savedquote/converter')->saveQuote($quote);
        Mage::helper('savedquote/converter')->saveQuoteItems(
            $quote,
            $savedQuote->getId()
        );
        Mage::helper('savedquote/converter')->saveQuoteShippingAddress(
            $quote,
            $savedQuote->getId()
        );

        return $savedQuote;
    }

    /**
     * Returns the unsaved/pending saved quote. If there is more than one for
     * some reason, return the latest.
     *
     * @param int $id
     *
     * @return SDM_SavedQuote_Model_Savedquote
     */
    protected function _getExistingPendindSavedQuote($id)
    {
        $collection = $this->getSavedQuoteCollection(
            array(
                'customer_id' => $this->getCustomer()->getId(),
                'quote_id' => $id,
                'is_active' => self::PENDING_FLAG
            )
        );
        $collection->setOrder('entity_id', 'DESC');

        return $collection->getFirstItem();
    }

    /**
     * Return a collection filtered with given parameters
     *
     * @param array $filters
     *
     * @return SDM_SavedQuote_Model_Resource_Savedquote_Collection
     */
    public function getSavedQuoteCollection($filters = array())
    {
        $collection = Mage::getModel('savedquote/savedquote')->getCollection()
            ->setFilters($filters);

        return $collection;

    }

    /**
     * Remove all pending saved quotes for the given customer
     *
     * @return void
     */
    public function clearAllPendingSavedQuotes()
    {
        $customerId = $this->getCustomer()->getId();

        if (!$customerId) {
            $this->log(
                'A customer does not seem to be logged in',
                Zend_Log::WARN
            );
            return;
        }

        $collection = $this->getSavedQuoteCollection(
            array(
                'customer_id' => $customerId,
                'is_active' => self::PENDING_FLAG
            )
        );

        foreach ($collection as $quote) {
            $quote->delete();
        }
    }

    /**
     * Converts a pending saved quote to a permanent saved quote
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function savePendingToActive($quote)
    {
        if ($quote->getIsActive() == SDM_SavedQuote_Helper_Data::PENDING_FLAG) {
            $quote->setIsActive(SDM_SavedQuote_Helper_Data::ACTIVE_FLAG)
                // ->setName($quote->getName()) // why is this here?
                ->save();
        } else {
            $msg = "Saved quote ID {$quote->getID()}: only pending quotes can be changed to active";
            Mage::throwException($msg);
        }
    }

    /**
     * Converts a pending saved quote to a permanent saved quote
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @throws Mage_Core_Exception
     * @return void
     */
    public function saveActiveToInactive($quote)
    {
        if ($quote->getIsActive() == SDM_SavedQuote_Helper_Data::ACTIVE_FLAG) {
            $quote->setIsActive(SDM_SavedQuote_Helper_Data::INACTIVE_FLAG)
                ->save();
        } else {
            $msg = "Saved quote ID {$quote->getID()}: only active quotes can be change to inactive";
            Mage::throwException($msg);
        }
    }

    /**
     * Checks to see if customer has updated the total with shipping cost
     *
     * @return bool
     */
    public function isShippingAppliedToTotal()
    {
        $address = $this->getQuote()->getShippingAddress();
        $shippingCode = $address->getShippingMethod();
        $postcode = $address->getPostcode();

        return $shippingCode && $postcode;
    }

    /**
     * Returns an array of item names not allowed for quote
     *
     * @param  bool $returnSku
     * @return array
     */
    public function getItemsNotAllowedForQuote($returnSku = false)
    {
        $productIds = array();
        foreach ($this->getQuote()->getAllItems() as $item) {
            $productIds[] = $item->getProductId();
        }

        $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToFilter('entity_id', array('in' => $productIds))
                ->addAttributeToSelect('allow_quote')
                ->addAttributeToSelect('name');

        $notAllowed = array();
        foreach ($collection as $product) {
            if (!$product->getAllowQuote()) {
                $notAllowed[] = $returnSku ? $product->getSku() : $product->getName();
            }
        }

        return $notAllowed;
    }

    /**
     * Returns the appropirate state name given the activity flag code
     *
     * @param integer $code
     *
     * @return string
     */
    public function getStateName($code)
    {
        switch ((int) $code) {
            case self::INACTIVE_FLAG:
                return $this->__('Converted');
            case self::ACTIVE_FLAG:
                return $this->__('Active');
            case self::PREORDER_PENDING_FLAG:
                return $this->__('Pending');
            case self::PREORDER_APPROVED_FLAG:
                return $this->__('Approved');
            case self::PREORDER_DENIED_FLAG:
                return $this->__('Denied');
            case self::QUOTE_CANCELED_FLAG:
                return $this->__('Canceled');
        }
        return '';
    }

    /**
     * Return the session's quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Return the customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Checks if the customer is logged ion
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Checks if the system configuration is set to allow logging
     *
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_LOGGING_ENABLED);
    }

    /**
     * Returns the number of days a saved quote is good for
     *
     * @return bool
     */
    public function validDays()
    {
        return Mage::getStoreConfig(self::XML_PATH_VALID_DAYS);
    }

    /**
     * Returns the number of days a saved quote is good for
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = 0)
    {
        return Mage::getStoreConfig(self::XML_PATH_IS_ENALBED, $storeId);
    }

    /**
     * Returns whether the store has the feature enabled
     *
     * @return bool
     */
    public function allowSavedQuote()
    {
        return (bool)$this->isEnabled($this->getStoreId());
    }

    /**
     * Returns the next incremental ID for the quote. Always returns a new
     * number. Note that the original getStoreConfig() method returns cached
     * values.
     *
     * @return str
     */
    public function getNextIncrementId()
    {
        $model = new Mage_Core_Model_Config;
        $oldNumber = $this->getUncachedStoreConfig(self::XML_PATH_SYSTEM_INCREMENT_ID);
        $newNumber = (int)$oldNumber + rand(1, 5);

        $model->saveConfig(
            self::XML_PATH_SYSTEM_INCREMENT_ID,
            $newNumber,
            'default',
            0
        );

        return $newNumber;
    }

    /**
     * Returns a saved quote (checks if we've already loaded it before loading new one)
     *
     * @param string $number The quote number
     *
     * @return SDM_SavedQuote_Model_Savedquote
     */
    public function getSavedQuote($number = null)
    {
        if ($number === null) {
            $number = $this->getSavedQuoteNumber();
        }
        if (!isset($this->_savedQuote[$number])) {
            $savedQuote = Mage::getModel('savedquote/savedquote')
                ->load($number, 'increment_id');
            $this->_savedQuote[$number] = $savedQuote->getId() ? $savedQuote : null;
        }
        return $this->_savedQuote[$number];
    }

    /**
     * Set saved quote to retired
     *
     * @return SDM_SavedQuote_Helper_Data
     */
    public function retireSavedQuote()
    {
        $this->getSavedQuote()
            ->setIsActive(SDM_SavedQuote_Helper_Data::INACTIVE_FLAG)
            ->save();
        return $this;
    }

    /**
     * Remove every item from the current cart session by inactivating it
     *
     * @return null
     */
    public function clearCurrentQuote()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        // Prevents from creating an additional dummy quote
        if ($quote->getId()) {
            $quote->setIsActive(SDM_SavedQuote_Helper_Data::INACTIVE_FLAG)
                ->save();
        }
    }

    /**
     * Determine if the current quote has pre order items
     *
     * @param SDM_SavedQuote_Model_Savedquote|null $quote
     *
     * @return boolean
     */
    public function isQuotePreOrder($quote = null)
    {
        if (!$quote) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        if ($quote instanceof SDM_SavedQuote_Model_Savedquote) {
            // collection
            $items = $quote->getItemCollection();
        } else {
            // array
            $items = $quote->getAllVisibleItems();
        }
        foreach ($items as $item) {
            if ($item->getIsPreOrder()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the status options array
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return array(
            self::ACTIVE_FLAG => Mage::helper('savedquote')->getStateName(self::ACTIVE_FLAG),
            self::INACTIVE_FLAG => Mage::helper('savedquote')->getStateName(self::INACTIVE_FLAG),
            self::PREORDER_PENDING_FLAG => Mage::helper('savedquote')->getStateName(self::PREORDER_PENDING_FLAG),
            self::PREORDER_APPROVED_FLAG => Mage::helper('savedquote')->getStateName(self::PREORDER_APPROVED_FLAG),
            self::PREORDER_DENIED_FLAG => Mage::helper('savedquote')->getStateName(self::PREORDER_DENIED_FLAG),
            self::QUOTE_CANCELED_FLAG => Mage::helper('savedquote')->getStateName(self::QUOTE_CANCELED_FLAG)
        );
    }
}
