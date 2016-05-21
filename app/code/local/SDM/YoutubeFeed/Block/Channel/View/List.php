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
 * Channel view list block
 */
class SDM_YoutubeFeed_Block_Channel_View_List
    extends SDM_YoutubeFeed_Block_Channel_Abstract
{
    const DEFAULT_LIMIT = 15;
    const PARAM_PAGE    = 'p';
    const PARAM_LIMIT   = 'limit';

    /**
     * Get rendered list of videos
     *
     * @return string
     */
    public function getListHtml()
    {
        return $this->getList()->toHtml();
    }

    /**
     * Get list of videos
     *
     * @return SDM_RenderCollection_Block_Listing
     */
    public function getList()
    {
        return $this->helper('rendercollection')
            ->initNewListing($this->getCollection(), 'video', $this->getToolbarOptions());
    }

    /**
     * List of settings for the toolbar/pager
     *
     * @return Varien_Object
     */
    public function getToolbarOptions()
    {
        return new Varien_Object(array(
            'pager_options' => array(
                'limit'           => $this->getCurrentLimit(),
                'page_var_name'   => self::PARAM_PAGE,
                'limit_var_name'  => self::PARAM_LIMIT,
                'available_limit' => array(
                    15 => 15,
                    30 => 30,
                    45 => 45,
                )
            )
        ));
    }

    /**
     * Current limit
     *
     * @return integer
     */
    public function getCurrentLimit()
    {
        return $this->getRequest()->getParam(self::PARAM_LIMIT, false)
            ? $this->getRequest()->getParam(self::PARAM_LIMIT)
            : self::DEFAULT_LIMIT;
    }

    /**
     * Get a collection of videos for this view
     *
     * @return SDM_YoutubeFeed_Model_Resource_Video_Collection
     */
    public function getCollection()
    {
        $collection = Mage::getModel('sdm_youtubefeed/video')->getCollection()
            ->addFieldToFilter('status', SDM_YoutubeFeed_Model_Video::STATUS_ENABLED)
            ->applyChannelFilter(Mage::registry('current_channel'));
        $playlist = Mage::registry('current_playlist');
        if ($playlist) {
            $collection->applyPlaylistFilter($playlist);
        }
        $search = trim($this->getRequest()->getParam('q'));
        if ($search) {
            $collection->applySearchFilter($search);
        }
        $collection
            ->setOrder('pv.position', Zend_Db_Select::SQL_ASC)
            ->setOrder('main_table.published_at', Zend_Db_Select::SQL_DESC)
            ->setPageSize($this->getCurrentLimit())
            ->setCurPage($this->getRequest()->getParam(self::PARAM_PAGE, false)
                    ? $this->getRequest()->getParam(self::PARAM_PAGE)
                    : 1);
        $collection->getSelect()->group('main_table.id');
        return $collection;
    }
}
