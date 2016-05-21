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
  * SDM_Wordpress_Model_Post
  */
class SDM_Wordpress_Model_Post extends Fishpig_Wordpress_Model_Post
{
    /**
     * Retrieve the read more anchor text
     *
     * @return string|false
     */
    protected function _getTeaserAnchor()
    {
        // Allows translation
        return stripslashes(Mage::helper('wordpress')->__('Continue reading &raquo;'));
    }

    /**
     * Retrieve the post teaser
     * This is the data from the post_content field upto to the MORE_TAG
     *
     * @return string
     */
    protected function _getPostTeaser($includeSuffix = true)
    {
        return Mage::helper('sdm_core/wordpress')->truncatePost(
            $this->getPostContent('excerpt'),
            $this->getPermalink()
        );
    }
}
