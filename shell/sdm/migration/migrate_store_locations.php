<?php
/**
 * Product migration script
 *
 * @category  SDM
 * @package   SDM_Shell
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'abstract_migrate.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'db.php');

class SDM_Shell_MigrateStoreLocations extends SDM_Shell_AbstractMigrate
{
    public function run()
    {
        ini_set('max_execution_time', 86400);   // 1 day
        ini_set('display_errors', 'On');
        ini_set('memory_limit', '10240M');

        $this->_initMongoDb();

        // Get a list of Ellison customers
        $locations = $this->getStoreLocations();
        // print_r($locations); die;

        // Create Magento customers
        $this->importStoreLocations($locations);
    }

    // Wraper to create all customers
    public function importStoreLocations($locations)
    {
        $this->_clearStoreLocationTable();

        foreach ($locations as $loc) {
            $coordinates = json_decode($loc->location);
            if ($loc->representative_serving_states) {
                $servingStates = json_decode($loc->representative_serving_states);
                $servingStates = implode('|', $servingStates);
            } else {
                $servingStates = '';
            }

            $location = Mage::getModel('gmapstrlocator/location')
                ->setStoreName($loc->name)
                ->setHasDesignCenter($loc->has_ellison_design_centers)
                ->setAgentType($this->_getAgentType($loc))
                ->setAddress($loc->address1)
                ->setAddress2($loc->address2)
                ->setCity($loc->city)
                ->setState($loc->state)
                ->setPostalCode($loc->zip_code)
                ->setCountry($loc->country)
                ->setLatitude($coordinates[0])
                ->setLongitude($coordinates[1])
                ->setStoreWebsite($loc->website)
                ->setStatus($loc->active)
                ->setInternalComments($loc->internal_comments)
                ->setCreatedTime($loc->created_at)
                ->setUpdatedTime($loc->updated_at)
                ->setStoreNumber($loc->store_number)
                ->setStorePhone($loc->phone)
                ->setStoreFax($loc->fax)
                ->setStoreEmail($loc->email)

                // Pip-delimited
                ->setStoreType( $this->_getStoreTypes($loc))
                ->setRepresentativeServing($servingStates)
                ->setProductLines($this->_getProductLines($loc))

                // Array
                ->setWebsiteId($this->_getWebsiteIds($loc))
                ->save();

                $this->out("Store {$loc->mongoid} saved!");
         }
    }

    protected function _getWebsiteIds($loc)
    {
        $storeIds = array();
        $systemsEnabled = explode('|', $loc->systems_enabled);

        foreach ($systemsEnabled as $code) {
            if ($code == 'szus') {
                $storeIds[] = 1;
            } elseif ($code == 'szuk') {
                $storeIds[] = 3;
            } elseif ($code == 'erus') {
                $storeIds[] = 4;
            } elseif ($code == 'eeus') {
                $storeIds[] = 5;
            }
        }

        return $storeIds;
    }

    protected function _getProductLines($loc)
    {
        $data = array();
        $types = Mage::getModel('gmapstrlocator/system_config_source_productlines')->toOptionArray();
        $types = array_flip($types);

        $prodLines = json_decode($loc->product_line);

        foreach ($prodLines as $line) {
            $data[] = ucwords($types[$line]);
        }

        if (empty($data)) {
            return '';
        }
        // return implode('|', $data);
        return '|' . implode('|', $data) . '|';
    }



    protected function _getAgentType($loc)
    {
        if (!$loc) {
            return '';
        }
        $agents = $loc->agent_type;
        $types = Mage::getModel('gmapstrlocator/system_config_source_agenttypes')->toOptionArray();
        $types = array_flip($types);

        if (!isset($types[$agents])) {
            return  '';
        }

        return $types[$agents];
    }

    protected function _getStoreTypes($loc)
    {
        $types = array();

        if ($loc->physical_store == 1) {
            $types[] = 'physical';
        }
        if ($loc->catalog_company == 1) {
            $types[] = 'catalog';
        }
        if ($loc->webstore == 1) {
            $types[] = 'online';
        }

        if (empty($types)) {
            return '';
        } else {
            return '|' . implode('|', $types) . '|';
            // return implode('|', $types);
        }
    }

    // Get all of the store location data from MongoDB
    public function getStoreLocations()
    {
        $q = "SELECT * FROM stores WHERE active = 1";
        $locations = $this->query($q);
        $this->out('# of locations found: ' . count($locations));

        return $locations;
    }

    protected function _clearStoreLocationTable()
    {
        $this->getConn('core_write')->query('TRUNCATE TABLE `gmapstrlocator_location`');
    }
}

$shell = new SDM_Shell_MigrateStoreLocations();
$shell->run();