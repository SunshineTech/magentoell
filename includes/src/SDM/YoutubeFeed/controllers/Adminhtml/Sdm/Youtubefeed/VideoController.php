<?php
/**
 * Separation Degrees Media
 *
 * Embed Youtube Videos and Playlists
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_YoutubeFeed
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Admin video controller
 */
class SDM_YoutubeFeed_Adminhtml_Sdm_Youtubefeed_VideoController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/cms/sdm_youtubefeed/video');
    }

    /**
     * Preparing layout for output
     *
     * @return SDM_YoutubeFeed_Adminhtml_SDM_YoutubeFeed_VideoController
     */
    protected function _initAction()
    {
        return $this->loadLayout()
            ->_setActiveMenu('cms/sdm_youtubefeed')
            ->_addBreadcrumb($this->__('CMS'), $this->__('CMS'))
            ->_addBreadcrumb($this->__('Youtube Feed'), $this->__('Youtube Feed'))
            ->_title($this->__('Youtube Feed'));
    }

    /**
     * Video grid list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('Manage Videos'), $this->__('Manage Videos'))
            ->_title($this->__('Manage Videos'))
            ->renderLayout();
    }

    /**
     * Edit video
     *
     * @return void
     */
    public function editAction()
    {
        $model = Mage::getModel('sdm_youtubefeed/video')
            ->load($this->getRequest()->getParam('id'));
        if ($model->getId()) {
            Mage::register('video_data', $model);
            $this->_initAction()
                ->_addBreadcrumb($this->__('Edit Video'), $this->__('Edit Video'))
                ->_title($this->__('Edit Video'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_video_edit'))
                ->_addLeft($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_video_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('sdm_youtubefeed')->__('Video does not exist.')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save a video
     *
     * @return void
     */
    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            try {
                if (isset($postData['designer'])) {
                    $postData['designer'] = implode(',', $postData['designer']);
                }
                $model = Mage::getModel('sdm_youtubefeed/video')
                    ->addData($postData)
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('sdm_youtubefeed')->__('Video was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setVideoData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setVideoData($postData);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Process mass enable request
     *
     * @return void
     */
    public function massEnableAction()
    {
        try {
            $ids = $this->getRequest()->getPost('ids', array());
            foreach ($ids as $id) {
                  Mage::getModel('sdm_youtubefeed/video')
                    ->load($id)
                    ->setStatus(SDM_YoutubeFeed_Model_Video::STATUS_ENABLED)
                    ->save();
            }
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('adminhtml')->__('Videos were successfully enabled'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Process mass disable request
     *
     * @return void
     */
    public function massDisableAction()
    {
        try {
            $ids = $this->getRequest()->getPost('ids', array());
            foreach ($ids as $id) {
                  Mage::getModel('sdm_youtubefeed/video')
                    ->load($id)
                    ->setStatus(SDM_YoutubeFeed_Model_Video::STATUS_DISABLED)
                    ->save();
            }
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('adminhtml')->__('Videos were successfully disabled'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Export grid to CSV format
     *
     * @return void
     */
    public function exportCsvAction()
    {
        $this->_prepareDownloadResponse(
            'videos.csv',
            $this->getLayout()
                ->createBlock('sdm_youtubefeed/adminhtml_video_grid')
                ->getCsvFile()
        );
    }

    /**
     * Export grid to XML format
     *
     * @return void
     */
    public function exportXmlAction()
    {
        $this->_prepareDownloadResponse(
            'videos.xml',
            $this->getLayout()
                ->createBlock('sdm_youtubefeed/adminhtml_video_grid')
                ->getExcelFile()
        );
    }
}
