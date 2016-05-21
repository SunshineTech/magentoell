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

/**
 * View for newsletter sign ups
 */
class SDM_Lyris_Block_Account extends SDM_Lyris_Block_Abstract
{
    /**
     * For action for new account creation
     *
     * @return string
     */
    public function getEditFormAction()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * For action for looking up an existing account
     *
     * @return string
     */
    public function getLookupFormAction()
    {
        return $this->getUrl('*/*/*');
    }

    /**
     * List of available genders
     *
     * @return string[]
     */
    public function getGenders()
    {
        return array(
            'female' => $this->__('Female'),
            'male'   => $this->__('Male'),
        );
    }

    /**
     * List of months in the year
     *
     * @return string[]
     */
    public function getBirthMonths()
    {
        return array(
            'January'   => $this->__('January'),
            'February'  => $this->__('February'),
            'March'     => $this->__('March'),
            'April'     => $this->__('April'),
            'May'       => $this->__('May'),
            'June'      => $this->__('June'),
            'July'      => $this->__('July'),
            'August'    => $this->__('August'),
            'September' => $this->__('September'),
            'October'   => $this->__('October'),
            'November'  => $this->__('November'),
            'December'  => $this->__('December'),
        );
    }

    /**
     * List of days in months
     *
     * @return integer[]
     */
    public function getBirthDays()
    {
        return range(1, 31);
    }

    /**
     * List of years
     *
     * @return integer[]
     */
    public function getBirthYears()
    {
        $todaysYear = Mage::getSingleton('core/date')->gmtDate('Y');
        return array_reverse(range($todaysYear - 100, $todaysYear));
    }

    /**
     * Determines if this is new subscription or editting an existing one
     *
     * @return boolean
     */
    public function isEditMode()
    {
        return (boolean) Mage::registry('sdm_lyris_account_edit');
    }

    /**
     * Get email newsletter thumnails
     *
     * @return string
     */
    public function getPreviousThumbnails()
    {
        $thumbnailCollection = Mage::getModel('sdm_lyris/ads')
            ->getCollection()
            ->addFieldtoSelect('title')
            ->addFieldtoSelect('image')
            ->addFieldToFilter('status', 1);

        return $thumbnailCollection;
    }
}
