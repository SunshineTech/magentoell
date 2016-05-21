<?php
/**
 * Separation Degrees One
 *
 * eCal Lite download request extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_EcalLite
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_EcalLite_Block_Adminhtml_Request_Grid class
 */
class SDM_EcalLite_Block_Adminhtml_Request_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Rows per page for import
     *
     * @var int
     */
    protected $_exportPageSize = 500;

    /**
     * Initialize
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('id');
        $this->setId('ecallite_request_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return SDM_EcalLite_Block_Adminhtml_Request_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('ecallite/request_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return SDM_EcalLite_Block_Adminhtml_Request_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->__('Record #'),
            'type' => 'number',
            'index' => 'id',
        ));
        $this->addColumn('firstname', array(
            'header' => $this->__('First Name'),
            'type' => 'text',
            'index' => 'firstname',
        ));
        $this->addColumn('lastname', array(
            'header' => $this->__('Last Name'),
            'type' => 'text',
            'index' => 'lastname',
        ));
        $this->addColumn('email', array(
            'header' => $this->__('Email'),
            'type' => 'text',
            'index' => 'email',
        ));
        $this->addColumn('code', array(
            'header' => $this->__('Code'),
            'type' => 'text',
            'index' => 'code',
        ));
        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'type' => 'options',
            'index' => 'status',
            'options' => Mage::helper('ecallite')->getStatuses()
        ));
        $this->addColumn('website_id', array(
            'header' => $this->__('Status'),
            'type' => 'options',
            'index' => 'website_id',
            'options' => Mage::helper('ecallite')->getWebsiteArray()
        ));
        $this->addColumn('requested_at', array(
            'header' => $this->__('Requested At (Local Time)'),
            'type' => 'datetime',
            'index' => 'requested_at',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));

        return parent::_prepareColumns();
    }
}
