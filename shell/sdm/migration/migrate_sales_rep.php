<?php

require_once(dirname(__FILE__) . '/abstract_migrate.php');

/**
 * Checks sales reps (Magento custoemrs) and assigns them customers based on
 * the file provided (CSV file exported from Excel file).
 */
class SDM_Shell_SalesRep extends SDM_Shell_AbstractMigrate
{
    protected $_logFile = 'sales_rep_migration.log';

    /**
     * Magento write connection
     */
    protected $_dbcR = null;

    /**
     * Associative array of rep IDs to customers IDS
     *
     * eeus_sales_rep_to_import.csv
     *
     * @var array
     */
    protected $_reps = array();

    /**
     * Loaded CSV data
     *
     * @var array
     */
    protected $_repData = array();

    protected $_websiteMapping = array(
        'szus' => 1,
        'szuk' => 3,
        'erus' => 4,
        'eeus' => 5
    );
    protected $_storeMapping = array(
        'szus' => 1,
        'szuk' => 7,
        'erus' => 5,
        'eeus' => 6
    );

    public function run()
    {
        // $this->deleteAllFiles('log');
        $this->_init();

        // Clear all quotes
        $this->log('Removing all sales rep associations...');
        $this->_deleteAllSalesRepAssociations();

        // Save test saved quote
        $this->log('Establishing sales rep-customers associations...');
        $this->_makeSalesRepAssociations();
    }

    protected function _deleteAllSalesRepAssociations()
    {
        $this->_dbcR->query("DELETE FROM am_perm");
    }

    /**
     * Wrapper to create sales rep-customer associations
     */
    protected function _makeSalesRepAssociations()
    {
        $sqlData = array(); // Associative array, rep ID to customer ID

        foreach ($this->_repData as $one) {
            // Reps must be available at this point
            if (!isset($this->_reps[$one['sales_rep_email']])) {
                print_r($this->_reps);
                print_r($one['sales_rep_email']);
                exit;
            }
            $salesRepId = $this->_reps[$one['sales_rep_email']];
            $email = $one['customer_email'];
            $customer = $this->_getCustomer($email);

            if ($customer && $customer->getId()) {
                $sqlData[$salesRepId][] = $customer->getId();
            } else {
                $this->log("Unable to find EEUS customer. Skipped. --> $email");
            }
        }

        foreach ($sqlData as $repId => $customerIds) {
            foreach ($customerIds as $customerId) {
                $q = "INSERT INTO am_perm (`uid`, `cid`) VALUES ($repId, $customerId);";
                try {
                    $this->_dbcR->query($q);
                } catch (Exceptino $e) {
                    $this->log("Query failed: $q");
                }
            }
        }
    }

    protected function _init()
    {
        // $this->_initMongoDb();   // Not required for this migration
        $this->_dbcR = $this->getConn('core_write');

        $this->_readSalesRepFile();
    }

    protected function _readSalesRepFile()
    {
        $filePath = Mage::getBasedir(). '/shell/sdm/migration/data/eeus_sales_rep_to_import.csv';
        $data = array();
        $salesRepEmails = array();
        $delimiter = ',';
        $newline = "\r";

        $fh = fopen($filePath, "r");
        if (!$fh) {
            $this->log('Unable to read file: ' . $filePath);
            exit;
        }

        $contents = fread($fh, filesize($filePath));
        $split = explode($newline, $contents);
        $i = 0;

        foreach ($split as $i => $row) {
            if ($i == 0) {
                $headers = explode($delimiter, $row);   // Don't need it
            } else {
                $line = explode($delimiter, $row);
                if ($line[1] !== 'eeus') {
                    $this->log('Unsupported website encountered: ' . $line[1]);
                    exit;
                }
                $data[$i]['customer_email'] = $line[0];
                $data[$i]['ellison_website_code'] = $line[1];
                $data[$i]['sales_rep_email'] = $line[2];
                $salesRepEmails[] = $line[2];
            }
        }
        $salesRepEmails = array_unique($salesRepEmails);

        // Retrieve sales rep customer account IDs
        $this->_reps = $this->_setSalesReps($salesRepEmails);
        $this->_repData = $data;
        // print_r($this->_reps); print_r($this->_repData);

    }

    /**
     * @param Retrieves sales rep customer IDs and checks that they are admin users
     */
    protected function _setSalesReps($emails)
    {
        $adminUserIds = array();
        $success = true;
        $missingEmails = array();

        foreach ($emails as $email) {
            $admin = $this->_getAdminUser($email);

            // Check if its an admin
            if ($admin && $admin->getId()) {
                $adminUserIds[$email] = $admin->getId();
            } else {
                $success = false;
                $missingEmails[] = $email;
            }
        }
        // $missingEmails = array_unique($missingEmails);

        if (!$success) {
            $this->log(
                'Error: You must create admin users for the following EEUS sales reps'
                     . ' with appropriate roles to continue. The admin user name must'
                     . ' be the string part of the email before the @ sign.'
            );
            $this->log('>> ' . implode(', ', $missingEmails));
            exit;
        } else {
            $this->log('All sales reps have admin user accounts: ' . implode(', ', $missingEmails));
        }

        return $adminUserIds;
    }

    /**
     * Returns the admin user (not customer)
     *
     * @return Mage_Admin_Model_User
     */
    protected function _getAdminUser($email)
    {
        $admin = Mage::getModel('admin/user')->load($email, 'email');

        return $admin;
    }

    protected function _getCustomer($email)
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(5)   // Only EEUS
            ->loadByEmail($email);

        return $customer;
    }

}

$shell = new SDM_Shell_SalesRep();
$shell->run();