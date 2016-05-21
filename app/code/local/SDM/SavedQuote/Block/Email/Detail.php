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
 * SDM_SavedQuote_Block_Email_Detail class
 */
class SDM_SavedQuote_Block_Email_Detail
    extends Mage_Core_Block_Template
{
    /**
     * Set template if not set already
     *
     * @return SDM_SavedQuote_Block_Email_Detail
     */
    protected function _prepareLayout()
    {
        if (!$this->hasTemplate()) {
            $this->setTemplate('sdm/savedquote/email/detail.phtml');
        }
        return parent::_prepareLayout();
    }
}
