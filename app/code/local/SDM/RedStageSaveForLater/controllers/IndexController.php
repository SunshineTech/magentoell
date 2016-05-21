<?php
/**
 * Separation Degrees One
 *
 * Fixes for RedStage_SaveForLater
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RedstageSaveForLater
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once Mage::getModuleDir('controllers', 'Redstage_SaveForLater') . DS . 'IndexController.php';

/**
 * SDM_RedStageSaveForLater_IndexController class
 */
class SDM_RedStageSaveForLater_IndexController
    extends Redstage_SaveForLater_IndexController
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_redirect('checkout/cart');
    }
    /**
     * Save action
     *
     * @return void
     */
    public function saveAction()
    {
        $quote_item_id = Mage::app()->getRequest()->getParam('item');

        if ($quote_item_id) {
            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            $customer_id = '';
            if ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
                $customer_id = $customer->getId();
            }

            foreach ($quote->getAllItems() as $quote_item) {
                if ($quote_item->getId() == $quote_item_id) {
                    $model = Mage::getModel('saveforlater/item')
                        ->setCustomerId($customer_id)
                        ->setQuoteId($quote->getId())
                        ->setProductId($quote_item->getProduct()->getId())
                        ->setName($quote_item->getName())
                        ->setQty($quote_item->getQty())
                        ->setPrice($quote_item->getPrice())
                        ->setBuyRequest(serialize(array_merge($quote_item->getBuyRequest()->getData(), array(
                            'product' => $quote_item->getProductId()
                        ))))
                        ->setDateSaved(date('Y-m-d h:i:s', Mage::getModel('core/date')->timestamp()));

                    try {
                        $model->save();
                        $quote->removeItem($quote_item->getId());
                        $quote->save();
                        Mage::getSingleton('checkout/session')
                            ->addSuccess($model->getName() .' was added to '. $this->__('Saved for Later'));
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    }

                    break;
                }
            }
        }

        $this->_redirect('checkout/cart');

    }

    /**
     * Copy action
     *
     * @return void
     */
    public function copyAction()
    {
        $saveforlater_item_id = Mage::app()->getRequest()->getParam('item');
        if ($saveforlater_item_id) {
            $saveforlater_item = Mage::getModel('saveforlater/item')->load($saveforlater_item_id);
            $buy_request = unserialize($saveforlater_item->getBuyRequest());
            $params = $this->getRequest()->getParams();
            $this->getRequest()->setParams($buy_request);
            $this->addAction();
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Move action
     *
     * @return void
     */
    public function moveAction()
    {
        $saveforlater_item_id = Mage::app()->getRequest()->getParam('item');
        if ($saveforlater_item_id) {
            $saveforlater_item = Mage::getModel('saveforlater/item')->load($saveforlater_item_id);
            $buy_request = unserialize($saveforlater_item->getBuyRequest());
            $params = $this->getRequest()->getParams();
            $this->getRequest()->setParams($buy_request);
            $this->addAction();
            $saveforlater_item->delete();
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        $saveforlater_item_id = Mage::app()->getRequest()->getParam('item');
        if ($saveforlater_item_id) {
            $saveforlater_item = Mage::getModel('saveforlater/item')->load($saveforlater_item_id);
            try {
                $saveforlater_item->delete();
                Mage::getSingleton('checkout/session')
                    ->addSuccess($saveforlater_item->getName() .' was removed from '. $this->__('Saved for Later'));
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')
                    ->addError($e->getMessage());
            }
        }
        $this->_redirect('checkout/cart');
    }
}
