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
 * Admin playlist controller
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class SDM_YoutubeFeed_Adminhtml_Sdm_Youtubefeed_PlaylistController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/cms/sdm_youtubefeed/playlist');
    }

    /**
     * Preparing layout for output
     *
     * @return SDM_YoutubeFeed_Adminhtml_SDM_YoutubeFeed_PlaylistController
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
     * Playlist grid list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('Manage Playlists'), $this->__('Manage Playlists'))
            ->_title($this->__('Manage Playlists'))
            ->renderLayout();
    }

    /**
     * Create new playlist
     *
     * @return void
     */
    public function newAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('New Playlist'), $this->__('New Playlist'))
            ->_title($this->__('New Playlist'));
        $model = Mage::getModel('sdm_youtubefeed/playlist')
            ->load($this->getRequest()->getParam('id'));
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('playlist_data', $model);
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_playlist_edit'))
            ->_addLeft($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_playlist_edit_tabs'));
        $this->renderLayout();
    }

    /**
     * Edit playlist
     *
     * @return void
     */
    public function editAction()
    {
        $model = Mage::getModel('sdm_youtubefeed/playlist')
            ->load($this->getRequest()->getParam('id'));

        //Added by mss
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
                $model->setData($data);
        }
        Mage::register('youtube_playlist_data', $model);

        // echo "<pre>";print_r($data);die;
        if ($model->getId()) {
            Mage::register('playlist_data', $model);
            $this->_initAction()
                ->_addBreadcrumb($this->__('Edit Playlist'), $this->__('Edit Playlist'))
                ->_title($this->__('Edit Playlist'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_playlist_edit'))
                ->_addLeft($this->getLayout()->createBlock('sdm_youtubefeed/adminhtml_playlist_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('sdm_youtubefeed')->__('Playlist does not exist.')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save a playlist
     *
     * @return void
     */
    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();

        //Added by Mss
        $postData['websites'] = implode(',', $postData['websites']);
        //echo "<pre>";print_r($postData['websites']);die;
        if ($postData) {
            try {
                $model = Mage::getModel('sdm_youtubefeed/playlist')
                    ->addData($postData)
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();
                if (isset($postData['video_position']) && count($postData['video_position']) > 0) {
                    foreach ($postData['video_position'] as $videoId => $position) {
                        Mage::getResourceModel('sdm_youtubefeed/playlist_video')
                            ->updatePosition($this->getRequest()->getParam('id'), $videoId, $position);
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('sdm_youtubefeed')->__('Playlist was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setPlaylistData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPlaylistData($postData);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Process delete playlist request
     *
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id > 0) {
            try {
                $model = Mage::getModel('sdm_youtubefeed/playlist');
                $model->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Playlist was successfully deleted')
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
            }
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
            'playlists.csv',
            $this->getLayout()
                ->createBlock('sdm_youtubefeed/adminhtml_playlist_grid')
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
            'playlists.xml',
            $this->getLayout()
                ->createBlock('sdm_youtubefeed/adminhtml_playlist_grid')
                ->getExcelFile()
        );
    }

    /**
     * Initialize this playlist
     *
     * @param  string $idFieldName
     * @return SDM_YoutubeFeed_Adminhtml_SDM_YoutubeFeed_PlaylistController
     */
    protected function _initPlaylist($idFieldName = 'id')
    {
        $id = (int) $this->getRequest()->getParam($idFieldName);
        $model = Mage::getModel('sdm_youtubefeed/playlist');
        if ($id) {
            $model->load($id);
        }
        Mage::register('current_playlist', $model);
        return $this;
    }

    /**
     * Playlist videos grid
     *
     * @return void
     */
    public function videosAction()
    {
        $this->_initPlaylist();
        $this->loadLayout();
        $this->renderLayout();
    }
}
