<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Ads Admin Controller
 */
class SDM_Lyris_Adminhtml_Sdm_Lyris_AdsController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Preparing layout for output
     *
     * @return SDM_Lyris_Adminhtml_Sdm_Lyris_AdsController
     */
    protected function _initAction()
    {
        return $this->loadLayout()
            ->_setActiveMenu('newsletter/sdm_lyris')
            ->_title($this->__('Newsletter'))->_title($this->__('Manage Thumbnails'))
            ->_addBreadcrumb($this->__('Newsletter'), $this->__('Newsletter'))
            ->_addBreadcrumb($this->__('Manage Thumbnails'), $this->__('Manage Thumbnails'));
    }

    /**
     * Ads grid list
     * @return SDM_Lyris_Adminhtml_Sdm_Lyris_AdsController
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Edit Ads
     * @return SDM_Lyris_Adminhtml_Sdm_Lyris_AdsController
     */
    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('sdm_lyris/ads')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('ads_data', $model);

            $this->loadLayout();

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('sdm_lyris/adminhtml_ads_edit'))
                ->_addLeft($this->getLayout()->createBlock('sdm_lyris/adminhtml_ads_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('sdm_lyris')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * Create new ads
     * @return SDM_Lyris_Adminhtml_Sdm_Lyris_AdsController
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save an Ad
     *
     * @return SDM_Lyris_Adminhtml_Sdm_Lyris_AdsController
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function saveAction()
    {
        $imgDataArray = array();
        if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
            try {
                $uploader = new Varien_File_Uploader('image');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $fileName = $_FILES['image']['name'];
                $filePath = Mage::getBaseDir('media').'/'.'sdm_lyris'.'/' ;
                $uploader->save($filePath, $fileName);
                $imgDataArray['image'] = 'sdm_lyris'.'/'.$fileName;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }

        if ($data = $this->getRequest()->getPost()) {
            if (!empty($imgDataArray['image'])) {
                $data['image'] = $imgDataArray['image'];
            } else {
                if (isset($data['image']['delete']) && $data['image']['delete'] == 1) {
                    if ($data['image']['value'] != '') {
                        Mage::helper('sdm_lyris')->deleteDir(Mage::getBaseDir('media')
                            .'/'.str_replace("\\", "/", $data['image']['value']));
                    }
                    $data['image'] = '';
                } else {
                    unset($data['image']);
                }
            }


            $model = Mage::getModel('sdm_lyris/ads');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == null || $model->getUpdateTime() == null) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                try {
                    $model->save($data);
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('sdm_lyris')->__('Item was successfully saved')
                    );
                    Mage::getSingleton('adminhtml/session')->setFormData(false);

                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                        return;
                    }
                    $this->_redirect('*/*/');
                    return;
                } catch (Exception $e) {
                    Mage::logException($e);
                    Mage::log($e->getAsString(), null, 'exception.log', true);
                }

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sdm_lyris')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Process delete Ads request
     * @return SDM_Lyris_Adminhtml_Sdm_Lyris_AdsController
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('sdm_lyris/ads');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Item was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Applying ACL
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/newsletter/sdm_lyris/ads');
    }
}
