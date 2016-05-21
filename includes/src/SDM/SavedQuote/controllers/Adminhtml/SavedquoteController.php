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
 * Controller for saved quote admin actions
 */
class SDM_SavedQuote_Adminhtml_SavedquoteController extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/savedquote');
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Saved Quotes'), $this->__('Saved Quotes'));

        return $this;
    }

    /**
     * Saved Quotes grid
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Saved Quotes'));

        $this->_initAction()->renderLayout();
    }

    /**
     * View a saved quote
     *
     * @return void
     */
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $quote = Mage::getModel('savedquote/savedquote')->load($id);

            if (!$quote->getId()) {
                Mage::getSingleton('adminhtml/session')->addNotice();
                return $this->_redirect('*/*/');
            }
        }

        Mage::register('saved_quote', $quote);

        $this->_initAction();

        $this->_title($this->__('Sales'))->_title($this->__('#' . $quote->getIncrementId()));

        $this->renderLayout();
    }

    /**
     * Edits and saves a quote
     *
     * @return null
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $quote = Mage::getModel('savedquote/savedquote')
                ->load($id);

            if ($quote->getIsActive() == SDM_SavedQuote_Helper_Data::INACTIVE_FLAG) {
                Mage::getSingleton('adminhtml/session')
                    ->addNotice('Unable to modify converted quote');
                return $this->_redirectReferer();
            }
            if (!$quote->getId()) {
                Mage::getSingleton('adminhtml/session')->addNotice('Quote could not be found');
                return $this->_redirectReferer();
            }
        }

        $incrementId = $quote->getIncrementId();
        $isPreorder = Mage::helper('sdm_preorder')->isQuotePreOrder($quote);

        try {
            $quoteItems = array();

            // Save AX info
            $axAccountId = $this->getRequest()->getParam('ax_account_id');
            $invoiceAccountId = $this->getRequest()->getParam('ax_invoice_id');
            $customer = Mage::getModel('customer/customer')
                ->load($quote->getCustomerId())
                ->setAxCustomerId($axAccountId)
                ->setAxInvoiceId($invoiceAccountId)
                ->save();

            // Update expiration date
            $exp = $this->getRequest()->getParam('expires_at');
            $quote->setData(
                'expires_at',
                gmdate('Y-m-d H:i:s', Mage::getModel('core/date')->gmtTimestamp($exp))
            );

            // Add current items to $quoteItems
            foreach ($quote->getItemCollection() as $item) {
                // Add item data to our array
                $quoteItems[$item->getSku()] = array(
                    'qty'             => $item->getQty(),
                    'price'           => $item->getPrice(),
                    'discount_amount' => $item->getDiscountAmount()
                );

                // Remove item from quote
                $item->delete();
            }

            // Loop through current items post array to see we changed QTY
            $current = $this->getRequest()->getParam('quote_items');
            if (!empty($current)) {
                foreach ($current as $sku => $item) {
                    $qty   = (int)$item['qty'];
                    $price = (float)$item['price'];
                    if ($qty <= 0) {
                        unset($quoteItems[$sku]);
                    } else {
                        $quoteItems[$sku]['qty']   = $qty;
                        $quoteItems[$sku]['price'] = round($price, 2);
                    }
                }
            }

            // Add new items to $quoteItems
            $newSkus    = $this->getRequest()->getParam('new_item_sku');
            $newPrices  = $this->getRequest()->getParam('new_item_price');
            $newQtys    = $this->getRequest()->getParam('new_item_qty');
            if (!empty($newSkus)) {
                foreach ($newSkus as $key => $sku) {
                    $qty   = (int)(isset($newQtys[$key]) ? $newQtys[$key] : 0);
                    $price = (float)(isset($newPrices[$key]) ? $newPrices[$key] : 0.00);
                    if ($qty <= 0) {
                        unset($quoteItems[$sku]);
                    } else {
                        $quoteItems[$sku] = array(
                            'qty'               => $qty,
                            'price'             => round($price, 2),
                            'discount_amount'   => 0.0
                        );
                    }
                }
            }

            // Create new items for this quote
            $subtotal = 0.0;
            foreach ($quoteItems as $sku => $item) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId($quote->getStoreId())
                    ->loadByAttribute('sku', $sku);
                if (!$product->getId()) {
                    continue;
                }

                // Check price. If less than 0, get default product price.
                // Price is NECESSARY for checkout, so don't allow $0 price
                // unless you're prepared to fix the resulting issues :)
                $item['price'] = $item['price'] <= 0 ? $product->getPrice() : $item['price'];

                // Calculate price, rowtotal, and add price to subtotal
                // Note: all original discounts are included in the saved prices. Any updates
                // must not re-apply any discounts.
                $pricePerItem = $item['price'];
                $rowTotal     = $pricePerItem * $item['qty'];
                $subtotal    += $rowTotal;

                // Create and save new quote item
                $newItem = Mage::getModel('savedquote/savedquote_item')
                    ->setSavedQuoteId($quote->getId())
                    ->setProductId($product->getId())
                    ->setStoreId($quote->getStoreId())
                    ->setSku($product->getSku())
                    ->setName($product->getName())
                    ->setQty($item['qty'])
                    ->setProductType($product->getTypeId())
                    // ->setItemOptions()   // no options needed for simple and grouped products
                    ->setPrice($pricePerItem)
                    //->setTaxPercent()     // Not needed; gets calculated at checkout
                    //->setTaxAmount()      // Not needed; gets calculated at checkout
                    ->setRowTotal($rowTotal)
                    ->setDiscountAmount($item['discount_amount'])
                    ->setIsPreOrder($isPreorder ? 1 : 0)
                    ->setPreOrderShippingDate($quote->getPreOrderShippingDate())
                    ->setPreOrderReleaseDate($product->getPreOrderReleaseDate());
                $newItem->save();
            }

            // Update totals
            if ($isPreorder) {
                // Update for preorder
                $quote->setSubtotal($subtotal)
                    ->setGrandTotal($isPreorder ? $subtotal : ($subtotal + $shipping + $tax));
            } else {
                // Update for saved quote
                $shippingSurcharge  = (float)$this->getRequest()->getParam('shipping_surcharge');
                $shippingSurcharge  = round($shippingSurcharge <= 0 ? 0 : $shippingSurcharge, 2);
                $shipping           = (float)$this->getRequest()->getParam('shipping');
                $shipping           = round($shipping <= 0 ? 0 : $shipping, 2);
                $tax                = (float)$this->getRequest()->getParam('tax');
                $tax                = round($tax <= 0 ? 0 : $tax, 2);
                $quote->setSubtotal($subtotal)
                    ->setTaxAmount($tax)
                    ->setShippingCost($shipping)
                    ->setSdmShippingSurcharge($shippingSurcharge)
                    ->setGrandTotal($subtotal + $shipping + $shippingSurcharge + $tax);
            }

            // Update internal comments
            $comments = trim($this->getRequest()->getParam('internal_comments'));
            $quote->setInternalComments($comments);

            // Save!
            $quote->save();

            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getHelper()->log("Quote #$incrementId failed to update. " . $e->getMessage());
            Mage::getSingleton('adminhtml/session')->addError('Quote could not be updated');
            return $this->_redirectReferer();
        }

        Mage::getSingleton('adminhtml/session')->addSuccess("Quote #$incrementId updated");

        return $this->_redirectReferer();
    }

    /**
     * Deletes a saved quote
     *
     * @return null
     */
    public function deleteAction()
    {
        // No longer possible to delete quotes or preorders
        Mage::getSingleton('adminhtml/session')->addNotice('Quotes or Preorders cannot be deleted');
        return $this->_redirectReferer();

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $quote = Mage::getModel('savedquote/savedquote')
                ->load($id);

            if ($quote->getIsActive() == SDM_SavedQuote_Helper_Data::INACTIVE_FLAG) {
                Mage::getSingleton('adminhtml/session')
                    ->addNotice('Unable to delete converted quote');
                return $this->_redirectReferer();
            }
            if (!$quote->getId()) {
                Mage::getSingleton('adminhtml/session')->addNotice('Quote could not be found');
                return $this->_redirectReferer();
            }
        }

        $incrementId = $quote->getIncrementId();

        try {
            $quote->delete();
        } catch (Exception $e) {
            $this->_getHelper()->log("Quote #$incrementId failed to delete. " . $e->getMessage());
            Mage::getSingleton('adminhtml/session')->addError('Quote could not be deleted');
            return $this->_redirectReferer();
        }

        Mage::getSingleton('adminhtml/session')->addSuccess("Quote #$incrementId deleted");

        return $this->_redirect('*/*/');
    }

    /**
     * Deny a pre order
     *
     * @return void
     */
    public function preorderDenyAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            Mage::helper('sdm_preorder')->deny($id);
            Mage::getSingleton('adminhtml/session')->addSuccess('Pre-order cancelled');
        }
        return $this->_redirectReferer();
    }

    /**
     * Cancel a saved quote
     *
     * @return void
     */
    public function savedQuoteCancelAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            Mage::helper('savedquote')->cancel($id);
            Mage::getSingleton('adminhtml/session')->addSuccess('Saved Quote cancelled');
        }
        return $this->_redirectReferer();
    }

    /**
     * Approve a pre order
     *
     * @return void
     */
    public function preorderApproveAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            Mage::helper('sdm_preorder')->approve($id);
            Mage::getSingleton('adminhtml/session')->addSuccess('Pre-order approved');
        }
        return $this->_redirect('*/*/');
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
}
