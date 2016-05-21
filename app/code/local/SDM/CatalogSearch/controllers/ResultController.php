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

require_once Mage::getModuleDir('controllers', 'Mage_CatalogSearch').DS."ResultController.php";

/**
 * SDM_CatalogSearch_ResultController class
 */
class SDM_CatalogSearch_ResultController
    extends Mage_CatalogSearch_ResultController
{
    /**
     * Retrieve catalog session
     *
     * @return Mage_Catalog_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('catalog/session');
    }
    /**
     * Display search result
     *
     * @return void
     */
    public function indexAction()
    {
        $query = Mage::helper('catalogsearch')->getQuery();
        /* @var $query Mage_CatalogSearch_Model_Query */

        $query->setStoreId(Mage::app()->getStore()->getId());

        if ($query->getQueryText() != '') {
            if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            } else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity()+1);
                } else {
                    $query->setPopularity(1);
                }

                if ($query->getRedirect()) {
                    $query->save();
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                } else {
                    $query->prepare();
                }
            }

            if ($this->_checkResultsForRedirect($query)) {
                return;
            }

            Mage::helper('catalogsearch')->checkNotes();

            $this->loadLayout();
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');

            $params = Mage::app()->getRequest()->getParams();
            if (isset($params['reload'])) {
                unset($params['reload']);
                $redirectUrl = Mage::getUrl('catalogsearch/result', array('_query' => $params));
                $this->_redirectUrl($redirectUrl);
            } else {
                $this->renderLayout();
            }

            if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->save();
            }
        } else {
            $this->_redirectReferer();
        }
    }

    /**
     * Check the number of results we have and see if we need to redirect
     * to the alternate type filter to check for results
     *
     * @param  object $query
     * @return bool
     */
    protected function _checkResultsForRedirect($query)
    {
        // This is being temporarily disabled as the new feature from SOLR
        // fullfils the base need from this request, and, this particular
        // implementation of the feature is causing issues with search.
        // Reenable this at your own risk.
        return false;
        
        if ($this->getRequest()->getParam('skip_alt_check') === null) {
            // Get number of results for this search query
            $numResults = (int)Mage::getSingleton('integernet_solr/result')
                ->getSolrResult()->response->numFound;

            // Get current url params
            $params = array_merge(
                $this->getRequest()->getParams(),
                array('_use_rewrite' => true, '_forced_secure' => true)
            );

            // Swap catalog type in case we're redirecting
            $params['type'] = Mage::helper('sdm_catalog')->getCatalogType();
            $params['type'] = $params['type'] === SDM_Catalog_Helper_Data::PRODUCT_CODE
                    ? SDM_Catalog_Helper_Data::IDEA_CODE
                    : SDM_Catalog_Helper_Data::PRODUCT_CODE;

            if ($this->getRequest()->getParam('alt_type') === null) {
                // If we have 0 results, check the opposite search type
                if ($numResults === 0) {
                    // Change params and redirect
                    $params['alt_type'] = 1;
                    $this->getResponse()->setRedirect(Mage::getUrl('*/*/*', $params));
                    return true;
                }
            } else {
                // If we were sent from the opposite type, and have 0 still, then redirect
                // back with skip_alt_check flag so we don't redirect again
                if ($numResults === 0) {
                    // Change params and redirect
                    unset($params['alt_type']);
                    $params['skip_alt_check'] = 1;
                    $this->getResponse()->setRedirect(Mage::getUrl('*/*/*', $params));
                    return true;
                }
            }
        }
        return false;
    }
}
