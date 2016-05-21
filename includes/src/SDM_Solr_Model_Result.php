<?php
/**
 * Separation Degrees Media
 *
 * Modifications to the IntegerNet_Solr module
 *
 * @category  SDM
 * @package   SDM_Solr
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2016 Separation Degrees Media (http://www.separationdegrees.com)
 */

/**
 * SDM_Solr_Model_Result class
 */
class SDM_Solr_Model_Result extends IntegerNet_Solr_Model_Result
{
    /**
     * Call the solr server and specify a custom type, and don't store the results anywhere
     * 
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getSolrTypeCount($type)
    {
        // Set override, which links to the changes we made in...
        // ==> lib/IntegerNet_Solr/Solr/Resource/ResourceFacade.php
        $_SESSION['solr_type_override'] = $type;

        // Ger result, and only store it in a temporary variable
        $result = $this->_solrRequest->doRequest($this->activeFilterAttributeCodes);

        // Remove override
        $_SESSION['solr_type_override'] = '';

        // Return number of results found
        return $result->response->numFound;
    }
}