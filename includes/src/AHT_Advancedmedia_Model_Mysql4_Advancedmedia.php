<?php

class AHT_Advancedmedia_Model_Mysql4_Advancedmedia extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the advancedmedia_id refers to the key field in your database table.
        $this->_init('advancedmedia/advancedmedia', 'advancedmedia_id');
    }

    /**
     * Returns the video ID
     *
     * @param  int $id
     *
     * @return int
     */
    public function getVideoIdByProductId($id)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('advancedmedia/advancedmedia'), 'advancedmedia_id')
            ->where('product_id = ?', $id)
            ->limit(1);
        $videoId = $adapter->fetchOne($select);

        return $videoId;
    }
}