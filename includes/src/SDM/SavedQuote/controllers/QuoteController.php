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
 * Controller for savequote actions
 */
class SDM_SavedQuote_QuoteController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check if customer is logged in here
     *
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_getHelper()->isCustomerLoggedIn()) {
            Mage::getSingleton('customer/session')
                ->addNotice('You must be logged in as a registered customer');

            // $this->getRequest()->setDispatched(true);    // Doesn't seem to be needed
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);   // Halts action execution

            return $this->_redirect('customer/account/login');
        }
    }

    /**
     * Displays the pending saved quote data. Customer must be logged in and must
     * have shipping applied to the total.
     *
     * @return void
     */
    public function indexAction()
    {
        if (!$this->_getHelper()->allowSavedQuote()) {
            return $this->_redirect('checkout/cart');
        }

        $redirect = false;
        $quote = $this->_getQuote();

        // Validate minimum amount
        $minAmount = null;
        if (!$quote->meetsMinimumOrderQuantity($minAmount)) {
            Mage::getSingleton('checkout/session')
                ->addError($this->__(
                    'Unable to proceed because minimum order amount is $%s.', $minAmount
                ));
            $redirect = true;
        }

        // Validate that the items here are valid cart items (aka. can be quoted/preordered)
        if (!Mage::helper('sdm_checkout/quote')->validateForCart()) {
            $redirect = true;
        }

        // Logic for EEUS quoting
        if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_ED)) {
            if (!$this->_getHelper()->isShippingAppliedToTotal()) {
                Mage::getSingleton('checkout/session')
                    ->addNotice('Shipping estimate must be applied to the total to save a quote');
                $redirect = true;
            }

            $notAllowed = $this->_getHelper()->getItemsNotAllowedForQuote(true);
            if (!empty($notAllowed)) {
                Mage::getSingleton('checkout/session')
                    ->addNotice('One or more items cannot be quoted at this time: "'.implode('", "', $notAllowed).'"');
                $redirect = true;
            }
        }

        // Logic for ERUS pre ordering
        if (Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)) {
            $isPreorder = Mage::helper('savedquote')->isQuotePreOrder($quote);
            if (!$isPreorder) {
                Mage::getSingleton('checkout/session')
                    ->addNotice('At least one item in your cart must be a preorderable '.
                        'to place a preorder. Try proceeding to checking out, instead.');
                $redirect = true;
            }
        }

        if ($redirect) {
            return $this->_redirect('checkout/cart');
        }

        // Save the pending saved quote for customer to review
        if ($quote->getId()) {
            try {
                $savedQuote = $this->_getHelper()->saveNewPendingSavedQuote($quote);

                // If for some reason there are no items, clear all and make a new one
                if ($savedQuote->getItemCollection()->count() === 0) {
                    $this->_getHelper()->clearAllPendingSavedQuotes();
                    $savedQuote = $this->_getHelper()->saveNewPendingSavedQuote($quote);
                }

            } catch (Exception $e) {
                $this->_getHelper()->log($e->getMessage());
                Mage::getSingleton('checkout/session')->addError('Quote could not be saved');
                return $this->_redirectReferer();
            }

            // Get post data from failed validation
            $shippingPost = Mage::getSingleton('core/session')->getShippingPost();
            if ($shippingPost) {
                Mage::getSingleton('core/session')->unsShippingPost();
                Mage::register('shippingPost', $shippingPost);
            }

            Mage::register('saved_quote', $savedQuote);   // Save it for the view

            $this->loadLayout()->getLayout()->getBlock('head')->setTitle($this->__(
                Mage::helper('savedquote')->isQuotePreOrder() ? 'Pre-Order' : 'Saved Quote'
            ));
            $this->renderLayout();
        }
    }

    /**
     * Save the quote
     *
     * @return void
     */
    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('checkout/cart/');
        }
        if (!$this->_getHelper()->allowSavedQuote()) {
            return $this->_redirect('checkout/cart');
        }

        // Validate that the items here are valid cart items (aka. can be quoted/preordered)
        if (!Mage::helper('sdm_checkout/quote')->validateForCart()) {
            return $this->_redirect('checkout/cart');
        }

        // First, save shipping info
        if (!Mage::helper('savedquote')->isQuotePreOrder()) {
            $shippingPost = $this->getRequest()->getPost('shipping');
            try {
                $this->_getHelper()->updateSavedQuoteAddress($shippingPost);
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
                return $this->_redirect('savedquote/quote');
            }

            $result = Mage::helper('sdm_av')->verifyAddress(
                $shippingPost,
                SDM_AddressVerification_Helper_Data::USPS_CODE
            );

            if ($result['error']) {
                Mage::getSingleton('core/session')->addError(
                    'Address could not be validated as entered. '
                        . 'Please review your address and make necessary changes.'
                );
                // Make it available on the redirected page
                Mage::getSingleton('core/session')->setShippingPost($shippingPost);
                $this->_getHelper()->log($result['message']);
                $this->_getHelper()->log($result['candidates']);

                return $this->_redirectReferer();

            } elseif ($result['code'] === SDM_AddressVerification_Helper_Data::RESPONSE_CODE_VALIDATION_FAILED) {
                // Address verification not applied and allowed to proceed in below cases
                $this->_getHelper()->log($result['message']);
            } elseif ($result['code'] === SDM_AddressVerification_Helper_Data::RESPONSE_CODE_EXTENSION_DISABLED) {
                // Address verification not applied and allowed to proceed
                $this->_getHelper()->log($result['message']);
            }
        }

        // Next, save the rest of our info
        $post = $this->getRequest()->getPost();
        $quote = Mage::getModel('savedquote/savedquote')
            ->load($post['saved_quote_id'])
            ->setName($post['saved_quote_name']);

        // Validate addresses again
        if (!Mage::helper('savedquote')->isQuotePreOrder()) {
            try {
                $quote->getShippingAddress()->validate();
            } catch (Exception $e) {
                $this->_getHelper()->log($e->getMessage());
                Mage::getSingleton('core/session')->addError($e->getMessage());

                return $this->_redirect('savedquote/quote');
            }
        }

        // Convert into a permanent saved quote
        try {
            $this->_getHelper()->savePendingToActive($quote);
        } catch (Exception $e) {
            $this->_getHelper()->log("Unable to make pending quote into active. " . $e->getMessage());
            Mage::getSingleton('core/session')->addError('Saved quote failed to save');

            return $this->_redirect('savedquote/quote');
        }

        // Clear all pending saved quotes
        try {
            $this->_getHelper()->clearAllPendingSavedQuotes();
        } catch (Exception $e) {
            $this->_getHelper()->log(
                'Failed to remove pending saved quote ID ' . $quote->getId()
                    . ' Cleaning required.',
                Zend_Log::ERR
            );
            // Continue; not too critical
        }

        // Parse shipping dates for pre orders
        if (Mage::helper('savedquote')->isQuotePreOrder()) {
            $preOrderShippingDates = array();
            $formDates = $this->getRequest()->getPost('pre_order_date');
            $formQty = $this->getRequest()->getPost('pre_order_qty');
            foreach ($formDates as $id => $dates) {
                foreach ($dates as $key => $date) {
                    $qty = isset($formQty[$id][$key]) ? (int)$formQty[$id][$key] : 0;
                    if ($qty > 0 && isset($preOrderShippingDates[$id][$date])) {
                        $preOrderShippingDates[$id][$date] += $qty;
                    } elseif ($qty > 0) {
                        $preOrderShippingDates[$id][$date] = $qty;
                    }
                }
            }

            // Save shipping dates
            foreach ($preOrderShippingDates as $id => $shipData) {
                try {
                    $item = Mage::getModel('savedquote/savedquote_item')->load($id);
                    if (array_sum($shipData) > $item->getQty()) {
                        // Too many
                        throw new Exception(
                            'The quantity of items shipped for SKU "'.$item->getSku().'" is greater '.
                            'than the quantity ordered. Please correct and try again.'
                        );
                    } elseif (array_sum($shipData) < $item->getQty()) {
                        // Not enough
                        throw new Exception(
                            'Not every item for SKU "'.$item->getSku().'" has a ship month. '.
                            'Please correct and try again.'
                        );
                    } else {
                        // Just right
                        $item->setPreOrderShippingDates(
                                Mage::helper('core')->jsonEncode($shipData)
                            )
                            ->save();
                    }
                } catch (Exception $e) {
                    $this->_getHelper()->log($e->getMessage());
                    Mage::getSingleton('core/session')->addError($e->getMessage());
                    return $this->_redirect('savedquote/quote');
                }
            }
        }

        if (Mage::helper('savedquote')->isQuotePreOrder()) {
            Mage::helper('sdm_preorder')->splitSavedQuote($quote);
            Mage::getSingleton('core/session')
                ->addSuccess('Your pre-order has been created!');
        } else {
            Mage::getSingleton('core/session')
                ->addSuccess('Your quote #' . $quote->getIncrementId() . ' has been saved!');
            $this->_sendConfirmationEmail($quote);
        }
        Mage::helper('savedquote')->clearCurrentQuote();

        return $this->_redirect('savedquote/quote/list');
    }

    /**
     * Lists all of the saved quotes for the customer
     *
     * @return void
     */
    public function listAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__(
            Mage::app()->getWebsite()->getCode() == SDM_Core_Helper_Data::WEBSITE_CODE_ER
                ? 'Pre-Order List'
                : 'Saved Quote List'
        ));

        // Highlight active tab
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('savedquote/quote/list');
        }

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl()); // for the "back" link
        }

        $this->renderLayout();
    }

    /**
     * View of a single saved quote from the cutomer account page
     *
     * @return void
     */
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $savedQuote = Mage::getModel('savedquote/savedquote')->load($id);

        if (!$savedQuote || !$savedQuote->getId() || $this->_getCustomer()->getId() != $savedQuote->getCustomerId()) {
            Mage::getSingleton('core/session')->addNotice('Quote was not found');
            return $this->_redirectReferer();
        }

        Mage::register('saved_quote', $savedQuote);

        $this->loadLayout();

        $this->getLayout()
            ->getBlock('head')
            ->setTitle($this->__(
                (Mage::helper('savedquote')->isQuotePreOrder($savedQuote) ? 'Pre-Order' : 'Saved Quote')
                    . ' #%s', $savedQuote->getIncrementId()
            ));

        // Highlight active tab
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('savedquote/quote/list');
        }

        $this->renderLayout();
    }

    /**
     * Print view of saved quote
     *
     * @return void
     */
    public function printAction()
    {
        $id = $this->getRequest()->getParam('id');
        $savedQuote = Mage::getModel('savedquote/savedquote')->load($id);

        if (!$savedQuote || !$savedQuote->getId() || $this->_getCustomer()->getId() != $savedQuote->getCustomerId()) {
            Mage::getSingleton('core/session')->addNotice('Quote was not found');
            return $this->_redirectReferer();
        }

        Mage::register('saved_quote', $savedQuote);

        $this->loadLayout();

        $this->getLayout()
            ->getBlock('head')
            ->setTitle($this->__(
                (Mage::helper('savedquote')->isQuotePreOrder($savedQuote) ? 'Pre-Order' : 'Saved Quote')
                    . ' #%s', $savedQuote->getIncrementId()
            ));

        $this->renderLayout();
    }

    /**
     * This will clear the current shopping session and attempt to load the items
     * the quote into the cart
     *
     * @return void
     */
    public function reorderAction()
    {
        $id = $this->getRequest()->getParam('id');
        $savedQuote = Mage::getModel('savedquote/savedquote')->load($id);

        if (!$savedQuote || !$savedQuote->getId() || $this->_getCustomer()->getId() != $savedQuote->getCustomerId()) {
            Mage::getSingleton('core/session')->addNotice('Quote was not found');
            return $this->_redirectReferer();
        }

        // Clear current quote
        $this->_getHelper()->clearCurrentQuote();

        // Create new quote
        $quote = Mage::getModel('sales/quote')->setStoreId($this->_getStoreId());
        $quote->assignCustomer($this->_getCustomer());

        // Add items to new quote
        foreach ($savedQuote->getItemCollection() as $item) {
            $quote->addProduct(
                $item->getProduct(),
                $item->getQty()
            );
        }

        // Collect totals, save, redirect
        $quote->collectTotals()->save();
        $this->_redirect('checkout/cart');
    }

    /**
     * Removes a saved quote
     *
     * @return void
     */
    public function deleteAction()
    {
        // No longer possible to delete quotes or preorders
        Mage::getSingleton('core/session')->addNotice('Quotes or Preorders cannot be deleted');
        return $this->_redirectReferer();

        $id = $this->getRequest()->getParam('id');
        $savedQuote = Mage::getModel('savedquote/savedquote')->load($id);

        if (!$savedQuote || !$savedQuote->getId() || $this->_getCustomer()->getId() != $savedQuote->getCustomerId()) {
            Mage::getSingleton('core/session')->addNotice("Quote $incrementId was not found");
            return $this->_redirectReferer();
        }

        $incrementId = $savedQuote->getIncrementId();

        if ($savedQuote->getIsActive() == SDM_SavedQuote_Helper_Data::INACTIVE_FLAG) {
            Mage::getSingleton('core/session')->addNotice('Converted quotes cannot be deleted');
            return $this->_redirectReferer();
        }

        if ($savedQuote->getIsActive() == SDM_SavedQuote_Helper_Data::QUOTE_CANCELED_FLAG) {
            Mage::getSingleton('core/session')->addNotice('Canceled quotes cannot be deleted');
            return $this->_redirectReferer();
        }

        try {
            $savedQuote->delete();
            Mage::getSingleton('core/session')->addSuccess("Quote $incrementId was deleted");
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError("Quote $incrementId");
        }

        return $this->_redirectReferer();
    }

    /**
     * Convert the quotes into an order
     *
     * @return void
     */
    public function initCheckoutAction()
    {
        $id = $this->getRequest()->getParam('id');
        $savedQuote = Mage::getModel('savedquote/savedquote')->load($id);

        if (!$savedQuote || !$savedQuote->getId() || $this->_getCustomer()->getId() != $savedQuote->getCustomerId()) {
            Mage::getSingleton('core/session')->addNotice('Quote was not found');
            return $this->_redirectReferer();
        }
        if ($savedQuote->getIsActive() == SDM_SavedQuote_Helper_Data::INACTIVE_FLAG) {
            Mage::getSingleton('core/session')->addNotice('This quote cannot be checked out');
            return $this->_redirectReferer();
        }

        // Check expiration date
        if ($this->_getHelper()->checkExpiration($savedQuote) === false) {
            Mage::getSingleton('core/session')
                ->addNotice('This quote is expired and cannot be converted to an order');
            return $this->_redirectReferer();
        }

        // Prepare a custom cart/quote from the saved quote
        try {
            $this->_getHelper('converter')->addSavedQuoteToSession($savedQuote);
        } catch (Exception $e) {
            $this->_getHelper()->log(
                "Saved quote #{$savedQuote->getIncrementId()} to quote converison could not be made. "
                    . $e->getMessage()
            );
            Mage::getSingleton('core/session')->addError('Order cannot be placed at this time (1)');

            return $this->_redirectReferer();
        }


        // Convert to order
        // try {
        //     $order = $this->_getHelper('converter')->convertQuoteToOrder($quote);
        // } catch (Exception $e) {
        //     $msg = "Unable to convert quote to order. " . $e->getMessage();
        //     $this->_getHelper()->log($msg);
        //     Mage::getSingleton('core/session')->addError('Order cannot be placed at this time (2)');

        //     return $this->_redirectReferer();
        // }

        // try {
        //     $this->_getHelper()->saveActiveToInactive($savedQuote);
        // } catch (Exception $e) {
        //     $this->_getHelper()->log("Unable to make active quote into inactive. " . $e->getMessage());
        //     Mage::getSingleton('core/session')->addError('Saved quote failed to save');

        //     return $this->_redirectReferer();
        // }

        // Mage::getSingleton('core/session')->addSuccess("Order #{$order->getIncrementId()} placed");
        return $this->_redirectUrl(Mage::helper('checkout/url')->getCheckoutUrl());
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get the specified helper
     *
     * @param string $name
     *
     * @return SDM_SavedQuote_Helper_Data
     */
    protected function _getHelper($name = '')
    {
        if (empty($name)) {
            $name = 'savedquote';
        } else {
            $name = "savedquote/$name";
        }

        return Mage::helper($name);
    }

    /**
     * Get current active quote instance. Used only to obtain the shopping cart
     * to saved.
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getStoreId()
    {
        return Mage::helper('core')->getStoreId();
    }

    /**
     * Get current customer
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Send confirmation email
     *
     * @param SDM_SavedQuote_Model_Savedquote $quote
     *
     * @return void
     */
    protected function _sendConfirmationEmail(SDM_SavedQuote_Model_Savedquote $quote)
    {
        Mage::getModel('core/email_template')
            ->setDesignConfig(array(
                'area'  => 'frontend',
                'store' => SDM_Core_Helper_Data::STORE_CODE_EE
            ))
            ->loadDefault('sdm_savedquote_confirmation')
            ->setSenderName(Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME))
            ->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'))
            ->setStoreId(0)
            ->send(
                $quote->getCustomerEmail(),
                $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname(),
                array(
                    'quote'      => $quote,
                    'email_html' => Mage::app()->getLayout()
                        ->createBlock('savedquote/email_detail')
                        ->setTemplate('sdm/savedquote/account/savedquote/print.phtml')
                        ->setQuote($quote)
                        ->toHtml()
                )
            );
        ;
    }
}
