<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Model_Mysql4_Message extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('amperm/message', 'message_id');
    }
}