<?php
/**
 * Separation Degrees One
 *
 * eClips Software Download
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Eclips
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Eclips_IndexController class
 */
class SDM_Eclips_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Form page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')
            ->setTitle($this->__('eClips Software Download'));

        if (Mage::getSingleton('core/session')->getValidSerialNumber() === true) {
            Mage::register('download', true);
            Mage::getSingleton('core/session')->unsValidSerialNumber();
        }

        $this->renderLayout();
    }

    /**
     * Form submit action
     *
     * @return void
     */
    public function submitAction()
    {
        // B050151
        $post = $this->getRequest()->getPost('serial_number');

        if (empty($post)) {
            Mage::getSingleton('core/session')
                ->addError($this->__('Please enter a serial number'));
            return $this->_redirect('*/');

        } else {
            $result = $this->_validateSerialNumber($post);

            if ($result !== true) {
                Mage::getSingleton('core/session')->addError($result);
                Mage::getSingleton('core/session')->setValidSerialNumber(false);

                return $this->_redirect('*/');

            } else {
                try {
                    $request = Mage::getModel('eclips/request')->load(1);
                    $counter = $request->getCount();
                    $request->setCount($counter + 1)
                        ->save();

                } catch (Exception $e) {
                    Mage::helper('eclips')->log('Failed to increase download counter');
                }

                Mage::getSingleton('core/session')
                    ->addSuccess($this->__('Serial number is valid. Download available below.'));
                Mage::getSingleton('core/session')->setValidSerialNumber(true);

                return $this->_redirect('*/');
            }
        }
    }

    /**
     * Validates the serial number submitted
     *
     * @param array $serialNumber
     *
     * @return bool|str
     */
    protected function _validateSerialNumber($serialNumber)
    {
        $serialNumber = trim($serialNumber);

        if (strlen($serialNumber) !== 7) {
            return $this->__('Serial number is invalid');
        }

        $prefix = strtolower(substr($serialNumber, 0, 3));
        $number = (int)substr($serialNumber, 3, strlen($serialNumber)-1);

        if ($prefix === 'b05' && ($number >= 1 && $number <= 5100)) {
            return true;
        } elseif ($prefix === 'b12' && ($number >= 1 && $number <= 5000)) {
            return true;
        } elseif ($prefix === 'b04' && ($number >= 1 && $number <= 4000)) {
            return true;
        } elseif ($prefix === 'c01' && ($number >= 1 && $number <= 5000)) {
            return true;
        } else {
            return $this->__('Serial number is invalid');
        }

        return false;
    }
}
