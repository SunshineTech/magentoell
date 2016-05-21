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
 * SDM_Lpms_Model_Asset_Image class
 */
class SDM_Lpms_Model_Asset_Image
    extends SDM_Lpms_Model_Abstract
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lpms/asset_image');
    }

    /**
     * Get the field names submitted through the frontend
     * @return array
     */
    public function getFrontendFields()
    {
        return array(
            'id',
            'file',
            'image_url',
            'image_alt',
            'image_href',
            'start_date',
            'end_date',
            'is_active',
            'week_days',
            'store_ids'
        );
    }
}
