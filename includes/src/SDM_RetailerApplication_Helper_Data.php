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
 * SDM_RetailerApplication_Helper_Data class
 */
class SDM_RetailerApplication_Helper_Data extends SDM_Core_Helper_Data
{
    const STATUS_PENDING        = 'pend';
    const STATUS_UNDER_REVIEW   = 'unde';
    const STATUS_APPROVED       = 'appr';
    const STATUS_DECLINED       = 'decl';
    const STATUS_SUSPENDED      = 'susp';
    const ONWER_ADDRESS_CODE    = 'owner';
    const SHIPPING_ADDRESS_CODE = 'shipping';
    const BILLING_ADDRESS_CODE  = 'billing';

    /**
     * Log file
     *
     * @var string
     */
    protected $_logFile = 'sdm_retailer_application.log';

    /**
     * All the possible statuses
     * @var array
     */
    protected $_statuses = array(
        self::STATUS_PENDING  => 'Pending',
        self::STATUS_UNDER_REVIEW  => 'Under Review',
        self::STATUS_APPROVED  => 'Approved',
        self::STATUS_DECLINED => 'Declined',
        self::STATUS_SUSPENDED  => 'Suspended'
    );

    /**
     * Sets the uneditable flag customer address entity table
     *
     * @param int $id
     *
     * @return void
     */
    public function makeAddressUneditable($id)
    {
        if (!$id) {
            $this->log("Unable to make address ID $id editable.");
            return;
        } else {
            $q = "UPDATE `{$this->getTableName('customer/address_entity')}` SET `is_editable` = 0 WHERE entity_id = $id";
            $this->getConn('core_write')->query($q);
        }
    }

    /**
     * Gets a singleton of the current logged in user's application
     *
     * @return SDM_RetailerApplication_Model
     */
    public function getCurrentApplication()
    {
        $application = Mage::getSingleton('retailerapplication/application');
        if (!$application->getId()) {
            $application->loadCurrentCustomer();
        }
        return $application;
    }

    /**
     * Checks if we're on ERUS. If not, return false.
     * If so, get an application and return it.
     *
     * @return bool|object
     */
    public function getApplicationLabelForHeader()
    {
        $application = $this->getCurrentApplication();
        if (!empty($application) && $application->getId()) {
            switch ($application->getStatus()) {
                case self::STATUS_PENDING:
                case self::STATUS_DECLINED:
                    return 'Complete Your Retailer Application';
                case self::STATUS_UNDER_REVIEW:
                    return 'Application Under Review';
                case self::STATUS_SUSPENDED:
                    return 'Retailer Account Suspended';
            }
        }
        return false;
    }

    /**
     * Get's the application based off the customerId provided
     *
     * @param  int $customerId
     * @return SDM_RetailerApplication_Model_Application
     */
    public function getApplicationByCustomer($customerId)
    {
        return Mage::getModel('retailerapplication/application')
            ->loadByCustomer($customerId);
    }

    /**
     * Returns all the possible statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        return $this->_statuses;
    }
}
