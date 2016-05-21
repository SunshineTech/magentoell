<?php
/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Gmapstrlocator_Adminhtml_GmapstrlocatorController class
 */
class SDM_Gmapstrlocator_Adminhtml_GmapstrlocatorController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/cms/gmapstrlocator');
    }

    /**
     * Default for all actions
     *
     * @return SDM_Gmapstrlocator_Adminhtml_GmapstrlocatorController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('gmapstrlocator/items')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Store Locator Manager'),
                Mage::helper('adminhtml')->__('Store Locator Manager')
            );
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        return $this;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Edit action
     *
     * @return void
     */
    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('gmapstrlocator/location')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('gmapstrlocator_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('gmapstrlocator/items');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Store Locator Manager'),
                Mage::helper('adminhtml')->__('Store Locator Manager')
            );
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Store Locator Manager'),
                Mage::helper('adminhtml')->__('Store Locator Manager')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            //$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);

            $this->_addContent(
                $this->getLayout()->createBlock('gmapstrlocator/adminhtml_gmapstrlocator_edit')
            )
                ->_addLeft(
                $this->getLayout()->createBlock('gmapstrlocator/adminhtml_gmapstrlocator_edit_tabs')
                );

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('gmapstrlocator')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * New action
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save action
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                if (isset($data['image'])) {
                    if (isset($data['image']['delete'])) {
                        $data['image'] = null;
                    } elseif (isset($data['image']['value'])) {
                        $data['image'] = $data['image']['value'];
                    }
                }
                if (isset($_FILES['image'])) {
                    if (isset($_FILES['image']['name'])
                        && !empty($_FILES['image']['name'])
                    ) {
                        try {
                            $uploader = new Varien_File_Uploader('image');
                            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                            $uploader->setAllowRenameFiles(false);
                            $uploader->setFilesDispersion(false);
                            $fileName = $_FILES['image']['name'];
                            $filePath = Mage::getBaseDir('media') . DS
                                . SDM_Gmapstrlocator_Model_Location::IMAGE_FOLDER . DS;
                            $result   = $uploader->save($filePath, $fileName);
                            $data['image'] = SDM_Gmapstrlocator_Model_Location::IMAGE_FOLDER . DS . $result['file'];
                        } catch (Exception $e) {
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                            return;
                        }
                    }
                }
                $model = Mage::getModel('gmapstrlocator/location');
                $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));
                $model->save();

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('gmapstrlocator')->__('Store was successfully saved'));
                Mage::getSingleton('adminhtml/session')
                    ->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')
            ->addError(Mage::helper('gmapstrlocator')->__('Unable to find Store to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('gmapstrlocator/location');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Store was successfully deleted')
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
     * Mass delete action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $gmapstrlocatorIds = $this->getRequest()->getParam('gmapstrlocator');
        if (!is_array($gmapstrlocatorIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Store(s)'));
        } else {
            try {
                foreach ($gmapstrlocatorIds as $gmapstrlocatorId) {
                    $gmapstrlocator = Mage::getModel('gmapstrlocator/location')->load($gmapstrlocatorId);
                    $gmapstrlocator->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($gmapstrlocatorIds)
                        )
                    );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Mass status action
     *
     * @return void
     */
    public function massStatusAction()
    {
        $gmapstrlocatorIds = $this->getRequest()->getParam('gmapstrlocator');
        if (!is_array($gmapstrlocatorIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Store(s)'));
        } else {
            try {
                foreach ($gmapstrlocatorIds as $gmapstrlocatorId) {
                    Mage::getSingleton('gmapstrlocator/location')
                    ->load($gmapstrlocatorId)
                    ->setStatus($this->getRequest()->getParam('status'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($gmapstrlocatorIds))
                    );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Export csv action
     *
     * @return void
     */
    public function exportCsvAction()
    {
        $fileName   = 'gmapstrlocator.csv';
        $content    = $this->getLayout()
            ->createBlock('gmapstrlocator/adminhtml_gmapstrlocator_grid')
            ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export xml action
     *
     * @return void
     */
    public function exportXmlAction()
    {
        $fileName   = 'gmapstrlocator.xml';
        $content    = $this->getLayout()
            ->createBlock('gmapstrlocator/adminhtml_gmapstrlocator_grid')
            ->getXml();
        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Send file
     *
     * @param string $fileName
     * @param string $content
     * @param string $contentType
     *
     * @return void
     */
    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}
