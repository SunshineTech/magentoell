<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Model_Source_Dealer
{
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 0,
                'label' => Mage::helper('amperm')->__('No')
            )
        );
        $dealers = Mage::helper('amperm')->getSalesPersonList();
        if (!empty($dealers)) {
            foreach ($dealers as $uid => $name) {
                $options[] = array(
                    'value' => $uid,
                    'label' => $name
                );
            }
        }
        return $options;
    }
}
