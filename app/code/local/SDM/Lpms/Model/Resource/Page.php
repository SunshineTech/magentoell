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
 * SDM_Lpms_Model_Resource_Page class
 */
class SDM_Lpms_Model_Resource_Page
    extends Mage_Cms_Model_Resource_Page
{
    /**
     * Process page data before saving
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return Mage_Cms_Model_Resource_Page
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->getType() == 'news' || $object->getType() == 'press') {
            // Clear taxonomy id field
            $object->setData('taxonomy_id', null);
        } elseif ($object->getType() == 'designer') {
            // Set taxonomy_id to null field if it's empty, to avoid a database error
            $taxonomyId = $object->getData('taxonomy_id');
            if (empty($taxonomyId)) {
                $object->setData('taxonomy_id', null);
            }
        } else {
            // Clear all "special" fields
            $object->setData('taxonomy_id', null);
            $object->setData('publish_time', null);
            $object->setData('publish_author', null);
        }

        return parent::_beforeSave($object);
    }
}
