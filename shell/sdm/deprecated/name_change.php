<?php

require_once(__DIR__ . '/abstract.php');
require_once(dirname(__FILE__) . '/db.php');

class SDM_Shell_NameChange extends SDM_Shell_Abstract
{

    protected $_db = 'sizzix_mongo_20141215';

    protected $_user = 'root';

    protected $_password = 'root';

    protected $_host = '127.0.0.1';

    public function __construct()
    {
        parent::__construct();
        $this->_init();
    }

    public function __destruct()
    {
        $this->_end();
    }

    public function run()
    {
        $newNames = array();
        $possiblePrefixes = array(
            'Sizzix.com',
            'Sizzix',
            'Ellison.com',
            'Ellison',
        );

        $this->deleteAllFiles('log');
        $this->_initMongoDb();

        $names = $this->_getAllNames();
        // $this->out($names);

        foreach ($names as $obj) {
            unset($k);
            $name = $obj->name;
            $newName = $name;
            if (isset($obj->item_num)) {
                $sku = $obj->item_num;
            } else {
                $sku = $obj->idea_num;
            }

            foreach ($possiblePrefixes as $prefix) {

                // If prefix matches, a new name must be constructed, even if no hyphens are found
                if (strpos($name, $prefix) !== false) {
                    $k = strpos($name, $prefix);
                    $subName = str_replace($prefix, '', $name);

                    $bow = explode('-', $subName);  // bag of words
                    // $this->out($bow);

                    if (count($bow) > 1) {
                        $prodLine = reset($bow);
                        unset($bow[0]);
                        $prodName = implode('-', $bow);
                        $newName = trim($prodName) .  ' - ' . trim($prodLine) . ' - ' . $prefix;
                    } else {
                        $newName = trim($subName) . ' - ' . $prefix;
                    }

                    break;
                }
            }
            // $this->out('SKU: ' . $sku);
            // $this->out('Old: ' . $name);
            // $this->out('New: ' . $newName);
            // echo PHP_EOL;

            $this->out("$sku\t$name\t$newName");

            // die;
        }
    }


    protected function _getAllNames()
    {
        $q1 = "SELECT p.`name`,p.`item_num` FROM `products` AS p";// where p.item_num = '7636'";
        $q2 = "SELECT i.`name`,i.`idea_num` FROM `ideas` AS i";

        $pNames = $this->query($q1);
        $iNames = $this->query($q2);
        // $this->out($pNames);
        // $this->out($iNames);

        return array_merge($pNames, $iNames);
    }

    public function query($q)
    {
        return $this->_dbc->query($q)->result();
    }

    protected function _initMongoDb()
    {
        $this->_dbc = new DB($this->_db, $this->_user, $this->_password, $this->_host);
    }
}

$shell = new SDM_Shell_NameChange();
$shell->run();
