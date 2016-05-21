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
 * SDM_Wordpress_Block_Post_List_Pager class file
 */
class SDM_Wordpress_Block_Post_List_Pager extends Fishpig_Wordpress_Block_Post_List_Pager
{
    /**
     * Construct
     *
     * @return null
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("page/html/pager-blog.phtml");
    }

    /**
     * Return the URL for a certain page of the collection
     *
     * @param array $params
     *
     * @return string
     */
    public function getPagerUrl($params = array())
    {
        $pageVarName = $this->getPageVarName();

        $slug = isset($params[$pageVarName])
            ? $pageVarName . '/' . $params[$pageVarName] . '/'
            : '';

        // This URL may have GET parameters
        $url = $this->getUrl('*/*/*', array(
            '_current' => true,
            '_escape' => true,
            '_use_rewrite' => true,
            '_query' => array('___refresh' => null),
        ));

        // GET parameters need to come at the end
        $urlBag = explode('?', $url);
        if (count($urlBag) == 2) {
            return rtrim($urlBag[0], '/') . '/' . trim($slug, '/') . '?' . ltrim($urlBag[1], '/');
        } else {
            return rtrim($url, '/') . '/' . $slug;
        }
    }
}
