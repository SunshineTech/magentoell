<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Adminhtml_SliderController class
 */
class SDM_Banner_Adminhtml_SliderController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/cms/banner');
    }

    /**
     * Initialize
     *
     * @return SDM_Banner_Adminhtml_SliderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('slider/items')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Items Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );

        return $this;
    }

    /**
     * Render index
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Render edit page
     *
     * @return void
     */
    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('slider/slider')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('slider_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('slider/items');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item News'),
                Mage::helper('adminhtml')->__('Item News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('slider/adminhtml_slider_edit'))
                ->_addLeft($this->getLayout()->createBlock('slider/adminhtml_slider_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('slider')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * Render new
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save post data
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function saveAction()
    {
        $imgDataArray = array();
        if (isset($_FILES['sliderimage']['name']) && $_FILES['sliderimage']['name'] != '') {
            try {
                $uploader = new Varien_File_Uploader('sliderimage');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $fileName = $_FILES['sliderimage']['name'];
                $filePath = Mage::getBaseDir('media').'/'.'sdm_banner'.'/' ;
                $uploader->save($filePath, $fileName);
                $imgDataArray['sliderimage'] = 'sdm_banner'.'/'.$fileName;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        //Mage::log($_FILES['mobileimage']['name']); die;
        $mobileImgDataArray = array();
        if (isset($_FILES['mobileimage']['name']) && $_FILES['mobileimage']['name'] != '') {
            try {
                $uploaderObj = new Varien_File_Uploader('mobileimage');
                $uploaderObj->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploaderObj->setAllowRenameFiles(false);
                $uploaderObj->setFilesDispersion(false);
                $fileName = 'mobile-' . $_FILES['mobileimage']['name'];
                $filePath = Mage::getBaseDir('media').'/'.'sdm_banner'.'/' ;
                $uploaderObj->save($filePath, $fileName);
                $mobileImgDataArray['mobileimage'] = 'sdm_banner'.'/'.$fileName;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }

        if ($data = $this->getRequest()->getPost()) {
            if (!empty($imgDataArray['sliderimage'])) {
                $data['sliderimage'] = $imgDataArray['sliderimage'];
            } else {
                if (isset($data['sliderimage']['delete']) && $data['sliderimage']['delete'] == 1) {
                    if ($data['sliderimage']['value'] != '') {
                        Mage::helper('slider')->deleteDir(Mage::getBaseDir('media').'/'
                            .str_replace("\\", "/", $data['sliderimage']['value']));
                    }
                    $data['sliderimage'] = '';
                } else {
                    unset($data['sliderimage']);
                }
            }

            if (!empty($mobileImgDataArray['mobileimage'])) {
                $data['mobileimage'] = $mobileImgDataArray['mobileimage'];
            } else {
                if (isset($data['mobileimage']['delete']) && $data['mobileimage']['delete'] == 1) {
                    if ($data['mobileimage']['value'] != '') {
                        Mage::helper('slider')->deleteDir(Mage::getBaseDir('media').'/'
                            .str_replace("\\", "/", $data['mobileimage']['value']));
                    }
                    $data['mobileimage'] = '';
                } else {
                    unset($data['mobileimage']);
                }
            }

            if (!empty($data['bannerurl'])) {
                $urlArray = parse_url($data['bannerurl']);
                if ($urlArray['scheme'] == "http" || $urlArray['scheme'] == "https") {
                    $validUrl = filter_var($data['bannerurl'], FILTER_VALIDATE_URL);

                    if (!$validUrl) {
                        unset($data['bannerurl']);
                    }

                }
            }

            $model = Mage::getModel('slider/slider');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
                //->setMobileimage($data['mobileimage'])
                //->save();
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
                        Mage::helper('slider')->__('Item was successfully saved')
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

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('slider')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Process delete
     *
     * @return void
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('slider/slider');

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
     * Process mass delete
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $sliderIds = $this->getRequest()->getParam('slider');
        if (!is_array($sliderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($sliderIds as $sliderId) {
                    $slider = Mage::getModel('slider/slider')->load($sliderId);
                    $slider->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($sliderIds)
                        )
                    );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Process mass status update
     *
     * @return void
     */
    public function massStatusAction()
    {
        $sliderIds = $this->getRequest()->getParam('slider');
        if (!is_array($sliderIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($sliderIds as $sliderId) {
                    Mage::getSingleton('slider/slider')
                    ->load($sliderId)
                    ->setStatus($this->getRequest()->getParam('status'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($sliderIds))
                    );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Process csv export
     *
     * @return void
     */
    public function exportCsvAction()
    {
        $fileName   = 'slider.csv';
        $content    = $this->getLayout()->createBlock('slider/adminhtml_slider_grid')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Process xml export
     *
     * @return void
     */
    public function exportXmlAction()
    {
        $fileName   = 'slider.xml';
        $content    = $this->getLayout()->createBlock('slider/adminhtml_slider_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Send response to download request
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
