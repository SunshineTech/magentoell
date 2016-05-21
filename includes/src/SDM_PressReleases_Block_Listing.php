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
 * SDM_PressReleases_Block_Listing class
 */
class SDM_PressReleases_Block_Listing extends Mage_Core_Block_Template
{
    const DEFAULT_LIMIT = 15;
    const PARAM_PAGE    = 'p';
    const PARAM_LIMIT   = 'limit';

    protected $_listingBlock = null;

    /**
     * Get listing block
     *
     * @return mixed
     */
    public function getListingBlock()
    {
        if ($this->_listingBlock === null) {
            $this->_listingBlock = Mage::helper('rendercollection')
                ->initNewListing(
                    Mage::helper('sdm_pressreleases')->getPressReleaseArticles(),
                    'news',
                    $this->getToolbarOptions()
                );
        }
        return $this->_listingBlock;
    }

    /**
     * List of settings for the toolbar/pager
     *
     * @return Varien_Object
     */
    public function getToolbarOptions()
    {
        return new Varien_Object(array(
            'pager_options' => array(
                'limit'           => $this->getCurrentLimit(),
                'page_var_name'   => self::PARAM_PAGE,
                'limit_var_name'  => self::PARAM_LIMIT,
                'available_limit' => array(
                    15 => 15,
                    30 => 30,
                    45 => 45,
                )
            )
        ));
    }

    /**
     * Current limit
     *
     * @return integer
     */
    public function getCurrentLimit()
    {
        return $this->getRequest()->getParam(self::PARAM_LIMIT, false)
            ? $this->getRequest()->getParam(self::PARAM_LIMIT)
            : self::DEFAULT_LIMIT;
    }
}
