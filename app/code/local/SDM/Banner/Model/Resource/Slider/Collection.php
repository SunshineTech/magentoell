<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Model_Resource_Slider_Collection class
 */
class SDM_Banner_Model_Resource_Slider_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('slider/slider');
    }

    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @param boolean                   $withAdmin
     *
     * @return Auguria_Contact_Model_Mysql4_Contacts_Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        $this->getSelect()
            ->join(array('wcs' => $this->getTable('slider/stores')), 'main_table.slider_id = wcs.slider_id', array())
            ->where('wcs.store_id in (?) ', $withAdmin ? array(0, $store) : $store);
        return $this;
    }
}
