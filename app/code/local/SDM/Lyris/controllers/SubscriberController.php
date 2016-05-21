<?php
/**
 * Separation Degrees One
 *
 * Lyris Newsletter Management
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lyris
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require Mage::getModuleDir('controllers', 'SDM_Lyris') . DS . 'AccountController.php';

/**
 * Pipes Newsletter routes to our controller
 */
class SDM_Lyris_SubscriberController
    extends SDM_Lyris_AccountController
{
    /**
     * Send to correct action
     *
     * @return void
     */
    public function newAction()
    {
        return parent::saveAction();
    }
}
