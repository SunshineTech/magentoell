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
 * Edit video
 */
class SDM_YoutubeFeed_Block_Adminhtml_Video_Edit
     extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize form
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId   = 'id';
        $this->_blockGroup = 'sdm_youtubefeed';
        $this->_controller = 'adminhtml_video';
        $this->_updateButton('save', 'label', Mage::helper('sdm_youtubefeed')->__('Save Video'));
        $this->_removeButton('delete');
        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('sdm_youtubefeed')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);
        $this->_formScripts[] = <<<SCRIPT
function saveAndContinueEdit(){
    editForm.submit($('edit_form').action+'back/edit/');
}
SCRIPT;
    }

    /**
     * Define header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $video = Mage::registry('video_data');
        if ($video && $video->getId()) {
            return Mage::helper('sdm_youtubefeed')
                ->__("Edit Video '%s'", $this->htmlEscape($video->getId()));

        } else {
            return Mage::helper('sdm_youtubefeed')->__('Add Video');
        }
    }
}
