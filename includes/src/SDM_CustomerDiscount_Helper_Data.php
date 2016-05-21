<?php
/**
 * Separation Degrees One
 *
 * Implements the customer/retailer discount logic for viewing and obtaining
 * discounts and prices, as wel as managing the customer groups.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CustomerDiscount
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CustomerDiscount_Helper_Data class
 */
class SDM_CustomerDiscount_Helper_Data extends SDM_Core_Helper_Data
{
    /**
     * Log file
     *
     * @var string
     */
    protected $_logFile = 'sdm_customer_discount.log';

    /**
     * Returns a 2-D array ("matrix") of the customer discounts used to render
     * the "matrix" view in admin.
     *
     * @return array
     */
    public function getMatrix()
    {
        $matrix = array();
        $groups = array();
        $q1 = "SELECT t.`entity_id` AS 'category_id',t.`name`,g.`customer_group_id`,g.`amount`
            FROM `{$this->getTableName('taxonomy/item')}` AS t
                INNER JOIN `{$this->getTableName('customerdiscount/discountgroup')}` AS g
                    ON t.`entity_id` = g.`category_id`
                INNER JOIN `{$this->getTableName('customer/customer_group')}` AS c
                    ON g.`customer_group_id` = c.`customer_group_id`";

        $q2 = "SELECT g.`customer_group_id`,g.`customer_group_code`
            FROM `{$this->getTableName('customer/customer_group')}` AS g
            WHERE g.`tax_class_id` = 5
            ORDER BY g.`position` ASC";    // Tax class is hardcoded: Wholesale Customer

        $discounts = $this->getConn()->fetchAll($q1);
        $groupData = $this->getConn()->fetchAll($q2);

        foreach ($groupData as $group) {
            $groups[$group['customer_group_id']] = $group['customer_group_code'];
        }

        foreach ($discounts as $discount) {
            $matrix[$discount['name']][$groups[$discount['customer_group_id']]] = $discount['amount'];
        }

        // Sort everything, so the matrix displays properly on frontend
        ksort($matrix);
        foreach ($matrix as $type => $discounts) {
            ksort($matrix[$type]);
        }

        return $matrix;
    }

    /**
     * Returns the customer group ID given customer group code/name
     *
     * @param str $name
     *
     * @return int
     */
    public function getCustomerGroupIdByCode($name)
    {
        $collection = Mage::getModel('customer/group')->getCollection()
            ->addFieldToFilter('customer_group_code', $name);

        return $collection->getFirstItem()->getId();
    }

    /**
     * Returns all of the customer groups
     *
     * @return array
     */
    public function getAllCustomerGroupOptions()
    {
        $options = array();
        $collection = Mage::getModel('customer/group')->getCollection()
            ->addFieldToSelect(array('customer_group_id', 'customer_group_code'));

        foreach ($collection as $customer) {
            $options[$customer->getCustomerGroupId()] = $customer->getCustomerGroupCode();

        }

        return $options;
    }

    /**
     * Get all of the discount categories from the taxonomy model
     *
     * @return array
     */
    public function getAllDiscountCategoryOptions()
    {
        $options = array();
        $collection = Mage::getModel('taxonomy/item')
            ->getCollection()
            ->addFieldToFilter(
                'type',
                SDM_Taxonomy_Model_Attribute_Source_Discountcategory::CODE
            );

        foreach ($collection as $category) {
            $options[$category->getId()] = $category->getName();
        }

        return $options;
    }
}
