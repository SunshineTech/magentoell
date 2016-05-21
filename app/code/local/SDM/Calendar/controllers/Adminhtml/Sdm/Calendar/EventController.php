<?php
/**
 * Separation Degrees One
 *
 * Ellison's Teachers' Planning Calendar
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Calendar
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Event admin controller
 */
class SDM_Calendar_Adminhtml_Sdm_Calendar_EventController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/cms/sdm_calendar/event');
    }

    /**
     * Preparing layout for output
     *
     * @return SDM_YoutubeFeed_Adminhtml_SDM_YoutubeFeed_CalendarController
     */
    protected function _initAction()
    {
        return $this->loadLayout()
            ->_setActiveMenu('cms/sdm_calendar')
            ->_addBreadcrumb($this->__('CMS'), $this->__('CMS'))
            ->_addBreadcrumb($this->__('Calendars'), $this->__('Calendars'))
            ->_title($this->__('Calendars'));
    }

    /**
     * Event grid list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('Manage Events'), $this->__('Manage Events'))
            ->_title($this->__('Manage Events'))
            ->renderLayout();
    }

    /**
     * Edit event
     *
     * @return void
     */
    public function editAction()
    {
        $model = Mage::getModel('sdm_calendar/event')
            ->load($this->getRequest()->getParam('id'));
        if ($model->getId()) {
            Mage::register('event_data', $model);
            $this->_initAction()
                ->_addBreadcrumb($this->__('Edit Event'), $this->__('Edit Event'))
                ->_title($this->__('Edit Event'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('sdm_calendar/adminhtml_event_edit'))
                ->_addLeft($this->getLayout()->createBlock('sdm_calendar/adminhtml_event_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('sdm_calendar')->__('Event does not exist.')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * Create new event
     *
     * @return void
     */
    public function newAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('New Event'), $this->__('New Event'))
            ->_title($this->__('New Event'));
        $model = Mage::getModel('sdm_calendar/event')
            ->load($this->getRequest()->getParam('id'));
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('event_data', $model);
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sdm_calendar/adminhtml_event_edit'))
            ->_addLeft($this->getLayout()->createBlock('sdm_calendar/adminhtml_event_edit_tabs'));
        $this->renderLayout();
    }

    /**
     * Save an event
     *
     * @return void
     */
    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            try {
                if (isset($postData['image'])) {
                    if (isset($postData['image']['delete'])) {
                        $postData['image'] = null;
                    } elseif (isset($postData['image']['value'])) {
                        $postData['image'] = $postData['image']['value'];
                    }
                }
                $this->_handleImageUpload($postData);
                $model = Mage::getModel('sdm_calendar/event')
                    ->addData($postData)
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('sdm_calendar')->__('Event was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setEventData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCalendarData($postData);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Do image upload
     *
     * @param array $postData
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function _handleImageUpload(array &$postData)
    {
        if (isset($_FILES['image'])
            && isset($_FILES['image']['name'])
            && !empty($_FILES['image']['name'])
        ) {
            $uploader = new Varien_File_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $fileName = $_FILES['image']['name'];
            $filePath = Mage::getBaseDir('media') . DS . SDM_Calendar_Model_Event::IMAGE_FOLDER . DS;
            $result   = $uploader->save($filePath, $fileName);
            $postData['image'] = SDM_Calendar_Model_Event::IMAGE_FOLDER . DS . $result['file'];
        }
    }

    /**
     * Process delete event request
     *
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id > 0) {
            try {
                $model = Mage::getModel('sdm_calendar/event');
                $model->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Event was successfully deleted')
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        $this->_redirect('*/*/');
    }
}
