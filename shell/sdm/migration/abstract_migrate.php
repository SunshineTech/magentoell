<?php
/**
 * Migration abstract class
 *
 * @category  SDM
 * @package   SDM_Shell
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'abstract.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'db.php');

abstract class SDM_Shell_AbstractMigrate extends SDM_Shell_Abstract
{
    protected $_db = 'ellison_mongodb_20151103';    // Needs to be updated with each dump
    protected $_user = 'root';
    protected $_password = 'root';
    protected $_host = '127.0.0.1';
    protected $_dbc = null;

    // Stage DB credential (for when running it from the dev server)
    protected $_dbStage = 'ellison_mongodb_20151103';   // Needs to be updated with each dump
    protected $_userStage = 'ellison';
    protected $_passwordStage = 'shimamudgadod';
    protected $_hostStage = '127.0.0.1';

    // Production DB credential (for when running it from the production server)
    protected $_dbProduction = 'mongo_20150827';    // This does not change
    protected $_userProduction = 'magento';
    protected $_passwordProduction = 'BLuX2/lQ8uq_lca0DVJ9';
    protected $_hostProduction = '172.31.14.47';   // Alias 'db1' on server

    /**
     * Product migration properties
     */

    // Hardcoded in Ellison
    protected $_customerGroupMapping = array(
        1 => 'Sizzix Dealer',
        2 => 'Sizzix Preferred Dealer',
        3 => 'Sizzix Executive Dealer',
        4 => 'Sizzix Elite Dealer',
        5 => 'Sizzix Buying Group',
        6 => 'Sizzix Key Account',
        7 => 'Sizzix Distributor',
        8 => 'Sizzix Sales Rep',
        9 => 'International Dealer',
        10 => 'International Preferred Dealer',
        11 => 'International Executive Dealer',
        12 => 'International Elite Dealer',
        13 => 'International Buying Group',
        14 => 'International Key Account',
        15 => 'International Distributor',
        16 => 'International Sales Rep',
        17 => 'Education No Stock Dealer',
        18 => 'Education Stocking Dealer',
        19 => 'Education 5K Dealer',
        20 => 'Education 10K Dealer',
        21 => 'Education 15K Dealer',
        22 => 'Education Key Account',
        23 => 'Education Catalog Dealer/Distributor',
        24 => 'Education Sales Rep',
        25 => 'Sizzix Partner Account',
        26 => 'Education Partner Account',
        27 => 'Internationa Partner Account',
    );

    /**
     * Customer migration properties
     */

    // Ellison group ID to magento group ID
    protected $_magentoCustomerGroupMapping = null;

    // Ellison codes => Magento codes
    protected $_websiteMapping = null;
    protected $_magentoWebsites = null;

    // Region mapping
    protected $_countryMapping = null;
    protected $_stateMapping = null;

    public function query($q)
    {
        return $this->_dbc->query($q)->result();
    }

    protected function _initMongoDb()
    {
        $type = $this->getArg('s');
        if ($type === 'stage') {
            $this->_dbc = new DB($this->_dbStage, $this->_userStage, $this->_passwordStage, $this->_hostStage);
        } elseif ($type === 'production') {
            $this->_dbc = new DB($this->_dbProduction, $this->_userProduction, $this->_passwordProduction, $this->_hostProduction);
        } else {
            $this->_dbc = new DB($this->_db, $this->_user, $this->_password, $this->_host);
        }
    }

    public function log($str, $level = null, $filename = null, $show = true)
    {
        if ($show) {
            $this->out($str);
        }

        if (!$filename) {
            $filename = $this->_logFile;
        }
        parent::log($str, $level, $filename);
    }

    /**
     * Customer migration methods
     *
     * Mainly for the customer migration, but order migration also needs it
     * since it creates customers if they're not found during the order migration.
     */

    protected function _initCustomerVars()
    {
        // Website code mapping
        $mapping = Mage::helper('sdm_core')->getEllisonSystemCodes();
        $this->_websiteMapping = array_flip($mapping);

        foreach (Mage::app()->getWebsites() as $website) {
            $this->_magentoWebsites[$website->getCode()] = $website->getId();
        }

        // Magento ID to Ellison website codes.. Just build it buy hand.
        $this->_websiteIdToEllisonCode = array(
            1 => 'szus',
            3 => 'szuk',
            4 => 'erus',
            5 => 'eeus',
        );

        // Customer group mapping
        $collection = Mage::getModel('customer/group')->getCollection();
        foreach ($collection as $group) {
            $code = $group->getCustomerGroupCode();
            $i = array_search($code, $this->_customerGroupMapping);
            if ($i !== false) {
                // Ellison group ID to magento group ID
                $this->_magentoCustomerGroupMapping[$i] = $group->getId();
            }
        }
        // print_r($this->_customerGroupMapping); print_r($this->_magentoCustomerGroupMapping); die;

        // Ellison country name to code mapping
        $this->_ellisonRegionMappings();
    }

    /**
     * Initialize some variables. This is mostly a duplicate of
     * SDM_Shell_MigrateCustomers::_ellisonRegionMappings()
     */
    protected function _ellisonRegionMappings()
    {
        /**
         * Countries
         * @var array
         */
        $exceptions = array(
            'usa' => 'US',
            'Northern Ireland' => 'IE',
            'Ireland (Republic of)' => 'IE',
            'Hong Kong' => 'HK',
            'PR' => 'PR',
            'Brunei Darussalam' => 'BN',
            'Korea,  Republic of (South)' => 'KR',
            'Russian Federation' => 'RU',
        );
        $filePath = Mage::getBaseDir() . '/shell/sdm/migration/lib/regions.xml';
        $doc = $this->_loadXml($filePath);
        $xpath = new DomXpath($doc);
        $cmapping = array();
        $crefs = array();
        $countries = $this->query("SELECT DISTINCT(country) FROM user_address");

        // Set the standard reference
        foreach ($xpath->query('//ldml/localeDisplayNames/territories/territory') as $rowNode) {
            $alt  = $rowNode->getAttribute('alt');
            if (empty($alt)) {
                $crefs[$rowNode->nodeValue] = $rowNode->getAttribute('type');
            }
        }

        // For each country name, search for its standardized code

        foreach ($countries as $one) {
            if (isset($crefs[$one->country])) {
                $cmapping[$one->country] = $crefs[$one->country];
            } else {
                if (isset($exceptions[$one->country])) {
                    $cmapping[$one->country] = $exceptions[$one->country];
                } else {
                    $this->out('ERROR: no mapping for ' . $one->country); exit;
                }
            }
        }

        $this->_countryMapping = $cmapping;

        /**
         * States (only US)
         */
        $smapping = array();
        $rmapping = array();
        $srefs = array();

        $collection = Mage::getModel('directory/region')->getResourceCollection()
            ->addCountryFilter('US');
        foreach ($collection as $region) {
            $srefs[strtolower($region->code)] = $region->code;
            $srefs[strtolower($region->default_name)] = $region->code;
            $rmapping[strtolower($region->code)] = $region->getId();
        }

        $states = $this->query("SELECT DISTINCT(state) FROM user_address WHERE country = 'United States' OR country = 'usa'");
        foreach ($states as $one) {
            if (isset($srefs[strtolower($one->state)])) {
                $smapping[strtolower($one->state)] = $srefs[strtolower($one->state)];
            } else {
                $smapping[strtolower($one->state)] = '';    // Don't save these codes as some seem like gibberish
                // $this->out('ERROR: no mapping for ' . $one->state); die;
            }
        }

        $this->_stateMapping = $smapping;
        $this->_regionMapping = $rmapping;
        // print_r($this->_countryMapping);
        // print_r($this->_stateMapping);
        // print_r($this->_regionMapping);
        // die;
    }

    /**
     * @see SDM_Shell_MigrateCustomers::_loadXml()
     */
    protected function _loadXml($filePath)
    {
        $doc = new DOMDocument();
        $doc->loadXML(file_get_contents($filePath));

        return $doc;
    }

    /**
     * Decodes the hashed data from the ported MongoDB. The serialization
     * failed to unserialize due to an offset issue, possibly due to some kind of
     * collation. In any case, base64-encoding solves this issue.
     *
     * @param str $hash
     *
     * @return str
     */
    public function decode($hash)
    {
        return unserialize(base64_decode($hash));
    }
}
