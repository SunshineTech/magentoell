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
 * SDM_YoutubeFeed_Model_Adminhtml_System_Config_Source_Channel class
 */
class SDM_YoutubeFeed_Model_Adminhtml_System_Config_Source_Channel
{
    /**
     * System config options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $sort = Mage::getResourceModel('sdm_youtubefeed/channel_collection')
            ->loadData()
            ->toOptionArray();
        $options = array();
        foreach ($sort as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }
}
