<?php
/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_SavedQuote_Model_Resource_Savedquote_Item_Collection class
 */
class SDM_SavedQuote_Model_Resource_Savedquote_Item_Collection
    extends SDM_SavedQuote_Model_Resource_Collection_Abstract
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('savedquote/savedquote_item');
    }
}
