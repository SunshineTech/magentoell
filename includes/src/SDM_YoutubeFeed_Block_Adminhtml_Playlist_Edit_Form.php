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
 * Edit playlist form
 */
class SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Form
extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Build the form
     *
     * @return SDM_YoutubeFeed_Block_Adminhtml_Playlist_Edit_Form
     */
    protected function _prepareForm()
    {
        $this->setForm(new Varien_Data_Form(array(
            'id'            => 'edit_form',
            'action'        => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'        => 'post',
            'enctype'       => 'multipart/form-data',
            'use_container' => true
        )));
        return parent::_prepareForm();
    }
}
