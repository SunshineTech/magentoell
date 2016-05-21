<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom Landing Page Management System (LPMS).
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Lpms
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Lpms_Model_Abstract class
 */
abstract class SDM_Lpms_Model_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Helper Instance
     * @var SDM_Lpms_Helper_Data
     */
    protected $_helper = null;

    /**
     * Make sure we can show this asset
     * @return boolean
     */
    public function canShowAsset()
    {
        // Check enabled
        if (!$this->getIsActive()) {
            return false;
        }

        // Check weekday
        $weekDay = strtolower(substr(date("l"), 0, 2));
        if (!in_array($weekDay, $this->getWeekDays())) {
            return false;
        }

        // Check store
        $stores = $this->getStoreIds();
        $storeId = (string)Mage::app()->getStore()->getId();
        if (!empty($stores) && !in_array("0", $stores) && !in_array($storeId, $stores)) {
            return false;
        }

        // Check start date
        $now = Mage::getModel('core/date')->timestamp(time());
        $start = $this->getStartDate();
        if (!empty($start) && strtotime($start) > $now) {
            return false;
        }

        // Check end date
        $end = $this->getEndDate();
        if (!empty($end) && strtotime($end) < $now) {
            return false;
        }

        return true;
    }

    /**
     * Returns an array of each LPMS Asset Type
     * @return array
     */
    public function getTypes()
    {
        return array(
            'freeform'      => $this->_getHelper()->__('Freeform'),
            'search'        => $this->_getHelper()->__('Search'),
            'image'         => $this->_getHelper()->__('Images'),
            'products'      => $this->_getHelper()->__('Products')
        );
    }

    /**
     * Returns the LPMS helper
     * @return SDM_Lpms_Helper_Data
     */
    protected function _getHelper()
    {
        if ($this->_helper === null) {
            $this->_helper = Mage::helper('lpms');
        }
        return $this->_helper;
    }

    /**
     * Handles converting week days from array to string
     * @param array|string $weekDays
     * @return $this
     */
    public function setWeekDays($weekDays)
    {
        if (is_array($weekDays)) {
            $weekDays = implode(',', array_map('trim', $weekDays));
        }
        $this->setData('week_days', $weekDays);
        return $this;
    }

    /**
     * Handles converting week days from string to array
     * @return array
     */
    public function getWeekDays()
    {
        $weekDays = explode(',', $this->getData('week_days'));
        return array_map('trim', $weekDays);
    }

    /**
     * Looksup and returns the store ids as an array
     * @return array
     */
    public function getStoreIds()
    {
        if ($this->getId() && !is_array($this->getData('store_ids'))) {
            $stores = $this->getResource()->lookupStoreIds($this->getId());
            $this->setData('store_ids', $stores);
        }
        return $this->getData('store_ids');
    }

    /**
     * Makes sure we save a valid is_active value
     * @param bool $isActive isActive
     * @return SDM_Lpms_Model_Abstract
     */
    public function setIsActive($isActive)
    {
        $this->setData('is_active', $isActive ? 1 : 0);
        return $this;
    }

    /**
     * Initialize the object with an array of data.
     * If an id is provided in the array, an existing object will be loaded
     * and the remaining data will overwrite it.
     * @param  array   $data
     * @param  integer $pageId
     * @return $this
     */
    public function initWithData($data, $pageId = null)
    {
        // Make sure we have all our frontend fields in this array
        $data = $this->_checkFrontendFields($data);

        // If this is NOT a new object, then load the old copy
        $id = $data['id'];
        unset($data['id']);
        if (!empty($id) && strpos($id, 'new') === false && (int)$id == $id) {
            $this->load($id);
        }

        // Loop through our data and set it to the object
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            switch ($key) {
                case 'week_days':
                    $this->setWeekDays($value);
                    break;
                case 'store_ids':
                    $this->setStoreIds($value);
                    break;
                case 'is_active':
                    $this->setIsActive($value);
                    break;
                case 'file':
                    $this->saveImageFile($value, $pageId);
                    break;
                case 'type':
                    $this->setType($value);
                    break;
                default:
                    $this->setData($key, $value);
                    break;
            }
        }
        // Special changes for certain types
        switch ($this->getData('type')) {
            case 'products':
                $content = $this->getData('content');
                $content = str_replace("\n", ",", $content);
                $content = explode(",", $content);
                $content = array_map('trim', $content);
                $content = implode(", ", array_filter($content));
                $this->setData('content', $content);
                break;
            case 'search':
                $content = $this->getData('content');
                $this->setData('content', trim($content));
                break;
        }
        return $this;
    }

    /**
     * Save an image
     *
     * @param string  $fileInputName
     * @param integer $pageId
     *
     * @return void
     */
    public function saveImageFile($fileInputName, $pageId)
    {
        if (isset($_FILES[$fileInputName]) && !empty($_FILES[$fileInputName])) {
            $uploader = new Varien_File_Uploader($fileInputName);
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $media = Mage::getBaseDir('media');
            $path = DS . "cms_asset_images" . DS . 'page-' . $pageId . DS;
            $newName = hash('sha256', rand().microtime().rand().$_FILES[$fileInputName]['name']);
            $exploded = explode('.', $_FILES[$fileInputName]['name']);
            $newName .= '.'.end($exploded);

            $uploader->save($media.$path, $newName);

            $this->setData('image_url', $path.$newName);
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getDataForFrontend()
    {
        $data = array();
        foreach ($this->getFrontendFields() as $field) {
            switch ($field) {
                case 'week_days':
                    $data[$field] = $this->getWeekDays();
                    break;
                case 'store_ids':
                    $data[$field] = $this->getStoreIds();
                    break;
                case 'start_date':
                case 'end_date':
                    // Remove the 00:00:00 form the timestamp
                    $data[$field] = trim($this->getData($field));
                    $data[$field] = explode(' ', $data[$field]);
                    $data[$field] = reset($data[$field]);
                    break;
                case 'id':
                    $data[$field] = $this->getId();
                    break;
                case 'asset_images':
                    $data[$field] = $this->getImageAssetData();
                    break;
                default:
                    $data[$field] = $this->getData($field);
                    break;
            }
            if ($data[$field] === null) {
                unset($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Get image data
     *
     * @return array
     */
    public function getImageAssetData()
    {
        $imageAssetData = array();
        $id = $this->getId();
        if (empty($id)) {
            return $imageAssetData;
        }
        $imageAssets = Mage::getModel('lpms/asset_image')
            ->getCollection()
            ->filterByAssetId($id)
            ->sortAssetImages();

        foreach ($imageAssets as $imageAsset) {
            $imageAssetData[] = $imageAsset->getDataForFrontend();
        }

        return $imageAssetData;
    }

    /**
     * Verifies the integrity of data supplied to this object against an
     * array of fields we expect this object to have
     * @param  array $data
     * @return array
     */
    private function _checkFrontendFields($data)
    {
        $checkedData = array();
        foreach ($this->getFrontendFields() as $field) {
            $checkedData[$field] = isset($data[$field]) ? $data[$field] : null;
        }
        return $checkedData;
    }
}
