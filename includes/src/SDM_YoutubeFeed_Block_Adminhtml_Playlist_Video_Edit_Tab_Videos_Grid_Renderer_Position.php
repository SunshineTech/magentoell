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
 * Renderer for position field in edit videos grid
 */
class SDM_YoutubeFeed_Block_Adminhtml_Playlist_Video_Edit_Tab_Videos_Grid_Renderer_Position
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders an editable text field
     *
     * @param  Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        return '<input type="text" value="' . $row->getPosition()
            . '" name="video_position[' . $row->getVideoId()
            . ']" style="width: 50px; text-align: right;" />';
    }
}
