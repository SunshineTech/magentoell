<?php
/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Taxonomy_Model_Attribute_Source_Category class
 */
class SDM_Taxonomy_Model_Attribute_Source_Category
    extends SDM_Taxonomy_Model_Attribute_Source_Abstract
{
    const CODE = 'category';

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return self::CODE;
    }
}
