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
 * Renders the available cell in the admin grid
 */
class SDM_SavedQuote_Block_Adminhtml_Savedquote_Grid_Renderer_Available
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Cell renderer
     *
     * @param SDM_SavedQuote_Model_Savedquote|Varien_Object $object
     *
     * @return string
     */
    public function render(Varien_Object $object)
    {
        if ($object->getIsActive() != SDM_SavedQuote_Helper_Data::PREORDER_PENDING_FLAG
        ) {
            return $this->__('--');
        }
        foreach ($object->getItemCollection() as $item) {
            if (!$item->canBePurchased()) {
                return $this->__('No');
            }
        }
        return $this->__('Yes');
    }
}
