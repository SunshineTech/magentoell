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
 * Calendar admin controller
 */
class SDM_Calendar_Adminhtml_Sdm_Calendar_CalendarController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/cms/sdm_calendar/calendar');
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
     * Calendar grid list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('Manage Calendars'), $this->__('Manage Calendars'))
            ->_title($this->__('Manage Calendars'))
            ->renderLayout();
    }

    /**
     * Edit calendar
     *
     * @return void
     */
    public function editAction()
    {
        $model = Mage::getModel('sdm_calendar/calendar')
            ->load($this->getRequest()->getParam('id'));
        if ($model->getId()) {
            Mage::register('calendar_data', $model);
            $this->_initAction()
                ->_addBreadcrumb($this->__('Edit Calendar'), $this->__('Edit Calendar'))
                ->_title($this->__('Edit Calendar'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('sdm_calendar/adminhtml_calendar_edit'))
                ->_addLeft($this->getLayout()->createBlock('sdm_calendar/adminhtml_calendar_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sdm_calendar')->__('Calendar does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Create new calendar
     *
     * @return void
     */
    public function newAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('New Calendar'), $this->__('New Calendar'))
            ->_title($this->__('New Calendar'));
        $model = Mage::getModel('sdm_calendar/calendar')
            ->load($this->getRequest()->getParam('id'));
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('calendar_data', $model);
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sdm_calendar/adminhtml_calendar_edit'))
            ->_addLeft($this->getLayout()->createBlock('sdm_calendar/adminhtml_calendar_edit_tabs'));
        $this->renderLayout();
    }

    /**
     * Save a calendar
     *
     * @return void
     */
    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            try {
                $model = Mage::getModel('sdm_calendar/calendar')
                    ->addData($postData)
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sdm_calendar')->__('Calendar was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setCalendarData(false);
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
     * Process delete calendar request
     *
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id > 0) {
            try {
                $model = Mage::getModel('sdm_calendar/calendar');
                $model->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Calendar was successfully deleted'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        $this->_redirect('*/*/');
    }
}
