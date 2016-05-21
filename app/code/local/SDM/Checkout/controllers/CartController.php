<?php
/**
 * Separation Degrees One
 *
 * Checkout-related customization
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Checkout
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php';

/**
 * SDM_Checkout_CartController class
 */
class SDM_Checkout_CartController
    extends Mage_Checkout_CartController
{
    /**
     * Shopping cart display action
     *
     * Rewritten to dispatch a custom event
     *
     * @see Mage_Checkout_CartController::indexAction
     *
     * @return void
     */
    public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();

            // Start of customization: custom event
            Mage::dispatchEvent(
                'checkout_cart_index_save_before',
                array('cart' => $cart)
            );
            // End of customization

            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Validate items in cart
        Mage::helper('sdm_checkout/quote')->validateForCart();
        
        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        // Place back session messages that were cleared from failing pre-order checkout
        $messages = Mage::getSingleton('checkout/session')->getSessionMessages();
        Mage::getSingleton('checkout/session')->unsSessionMessages();
        Mage::helper('sdm_core')->injectSessionMessages($messages); // Place messages back into session

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }

    /**
     * Initialize coupon
     *
     * Rewritten to modify flash messages
     *
     * @see Mage_Checkout_CartController::couponPostAction
     *
     * @return void
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $quote = $this->_getQuote();
                    $skus = $quote->getAffectedSku();

                    if ($quote->getSubtotal() == $quote->getSubtotalWithDiscount()) {
                        $message = $this->__(
                            'Coupon code "%s" was applied, but no applicable discounts were found.',
                            Mage::helper('core')->escapeHtml($couponCode)
                        );
                    } else {
                        if ($skus) {
                            $message = $this->__(
                                'Coupon code "%s" was applied to %s.',
                                Mage::helper('core')->escapeHtml($couponCode),
                                rtrim(implode(', ', $skus))
                            );
                        } else {
                            $message = $this->__('Coupon code was applied.');
                        }
                    }
                    $this->_getSession()->addSuccess($message);

                } else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }

    /**
     * Add product to shopping cart action; added ajaxcart functionality
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function ajaxaddAction()
    {
        Mage::register('isAjaxCart', true);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $response = array(
            'status' => 'SUCCESS',
            'message' => '',
            'cartBlock' => ''
        );

        if (!$this->_validateFormKey()) {
            $response['status'] = "ERROR";
            $response['message'] = $this->__("Error adding item to cart. Please refresh the page and try again.");
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }
        $cart          = $this->_getCart();
        $params        = $this->_parseParams();
        $messages      = array();
        $addedProducts = array();
        $lastAddedName = "";
        try {
            foreach ($params as $productId => $qty) {
                // Get product we want to add
                $product = $this->_getProduct($productId);

                // Ensure there is a valid product ID
                if ($product === false) {
                    $response['status'] = "ERROR";
                    $response['message'] = $this->__("Error adding item to cart.");
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
                    return;
                }

                // Verify quantity and add
                $qty = $this->_verifyProductQuantity($product, $qty, $messages);
                if (!empty($qty)) {
                    $cart->addProduct($product, array('qty' => $qty));
                    $addedProducts[] = $product;
                    $lastAddedName = $product->getName();
                }

            }

            // Add related product
            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            foreach ($addedProducts as $product) {
                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );
            }

            $response = $this->_addCartContentToResponse($response);

            // Add "successfully added" message for products we were able to add
            if (count($addedProducts) == 1) {
                $messages[] = $this->__(
                    '%s was added to your shopping cart.',
                    Mage::helper('core')->escapeHtml($lastAddedName)
                );
            } elseif (count($addedProducts) > 1) {
                $messages[] = $this->__(
                    '%s products were added to your shopping cart.',
                    (string) count($addedProducts)
                );
            }

            // Notice when min. qty. add was applied
            if ($cart->getMinQtyMessage()) {
                $response['minQtyOverride'] = $cart->getMinQtyMessage();
            }

            // Add messages to response
            if (!empty($messages)) {
                $response['message'] = implode("<hr>", $messages);
            }

        } catch (Mage_Core_Exception $e) {
            $response['status'] = "ERROR";
            $response['message'] = implode("<hr>", array_unique(explode("\n", $e->getMessage())));
        } catch (Exception $e) {
            $response['status'] = "ERROR";
            $response['message'] = $this->__('Cannot add the item to shopping cart.');
            Mage::log($e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    /**
     * Ajax add multiple action
     *
     * @return void
     */
    public function ajaxaddmultipleAction()
    {
        $this->ajaxaddAction();
        return;
    }

    /**
     * Update function to add minicart_head
     *
     * @return void
     */
    public function ajaxUpdateAction()
    {
        try {
            parent::ajaxUpdateAction();

            $response = Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
            $response = $this->_addCartContentToResponse($response);
        } catch (Exception $e) {
            $response = array('error' => $e->getMessage());
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));

        return;
    }

    /**
     * Update function to add minicart_head
     *
     * @return void
     */
    public function ajaxDeleteAction()
    {
        try {
            parent::ajaxDeleteAction();

            $response = Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
            $response = $this->_addCartContentToResponse($response);
        } catch (Exception $e) {
            $response = array('error' => $e->getMessage());
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));

        return;
    }

    /**
     * Adds the cart header and content blocks to the response
     *
     * @param  array $response
     * @return array $response
     */
    protected function _addCartContentToResponse($response)
    {
        $this->loadLayout();
        $block = $this->getLayout()
            ->getBlock('minicart_head')
            ->setIsAjaxCall(true)
            ->toHtml();
        $response['cart_header'] = $block;

        $block = $this->getLayout()
            ->getBlock('minicart_content')
            ->setIsAjaxCall(true)
            ->toHtml();
        $response['cart_content'] = $block;

        return $response;
    }

    /**
     * Checks the stock amount for a product you're adding to cart
     *
     * @param Mage_Catalog_Model_Product $product
     * @param integer                    $qty
     * @param array                      $messages
     *
     * @return int   The quantity allowed to add for this product
     */
    protected function _verifyProductQuantity($product, $qty, &$messages)
    {
        if (!$product->isSalable()) {
            $messages[] = $this->__(
                "Unable to add item #%s to cart: no longer available for purchase.",
                (string)$product->getSku()
            );
            return 0;
        }
        if (!(bool)$product->getData('allow_cart_backorder') && !$product->isPrintCatalog()) {
            $item = Mage::getSingleton('checkout/session')->getQuote()->getItemByProduct($product);
            $qtyAvail = (int)$product->getStockItem()->getQty();
            $qtyInCart = $item === false ? 0 : (int)$item->getQty();
            $qtyDesired = $qtyInCart + $qty;
            if ($qtyAvail - $qtyDesired < 0) {
                if ($qtyAvail - $qtyInCart <= 0) {
                    $messages[] = $this->__(
                        "Unable to add item #%s to cart: no more stock available.",
                        (string)$product->getSku()
                    );
                    return 0;
                } else {
                    $messages[] = $this->__(
                        'There are only %s unit(s) available of item #%s. '
                            . 'The amount added to your cart has been adjusted accordingly.',
                        (string)floor($qtyAvail),
                        (string)$product->getSku()
                    );
                    return $qtyAvail - $qtyInCart;
                }
            }
        }
        return $qty;
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _parseParams()
    {
        $params = array();

        // Get qty filter
        $filter = new Zend_Filter_LocalizedToNormalized(
            array('locale' => Mage::app()->getLocale()->getLocaleCode())
        );

        // Parse multi add ids
        $multiAddIds = $this->getRequest()->getParam('multi_add_products');
        if (!empty($multiAddIds)) {
            foreach ($multiAddIds as $productId => $qty) {
                $productId = (int)$productId;
                $qty = (int)$filter->filter($qty);
                ;
                if (!empty($productId) && !empty($qty)) {
                    $params[$productId] = $qty;
                }
            }
        }

        // Return if not empty
        if (!empty($params)) {
            return $params;
        }

        // Parse super group ids
        $superGroupIds = $this->getRequest()->getParam('super_group');
        if (!empty($superGroupIds)) {
            foreach ($superGroupIds as $productId => $qty) {
                $productId = (int)$productId;
                $qty = (int)$filter->filter($qty);
                ;
                if (!empty($productId) && !empty($qty)) {
                    $params[$productId] = $qty;
                }
            }
        }

        // Return if not empty
        if (!empty($params)) {
            return $params;
        }

        $productId = (int) $this->getRequest()->getParam('product');
        $qty = (int) $this->getRequest()->getParam('qty');
        $qty = empty($qty) ? 1 : $filter->filter($qty);
        if (!empty($productId) && !empty($qty)) {
            $params[$productId] = $qty;
        }

        return $params;
    }

    /**
     * Initializes a product
     *
     * @param  int $productId
     * @return false|SDM_Catalog_Model_Product
     */
    protected function _getProduct($productId)
    {
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }
}
