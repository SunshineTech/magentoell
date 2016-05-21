<?php

class AHT_Advancedmedia_Model_Advancedmedia extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('advancedmedia/advancedmedia');
    }

    /**
     * Returns the video object
     *
     * @param  int $id
     *
     * @return AHT_Advancedmedia_Model_Advancedmedia
     */
    public function loadByProductId($productId)
    {
        $videoId = $this->_getResource()->getVideoIdByProductId($productId);
        if ($videoId) {
            return $this->load($videoId);
        }

        return false;
    }
}