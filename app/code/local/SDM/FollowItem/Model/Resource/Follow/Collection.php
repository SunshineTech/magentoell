<?php
/**
 * Separation Degrees One
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_FollowItem_Model_Resource_Follow_Collection class
 */
class SDM_FollowItem_Model_Resource_Follow_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('followitem/follow');
    }
}
