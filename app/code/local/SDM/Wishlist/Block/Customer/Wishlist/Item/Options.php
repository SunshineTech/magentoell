<?php
/**
 * Separation Degrees Media
 *
 * Fixes an issue with wishlists
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Wishlist
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Wishlist_Block_Customer_Wishlist_Item_Options class
 */
class SDM_Wishlist_Block_Customer_Wishlist_Item_Options
    extends Mage_Wishlist_Block_Customer_Wishlist_Item_Options
{
    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        $item = $this->getItem();

        // If $item is it not instance of Mage_Wishlist_Block_Customer_Wishlist_Item_Options
        if ($item) {
            $data = $this->getOptionsRenderCfg($item->getProduct()->getTypeId());

            if (empty($data['template'])) {
                $data = $this->getOptionsRenderCfg('default');
            }
        } else {
              $data = $this->getOptionsRenderCfg('default');
        }

        return empty($data['template']) ? '' : $data['template'];
    }
}
