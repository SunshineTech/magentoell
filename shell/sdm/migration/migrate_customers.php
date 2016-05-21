<?php
/**
 * Product migration script
 *
 * @category  SDM
 * @package   SDM_Shell
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

ini_set('display_errors', 1);

require_once(dirname(__FILE__) . '/abstract_migrate.php');
require_once(dirname(__FILE__) . '/lib/customer.php');

class SDM_Shell_MigrateCustomers extends SDM_Shell_AbstractMigrate
{
    const RETAILER_APP_FILE_PREFIX = 'http://www.sizzix.com/grid/retailer_application/';

    /**
     * Set to false to migrate only new accouts and true to always create and
     * update existing accounts.
     */
    protected $_alwaysUpdate = false;   // Manually set for customer objects

    protected $_appOnly = null; // Overrides $_alwaysUpdate but only for retailer apps

    protected $_logFile = 'customer_migration.log';

    // For testing
    protected $_searchLimit = '' ; // 'LIMIT 20';

    protected $_raStatusMapping = array(
        'pending' => 'pend',
        'Inactive' => 'unde',
        'active' => 'appr',
        'declined' => 'decl',
        'suspended' => 'susp',
    );

    protected $_raResellBrandMapping = array(
        'Sizzix' => 0,
        'Ellison' => 1,
        'AllStar' => 2,
    );

    /**
     * Manually mapped out Ellison to Magento retailer application data values
     *
     * @var array
     */
    protected $_raMappings = array(
        'signing_up_for' => array(
            'Distributor' => 'dist',
            'Wholesale' => 'whol',
        ),
        'business_type' => array(
            'Proprietorship' => 'prop',
            'Corporation' => 'corp',
            'Other' => 'misc',
            'Partnership' => 'part',
            'Chain' => 'chai',
        ),
        'how_did_you_learn_about_us' => array(
            'Magazine/Advertising' => 'maga',
            'Other' => 'othe',
            'Tradeshow' => 'trad',
            'Salesperson/Representative' => 'sale',
            'Internet' => 'inte',
            'Mailer' => 'mail',
            'TV' => 'tv',
        ),
        'payment_method' => array(
            'Credit Card' => 'cred',
            'Prepaid' => 'prep',
        ),
        'store_department' => array(
            'School Supplies' => 'scho',
            'General Craft' => 'gene',
            'Scrapbooking/Stationary' => 'scra',
            'Rubberstamp' => 'rubb',
            'Other' => 'misc',
            'Office Supplies' => 'offi',
            'Quilting' => 'quil',
            'Contract Stationer' => 'cont',
            'Photo Specialty' => 'phot',
            'District Buying Group' => 'dist',
        ),
        'store_location' => array(
            'Shopping Center' => 'shop',
            'Downtown Business' => 'down',
            'Internet' => 'inte',
            'Outlying Business' => 'outl',
            'Other' => 'misc',
            'Residence' => 'resi',
            'Catalog' => 'cata',
        ),
        'store_square_footage' => array(
            'Less than 1,000 sq.ft.' => '1',
            '1,000-2,000 sq.ft' => '2',
            '2,001-3,000 sq.ft' => '3',
            '3,001-5,000 sq.ft' => '4',
            '5,001-10,000 sq.ft' => '5',
            '10,0001 and over' => '6',
        ),
    );

    protected $_institutionCodeMappings = array(
        'DA' => 'Day Care 3-6 yrs & Afterschool and Summer School',
        'DM' => 'District Media Center HE Head Start - Even Start',
        'IN' => 'Individuals, Teachers, Crafters, or Designers',
        'NP' => 'Non-Profit Organisation Hospitals',
        'PL' => 'Public Library SC School - Church',
        'SD' => 'School - District',
        'SE' => 'School - Elementary SG School - Government, Government Agencies',
        'SH' => 'School - High School',
        'SJ' => 'School - Junior High',
        'SP' => 'School - Pre-School, Early Childhood Centers',
        'PR' => 'School - Private SCHE School Charter Elementary, Jr High, High'
    );

    public function run()
    {
        ini_set('max_execution_time', 186400);   // Many days
        ini_set('display_errors', 1);
        ini_set('memory_limit', '10240M');

        // $this->out('*** Install indices on search columns of the Ellison DB! ***');
        // $this->deleteAllFiles('log');
        $this->setArgs();
        $this->_initMongoDb();
        $this->_initCustomerVars();

        if (0) {
            $this->_analyzeRetailerApplicationData(true);    // Only need to run once
            exit;
        }

        // Clean up unnecessary data
        $this->removeUnnecessaryRecords();   // Only needs to run once on the DB

        // Create Magento customers
        // Get a list of Ellison customers
        $customerList = $this->getEllisonCustomerList();
        // print_r($customerList);

        $this->updateAllCustomers($customerList);
        // $this->out('Skipping updating customers. They have been updated already.');

        /**
         * Note: addresses are updated independently of $customerList. Instead,
         *       all email addresses in the Magento DB are considered.
         */
        $this->updateAllCustomerAddresses();
        // $this->out('Skipping updating addresses. They have been updated already.');

        /**
         * Note: Retailer application address associations must be established
         *       after addresses have been updated.
         */
        $this->updateRetailerApplicationAddresses();
    }

    /**
     * Wrapper to update all customer addresses
     */
    public function updateAllCustomerAddresses()
    {
        $this->log('Updating addresses...');

        /**
         * Get all customers in the Magento DB
         * Note: Magento has one account per website.
         *       Ellison has one account for all websites.
         */
        $emails = $this->getAllCustomerEmails();    // One account per website
        $N = count($emails);
        $progressBar = $this->progressBar($N);

        foreach ($emails as $i => $mData) {
            $n = $i+1;
            $email = $mData['email'];
            $websiteId = $mData['website_id'];
            $entityId = $mData['entity_id'];
            $this->log("$n/$N: $email's address | Website ID: $websiteId", null, null, false);
            $progressBar->update($i);

            // Get billing, shipping, and home addresses from Ellison DB
            // $this->out('Address loop: start');
            $ellisonCode = $this->_websiteIdToEllisonCode[$websiteId];
            $ellisonAddresses = getEllisonAddresses($email, $ellisonCode, $this->_dbc);
            // $this->out('Address loop: Ellison addresses obtained');

            if (empty($ellisonAddresses)) {
                // $this->out('Address N/A');
                continue;
            }

            $ellisonAddresses = validateAddresses(
                $ellisonAddresses,
                $this->_countryMapping,
                $this->_stateMapping,
                $this->_regionMapping,
                $websiteId
            );
            // $this->out('Address loop: Ellison addresses validated');
            // echo 'Site code: ' . $ellisonCode . PHP_EOL;
            // print_r($ellisonAddresses);

            // Save each address to magento
            updateAddresses($ellisonAddresses, $entityId);
            // $this->out('Address loop: Ellison addresses saved to Magento');
        }
    }

    // Get (only) a list of all customers for all websites
    public function getEllisonCustomerList()
    {
        $q = "SELECT id, mongoid, email, systems_enabled
            FROM users
            WHERE systems_enabled != ''
                -- AND (systems_enabled LIKE '%eeus%' OR systems_enabled LIKE '%erus%')
                -- AND email = '111' -- Has RA file attachments
                -- AND email = 'battisto65355667@aol.com' -- qqq
                -- AND email = 'anthony\'smom_04@hotmail.com'
            GROUP BY email
            ORDER BY id
            {$this->_searchLimit}";
        $customers = $this->query($q);
        $this->out('# of customers found: ' . count($customers));

        return $customers;
    }

    /**
     * Get Magento customer emails
     */
    public function getAllCustomerEmails()
    {
        $q = "SELECT `entity_id`,`email`,`website_id`
            FROM `customer_entity`
            -- WHERE email = 'battisto65355667@aol.com'
            ORDER BY entity_id ASC";
        $results = $this->getConn()->fetchAll($q);

        return $results;
    }

    /**
     * Wraper to create all customers
     *
     * First create customer account without addresses, and associate addresses
     * once the accounts are created.
     */
    public function updateAllCustomers($list)
    {
        $this->log('Updating customers...');
        $i = 0;
        $N = count($list);
        $progressBar = $this->progressBar($N);

        foreach ($list as $data) {
            $i++;
            $id = trim($data->mongoid);
            // print_r($data);

            // Get raw data
            $cutomerData = getEllisonCustomer($id, $this->_dbc);

            // Clean up data
            $cutomerData = $this->_cleanUpCustomerData($cutomerData);
            $email = trim($cutomerData->email);
            // print_r($appData);die;
            // print_r($cutomerData); die;

            // Determine if it needs to be migrated before proceeding. Depends
            // on update flag.
            if ($this->_alwaysUpdate || $this->_appOnly) {
                $websitesIds = $cutomerData->websites;
            } else {
                $websitesIds = $this->_needToMigrate($cutomerData);
            }

            $progressBar->update($i);
            @$this->log("$i/$N: {$cutomerData->email}'s' account | Websites: " . implode(', ', $websitesIds), null, null, false);

            if ($websitesIds !== false) {
                foreach ($websitesIds as $websiteId) {

                    if (!$this->_appOnly) {
                        $customer = updateCustomer($cutomerData, $websiteId, $this->_institutionCodeMappings);
                    } else {
                        $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)
                            ->loadByEmail($data->email);
                    }

                    // Update the retailer customer application if necessary
                    if ($websiteId == 4) {
                        $this->updateRetailerApplication($cutomerData, $customer->getId());
                    }
                }
            }
        }
    }

    /**
     * Check against Magento's DB. Returns false or an array of website IDs under
     * which this customer needs to be created.
     *
     * @return bool|array
     */
    protected function _needToMigrate($data)
    {
        $createIds = array();
        $email = trim($data->email);
        $ellisonWebsiteIds = $data->websites;

        // Get website IDs of the Magento customer accounts in the DB, if any
        $q = "SELECT website_id from customer_entity WHERE email = '$email'";
        $magentoWebsiteIds = $this->getConn()->fetchCol($q);

        // Determine which websites this account needs to be created, if any
        if ($ellisonWebsiteIds) {
            foreach ($ellisonWebsiteIds as $id) {
                if (array_search($id, $magentoWebsiteIds) === false) {
                    $createIds[] = $id;
                }
            }
        } else {
            return false;
        }

        if (empty($createIds)) {
            return false;
        }
        // print_r($createIds);

        return $createIds;
    }

    /**
     * Creates or update retailer applications, of which exist one per customer
     *
     * @param stdCLass $data
     * @param int $customerId
     */
    public function updateRetailerApplication($data, $customerId)
    {
        // If no application, some dummy ones still need to be created
        if (empty($data->retailer_application)) {
            if (isset($this->_raStatusMapping[$data->status])) {
                $appStatus = $this->_raStatusMapping[$data->status];
            } else {
                $appStatus = SDM_RetailerApplication_Helper_Data::STATUS_PENDING;
            }
            $application = Mage::getModel('retailerapplication/application')
                ->loadByCustomer($customerId);
            $application->setCustomerId($customerId)
                ->setStatus($appStatus)
                ->setAdminNotes(
                    'This application was created automatically during migration '
                        . 'because the customer did not have an existing one.'
                )
                ->save();
            return;
        }

        $appData = unserialize(base64_decode($data->retailer_application));
        // print_r($data);
        // print_r($appData);

        // Overwrite on the existing application, if available
        $application = Mage::getModel('retailerapplication/application')->loadByCustomer($customerId);
        $application->setData('customer_id', $customerId);

        // Address associations are done after addresses are migrated

        // Custom logic maping
        if (empty($data->status) || !isset($this->_raStatusMapping[$data->status])) {
            $application->setStatus(SDM_RetailerApplication_Helper_Data::STATUS_PENDING);
        } else {
            // If app exists and Pending, it should be Under Review
            if ($this->_raStatusMapping[$data->status] == 'pend') {
                $application->setStatus('unde');
            } else {
                $application->setStatus($this->_raStatusMapping[$data->status]);
            }

            // Regardless of state, always check the terms
            $application->setAcceptApplicationPolicy(1);
            $application->setAcceptTerms(1);
        }

        if ($appData['no_website']) {
            $application->setData('company_website', 'N/A');
        } else {
            $application->setData('company_website', $appData['website']);
        }

        $resellBrands = array();
        foreach ($appData['brands_to_resell'] as $brand) {
            $resellBrands[] = $this->_raResellBrandMapping[$brand];
        }
        $application->setData('brands_to_resell', implode(',', $resellBrands));

        // Static mapping
        $application->setData('company_name',$data->company);
        $application->setData('company_authorized_buyers', $appData['authorized_buyers']);
        $application->setData('company_years', $appData['years_in_business']);
        $application->setData('company_employees', $appData['number_of_employees']);
        $application->setData('company_resale_number', $appData['resale_number']);
        $application->setData('company_tax_id', $appData['tax_identifier']);

        if ($appData['annual_sales']) { // This is now a varchar field
            $application->setData('company_annual_sales', $appData['annual_sales']);
        }

        // Custom mapping
        $application->setData('application_type', $this->_raMappings['signing_up_for'][$appData['signing_up_for']]);
        $application->setData('company_type', $this->_raMappings['business_type'][$appData['business_type']]);
        $application->setData('payment_method', $this->_raMappings['payment_method'][$appData['payment_method']]);
        $application->setData('company_store_department', $this->_raMappings['store_department'][$appData['store_department']]);
        $application->setData('company_store_location', $this->_raMappings['store_location'][$appData['store_location']]);
        $application->setData('company_store_sqft', $this->_raMappings['store_square_footage'][$appData['store_square_footage']]);
        $application->setData('how_did_you_learn', $this->_raMappings['how_did_you_learn_about_us'][$appData['how_did_you_learn_about_us']]);

        $pathPrefix = 'retailer_application_files' . DS . 'migrated' . DS
            . $appData['id'];

        // Files, if available. If not, check "will fax" boxes
        if (isset($appData['business_license_filename'])) {
            $application->setData(
                'file_business_license',
                $pathPrefix . '-business_license-' . $appData['business_license_filename']
            );
        } else {
            $application->setData('file_business_license', 'fax');
        }

        if (isset($appData['resale_tax_certificate_filename'])) {
            $application->setData(
                'file_resale_tax_certificate',
                $pathPrefix . '-resale_tax_certificate-' . $appData['resale_tax_certificate_filename']
            );
        } else {
            $application->setData('file_resale_tax_certificate', 'fax');
        }

        if (isset($appData['store_photo_filename'])) {
            $application->setData(
                'file_store_photo',
                $pathPrefix . '-store_photo-' . $appData['store_photo_filename']
            );
        } else {
            $application->setData('file_store_photo', 'fax');
        }

        $timeNow = Mage::getSingleton('core/date')->gmtDate();
        $application->setCreatedAt($timeNow);
        $application->setCreatedAt($timeNow);

        // print_r($application->debug());
        $application->save();

        $this->log("     {$data->email}'s retailer app.", null, null, true);
    }

    /**
     * Clean up Ellison customer data to be more suitable for Magento
     */
    protected function _cleanUpCustomerData($data)
    {
        // Fix name
        $bow = explode(' ', $data->name);
        $data->firstname = array_shift($bow);
        $data->lastname = implode(' ', $bow);
        $data->websites = array();
        $data->email = mysql_real_escape_string($data->email);
        // print_r($data->email);

        // Fix ERP
        $data->erp = trim($data->erp);
        if (strtolower($data->erp) === 'new') {
            $data->erp = '';
        }

        // Convert website assignment
        $systems = explode('|', trim($data->systems_enabled));
        foreach ($systems as $code) {
            if ($code === 'eeuk') { // Store no longer exists
                continue;
            }
            if (isset($this->_magentoWebsites[$this->_websiteMapping[$code]])) {
                $data->websites[$this->_magentoWebsites[$this->_websiteMapping[$code]]]
                    = $this->_magentoWebsites[$this->_websiteMapping[$code]];
            }
        }

        // Assign proper customer group
        if (isset($this->_magentoCustomerGroupMapping[$data->discount_level]) && $data->discount_level != 0){
            $data->customer_group_id = $this->_magentoCustomerGroupMapping[$data->discount_level];
        // Otherwise, it's just a regulat customer
        } else {
            $data->customer_group_id = 1;   // "General" Magento cutomer group
        }

        return $data;
    }

    public function removeUnnecessaryRecords()
    {
        // "Test" customer records (This is not all but most)
        $q = "DELETE FROM `users`
            WHERE `email` LIKE 'test%@%' OR `email` LIKE '%test@%'
                OR `email` LIKE '%@test.com' OR `email` LIKE '%@test%.com'
                OR `email` NOT LIKE '%@%'";
        $this->query($q);

        // Inactive customer records?

        $this->out('>> Unnecessary customer records removed.');

        // Remve orphan addresses
        $this->query("DELETE FROM user_address WHERE user_id NOT IN (SELECT id FROM users)");

        // Remove "test" addresses
        $this->query("DELETE FROM `user_address`
            WHERE `first_name` LIKE '%test%' OR `last_name` LIKE '%test%'");

        // Remove duplicated addresses by the mongoid field
        $q2 = "SELECT mongoid,count(mongoid) AS cnt
            FROM user_address
            GROUP BY mongoid
            HAVING cnt > 1";
        $mongoIds = $this->query($q2);

        foreach ($mongoIds as $one) {
            $limit = $one->cnt - 1;   // Allows saving one unique record
            $q3 = "DELETE FROM user_address WHERE mongoid = '{$one->mongoid}' LIMIT $limit";
            $this->query($q3);
        }
        $this->out('>> Unnecessary customer address records removed.');
    }

    /**
     * For the applications in the DB, update the addresses using the customer's addresses
     */
    public function updateRetailerApplicationAddresses()
    {
        $this->log('Updating retailer application addresses...');

        // Get all applications' customer IDs
        $collection = Mage::getModel('retailerapplication/application')->getCollection()
            ->addFieldToSelect('customer_id');

        foreach ($collection as $app) {
            // $customer = Mage::getModel('customer/customer')->load($app->getCustomerId());
            $q = "SELECT entity_id,parent_id,is_editable
                FROM `customer_address_entity`
                WHERE  parent_id = {$app->getCustomerId()}";

            $addresses = $this->getConn()->fetchAll($q);
            $addressIds = array();
            $application = Mage::getModel('retailerapplication/application')->loadByCustomer($app->getCustomerId());

            foreach ($addresses as $address) {
                if ($address['is_editable'] == 0) {
                    $application->setData('owner_address_id', $address['entity_id']);
                } else {
                    $addressIds[] = $address['entity_id'];
                }
            }

            if (count($addressIds) == 1) {
                $application->setData('shipping_address_id', $addressIds[0]);
                $application->setData('billing_address_id', $addressIds[0]);
            } elseif (count($addressIds) >= 2) {
                $application->setData('shipping_address_id', $addressIds[0]);
                $application->setData('billing_address_id', $addressIds[1]);
            }

            $application->save();
        }
    }

    /**
     * Examines the retailer application data and outputs unique values for
     * a set of given fields set in $keys array.
     *
     * Also downloads retailer application files.
     */
    protected function _analyzeRetailerApplicationData($skipImages = false)
    {
        $data = array();
        $keys = array(
            'signing_up_for',
            'business_type',
            'how_did_you_learn_about_us',
            'payment_method',
            'store_department',
            'store_location',
            'store_square_footage',
        );
        // URL key => array indices
        $fileList = array(
            'business_license' => 'business_license_filename',
            'resale_tax_certificate' => 'resale_tax_certificate_filename',
            'store_photo' => 'store_photo_filename',
        );
        $savePath = Mage::getbaseDir('media') . DS . 'retailer_application_files'
            . DS . 'migrated' . DS;

        $q = "SELECT retailer_application FROM users WHERE retailer_application != ''";
        // $q .= " AND mongoid = '4ea82f585b76e1257e000bdf'";
         $results = $this->query($q);
        $N = count($results);
        $i = 1;

        // Find unique values and get a list of files to download
        $this->out('Processing retailer application data...');
        foreach ($results as $one) {
            $app = unserialize(base64_decode($one->retailer_application));
            $mongoId = $app['id'];

            // Collect values
            foreach ($keys as $key) {
                if (isset($app[$key])) {
                    $data[$key][] = $app[$key];
                }
            }

            if ($skipImages) {
                continue;
            }

            // Collect files
            foreach ($fileList as $urlKey => $file) {
                if (isset($app[$file])) {
                    // Source file
                    $fileUrl = self::RETAILER_APP_FILE_PREFIX . $urlKey . DS .  $mongoId
                        . DS . $app[$file];
                    // Full save path including file name
                    $saveFullPath = $savePath . "$mongoId-$urlKey-{$app[$file]}";
                    echo $fileUrl . PHP_EOL; echo $saveFullPath . PHP_EOL;

                    $this->_downloadFile($fileUrl, $saveFullPath);
                }
            }
            $i++;
        }

        $this->out('');
        $this->out('Displaying unique values of the retailer application fields.');
        foreach ($data as $key => &$one) {
            $one = array_unique($one);
            $this->out("$key");
            foreach ($one as $value) {
                $this->out("    $value");
            }
        }
        $this->out('');
        $this->out('Finished analyzing and downloading retailer application data.');
        $this->out('Restart script with the analyzer disabled once all files are downloaded.');
        $this->out(''); exit;
    }

    /**
     * Given a full URL, download the image into the given directory.
     *
     * @param str $url Full path including file name
     * @param str $fullPath Full path including file name
     *
     * @return bool
     */
    protected function _downloadFile($url, $fullPath)
    {
        if (file_exists($fullPath)) {
            $this->log("File already exists: $fullPath", null, 'ra_file_download.log');
            return;
        }

        $content = file_get_contents($url);

        if ($content !== false) {
            if (file_put_contents($fullPath, $content)) {
                $this->log("File saved: $fullPath", null, 'ra_file_download.log');
                return true;

            } else {
                $this->log("Failed to save file: $fullPath from source $url", null, 'ra_file_download.log');
                unlink($fullPath);  // Sometimes an empty file is saved
                return false;
            }

        } else {
            $this->log("Failed to download file: $url", null, 'ra_file_download.log');

            return false;
        }

        return false;
    }

    /**
     * Load XML file
     *
     * @param str $filePath
     *
     * @return DOMDocument
     */
    protected function _loadXml($filePath)
    {
        $doc = new DOMDocument();
        $doc->loadXML(file_get_contents($filePath));

        return $doc;
    }

    /**
     * Set script arguments
     */
    public function setArgs()
    {
        if ($this->getArg('app-only')) {
            $this->out('Running in retailer application-only migration mode. Truncating retailer data!');
            $this->_appOnly = true;

            // Truncate retailer app data
            $this->getConn('core_write')->query('TRUNCATE TABLE `sdm_retailerapplication`');

        } else {
            $this->_appOnly = false;
        }
    }
}

$shell = new SDM_Shell_MigrateCustomers();
$shell->run();
