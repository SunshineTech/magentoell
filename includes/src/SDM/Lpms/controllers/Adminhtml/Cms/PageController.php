<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom Landing Page Management System (LPMS).
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lpms
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Cms' . DS . 'PageController.php';

/**
 * SDM_Lpms_Adminhtml_Cms_PageController
 */
class SDM_Lpms_Adminhtml_Cms_PageController extends Mage_Adminhtml_Cms_PageController
{
    /**
     * Save action
     *
     * @return void
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            $data = $this->_filterPostData($data);
            //init model and set data
            $model = Mage::getModel('cms/page');

            if ($id = $this->getRequest()->getParam('page_id')) {
                $model->load($id);
            }

            // handle image
            $this->_handleImages($data, $model);
            
            $model->setData($data);

            Mage::dispatchEvent('cms_page_prepare_save', array('page' => $model, 'request' => $this->getRequest()));

            //validating
            if (!$this->_validatePostData($data)) {
                $this->_redirect('*/*/edit', array('page_id' => $model->getId(), '_current' => true));
                return;
            }

            // try to save it
            try {
                // save the data
                $model->save();

                // save the asset data
                $this->_saveAssetData($model->getId());

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('cms')->__('The page has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('page_id' => $model->getId(), '_current'=>true));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('cms')->__('An error occurred while saving the page.'));
            }
            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array('page_id' => $this->getRequest()->getParam('page_id')));
            return;
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('page_id')) {
            $title = "";
            try {
                // init model and delete
                $model = Mage::getModel('cms/page');
                $model->load($id);
                //$this->_deleteHeroImage($model);
                $title = $model->getTitle();
                $model->delete();
                // delete assets for this page
                Mage::helper('lpms')->deleteAllPageAssets($id);
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('cms')->__('The page has been deleted.'));
                // go to grid
                Mage::dispatchEvent('adminhtml_cmspage_on_delete', array('title' => $title, 'status' => 'success'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::dispatchEvent('adminhtml_cmspage_on_delete', array('title' => $title, 'status' => 'fail'));
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('page_id' => $id));
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('Unable to find a page to delete.'));
        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * _saveAssetData
     *
     * @param string $pageId
     *
     * @return void
     */
    protected function _saveAssetData($pageId)
    {
        $assetData = $this->getRequest()->getParam('lpms_asset_data');
        $assetData = json_decode($assetData, true);
        Mage::helper('lpms')->saveAssetData($assetData, $pageId);
    }

    /**
     * _handleImages
     *
     * @param array         $data
     * @param Varien_Object $model
     *
     * @return SDM_Lpms_Adminhtml_Cms_PageController
     */
    protected function _handleImages(&$data, $model)
    {
        $filename = 'hero_image';
        $media = Mage::getBaseDir('media');
        $path = DS . "hero_images" . DS;

        if (isset($_FILES[$filename]['name']) && $_FILES[$filename]['name'] != '') {
            // First delete any existing image
            $oldFile = $model->getData($filename);
            if (isset($oldFile) && !empty($oldFile)) {
                unlink($media . $oldFile);
            }

            // Now, handle the upload process for the image
            $uploader = new Varien_File_Uploader($filename);
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $newName = hash('md5', rand().microtime().rand().$_FILES[$filename]['name']);
            $nameSplit = explode('.', $_FILES[$filename]['name']);
            $newName .= '.'.end($nameSplit);
            $uploader->save($media.$path, $newName);

            $data[$filename] = $path.$newName;
        } else {
            if (isset($data[$filename]['delete']) && $data[$filename]['delete'] == 1) {
                $url = $model->getData($filename);
                if (isset($url) && !empty($url)) {
                    unlink(Mage::getBaseDir('media') . $url);
                }
                $data[$filename] = '';
            } else {
                // In edit mode when user did nothing then you must
                // remove element from data so magento will ignore field
                unset($data[$filename]);
            }
        }

        return $this;
    }
}
