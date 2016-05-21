<?php
/**
 * Separation Degrees One
 *
 * Updates to Auguria_Sliders
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Auguria
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once Mage::getModuleDir('controllers', 'Auguria_Sliders') . DS
    . 'Adminhtml' . DS . 'SlidersController.php';

/**
 * SDM_Auguria_Adminhtml_SlidersController
 */
class SDM_Auguria_Adminhtml_SlidersController
    extends Auguria_Sliders_Adminhtml_SlidersController
{
    /**
     * Edit action
     *
     * @return null
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('auguria_sliders/sliders')->load($id);
        
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            
            Mage::register('sliders_data', $model);
            
            $this->loadLayout();
            $this->_setActiveMenu('cms/sliders');
            
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item News'),
                Mage::helper('adminhtml')->__('Item News')
            );
            
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            
            $this->_addContent(
                    $this->getLayout()->createBlock('auguria_sliders/adminhtml_sliders_edit')
                )
                ->_addLeft(
                    $this->getLayout()->createBlock('auguria_sliders/adminhtml_sliders_edit_tabs')
                );
            
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('auguria_sliders')->__('This slider no longer exists.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save Action
     *
     * @return null
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('auguria_sliders/sliders')->load($id);
            if (!$model->getId() && $id) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('auguria_sliders')->__('This slider no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            
            // Set categories
            if (isset($data['category_ids'])) {
                $categoryIds = array_unique(explode(',', $data['category_ids']));
                $data['category_ids'] = $categoryIds;
            }
            
            // Set new image
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('image');
                     
                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setFilesDispersion(false);
                     
                    // Upload image and copy into product dir
                    $path = Mage::getBaseDir('media') . DS . 'auguria' . DS . 'sliders' .DS;
                    $fileName = $_FILES['image']['name'];
                    $uploader->save($path, $fileName);
                     
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
                 
                //this way the name is saved in DB
                $data['image'] = 'auguria/sliders/'.$_FILES['image']['name'];
            } elseif (isset($data['image']['delete'])) {
                // Delete old image
                $image = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $model->getImage();
                if (file_exists($image)) {
                    unlink($image);
                }
                $data['image'] = '';
            } elseif (isset($data['image'])) {
                // Remove null values from data
                unset($data['image']);
            }


            // Set new image_mobile
            if (isset($_FILES['image_mobile']['name']) && $_FILES['image_mobile']['name'] != '') {
                try {
                    /* Starting upload */
                    $mobileUploader = new Varien_File_Uploader('image_mobile');
                     
                    // Any extention would work
                    $mobileUploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $mobileUploader->setFilesDispersion(false);
                     
                    // Upload image_mobile and copy into product dir
                    $path = Mage::getBaseDir('media') . DS . 'auguria' . DS . 'sliders' .DS;
                    $fileName = $_FILES['image_mobile']['name'];
                    $mobileUploader->save($path, $fileName);
                     
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
                 
                //this way the name is saved in DB
                $data['image_mobile'] = 'auguria/sliders/'.$_FILES['image_mobile']['name'];
            } elseif (isset($data['image_mobile']['delete'])) {
                // Delete old image_mobile
                $image_mobile = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $model->getImage();
                if (file_exists($image_mobile)) {
                    unlink($image_mobile);
                }
                $data['image_mobile'] = '';
            } elseif (isset($data['image_mobile'])) {
                // Remove null values from data
                unset($data['image_mobile']);
            }
            
            // Set all data
            if (is_array($data) && count($data) > 0) {
                foreach ($data as $key => $value) {
                    $model->setData($key, $value);
                }
            }
            
            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('auguria_sliders')->__('The slider has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
}
