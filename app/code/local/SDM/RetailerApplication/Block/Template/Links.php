<?php
/**
 * Separation Degrees One
 *
 * Manages the retailer application
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_RetailerApplication
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_RetailerApplication_Block_Template_Links class
 */
class SDM_RetailerApplication_Block_Template_Links extends Mage_Page_Block_Template_Links
{
    /**
     * Add action we can call from layout to check retailer application
     *
     * @return $this
     */
    public function checkRetailerApplication()
    {
        $applicationLabel = Mage::helper('retailerapplication')->getApplicationLabelForHeader();
        if (!empty($applicationLabel)) {
            // Let the parent know we need an application
            $this->getParentBlock()->setData('need_application', true);

            // Remove checkout link
            $this->removeLinkByLabel('Checkout');

            // Only way I could figure out for hiding the wishlist link
            if ($this->getChild('wishlist_link')) {
                $this->getChild('wishlist_link')->setTemplate(null);
            }

            // Add link for application
            $this->addLink(
                $applicationLabel,
                '/retailerapplication/application/view',
                $applicationLabel,
                false,
                array(),
                20,
                array('class' => 'retailer-application')
            );
        }
    }

    /**
     * Add ability to remove a link based off its label
     *
     * @param  string $label
     * @return $this
     */
    public function removeLinkByLabel($label)
    {
        foreach ($this->_links as $k => $v) {
            if (strtolower(trim($v->getLabel())) == strtolower(trim($label))) {
                unset($this->_links[$k]);
            }
        }
        return $this;
    }
}
