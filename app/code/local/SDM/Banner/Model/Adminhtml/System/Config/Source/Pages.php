<?php
/**
 * Separation Degrees One
 *
 * Banner Ads
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Banner
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */
/**
 * SDM_Banner_Model_Adminhtml_System_Config_Source_Pages class
 */
class SDM_Banner_Model_Adminhtml_System_Config_Source_Pages
{
    protected $_options;

    /**
     * Option array of pages
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $collection = Mage::getResourceModel('cms/page_collection');
            if ($collection && $collection->count()>0) {
                foreach ($collection as $item) {
                    $this->_options[] = array(
                        'value'=> $item->getData('page_id'),
                        'label'=> $item->getData('title'),
                        'content'=> $item->getData('content'));
                }
            }
        }
        return $this->_options;
    }

    /**
     * List of custom page
     *
     * @return $_options
     */
    public function pageOptionArray()
    {
        if (!$this->_options) {
            $pageCollection = Mage::getResourceModel('slider/layouts_collection');
            if (!empty($pageCollection)) {
                foreach ($pageCollection as $page) {
                    $this->_options[] = array(
                        'value'=> $page['layout_id'],
                        'label'=> $page['title'],
                        'content'=> $page['layout_update_xml']);
                }
            }
        }
        return $this->_options;
    }
}
