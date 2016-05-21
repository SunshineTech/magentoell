<?php
/**
 * Separation Degrees One
 *
 * Press release listing and article rendering
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_PressReleases
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_PressReleases_IndexController class
 */
class SDM_PressReleases_IndexController extends Mage_Core_Controller_Front_Action
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
            ->addHandle('pressrelease_listing');

        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        // $this->getLayout()->getBlock('head')->setTitle($designer->getName());
        $this->renderLayout();

        return $this;
    }
}
