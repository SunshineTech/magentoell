<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5.5
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Saves applications and addresses for the retailer application
 */
class SDM_RetailerApplication_ApplicationController extends Mage_Core_Controller_Front_Action
{
    /**
     * Only allow this page on the retailer site
     *
     * @return null
     */
    protected function _construct()
    {
        if (!Mage::helper('sdm_core')->isSite(SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE)) {
            $this->_redirect('customer/account');
            return;
        }
        return parent::_construct();
    }

    /**
     * Forwards index action to view action
     *
     * @return $this
     */
    public function indexAction()
    {
        $this->_redirect('retailerapplication/application/view');
        return $this;
    }

    /**
     * Handles displaying a retailer application on the frontend
     *
     * @return $this
     */
    public function viewAction()
    {
        $loggedIn = Mage::helper('customer')->isLoggedIn();
        if (!$loggedIn) {
            $this->_redirect('customer/account');
            return $this;
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Retailer Application'));
        $this->renderLayout();

        return $this;
    }

    /**
     * Handles saving a retailer application from the frontend
     *
     * @return $this
     */
    public function saveAction()
    {
        $loggedIn = Mage::helper('customer')->isLoggedIn();
        if (!$loggedIn) {
            $this->_redirect('customer/account');
            return;
        }

        $application = Mage::helper('retailerapplication')->getCurrentApplication();
        $status = $application->getStatus();
        if ($status !== SDM_RetailerApplication_Helper_Data::STATUS_PENDING
            && $status !== SDM_RetailerApplication_Helper_Data::STATUS_DECLINED
        ) {
            Mage::getSingleton('core/session')
                ->addError(
                    'Your application cannot be modified at this time. Please contact '
                        . 'customer service for assistance.'
                );
            $this->_redirect('retailerapplication/application/view');
            return $this;
        }

        $fields = Mage::helper('retailerapplication/fields')->getFrontendFieldsToSave();
        $needsData = array();
        foreach ($fields as $fieldName => $fieldData) {
            // Get data to save for field
            $data = null;
            switch ($fieldData['type']) {
                case 'address':
                    $addressPrefix = explode('_', $fieldName);
                    $addressPrefix = reset($addressPrefix);
                    $data = Mage::app()->getRequest()->getParam($addressPrefix);

                    // Once a fax number is saved, it cannot be removed
                    if ($addressPrefix === SDM_RetailerApplication_Helper_Data::ONWER_ADDRESS_CODE) {
                        $faxCannotChange = Mage::app()->getRequest()->getPost('fax_cannot_change');
                        if ($faxCannotChange && empty($data['fax'])) {
                            Mage::getSingleton('core/session')
                                ->addError('Fax number can only be edited but not removed once saved.');
                            return $this->_redirect('retailerapplication/application/view');
                        }
                    }
                    break;

                default:
                    $data = Mage::app()->getRequest()->getParam($fieldName);
                    break;
            }

            // Format data to save for fields
            switch ($fieldData['type']) {
                case 'int':
                    $data = empty($data) ? null : (int)$data;
                    break;
                case 'array':
                    $data = array_key_exists($data, $fieldData['values']) ? $data : '';
                    break;
                case 'text':
                case 'textarea':
                    if (isset($fieldData['noval']) && $fieldData['noval']) {
                        $data = $data === null ? 'N/A' : $data;
                        $data = strtolower($data) === 'n/a' ? 'N/A' : $data;
                    }
                    $data = trim($data);
                    break;
                case 'multiselect':
                    $data = implode(',', Mage::app()->getRequest()->getPost($fieldName));
                    break;
                default:
                    break;
            }

            // Save data to application
            switch ($fieldData['type']) {
                case 'address':
                    $this->_saveAddress($addressPrefix, $application, $data);
                    break;
                case 'file':
                    $this->_saveFile($fieldName, $application);
                    break;
                default:
                    $application->setData($fieldName, $data);
                    break;
            }

            if (empty($data)) {
                $needsData[] = $fieldData['name'];
            }
        }

        // Submit this application for review?
        $submitForReview = Mage::app()->getRequest()->getParam('application_submit_review');
        if (!empty($submitForReview)) {
            $application->setData('status', SDM_RetailerApplication_Helper_Data::STATUS_UNDER_REVIEW);
        }
        $application->save();
        return $this->_redirect('retailerapplication/application/view');
    }

    /**
     * Saves address data for an application
     *
     * @param string $addressPrefix What type of addres is this?
     * @param object $application
     * @param array  $addressData
     *
     * @return $this
     */
    protected function _saveAddress($addressPrefix, $application, $addressData)
    {
        $address = $application->getAddress($addressPrefix);
        // Handle address default assignment
        if ($addressPrefix === SDM_RetailerApplication_Helper_Data::SHIPPING_ADDRESS_CODE) {
            $address->setIsDefaultShipping(true);
        } elseif ($addressPrefix === SDM_RetailerApplication_Helper_Data::BILLING_ADDRESS_CODE) {
            $address->setIsDefaultBilling(true);
        }

        // Cycle through submitted data
        foreach ($addressData as $key => $value) {
            $value = is_array($value) ? $value : trim($value);

            if ($key === 'address_id' || empty($value)) {
                // Skip address id or empty values
                continue;
            } elseif ($key === 'region_id' || $key === 'region') {
                // Handle regions
                $address->setData('region', $key === 'region' ? $value : "0");
                $address->setData('region_id', $key === 'region_id' ? (int)$value : null);
            } else {
                // Set the data
                $address->setData($key, $value);
            }
        }

        $address->save();
        if ($addressPrefix === SDM_RetailerApplication_Helper_Data::ONWER_ADDRESS_CODE) {
            Mage::helper('retailerapplication')->makeAddressUneditable($address->getId());
        }

        $application->setData(
            $addressPrefix . "_address_id",
            $address->getId()
        );
        return $this;
    }

    /**
     * Saves a file for an application
     *
     * @param string $fieldName
     * @param object $application
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function _saveFile($fieldName, $application)
    {
        // Check if faxing document
        $fax = Mage::app()->getRequest()->getParam($fieldName . "_fax");
        if ($fax) {
            $oldFile = $application->getData($fieldName);
            $this->_deleteSavedFile($oldFile);
            $application->setData($fieldName, 'fax');
            return $this;
        }

        // Check if uploading file
        if (isset($_FILES[$fieldName]) && !empty($_FILES[$fieldName]['name'])) {
            // Delete old file
            $oldFile = $application->getData($fieldName);
            $this->_deleteSavedFile($oldFile);

            try {
                // Setup uploader for new file
                $uploader = new Varien_File_Uploader($fieldName);
                $uploader->setAllowedExtensions(array('jpg','jpeg','png','doc','docx','pdf'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                // Get filename parts
                $media    = Mage::getBaseDir('media');
                $path     = DS . "retailer_application_files" . DS . 'customer-';
                $path    .= Mage::helper('customer')->getCustomer()->getId() . DS;
                $path     = strtolower($path);
                $newName  = hash('sha256', rand().microtime().rand().$_FILES[$fieldName]['name']);
                $exploded = explode('.', $_FILES[$fieldName]['name']);
                $newName .= '.'.end($exploded);
                $newName  = strtolower($newName);

                // Save file to media
                $uploader->save($media.$path, $newName);

                // Record changes
                $application->setData($fieldName, $path.$newName);
            } catch (Exception $e) {
                Mage::getSingleton('core/session')
                    ->addError("Error saving file: " . $e->getMessage());
            }

        }
        return $this;
    }

    /**
     * Delete a saved file
     *
     * @param string $oldFile
     *
     * @return $this
     */
    protected function _deleteSavedFile($oldFile)
    {
        if (!empty($oldFile) && $oldFile !== 'fax') {
            unlink(Mage::getBaseDir('media') . $oldFile);
        }
        return $this;
    }
}
