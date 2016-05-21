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
 * Router calendar requests to the correct controller
 */
class SDM_Calendar_Controller_Router
    extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * This has to be here for some reason
     *
     * @param mixed $configArea
     * @param mixed $useRouterName
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD)
     */
    public function collectRoutes($configArea, $useRouterName)
    {
        // ¯\_(ツ)_/¯
    }

    /**
     * Match the request
     *
     * @param Zend_Controller_Request_Http $request
     *
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }
        $pathInfo = explode('/', trim($request->getPathInfo(), '/'));
        $path = $pathInfo[0];
        $calendars = Mage::getModel('sdm_calendar/calendar')->getCollection()
            ->addFieldToFilter('url', $path);
        $calendars->join(
            array('calendar_website' => 'sdm_calendar/calendar_website'),
            'main_table.id = calendar_website.calendar_id AND calendar_website.website_id = '
                . Mage::app()->getWebsite()->getId(),
            false
        );
        $calendar = $calendars->getFirstItem();
        if (!$calendar || !$calendar->getId()) {
            return false;
        }
        include_once Mage::getModuleDir('controllers', 'SDM_Calendar') . DS . 'EventController.php';
        $controllerInstance = Mage::getControllerInstance(
            'SDM_Calendar_EventController',
            $request,
            $this->getFront()->getResponse()
        );
        $request->setModuleName('sdm_calendar');
        $request->setRouteName('sdm_calendar');
        $request->setControllerName('event');
        $request->setActionName('list');
        $request->setControllerModule('SDM_Calendar');
        $request->setParam('id', $calendar->getId());
        if (count($pathInfo) == 1) {
            $pathInfo[1] = Mage::getSingleton('core/date')->date('Y');
            $pathInfo[2] = Mage::getSingleton('core/date')->date('m');
        }
        $request->setParam('year', $pathInfo[1]);
        $request->setParam('month', $pathInfo[2]);
        $request->setDispatched(true);
        $controllerInstance->dispatch('list');
        return true;
    }
}
