<?php
/**
 * Separation Degrees Media
 *
 * Wordpress/Fishpig Fixes
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Wordpress
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Wordpress_Block_Menu
 */
class SDM_Wordpress_Block_Menu extends Fishpig_Wordpress_Block_Menu
{
    /**
     * Retrieve the menu title. If the menu object is not set for some reason,
     * attempt to re-load and set it.
     *
     * @return string
     */
    public function getTitle()
    {
        // Should be already set
        if ($this->getMenu()) {
            return $this->getMenu()->getName();
        }

        // If not, try to load it again
        if ($this->getMenuId()) {
            $menu = Mage::getModel('wordpress/menu')->load($this->getMenuId());
            if ($menu->getId()) {
                $this->setMenu($menu);
                return $menu->getName();
            }
        }

        return '';  // Could not get menu name
    }
}
