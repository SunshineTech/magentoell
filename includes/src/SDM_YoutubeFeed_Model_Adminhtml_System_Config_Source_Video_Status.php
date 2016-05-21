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
 * SDM_YoutubeFeed_Model_Adminhtml_System_Config_Source_Video_Status class
 */
class SDM_YoutubeFeed_Model_Adminhtml_System_Config_Source_Video_Status
{
    /**
     * System config options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => SDM_YoutubeFeed_Model_Video::STATUS_DISABLED,
                'label' => Mage::helper('sdm_youtubefeed')->__('Disabled'),
            ),
            array(
                'value' => SDM_YoutubeFeed_Model_Video::STATUS_ENABLED,
                'label' => Mage::helper('sdm_youtubefeed')->__('Enabled'),
            ),
        );
    }
}
