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
 * Channel controller
 */
class SDM_YoutubeFeed_Adminhtml_Sdm_Youtubefeed_ChannelController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/cms/sdm_youtubefeed/channel');
    }

    /**
     * Preparing layout for output
     *
     * @return SDM_YoutubeFeed_Adminhtml_SDM_YoutubeFeed_ChannelController
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
     * Channel grid list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('Manage Channels'), $this->__('Manage Channels'))
            ->_title($this->__('Manage Channels'))
            ->renderLayout();
    }

    /**
     * Edit channel
     *
     * @return void
     */
    public function editAction()
    {
        $model = Mage::getModel('sdm_youtubefeed/channel')
            ->load($this->getRequest()->getParam('id'));
        if ($model->getId()) {
            Mage::register('channel_data', $model);
            $this->_initAction()
                ->_addBreadcrumb($this->__('Edit Channel'), $this->__('Edit Channel'))
                ->_title($this->__('Edit Channel'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_channel_edit'))
                ->_addLeft($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_channel_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('sdm_youtubefeed')->__('Channel does not exist.')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * Create new channel
     *
     * @return void
     */
    public function newAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('New Channel'), $this->__('New Channel'))
            ->_title($this->__('New Channel'));
        $model = Mage::getModel('sdm_youtubefeed/channel')
            ->load($this->getRequest()->getParam('id'));
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('channel_data', $model);
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_channel_edit'))
            ->_addLeft($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_channel_edit_tabs'));
        $this->renderLayout();
    }

    /**
     * Save a channel
     *
     * @return void
     */
    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            try {
                $model = Mage::getModel('sdm_youtubefeed/channel')
                    ->addData($postData)
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('sdm_youtubefeed')->__('Channel was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setChannelData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setChannelData($postData);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Process delete channel request
     *
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id > 0) {
            try {
                $model = Mage::getModel('sdm_youtubefeed/channel');
                $model->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Channel was successfully deleted')
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Process mass delete request
     *
     * @return void
     */
    public function massDeleteAction()
    {
        try {
            $ids = $this->getRequest()->getPost('ids', array());
            foreach ($ids as $id) {
                  Mage::getModel('sdm_youtubefeed/channel')
                    ->setId($id)
                    ->delete();
            }
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('adminhtml')->__('Channel(s) was successfully removed'));
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
            'channels.csv',
            $this->getLayout()
                ->createBlock('sdm_youtubefeed/adminhtml_channel_grid')
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
            'channels.xml',
            $this->getLayout()
                ->createBlock('sdm_youtubefeed/adminhtml_channel_grid')
                ->getExcelFile()
        );
    }
}
