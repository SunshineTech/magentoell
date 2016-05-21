<?php
/**
 * Separation Degrees One
 *
 * Magento catalog search customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogSearch
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

/**
 * SDM_CatalogSearch_Model_Resource_Fulltext class
 */
class SDM_CatalogSearch_Model_Resource_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{
    /**
     * Optimizations to resetting search results
     *
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function resetSearchResults()
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->update(
            $this->getTable('catalogsearch/search_query'),
            array('is_processed' => 0),
            array('is_processed != ?' => 0)
        );
        $adapter->commit();
        $adapter->truncateTable($this->getTable('catalogsearch/result'));
        $adapter->beginTransaction();

        Mage::dispatchEvent('catalogsearch_reset_search_result');

        return $this;
    }
}
