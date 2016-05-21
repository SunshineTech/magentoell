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
 * SDM_Taxonomy_Model_Attribute_Source_Machinecompatibility class
 */
class SDM_Taxonomy_Model_Attribute_Source_Machinecompatibility
    extends SDM_Taxonomy_Model_Attribute_Source_Abstract
{
    const CODE = 'machine_compatibility';

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
