<?php
/**
 * Separation Degrees One
 *
 * Handles rendering the instructions page
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Instructions
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_Instructions_IndexController class
 */
class SDM_Instructions_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Controller to render designer pages
     *
     * @return $this
     */
    public function indexAction()
    {
        $this->getLayout()->getUpdate()
            ->addHandle('default')
            ->addHandle('instructions_page');

        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->getLayout()->getBlock('head')->setTitle('Instructions');
        $this->renderLayout();

        return $this;
    }
}
