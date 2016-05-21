<?php

require_once 'abstract.php';

class SDM_Shell_SavedQuotes extends SDM_Shell_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->_init();
    }

    public function __destruct()
    {
        $this->_end();
    }

    public function run()
    {
        // Clear all quotes
        $this->out('Removing all saved quotes...');
        $this->_deleteAllSavedQuote();

        // Save test saved quote
        $this->out('Creating new sample quotes...');
        $emails = array(
            'younwkim@gmail.com',
            'youn0813@hotmail.com'
        );

        $this->_addQuotes($emails);
    }

    protected function _addQuotes($emails)
    {
        foreach ($emails as $i => $email) {
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(1)
                ->loadByEmail($email);

            $quote = Mage::getModel('savedquote/savedquote');
            $quote->setName("Test Saved Quote #$i")
                ->setCustomerId($customer->getId())
                ->setStoreId($customer->getStoreId())
                ->setCustomerGroupId($customer->getGroupId())
                ->setCustomerEmail($customer->getEmail())
                ->setCustomerFirstname($customer->getFirstname())
                ->setCustomerLastname($customer->getLastname())
                ->setIncrementId(rand(10000000,19999999))
                ->setTaxAmount(0.85)
                ->setGrandTotal(10.00)
                ->save();
            $id = $quote->getId();

            $this->_addItems($id);
            $this->_addAddresses($id);
        }

    }

    protected function _addItems($quoteId)
    {
        $skus = array(
            'sku-1' => 1,
            'sku-2' => 2
        );

        foreach ($skus as $sku => $qty) {
            $quoteItem = Mage::getModel('savedquote/savedquote_item');
            $quoteItem->setSavedQuoteId($quoteId)
                ->setSku($sku)
                ->setQty($qty)
                // Add more data..
                ->save();
        }

    }

    protected function _addAddresses($quoteId)
    {
        $address = Mage::getModel('savedquote/savedquote_address');
        $address->setSavedQuoteId($quoteId)
            ->setAddressType('billing')
            ->setStreet('3420 Bristol St.')
            ->setFirstname('Youn')
            ->setLastname('Kim')
            ->setCity('Costa Mesa')
            ->setPostcode('92000')
            ->save();

        $address = Mage::getModel('savedquote/savedquote_address');
        $address->setSavedQuoteId($quoteId)
            ->setAddressType('shipping')
            ->setStreet('3420 Bristol St.')
            ->setFirstname('Youn')
            ->setLastname('Kim')
            ->setCity('Costa Mesa')
            ->setPostcode('92000')
            ->save();

    }

    protected function _deleteAllSavedQuote()
    {
        $collection = Mage::getModel('savedquote/savedquote')->getCollection();
        // $collection = Mage::getResourceModel('savedquote/quote_collection');
        $collection->addFieldToSelect(array('entity_id', 'name'));

        foreach ($collection as $one) {
            $this->out($one->getId() . ': ' . $one->getName() . ' --> Deleted');
            $one->delete();
        }
    }
}

$shell = new SDM_Shell_SavedQuotes();
$shell->run();