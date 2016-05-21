<?php
/**
 * Separation Degrees One
 *
 * A Generic extension to include extension conflict resolutions
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_ConflictResolution
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_ConflictResolution_Helper_Data class
 */
class SDM_ConflictResolution_Helper_Data extends SFC_CyberSource_Helper_Payment
{
    /**
     * Retrieve all payment methods. Since it is unknown what SFC's rewritten
     * method does due to the obfuscation, run SFC's method via parent::
     * and run the Ebizmart's logic.
     *
     * @param mixed $store
     *
     * @return array
     */
    public function getPaymentMethods($store = null)
    {
        $methods = parent::getPaymentMethods($store);

        if (isset($methods['sagepaysuite'])) {
            unset($methods['sagepaysuite']);
        }

        return $methods;
    }
}
