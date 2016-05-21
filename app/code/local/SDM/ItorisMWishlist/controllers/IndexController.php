<?php
/**
 * Separation Degrees One
 *
 * Adds visibility filter to Itoris Multiple Wishlist
 *
 * @category  SDM
 * @package   SDM_ItorisMWishlist
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once Mage::getModuleDir('controllers', 'Itoris_MWishlist') . DS . 'IndexController.php';

/**
 * SDM_ItorisMWishlist_IndexController
 */
class SDM_ItorisMWishlist_IndexController extends Itoris_MWishlist_IndexController
{
    /**
     * Itoris_MWishlist_IndexController::fromcartAction() could not be elegantly
     * rewritten due to the way it was structured. This method incoporates Mage's
     * and Itoris' fromcartAction() actions and combines them into a new method,
     * eliminating referencing "parent".
     *
     * @see Mage_Wishlist_IndexController::fromcartAction()
     * @see Itoris_MWishlist_IndexController::fromcartAction()
     *
     * @return void
     */
    public function fromcartAction()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }
        $itemId = (int) $this->getRequest()->getParam('item');

        /* @var Mage_Checkout_Model_Cart $cart */
        $cart = Mage::getSingleton('checkout/cart');
        $session = Mage::getSingleton('checkout/session');

        try {
            $item = $cart->getQuote()->getItemById($itemId);
            if (!$item) {
                Mage::throwException(
                    Mage::helper('wishlist')->__("Requested cart item doesn't exist")
                );
            }

            // $productName = $item->getProduct()->getName();
            if (!$this->getRequest()->getParam('qty')) {
                $this->getRequest()->setParam('qty', $item->getQty());
                $this->getRequest()->setParam('product', $item->getProductId());
            }
            $productId  = $item->getProductId();
            $buyRequest = $item->getBuyRequest();

            $wishlist->addNewItem($productId, $buyRequest);

            $productIds[] = $productId;
            $cart->getQuote()->removeItem($itemId);
            $cart->save();
            Mage::helper('wishlist')->calculate();
            $productName = Mage::helper('core')->escapeHtml($item->getProduct()->getName());
            $wishlistName = Mage::helper('core')->escapeHtml($wishlist->getName());
            $session->addSuccess(
                Mage::helper('wishlist')->__("%s has been moved to wishlist %s", $productName, $wishlistName)
            );
            $wishlist->save();
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('wishlist')->__('Cannot move item to wishlist'));
        }

        $successMessages = $session->getMessages()->getItemsByType('success');
        if (count($successMessages)) {
            foreach ($successMessages as $message) {
                $session->getMessages()->deleteMessageByIdentifier($message->getIdentifier());
            }
            $session->addSuccess(
                Mage::helper('wishlist')->__("%s has been moved to wishlist %s", $productName, '')
            );
        }

        return $this->_redirectUrl(Mage::helper('checkout/cart')->getCartUrl());
    }
}
