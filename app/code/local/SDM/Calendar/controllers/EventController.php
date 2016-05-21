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
 * Event view
 */
class SDM_Calendar_EventController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Display event details
     *
     * @return void
     */
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id', false);
        if ($id === false) {
            return $this->norouteAction();
        }
        $model = Mage::getModel('sdm_calendar/event')->load($id);
        if (!$model || !$model->getId()) {
            return $this->norouteAction();
        }
        Mage::register('current_event', $model);
        $this->loadLayout();
        $this->getLayout()
            ->getBlock('head')
            ->setTitle($model->getName());
        $this->renderLayout();
    }

    /**
     * Display event calendar
     *
     * @return void
     */
    public function listAction()
    {
        $id = $this->getRequest()->getParam('id', false);
        if ($id === false) {
            return $this->norouteAction();
        }
        $model = Mage::getModel('sdm_calendar/calendar')->load($id);
        if (!$model || !$model->getId()) {
            return $this->norouteAction();
        }
        Mage::register('current_calendar', $model);
        $this->loadLayout();
        $this->getLayout()
            ->getUpdate()
            ->addHandle('sdm_calendar_event_list');
        $this->getLayout()
            ->getBlock('head')
            ->setTitle($model->getName());
        $this->renderLayout();
    }
}
