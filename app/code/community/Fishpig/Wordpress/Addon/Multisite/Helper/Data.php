<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_Multisite_Helper_Data extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * Determine whether the extension can run
	 *
	 * @return bool
	 */
	public function canRun()
	{
		return Mage::getStoreConfigFlag('wordpress/mu/enabled') && Mage::helper('wordpress')->isEnabled();
	}
	
	/**
	 * Determine whether the current site is the default site
	 *
	 * @return bool
	 */
	public function isDefaultBlog()
	{
		return $this->getBlogId() <= 1;
	}

	/**
	 * Retrieve the current blog ID
	 * If null returned, this is the default site
	 *
	 * @return int|null
	 */
	public function getBlogId()
	{
		return (int)$this->getConfigValue('wordpress/mu/blog_id');	
	}
	
	/**
	 * Retrieve the current site ID
	 *
	 * @return int|null
	 */
	public function getSiteId()
	{
		return 1;
	}
	
	/**
	 * Retrieve a WordPress site option
	 *
	 * @param string $key
	 * @param mixed $default = null
	 * @return mixed
	 */
	public function getWpSiteOption($key, $default = null)
	{
		$helper = Mage::helper('wordpress/database');
		
		if ($helper->isConnected()) {
			$cacheKey = '_wp_site_option_' . $key;
	
			if (!$this->_isCached($cacheKey)) {
				$this->_cache($cacheKey, $default);
				
				try {
					$select = $helper->getReadAdapter()
						->select()
						->from($helper->getTableName('sitemeta'), 'meta_value')
						->where('meta_key = ?', $key)
						->where('site_id=?', $this->getSiteId())
						->limit(1);
	
					$this->_cache($cacheKey, $helper->getReadAdapter()->fetchOne($select));
				}
				catch (Exception $e) {}
			}
			
			return $this->_cached($cacheKey);
		}
		
		return false;
	}
	
	/**
	 * Called before a connection to the WordPress database is established
	 * This maps some WP tables with the WP MU table prefix
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function beforeConnectObserver(Varien_Event_Observer $observer)
	{
		$helper = $observer->getEvent()->getHelper();

		if ($this->canRun() && !$this->isDefaultBlog()) {
			$prefix = $helper->getTablePrefix() . $this->getBlogId() . '_';

			$entities = (array)Mage::app()->getConfig()->getNode()->wordpress->database->before_connect->tables_mu;

			foreach($entities as $entity => $table) {
				$helper->mapTable((string)$table->table, $prefix . $table->table);
			}
		}
	
		return $this;
	}
}
