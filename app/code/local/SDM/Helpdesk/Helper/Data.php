<?php
/**
 * Separation Degrees One
 *
 * Modifications to Mirasvit_Helpdesk Extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Helpdesk
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * Rewriting Mirasvit_Helpdesk_Helper_Data
 */
class SDM_Helpdesk_Helper_Data extends Mirasvit_Helpdesk_Helper_Data
{

    /**
     * Array of website names
     * @var null|array
     */
    protected $_websiteNames = null;

    /**
     * Make customer array include website info
     *
     * @param  boolean $q
     * @param  boolean $customerId
     * @param  boolean $addressId
     * @return array
     */
    public function getCustomerArray($q = false, $customerId = false, $addressId = false)
    {
        $firstnameId = Mage::getModel('eav/entity_attribute')->loadByCode(1, 'firstname')->getId();
        $lastnameId = Mage::getModel('eav/entity_attribute')->loadByCode(1, 'lastname')->getId();

        $collection = Mage::getModel('customer/customer')->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->getSelect()->limit(20);

        if ($q) {
            $resource = Mage::getSingleton('core/resource');
            $collection->getSelect()
                ->joinLeft(
                    array('varchar1' => $resource->getTableName('customer/entity').'_varchar'),
                    'e.entity_id = varchar1.entity_id and varchar1.attribute_id = '.$firstnameId,
                    array('firstname' => 'varchar1.value')
                )
                ->joinLeft(
                    array('varchar2' => $resource->getTableName('customer/entity').'_varchar'),
                    'e.entity_id = varchar2.entity_id and varchar2.attribute_id = '.$lastnameId,
                    array('lastname' => 'varchar2.value')
                )->joinLeft(
                    array('orders' => $resource->getTableName('sales/order')),
                    'e.entity_id = orders.customer_id',
                    array('order' => 'orders.increment_id')
                )->group('e.entity_id');
            $search = Mage::getModel('helpdesk/search');
            $search->setSearchableCollection($collection);
            $search->setSearchableAttributes(array(
                'e.entity_id' => 0,
                'e.email' => 0,
                'firstname' => 0,
                'lastname' => 0,
                'order' => 0,
                'website_id' => 0
            ));
            $search->setPrimaryKey('entity_id');
            $search->joinMatched($q, $collection, 'e.entity_id');
        }

        if ($customerId !== false) {
            $collection->addFieldToFilter('entity_id', $customerId);
        }

        $result = array();
        /**
         * @var Mage_Customer_Model_Customer $customer
         */
        foreach ($collection as $customer) {
            $result[] = array(
                'id' => $customer->getId(),
                'name' => $customer->getFirstname().' '.$customer->getLastname().
                            ' ['.$this->_getWebsiteName($customer->getWebsiteId()).'] ('.
                            $customer->getEmail().')',
                'email' => $customer->getEmail()
            );
        }

        
        //unregstered search
        $collection = Mage::getModel('sales/order_address')->getCollection();
        $collection
            ->getSelect()
            ->group('email')
            ->limit(20);
        if ($q) {
            $search = Mage::getModel('helpdesk/search');
            $search->setSearchableCollection($collection);
            $search->setSearchableAttributes(array(
                'email' => 0,
                'firstname' => 0,
                'lastname' => 0,
            ));
            $search->setPrimaryKey('entity_id');
            $search->joinMatched($q, $collection, 'main_table.entity_id');
        }
        if ($addressId !== false) {
            $collection->addFieldToFilter('main_table.entity_id', $addressId);
        }

        foreach ($collection as $address) {
            if (!$address->getEmail()) {
                continue;
            }
            $result[] = array(
                'id' => 'address_'.$address->getId(),
                'order_id' => $address->getOrderId(),
                'name' => $address->getFirstname().' '.$address->getLastname().
                          ' [Unregistered] ('.$address->getEmail().')',
                'email' => $address->getEmail()
            );
        }

        return $result;
    }

    /**
     * Add website to customer array
     *
     * @param  string $q
     * @return array
     */
    public function findCustomer($q)
    {
        $customers = $this->getCustomerArray($q);
        foreach ($customers as $key => $customer) {
            $customerId = false;
            if (isset($customer['id'])) {
                $customerId = (int) $customer['id'];
            }
            $orders = $this->getOrderArray($customer['email'], $customerId);
            array_unshift($orders, array('id' => 0, 'name' => $this->__('Unassigned')));
            $customers[$key]['orders'] = $orders;
        }

        return $customers;
    }

    /**
     * Get a website's name from it's ID
     *
     * @param  int $id
     * @return string
     */
    public function _getWebsiteName($id)
    {
        if ($this->_websiteNames === null) {
            $this->_websiteNames = array();
            foreach (Mage::app()->getWebsites() as $website) {
                $this->_websiteNames[$website->getId()] = $website->getName();
            }
        }
        return isset($this->_websiteNames[$id]) ? $this->_websiteNames[$id] : 'Unknown Site';
    }
}
