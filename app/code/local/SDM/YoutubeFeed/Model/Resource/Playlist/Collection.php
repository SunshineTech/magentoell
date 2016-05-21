<?php
/**
 * Separation Degrees Media
 *
 * Embed Youtube Videos and Playlists
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_YoutubeFeed
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * Playlist resource collection model
 */
class SDM_YoutubeFeed_Model_Resource_Playlist_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/playlist');
    }

    /**
     * Fixes pagination in admin playlist grid when grouping
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns('COUNT(DISTINCT ' . implode(', ', $group) . ')');
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_toOptionArray('id', 'name');
        $sort    = array();
        foreach ($options as $data) {
            $name = $data['label'];
            if (!empty($name)) {
                $sort[$name] = $data['value'];
            }
        }
        Mage::helper('core/string')->ksortMultibyte($sort);
        return array_flip($sort);
    }

    public function addStoreFilter($store, $withAdmin = true)
    {

        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        if (!is_array($store)) {
            $store = array($store);
        }

        $this->addFilter('store_id', array('in' => $store));

        return $this;
    }

}
