<?php
 /**
 * Separation Degrees One
 *
 * Ellison's Mage_Sales customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Customer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http:__www.separationdegrees.com)
 */

/**
 * SDM_Designer_PageController class
 */
class SDM_Designer_PageController extends Mage_Core_Controller_Front_Action
{
    /**
     * Controller to render designer pages
     *
     * @return $this
     */
    public function viewAction()
    {
        // Get the page's designer
        $designer = Mage::helper('sdm_designer')->getCurrentDesigner();
        if (empty($designer)) {
            return $this->_forward('defaultNoRoute');
        }

        $this->getLayout()->getUpdate()
            ->addHandle('default')
            ->addHandle('designer')
            ->addHandle('designer_page');

        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->getLayout()->getBlock('head')->setTitle($designer->getName());
        $this->renderLayout();

        return $this;
    }
}
