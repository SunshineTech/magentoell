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
 * Channel resource collection model
 */
class SDM_YoutubeFeed_Model_Resource_Channel_Collection
     extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdm_youtubefeed/channel');
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_toOptionArray('id', 'name');
        $sort = array();
        foreach ($options as $data) {
            $name = $data['label'];
            if (!empty($name)) {
                $sort[$name] = $data['value'];
            }
        }
        Mage::helper('core/string')->ksortMultibyte($sort);
        return array_flip($sort);
    }
}
