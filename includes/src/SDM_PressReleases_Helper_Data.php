<?php
/**
 * Separation Degrees One
 *
 * Press release listing and article rendering
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_PressReleases
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_PressReleases_Helper_Data class
 */
class SDM_PressReleases_Helper_Data
    extends SDM_Core_Helper_Data
{

    protected $_dates = null;
    protected $_articles = null;

    /**
     * Gets all simple products from this designer
     *
     * @return $articles
     */
    public function getPressReleaseArticles()
    {
        if ($this->_articles === null) {
            $currentStore = Mage::app()->getStore()->getStoreId();
            $this->_articles = Mage::getModel('cms/page')
                ->getCollection()
                ->addFieldToFilter('is_active', '1')
                ->addFieldToFilter('type', 'press');
            // Get cms page store table
            $table = Mage::getSingleton('core/resource')
                ->getTableName('cms/page_store');
            $this->_articles->getSelect()
                ->joinLeft(
                    array('cps' => $table),
                    'main_table.page_id = cps.page_id',
                    array()
                )
                ->columns('cps.store_id')
                ->where('cps.store_id in (?)', array(0, $currentStore))
                ->order('publish_time DESC')
                ->group('main_table.page_id');

            $filter = $this->getActiveFilter();
            if ($filter !== false) {
                $this->_articles->addFieldToFilter('publish_time', array(
                    'from' => $filter['from'],
                    'to' => $filter['to'],
                    'date' => true
                ));
            }
        }

        return $this->_articles;
    }

    /**
     * Get active filters
     *
     * @return mixed
     */
    public function getActiveFilter()
    {
        $dates = $this->getPressReleaseDates();
        $filter = $this->getCurrentFilter();
        if (isset($dates[$filter])) {
            return $dates[$filter];
        }
        return false;
    }

    /**
     * get Press articles publish time.
     * @return string
     */
    public function getPressReleaseDates()
    {
        if ($this->_dates === null) {
            $currentStore = Mage::app()->getStore()->getStoreId();
            $collection = Mage::getModel('cms/page')
                ->getCollection()
                ->addFieldToFilter('is_active', '1')
                ->addFieldToFilter('type', 'press');

            // Get cms page store table
            $table = Mage::getSingleton('core/resource')
                ->getTableName('cms/page_store');
            $collection->getSelect()
                ->joinLeft(
                    array('cps' => $table),
                    'main_table.page_id = cps.page_id',
                    array()
                )
                ->columns('cps.store_id')
                ->where('cps.store_id in (?)', array(0, $currentStore))
                ->group('main_table.page_id');

            $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('publish_time')
                ->where('publish_time IS NOT NULL')
                ->order('publish_time ASC');

            foreach ($collection as $row) {
                $time = $row->getPublishTime();
                $date = explode('-', $time);
                $this->_addNewDateFilter($time, $date['0']);
                $this->_addNewDateFilter($time, $date['0'], $date['1']);
            }

            if (!empty($this->_dates)) {
                $reversedDates = array_reverse($this->_dates, true);
                $this->_dates = array();
                foreach ($reversedDates as $year => $filters) {
                    foreach ($filters as $filter) {
                        $this->_dates[$filter['id']] = $filter;
                    }
                }
            }

        }

        return $this->_dates;
    }

    /**
     * New date filter
     *
     * @param string $time
     * @param string $year
     * @param string $month
     *
     * @return SDM_PressReleases_Helper_Data
     */
    protected function _addNewDateFilter($time, $year, $month = false)
    {
        $filterId = $year. (empty($month) ? "" : "-".$month);
        if (isset($this->_dates[$year][$filterId])) {
            return $this;
        }

        $isActive = $this->getCurrentFilter() == $filterId;
        if ($month) {
            if ($isActive) {
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $from = $year."-".$month."-01 00:00:00";
                $to = $year."-".$month."-".$daysInMonth." 23:59:59";
            }
            $label = date('F', strtotime($time));
        } else {
            if ($isActive) {
                $from = $year."-01-01 00:00:00";
                $to = $year."-12-31 23:59:59";
            }
            $label = $year;
        }

        $type = $month ? 'month' : 'year';
        $this->_dates[$year][$filterId] = array(
            'id'      => $filterId,
            'type'    => $type,
            'classes' => $type . ($isActive ? ' active' : ''),
            'url'     => Mage::getUrl('pressreleases')."?filter=".$filterId,
            'label'   => $label,
            'from'    => $isActive ? $from : false,
            'to'      => $isActive ? $to : false
        );

        return $this;
    }

    /**
     * Get current filter
     *
     * @return mixed
     */
    public function getCurrentFilter()
    {
        return Mage::app()->getRequest()->getParam('filter');
    }
}
