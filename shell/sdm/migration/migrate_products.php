<?php
/**
 * Product migration script
 *
 * @category  SDM
 * @package   SDM_Shell
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

require_once(dirname(__FILE__) . '/abstract_migrate.php');

class SDM_Shell_MigrateProducts extends SDM_Shell_AbstractMigrate
{
    /**
     * Class constants and properties need to be manually set prior to the migration.
     */
    const SIZZIX_WEBSITE = 'http://www.sizzix.com';
    const ELLISON_WEBSITE = 'http://www.ellison.com';
    const EDUCATION_WEBSITE = 'http://www.ellisoneducation.com';
    const PRODUCT_SKU_COLUMN = 'item_num';  // item_code seemingly very similar but does vary
    const IDEA_SKU_COLUMN = 'idea_num';  // same
    const PRODUCT_PRODUCT_TYPE = 'simple';
    const IDEA_PRODUCT_TYPE = 'grouped';
    const PRODUCT_ATTRIBUTE_SET_NAME = 'Product';
    const IDEA_ATTRIBUTE_SET_NAME = 'Idea';
    const CATALOG_CATEGORY_NAME = 'Catalog';    // this is set up in admin

    /**
     * Set to true to always save
     *
     * @var bool
     */
    protected $_alwaysUpdate = true;

    /**
     * Fillter start date for 0000-00-00 00:00:00, which causes rendering issue in Magento admin
     * @var string
     */
    protected $_someStartDate = '1990-01-01 00:00:00';

    /**
     * Limits number of products to process set this to null for regular runs
     * @var integer
     */
    protected $_pageSize = null;

    protected $_paginationSteps = null;

    protected $_breakAfterOnePage = true;  // Breaks out of the script after designated page

    protected $_productType = null;

    protected $_taxonomyMappingFile = null;

    protected $_taxonomyDataFile = null;

    protected $_taxonomyData = null;

    protected $_compatibilityProductLineMappingFile = null;

    protected $_dbScp = 'scpdb';    // SCP's DB

    protected $_recursiveCount = 0;

    protected $_logFile = 'product_migration.log';

    // protected $_dbc = null;

    protected $_dbcScp = null;

    protected $_shellDir = '';

    protected $_orderableColumn = 'orderable_szus';

    protected $_productAttSetId = null;

    protected $_projectAttSetId = null;

    protected $_usCatId = null;

    protected $_ukCatId = null;

    protected $_reCatId = null;

    protected $_edCatId = null;

    protected $_helper = null;

    protected $_websiteMapping = null;

    protected $_storeMapping = null;

    protected $_systemCodes = null;

    protected $_categories = null;

    protected $_productLineMapping = null;

    protected $_compatibilityProductLineMapping = null;

    protected $_shortDescriptions = array();

    protected $_productAttributes  = array(
        '*'
    );

    protected $_downloadImages = true;

    protected $_updateData = false;

    /**
     * Tags that are associated with the Magento taxonomy model. Key is the tag
     * code from Ellison and the value is the Magento attribute.
     *
     * @var array
     */
    protected $_taxonomy = array(
        'category' => 'tag_category',
        'subcategory' => 'tag_subcategory',
        'curriculum' => 'tag_curriculum',
        'subcurriculum' => 'tag_subcurriculum',
        'theme' => 'tag_theme',
        'subtheme' => 'tag_subtheme',
        'product_line' => 'tag_product_line',    // this needs to be mapped
        'artist' => 'tag_artist',
        'designer' => 'tag_designer',
        // 'subproduct_line' => 'tags_product_line',    // This will be generated using taxonomy_mapping.xls
        'material_compatibility' => 'tag_material_compatibility',
        'machine_compatibility' => 'tag_machine_compatibility',
        'calendar_event' => 'tag_event',

        // These will not be part of taxonomy; instead, will be regular attributes
        // 'grade_level' => 'tag_grade_level',
        // 'release_date' => 'release_date',
        // 'size' => 'die_size',
        // 'product_family' => '',  // Discard this
    );

    /**
     * These are part of taxonomy in Ellison's old site, but they are converted
     * into regular attributes.
     *
     * @var array
     */
    protected $_tagsToRegular = array(
        'grade_level' => 'grade_level',
        'release_date' => 'release_date',
        'size' => 'die_size',
    );

    /**
     * For ideas, tags are migrated directly but only the following.
     *
     * @var array
     */
    protected $_ideaTabToMigrate = array(
        'Standards' => 'idea_standards',
        'Introduction' => 'idea_introduction',
        'Instructions' => 'idea_instructions',
    );

    /**
     * Sizzix "tag" attributes. In Sizzix, some attributes are "tagged" and
     * stored as "tags".
     *
     * @var array
     */
    // Not used yet
    protected $_tagAttributes  = array(
        'name',
        'systems_enabled',
        'tag_type'
    );

    /**
     * Attributes that should be inherited from the default store view. It is
     * required as loading a product by store ID loads the full set of inherited
     * data and saving it explicitly assigns data to the store view.
     *
     * @var array
     */
    protected $_removeFromStore = array(
        'handling_fee',
        'name',
        'status',
        'description',
        'url_key',
        'url_path',
        'price',
        'homefeed_product',
        'tax_class_id',
        // 'media_gallery',    // Cannot prevent this from being added to all stores for some reason
        // 'price',
        // Anything else? Tax, Comp. product line?
    );

    /**
     * Array used to map Ellison's release_date tag to a timestamp
     *
     * @var array
     */
    protected $_releaseDateMapping = array(
        'january' => '01',
        'february' => '02',
        'march' => '03',
        'april' => '04',
        'may' => '05',
        'june' => '06',
        'july' => '07',
        'august' => '08',
        'september' => '09',
        'october' => '10',
        'november' => '11',
        'december' => '12'
    );

    /**
     * Possible product name prefixes for the SEO modification
     *
     * @var array
     */
    protected $_possiblePrefixes = array(
        'Sizzix.com',
        'Sizzix',
        'Ellison.com',
        'Ellison',
    );

    public function __construct()
    {
        parent::__construct();
        // $this->_init(); // Deprecated

        $this->_taxonomyMappingFile = Mage::getBasedir()
            . '/shell/sdm/migration/data/taxonomy_mappings.txt';

        $this->_compatibilityProductLineMappingFile = Mage::getBasedir()
            . '/shell/sdm/migration/data/Product Associations for Product Line For Compatibility.txt';

        $this->_taxonomyDataFile = Mage::getBasedir()
            . '/shell/sdm/migration/data/tags_images_descriptions_to_migrate.csv';

        $this->getConn()->startSetup();
    }

    public function __destruct()
    {
        parent::__destruct();
        $this->getConn()->endSetup();
    }

    public function run()
    {
        if (!$this->isExtensionEnabled('SDM_Migration')) {
            $this->log('SDM_Migration is not installed or enabled. Aborted.');
            exit;
        }

        error_reporting(-1);
        ini_set('max_execution_time', 186400);   // Many days
        ini_set('display_errors', 'On');
        ini_set('memory_limit', '10240M');
        $this->out(
            'NOTE: Class constants and properties need to be manually set prior to the migration!',
            2
        );

        /**
         * Clear log files and set up some variables to use throughout
         */
        // $this->deleteAllFiles('log');
        $this->_initMongoDb();
        $this->_initScpDb();
        $this->_initVars();
        $this->_initProductLineConsolidationMapping();
        $this->_initCompatibilityProductLineMapping();
        $this->_initTaxonomyData();
        $this->setArgs();

// echo 'TESTING!' .PHP_EOL;
// $this->deleteAllFiles('log');
// $this->_getTaxonomyImages(); die;

        /**
         * Download images
         */
        if ($this->_downloadImages) {
            $this->_getTaxonomyImages();
            $this->_getInstructionsImages();
            $this->_getProductImages();  // Download all images first
            $this->out('All images are downloaded');
        }

        /**
         * Pre-processes required for a proper migration
         */
        $this->_checkSkusAreUnique();    // Check all SKUs are unique
        $this->_initShortDescriptions();

        // These will remove manually updated data!
        if ($this->_updateData) {
            // $this->_updateRetailCustomers(); // Do not re-run
            $this->_updateCompatibilityMatrix();
            $this->_updateTaxonomyTable();
            $this->_updateDiscountMatrix();
        }

        /**
         * Migrate products
         */
        // Related product data is not available, yet.
        if (isset($this->_productType) && $this->_productType == 'products') {
            $this->_createProducts();
        }

        // Related projects data is not available
        if (isset($this->_productType) && $this->_productType == 'ideas') {
            $this->_createIdeas();
        }
    }

    /**
     * Wrapper to create Magento products from Ellison products.
     *
     * 655268: main yes; extra yes
     * 654448: main yes; extra no
     * 654324: main no; extra yes
     * 654376: main no; extra no
     * 655397,657650: has Accessories tab data
     * 27532-LG: has die size
     * 657012: has release_date
     */
    protected function _createProducts()
    {
        $skuCol = self::PRODUCT_SKU_COLUMN;

        /**
         * Sizzix "products" from table `products`
         */
        $qOrg = "SELECT p.`id`,p.`mongoid`,p.`$skuCol`
            FROM products AS p
            WHERE "
            . $this->_getOrConditionForSystemsEnabled()
            // . " AND p.`item_num` IN ('656430','656433 ')"   // For testing qqq
            // . " AND p.`item_num` IN ('654472')"
            . " GROUP BY p.`$skuCol`"
            . " ORDER BY p.`id`";
        // $this->out($qOrg); die;

        if (isset($this->_paginationSteps['products'])) {
            // print_r($this->_paginationSteps); die;
            $n = count($this->_paginationSteps['products']);
            $addLimit = true;
        } else {
            $n = 1;
            $addLimit = false;
        }

        $k = 1;
        for ($i = 0; $i < $n; $i++) {
            // Mage::log("i: $i, n: $n");
            $page = $this->_paginationSteps['products'][$i]['page'];
            $size = $this->_paginationSteps['products'][$i]['size'];

            if ($addLimit) {
                $q = $qOrg . " LIMIT $page, $size";
            }
            $products = $this->query($q);
            // print_r($products);

            $N = count($products);
            $this->out("Found $N products: page $page | size $size");
            foreach ($products as $product) {
                /**
                 * Check SKU's exists prior to loading any data. There has been
                 * significant amount of tags added to products that makes
                 * _getProductTags() significantly slow.
                 */
                if ($this->_doesSkuExist($product->{$skuCol})) {
                    $this->out('--> SKU already exists: ' . $product->{$skuCol});
                } else {
                    $allData = $this->_getAllProductData($product);
                    $this->_updateMagentoProduct($allData, 'product', "$k/$N * Total Pages");
                }
                $k++;
            }
            if ($this->_breakAfterOnePage) {
                $this->out('');
                $this->out('Exiting after one page as requested.'); exit;
            }
        }
    }

    /**
     * Wrapper to create Magento products from Ellison ideas.
     *
     * 2082: main yes; extra yes
     * 1489: main yes; extra no
     * 302: main no; extra yes
     * 126: main no; extra no
     * 10006: "Instructions" tab has images
     * 12700: multi-instructions (take EEUS always as default)
     * 10227:: has grade level
     */
    protected function _createIdeas()
    {
        $skuColProject = self::IDEA_SKU_COLUMN;

        /**
         * Sizzix "projects" from table `idea`
         */
        $q = "SELECT i.`id`,i.`mongoid`,i.`$skuColProject`
            FROM ideas AS i
            WHERE "
            . $this->_getOrConditionForSystemsEnabled()
            // . " AND i.`idea_num` IN ('654449')"   // For testing
            . " GROUP BY i.`$skuColProject`"
            . " ORDER BY i.`id`";

        if (isset($this->_paginationSteps['ideas'])) {
            $n = count($this->_paginationSteps['ideas']);
            $addLimit = true;
        } else {
            $n = 1;
            $addLimit = false;
        }

        $k = 1;
        for ($i = 0; $i < $n; $i++) {
            $page = $this->_paginationSteps['ideas'][$i]['page'];
            $size = $this->_paginationSteps['ideas'][$i]['size'];

            if ($addLimit) {
                $q = $q . " LIMIT $page, $size";
            }

            $ideas = $this->query($q);

            $N = count($ideas);
            $this->out("Found $N ideas: page $page | size $size");
            foreach ($ideas as $idea) {
                $allData = $this->_getAllIdeaData($idea);
                $this->_updateMagentoProduct($allData, 'idea', "$k/$N * Total Pages");
                $k++;
            }
            if ($this->_breakAfterOnePage) {
                $this->out('Exiting after one page as requested.'); exit;
            }
        }
    }

    protected function _updateMagentoProduct($data, $type, $progress = '')
    {
        // Mage::log($data);
        if ($type == 'product') {
            $sku = $data->{self::PRODUCT_SKU_COLUMN};
        } elseif ($type == 'idea') {
            $sku = $data->{self::IDEA_SKU_COLUMN};
        } else {
            $this->out("***Product type is not recognized: $type. Aborting.");
            exit;
        }

        $product = $this->_getMagentoProduct($sku, $type);
        if ($product->getId()) {
            $this->out("Existing Product: $progress");

            /**
             * This is deprecated in favor of checking only for SKU's existence
             * prior this method.
             */
            // if(!$this->_productDataChanged($product, $data)) {
            //     $this->out('--> No data change. Skipping.'); return;
            // }

        } else {
            $this->out("New Product");
        }
        $this->out("$progress: Processing SKU $sku ($type - ID {$data->id})");
        $product->setMd5Hash($this->getHash($data));

        /**
         * Website assignment. If false, it means that this product doesn't belong
         * to one of the websites we're migrating products to.
         */
        $webIds = $this->_getWebsiteIds($data->systems_enabled);
        if ($webIds) {
            $product->setWebsiteIds($webIds);   // Magento website IDs
        } else {
            $this->out('--> Website not applicable');
            return;
        }

        // Categorization
        $this->_setCategories($product, $data->systems_enabled);

        // `type_id`-specific data
        if ($type == 'product') {
            $product->setAttributeSetId($this->_productAttSetId);
            $product->setTypeId(self::PRODUCT_PRODUCT_TYPE);

        } elseif ($type == 'idea') {
            $product->setAttributeSetId($this->_projectAttSetId);
            $product->setTypeId(self::IDEA_PRODUCT_TYPE);

            // There is a whole bunch of data from `tag_to_idea` that I missed
            // to consider

        } else {
            $this->out("*Product type is not recognized: $type. Aborting.");
            exit;
        }

        /**
         * Default attributes
         */
        // Product availability
        $this->_setProductAvailability($product, $data);
        $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);

        // All images
        $this->_addImages($product, $data);

        // Name is rearranged
        $this->_setProductName($product, $data);

        // Straightforward
        $product->setMetaKeyword($data->keywords);
        if (isset($data->minimum_quantity) && (int)$data->minimum_quantity > 1) {
            $product->setMinQty((int)$data->minimum_quantity);
        }
        $this->_setMetaDescription($product, $data);
        // Meta title is set in _setProductName()

        // @see Mage/Tax/data/tax_setup/data-install-1.6.0.0.php
        $product->setTaxClassId(2); // Taxable Goods; all ellison products have non-tax-exmpty status

        /**
         * Custom attributes
         */
        if ($type == 'product') {
            // $product->setAvailabilityMessage($data->availability_message_szus);
            $product->setUpc($data->upc);
            $product->setWeight($data->weight);
            $product->setHandlingFee($data->handling_price_usd);    // Global scope
        }

        // PDF paths are now in the uploads/ directory
        if ($data->instructions) {
            $pdfPath = str_replace('images/', 'uploads/', $data->instructions);
            $pdfPath = ltrim($pdfPath, '/');    // Must be left-trimmed
            $product->setInstructionFile($pdfPath);
        }

        $product->setInStore(1);
        $product->setPurchaseHold(0);
        $product->setHomefeedProduct(0);    // All to No by default

        $this->_setDefaultPrice($product, $data);    // Default price
        $this->_setStatus($product, $data);
        $this->_setDescriptions($product, $data);

        /**
         * Custom attributes. Need a little more work for processing.
         */
        $this->_setLifecyle($product, $data);
        $this->_setProductType($product, $data);
        $this->_setItemGroup($product, $data);

        /**
         * Global "tab" data (ellison.tab_data_for_m, .idea_tabs; excludes "details"),
         * and additional data.
         *
         * Note: Madhavi has stated that some of this will cleaned up manually
         * and imported later
         */
        if ($type == 'product') {   // simple products
            $this->_addRelatedAccessories($product, $data->tabs['Accessories']);

            // Discount category (taxonomy in Magento)
            $this->_addDiscountCategory($product, $data);

        } else {    // grouped products
            $this->_addIdeaTabData($product, $data->tabs);

            // Establish product associations for non-simple products
            $this->_addUsedProducts($product, $data->usedProducts);
        }

        // Time stamps
        $product->setCreatedAt($data->created_at);

        // Taxonomy (note: as a custom source model)
        $this->_addTaxonomy($product, $data); // Now global attribute

        /**
         * global "Details" tab (ellison.tab_details_for_m)
         *
         * @todo Client will clean up and consolidate the attributes to a more
         * manageable set.
         */
        // Do nothing

        // Some corrections..
        // Note: These are assigned to the website level but display_start is required
        $this->_setDisplayStartDate($product, $data);

        try {
            $product->save();

            $thisId = $product->getId();
            $this->out("--> Product saved: SKU {$product->getSku()}");
        } catch (Exception $e) {
            $this->log(
                "--> Failed to save product SKU {$product->getSku()}. Error: "
                    . $e->getMessage()
            );
            return;
        }

        /**
         * Set website-specific data. Product object must be loaded again.
         *
         * @todo Qty will need to be saved on a website scope as well. Not developed yet.
         */
        $tags = $data->tags;

        // For some reason, passing the product object causes Magento to save new
        // product and subsequently causing SQL error.
        $websiteSpecificAttributes = array(
            'display_start_date' => 'start_date_',
            'display_end_date' => 'end_date_',
            'short_description' => 'description_',
            'virtual_weight_end_date' => 'virtual_weight_ends_',
            'is_orderable' => 'orderable_',  // this is boolean; manually set this.
            'availability_message' => 'availability_message_',
            'news_from_date' => 'start_date_',  // No idea why. Req. doc. said so.
            'news_to_date' => 'distribution_life_cycle_ends_',  // Ellison stores "New" in 'distribution_life_cycle_'. Used for updating news_to_date
        );

        // Converted to Magento taxonomy items
        // $websiteSpecificAttributes2 = array(
        //     'tag_machine_compatibility' => 'machine_compatibility',
        //     'tag_material_compatibility' => 'material_compatibility',
        // );

// print_r($webIds);
// print_r($this->_websiteMapping);

        // Go through all of the websites available but mapped to store IDs
        foreach ($this->_storeMapping as $code => $storeId) {

            // Note: $this->_websiteMapping[$code];  // Returns website ID
            if (!in_array($this->_websiteMapping[$code], $webIds)) {
                continue;   // Only save associated website/store
            }

            unset($product);
            $product = Mage::getModel('catalog/product')
                ->setStoreId($storeId)
                ->load($thisId);
            $this->out("Setting website $code | Store ID: $storeId");

            // Need to remove some attributes because they don't need to be
            // re-saved for each store
            foreach ($this->_removeFromStore as $att) {
                $product->unsetData($att);
            }
            // $product->setData(array());  // Can't use because it removed essential product data

            $this->_setSpecificPrices($product, $data);

            // UK has its own inventory
            if ($storeId == 7) {
                $this->_updateUkInventory($product, $data);
            }

            // Shipping surchages/handling fees
            if ($type == 'product') {
                if ($storeId == 7) {
                    if ($data->handling_price_gbp > 0) {
                        $product->setHandlingFee($data->handling_price_gbp);
                    }

                    // Loop does not iterate through Euro store, so do it manually
                    // to avoid loading an entire product object just for this save.
                    $this->getConn('core_write')->query(
                        "DELETE FROM `catalog_product_entity_varchar`
                        WHERE entity_id = {$product->getId()} AND attribute_id = 157 AND store_id = 4"
                    );
                    if ($data->handling_price_eur > 0) {
                        $this->getConn('core_write')->query(
                            "INSERT INTO `catalog_product_entity_varchar` (`entity_type_id`,`attribute_id`,`store_id`,`entity_id`,`value`)
                            VALUES (4, 157, 4, {$product->getId()}, {$data->handling_price_eur})"
                        );
                    }
                }
            }

            // For products and ideas
            // $value = null skips saving the attribute data
            // $value = '' overwrites with null value
            foreach ($websiteSpecificAttributes as $magentoAtt => $ellisonAtt) {
                $value = null;
                if (!isset($data->{$ellisonAtt . $code}) || empty($data->{$ellisonAtt . $code})) {
                    continue;
                }

                $ellisonValue = $data->{$ellisonAtt . $code};

                // And now, let the Game of Custom Logics begin.
                if ($magentoAtt == 'news_to_date' || $magentoAtt == 'news_from_date') {
                    $distributionLifeCycle = $data->{'distribution_life_cycle_' . $code};   // Could be "New"

                    // Only migrate if "New" and not expired
                    if ($distributionLifeCycle == 'New') {
                        // End date should always be checking distribution_life_cycle_ends_<code>
                        $endDate = strtotime($data->{'distribution_life_cycle_ends_' . $code});
                        $today = strtotime(now());
                        if ($endDate > $today) {
                            $value = $ellisonValue;
                        } else {
                            $value = '';
                        }
                    } else {
                        $value = '';
                    }

                } elseif ($magentoAtt == 'availability_message') {  // Depends on is_orderable
                    if ($data->{'orderable_' . $code} == 0) {
                        $value = trim($ellisonValue);
                    } else {
                        $value = '';
                    }

                } elseif ($magentoAtt == 'display_start_date') {
                    // If not empty, just use SZUS
                    if ($ellisonValue) {
                        $value = $ellisonValue;
                    } else {
                        $value = $data->{$ellisonAtt . 'szus'};
                    }

                } elseif ($magentoAtt == 'display_end_date') {
                    // Only migrate if date is expired. Otherwise, leave it blank.
                    $endDate = strtotime($ellisonValue);
                    $today = strtotime(now());
                    if ($endDate <= $today) {
                        $value = $ellisonValue;
                    } else {
                        $value = '';
                    }

                } else {
                    // For all other cases, clean and just use the mapped value
                    $value = $this->_helper->cleanStr($ellisonValue);
                }

                if ($ellisonAtt == 'distribution_life_cycle_') {
                    if ($value == 'New') {  // Don't know why but Ellison has non-date in a date field
                        $value = null;  // Skip saving this if it ever comes to it. It shouldn't.
                    }
                }

                $keyword = $this->_helper->transformAttributeName($magentoAtt);

                // Regardless of what field it is, is it's a non-valid timestamp, replace it
                $value = $this->_fixZeroDate($value);

                if ($value == '') {
                    // eval('$product->set' . $keyword . '(null);');
                    $product->{'set' . $keyword}(null);
                } elseif (!is_null($value)) {
                    // eval('$product->set' . $keyword . '($value);');
                    $product->{'set' . $keyword}($value);
                    // $this->out('$product->set' . $keyword . '($value);');
                    // $this->out("Value | {$ellisonAtt}{$code}: $value", 2);
                } else {
                    eval('$product->set' . $keyword . '(null);');
                }
            }

            // Compatibility product line
            $this->_addCompatibilityProductLine($product, $data, $code); // Now global attribute

            try {
                // Mage::log($product->debug());
                $product->save();
                // $this->out("   --> Website $code data saved.");
            } catch (Exception $e) {

                $this->log(
                    "   --> Failed to save product SKU {$product->getSku()} ($code). Error: "
                        . $e->getMessage()
                );
            }
        }   // End of website iteration

        // Videos are a separate model from products: see AHT_Advancedmedia.
        // Must be run after doing store saves as it gets removed otherwise.
        $this->_saveVideo($product, $data);

        $this->out($this->getMemoryUsageNow());
    }

    /**
     * Process Ellison's taxonomy data. Not all Ellison Taxonomy becomes parts of
     * Magento's taxonomy. Some are converted into regular attributes.
     *
     * Note that product line and subproduct
     * line has custom mapping provided in taxonomy_mapping.xls.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $tags
     */
    protected function _addTaxonomy($product, $data)
    {
        $tags = $data->tags;

        // These have a Magento taxonomy model
        foreach ($this->_taxonomy as $ellisonAtt => $magentoAtt) {
            // $this->out("Taxonomy: $ellisonAtt ($magentoAtt)");

            if (isset($tags[$ellisonAtt])) {

                // Special case
                if ($ellisonAtt == 'product_line') {    // This has mapped values
                    $this->_addProductLineTaxonomy( // add product and subproduct lines
                        $product,
                        $tags[$ellisonAtt]
                    );

                // Typical case
                } else {
                    $valuesIdsToAdd = array();
                    foreach ($tags[$ellisonAtt] as $value) {
                        if ($ellisonAtt == 'calendar_event') {
                            $magentoTaxAtt = 'event';   // Fixes the calendar event tag from not getting assigned
                        } else {
                            $magentoTaxAtt = $ellisonAtt;
                        }

                        // Check using the taxonomy code
                        $tagCode = Mage::helper('taxonomy')->transformNameToCode($value['name']);
                        $temp = $this->_findTaxonomyId($magentoTaxAtt, $tagCode);

                        if ($temp) {
                            // $this->out(">> {$value['name']} --> ID $temp");
                            $valuesIdsToAdd[] = $temp;
                        } else {
                            // $this->out(">> {$value['name']} --> ID N/A");
                        }
                    }
                }

                if (!empty($valuesIdsToAdd)) {
                    $product->setData($magentoAtt, implode(',', $valuesIdsToAdd));
                    // $this->out('Values: ' . implode(',', $valuesIdsToAdd));
                    unset($valuesIdsToAdd);
                }

            } else {
                // $this->out('Values: N/A');
            }
            // $this->out('');
        }

        // Ellison taxonomy items that are converted into regular attributes in
        // Magento and are global
        foreach ($this->_tagsToRegular as $ellisonAtt => $magentoAtt) {
            if (!isset($tags[$ellisonAtt])) {
                continue;
            }

            // Multiselect
            if ($ellisonAtt == 'calendar_event' || $ellisonAtt == 'grade_level') {
                $ids = array();
                foreach ($tags[$ellisonAtt] as $tag) {
                    $ids[] = $this->_getAttributeOptionId($tag['name'], $magentoAtt);
                }
                $product->setData($magentoAtt, implode(',', $ids));

            // Select
            } else {
                $tag = reset($tags[$ellisonAtt]);   // Just get the first item of array

                if ($magentoAtt == 'release_date') {
                    $releaseDate = $this->_setReleaseDate($product, $tag['name']);
                } else {
                    $optId = $this->_getAttributeOptionId($tag['name'], $magentoAtt);
                    $product->setData($magentoAtt, $optId);
                }
            }
        }
    }

    /**
     * Sets the default display_start date. Use ERUS's. If not availble, use
     * SZUS's if brand = Sizzix or EEUS's if brand = Ellison.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param stdClass $data
     */
    protected function _setDisplayStartDate($product, $data)
    {
        $timestamp = null;
        if ($data->start_date_erus) {
            $timestamp = $data->start_date_erus;
        } else {
            if ($product->getBrand() == 3) {
                $timestamp = $data->start_date_szus;
            } else {
                $timestamp = $data->start_date_eeus;
            }
        }
        $timestamp = $this->_fixZeroDate($timestamp);
        $product->setDisplayStartDate($timestamp);
    }

    /**
     * Converts the month-year string into a timestamp
     *
     * @param Mage_Catalog_Model_Product $product
     * @param str $word
     *
     * @return str
     */
    protected function _setReleaseDate($product, $str)
    {
        $str = strtolower($str);
        $data = explode(' ', $str);
        $month = trim($data[0]);
        $year = trim($data[1]);

        if (isset($this->_releaseDateMapping[$month])
            && $month && $year
        ) {
            $date = "$year-{$this->_releaseDateMapping[$month]}-01";
            $product->setReleaseDate($date);
        }
    }

    /**
     * Process Ellison's taxonomy data. Not all Ellison Taxonomy becomes parts of
     * Magento's taxonomy. Some are converted into regular attributes.
     *
     * Note that product line and subproduct
     * line has custom mapping provided in taxonomy_mapping.xls.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $tags
     * @param $websiteCode str
     */
    protected function _addCompatibilityProductLine($product, $data, $websiteCode)
    {
        $tags = $data->tags;

        // Compatibility product line (different from taxonomy and managed separately)
        if (isset($tags['product_line'])) { // Supposed to be one product line per product

            $productLine = reset($tags['product_line']);    // One tag assumed
            if (strpos($productLine['system_enabled'], $websiteCode) !== false) {

                // Check if SKU has a custom-mapped product line
                if (isset($this->_compatibilityProductLineMapping[$product->getSku()])) {
                    // $this->out('----> Custom compatibility prod line mapping');
                    $code = $this->_compatibilityProductLineMapping[$product->getSku()];
                } else {
                    // $this->out('----> NO Custom compatibility prod line mapping');
                    $code = Mage::helper('taxonomy')->transformNameToCode($productLine['name']);
                }

                $prodLineId = $this->_findProductLineId($code);

                if (!$prodLineId) {
                    $this->log(
                        'Compatibility product line not found! ' . $productLine['name']
                    );
                } else {
                    $product->setCompatibilityProductLine($prodLineId);
                }
            }
        }
    }

    protected function _getOrConditionForSystemsEnabled()
    {
        $str = '(';
        $conditions = array();

        foreach ($this->_systemCodes as $one) {
            $conditions[] = "systems_enabled LIKE '%$one%'";
        }
        $str .= implode(' OR ', $conditions);

        return $str . ')';
    }

    protected function _addDiscountCategory($product, $data)
    {
        $id = $this->_findTaxonomyId(
            'discount_category',
            $this->_helper->transformNameToCode($data->discount_category_name)
        );

        if ($id) {
            $product->setTagDiscountCategory((int)$id);
        }
    }

    protected function _isValidDateTimeString($str) {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $str);

        return ($date === false ? false : true);
    }

    /**
     * Add associated SKUs to ideas, which are grouped products.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $skus
     */
    protected function _addUsedProducts($product, $skus)
    {
        $ids = array();
        // Reload because cataloginventory_stock_item has issues; ? - not sure what this is about YK
        // $product = Mage::getModel('catalog/product')->load($product->getId());

        // Get entity IDs of the associated products
        foreach ($skus as $sku) {
            $id = Mage::getModel("catalog/product")->getIdBySku($sku);
            if ($id) {
                $ids[$id] = array(
                    'qty'      => 1,
                    'position' => 0,
                    // 'ids'      => $id
                );
            }
        }

        $product->setGroupedLinkData($ids);
    }

    /**
     * Add related accessories for the given product. It's a global attribute.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     */
    protected function _addRelatedAccessories($product, $data)
    {
        // Discard website codes; related accessories are global
        $product->setRelatedAccessories($data['skus']);
    }

    /**
     * For ideas, the following tabs are global attributes with some
     * custom things added.
     * - 'Standards', 'Introduction': always global
     * - 'Instructions': pick EEUS as the default data. Discard the rest.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     */
    protected function _addIdeaTabData($product, $data)
    {
        foreach ($this->_ideaTabToMigrate as $ideaTab => $magentoAtt) {
            if(isset($data[$ideaTab])) {
                // && strpos($data[$ideaTab]['system_enabled'], $code) !== false) {

                $keyword = $this->_helper->transformAttributeName($magentoAtt);

                if ($ideaTab === 'Instructions') {
                    // Pick EEUS as the default, if available
                    $i = $this->_getDefaultInsturctionDataKey($data[$ideaTab]);
                    $value = $this->_helper->cleanStr($data[$ideaTab][$i]['text']);
                    // $value = $this->_helper->cleanStr($data[$ideaTab][$i]['text']);  // Special character causes data loss

                    // Process image
                    $images = $this->_extractInstructionImages($data[$ideaTab][$i]['images']);

                    // Add it as a media gallery image
                    foreach ($images as $image) {
                        try {
                            $label = key($image);
                            $imageFilename = reset($image);
                            $imgPath = Mage::getBaseDir() . DS . 'shell' . DS . 'images'
                                . DS . 'insturctions' . DS . $product->getSku() . '-' . $imageFilename;

                            $product->addImageToMediaGallery(
                                $imgPath,
                                array(),
                                false,
                                false,
                                "instruction:$label"    // Rewrote to add labels
                            );
                        } catch (Exception $e) {
                            $this->log(
                                "Failed to add image: $imgPath. Error: "
                                    . $e->getMessage()
                            );
                        }
                    }

                } else {
                    $value = $this->_helper->cleanStr($data[$ideaTab]['text']);
                    // $value = $this->_helper->cleanStr($data[$ideaTab]['text']);  // Special character causes data loss
                }

                if (!empty($value)) {
                    eval('$product->set' . $keyword . '($value);');
                    // $this->out('$product->set' . $keyword . '($value);');
                    // $this->out("Value | {$ideaTab}{$code}: $value", 2);
                }
            }
        }
    }

    /**
     * Given instruction tab data of ideas, return EEUS as the default, if available.
     *
     * @param array $data
     *
     * @return str
     */
    protected function _getDefaultInsturctionDataKey($data)
    {
        // First, if only one Instruction, then just pick that
        if (count($data) == 1) {
            return 0;

        } else {
            foreach ($data as $i => $one) {
                if (strpos($one['system_enabled'], 'eeus') !== false) {
                    return $i;
                }
            }
        }

        // If still nothing is returned, just return the first one.
        return 0;
    }

    /**
     * Given a string consisting of comma-delimited Instruction tab figure and image file
     * names, return an array whose keys are the figure names and values are the
     * file names. Note that the returned array is two-level deep.
     *
     * @param  str $str
     *
     * @return array
     */
    protected function _extractInstructionImages($str)
    {
        $imagesDecoded = array();
        $str = trim($str);
        if (empty($str)) {
            return $imagesDecoded;
        }

        $images = explode(',', $str);
        $i = 0;

        foreach ($images as $imageStr) {
            $data = explode('---', $imageStr);
            $label = str_replace('(', '', trim($data[0]));
            $label = str_replace(')', '', trim($label));

            // Note that the assumption that all labels are unique cannot be made.
            // Therefore, keep an extra array layer with $i
            $imagesDecoded[$i][$label] = trim($data[1]);

            $i++;
        }

        return $imagesDecoded;
    }

    /**
     * Returns the attribute option ID of the option value
     *
     * @param str $value
     * @param str $attribute
     *
     * @return int
     */
    protected function _getAttributeOptionId($value, $attribute)
    {
        $attribute = Mage::getModel('eav/config')
            ->getAttribute('catalog_product', $attribute);
        $attributeValueId = $this->_getAttributeValueId(
            $attribute,
            $value
        );
        $this->_recursiveCount = 0; // reset counter

        return $attributeValueId;
    }

    /**
     * Returns the taxonomy IDs of product line given. Invocation of this function
     * assumes that the product is enabled for the website, $code. It is possible
     * that the tags have inconsistent website scopes compared to the product,
     * as they have their own website assignments. Therefore, the tags' own
     * scopes must be compared with $code before setting $product data.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $tags Product tag data only
     *
     * @return array
     */
    protected function _addProductLineTaxonomy($product, $tags)
    {
        $helper = Mage::helper('taxonomy');
        $ids = array();
        $ids2 = array();
// print_r($tags);
// Mage::log($this->_productLineMapping);
        foreach ($tags as $tag) {
            // Check is tag is applicable for this website scope
// $this->out("Scope: $code vs {$tag['system_enabled']}");
            unset($id); unset($id2);
            $key = Mage::helper('taxonomy')->transformNameToCode($tag['name']);

            // If mapping is available, using mapped data, which includes subproduct line
            if (isset($this->_productLineMapping[$key])) {
// $this->out("Mapping found: $code, {$tag['name']}, {$tag['system_enabled']}");
                $row = $this->_getTagRecord($key);
// var_dump($row);
                $code1 = $helper->transformNameToCode($row->product_line);
                $code2 = $helper->transformNameToCode($row->subproduct_line);

                // Find existing tax item IDs
                $id = $this->_findTaxonomyId('product_line', $code1);
                $id2 = $this->_findTaxonomyId('subproduct_line', $code2);

            // No mapping; Ellison will need to review this and fix manually.
            } else {
                $this->log("No product line mapping for {$tag['name']} found using key $key. SKU '{$product->getSku()}'");
                $this->log($tag, null, null, false);
                // $id = $this->_findTaxonomyId('product_line', $key);
                continue;
            }

            if (isset($id)) {  // Product line IDs
                $ids[] = $id;
            }
            if (isset($id2)) {  // Product line IDs
                $ids2[] = $id2;
            }
        }

        $ids = array_unique($ids);
        $ids2 = array_unique($ids2);
        if (!empty($ids)) {
            $product->setData('tag_product_line', implode(',', $ids));
        }
        if (!empty($ids2)) {
            $product->setData('tag_subproduct_line', implode(',', $ids2));
        }
    }

    /**
     * Returns the unmapped/compatibility product line record ID
     *
     * @param str $code
     *
     * @return int
     */
    protected function _findProductLineId($code)
    {
        $id = Mage::getResourceModel('compatibility/productline')->getIdByCode($code);

        return $id;
    }

    /**
     * Returns the taxonomy record ID
     *
     * @param str $type
     * @param str $code
     *
     * @return int
     */
    protected function _findTaxonomyId($type, $code)
    {
        $id = Mage::getResourceModel('taxonomy/item')->getIdByCode($type, $code);

        return $id;
    }

    /**
     * Returns the EAV value ID of the desired option value. If it doesn't exist,
     * save the option value, and return its ID. Recursive.
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param str $value
     * @param bool $reload
     *
     * @return str
     */
    protected function _getAttributeValueId($attribute, $value, $reload = false)
    {
        $this->_recursiveHandBreak();

        if ($reload) {
            $attribute = Mage::getModel('eav/config')
                ->getAttribute('catalog_product', $attribute->getAttributeCode());
        }
        $attributeId = $attribute->getAttributeId();
        $attributeOptions = $attribute->getSource()->getAllOptions(false);

        // Loop through and find the value
        foreach ($attributeOptions as $option) {
            if ($option['label'] == $value) {
                $valueId = $option['value'];    // ID
                return $valueId;
            }
        }

        // Value doesn't exist in the attribute at this point
        $newOption['value']['option'] = array($value);
        $newOption['order']['option'] = 0;
        $attribute->setData('option', $newOption);
        // $attribute->setDefaultValue($attribute->getSource()->getOptionId($option));  // doesn't work; don't need it.

        try {
            $attribute->save();
// echo 'Saving...' . PHP_EOL;
// print_r($newOption);
        } catch (Exception $e) {
            $this->log(
                "Failed to save value $value for attribute "
                    . $attribute->getAttributeCode()
            );
        }

        return $this->_getAttributeValueId($attribute, $value, true);   // Recursive
    }

    /**
     * Saves Youtube videos. See AHT_Advancedmedia for more details.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param stdClass $data
     */
    protected function _saveVideo($product, $data)
    {
        if (!isset($data->video) || empty($data->video)) {
            return;
        }
        $productId = $product->getId();

        $this->_removeVideo($product);  // Remove existing videos first
        $this->_addVideo($product, $data->video);   // add a video
    }

    protected function _addVideo($product, $videoStr)
    {
        // width="560" height="315"
        $embeddedStr = '<iframe src="//www.youtube.com/embed/'
            . $videoStr . '" frameborder="0" allowfullscreen></iframe>';
        $label = '';    // This is not available from Sizzix's DB

        $video = Mage::getModel('advancedmedia/advancedmedia')
            ->setProductId($product->getId())
            ->setMediaType(1)
            ->setMediaEmbed($embeddedStr)
            ->setMediaLabel($label)
            ->setUseType(2)
            ->setMediaPosition(0)
            ->setIsExclude(0);

        try {
            $video->save();
        } catch (Exception $e) {
            $this->log(
                "Failed to save YouTube video. Product ID {$product->getId()}"
                    . ". Error: {$e->getMessage()}"
            );
        }
    }

    protected function _removeVideo($product)
    {
        $oldVideos = Mage::getModel('advancedmedia/advancedmedia')
            ->getCollection()
            ->addFieldToSelect(array('advancedmedia_id','product_id'))
            ->addFieldToFilter('product_id', $product->getId());

        if (count($oldVideos) > 0){
            foreach($oldVideos as $one){
                try {
                    Mage::getModel('advancedmedia/advancedmedia')
                        ->load($one->getId())
                        ->delete();
                } catch (Exception $e) {
                    $this->log(
                        "Failed to delete YouTube video. Product ID {$product->getId()}"
                            . ". Error: {$e->getMessage()}"
                    );
                }
            }
        }
    }

    /**
     * Sets 'brand'
     *
     * @param Mage_Catalog_Model_Product $product
     * @param stdClass $data
     */
    protected function _setItemGroup($product, $data)
    {
        $magentoAttribute = 'brand';
        $sizzixValue = trim($data->item_group);

        if (empty($sizzixValue)) {
            $this->log("Failed to save 'brand'. Sizzix data is unavailable");
            return;
        } else {
            $thisValue = $sizzixValue;
        }

        $attribute = Mage::getModel('eav/config')
            ->getAttribute('catalog_product', $magentoAttribute);
        $attributeValueId = $this->_getAttributeValueId(
            $attribute,
            $thisValue
        );
        $this->_recursiveCount = 0; // reset counter

        $product->setBrand($attributeValueId);
    }

    /**
     * Set 'product_type'. Does not apply to ideas/projects.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param stdClass $data
     */
    protected function _setProductType($product, $data)
    {
        if ($product->getTypeId() == self::IDEA_PRODUCT_TYPE) {
            return;
        }

        $magentoAttribute = 'product_type';
        $sizzixValue = trim($data->item_type);

        if (empty($sizzixValue)) {
            $this->log("Failed to save 'product_type'. Sizzix data is unavailable");
            return;
        } else {
            $thisValue = ucfirst($sizzixValue);
        }

        $attribute = Mage::getModel('eav/config')
            ->getAttribute('catalog_product', $magentoAttribute);
        $attributeValueId = $this->_getAttributeValueId(
            $attribute,
            $thisValue
        );
        $this->_recursiveCount = 0; // reset counter

        $product->setProductType($attributeValueId);
    }

    /**
     * Set 'life cyle'. Does not apply to ideas.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param stdClass $data
     */
    protected function _setLifecyle($product, $data)
    {
        if ($product->getTypeId() == self::IDEA_PRODUCT_TYPE) {
            return;
        }

        $magentoAttribute = 'life_cycle';
        $sizzixValue = trim($data->life_cycle);

        // Mapping
        if ($sizzixValue == strtolower('pre-release')) {
            $thisValue = 'Pre-Release';
        } elseif ($sizzixValue == strtolower('unavailable')) {
            $thisValue = 'Inactive';
        } elseif ($sizzixValue == strtolower('discontinued')) {
            $thisValue = 'Discontinued';
        } elseif ($sizzixValue == strtolower('available')) {
            $thisValue = 'Active';
        } else {
            $this->log("Unable to map Sizzix life cycle to Magento: $sizzixValue");
            return;
        }

        $attribute = Mage::getModel('eav/config')
            ->getAttribute('catalog_product', $magentoAttribute);
        $attributeValueId = $this->_getAttributeValueId(
            $attribute,
            $thisValue
        );
        $this->_recursiveCount = 0; // reset counter

        // Set only if it's different value
        if ((int)$product->getLifeCycle() !== (int)$attributeValueId) {
            $product->setLifeCycle($attributeValueId);
        }

        // On inital import, set to "no" if LC = 'pre-release'. Otherwise, 'yes'.
        if ($thisValue == 'Pre-Release') {
            $product->setInStore(0);
        } else {
            $product->setInStore(1);
        }
    }

    /**
     * Note only MSRP is for all stores/websites. "Oulet price" for Sizzix.com
     * is not migrated. "Wholesale price" is only for retailer. Pound and Euro
     * prices are for the UK site.
     */
    protected function _setDefaultPrice($product, $data)
    {
        if ($product->getTypeId() == self::IDEA_PRODUCT_TYPE) {
            return;
        }

        $product->setPrice($data->msrp_usd);
        $product->setSpecialPrice(null);    // This must be set at the global scope
                                            // in order for store-level  special price to be loaded
    }

    /**
     * Ellison has website level prices. Euro pricing is global but has custom
     * implementation to apply for only the UK Euro store.
     *
     * Only SZUK and ERUS get unique 'price'.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     */
    protected function _setSpecificPrices($product, $data)
    {
        if ($product->getTypeId() == self::IDEA_PRODUCT_TYPE) {
            return;
        }

        // SZUK GBP: Uses 'price', assign Euro prices
        if ($product->getStoreId() == 7) {
            $product->setPrice($data->msrp_gbp);
            $product->setPriceEuro($data->msrp_eur);    // Note: global attribute

        // ERUS: uses 'price' and 'msrp'
        } elseif ($product->getStoreId() == 5) {
            $product->setMsrp($data->msrp_usd);
            if ($data->wholesale_price_usd > 0) {
                $product->setPrice($data->wholesale_price_usd);
            } else {
                $product->setPrice(round($data->msrp_usd/2, 2));
            }


        // SZUS: may have special price
        } elseif ($product->getStoreId() == 1) {
            if ($data->outlet == 1 && $data->price_szus_usd > 0) {
                $product->setSpecialPrice($data->price_szus_usd);
            }
        }
    }

    protected function _setStatus($product, $data)
    {
        if ($data->active == 1) {
            $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        } else {
            $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
        }
    }

    /**
     * Name is rearranged when applicable for SEO.
     *
     * From : Brand - Product Line - Product Name
     * To: Product Name - Product line - Brand
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     */
    protected function _setProductName($product, $data)
    {
        $newName = trim($data->name);
        $newName = $this->_helper->cleanStr($newName);

        // Re-naming for SEO requested by Stewart;
        // however, Ellison requested this to be removed.
        // foreach ($this->_possiblePrefixes as $prefix) {
        //     // If prefix matches, a new name must be constructed, even if no hyphens are found
        //     if (strpos($name, $prefix) !== false) {
        //         $k = strpos($name, $prefix);
        //         $subName = str_replace($prefix, '', $name);

        //         $bow = explode('-', $subName);  // bag of words

        //         if (count($bow) > 1) {
        //             $prodLine = reset($bow);
        //             unset($bow[0]);
        //             $prodName = implode('-', $bow);
        //             $newName = trim($prodName) .  ' - ' . trim($prodLine) . ' - ' . $prefix;
        //         } else {
        //             $newName = trim($subName) . ' - ' . $prefix;
        //         }
        //         break;
        //     }
        // }

        $product->setName($newName);
        $product->setMetaTitle($newName);
        // $this->log("Old name: $name", null, null, false);
        // $this->log("New name: $newName", null, null, false);
    }

    /**
     * Set meta description using SCP's short description. Use up to 150
     * cahracters and don't trim whole words.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param stdClass $data
     */
    protected function _setMetaDescription($product, $data)
    {
        $sku = $product->getSku();

        if (isset($this->_shortDescriptions[$sku])) {
            $str = $this->_shortDescriptions[$sku];
            if (preg_match('/^.{1,150}\b/s', $str, $match)) {
                $line = $match[0];
            }
            $product->setMetaDescription($line);
            // $this->log("Short descrip. Found: SKU $sku.", null, null, false);
            // $this->log(substr($line, 0, 150), null, null, false);
        } else {
            // $this->log("Short descrip. NOT found: SKU $sku.", null, null, false);
        }

        // $product->setMetaKeyword($data->keywords);   // Set in the main method
    }

    /**
     * Adds images to the product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     */
    protected function _addImages($product, $data)
    {
        $sku = $product->getSku();
        $imgPaths = array();

        if ($product->getTypeId() == 'simple') {
            $code = 'products';
        } else {
            $code = 'ideas';
        }

        $mainImg = trim($data->image_filename);
        $galleryImgs = explode('|', trim($data->images, '|'));

        // Add main image; may have secondary source
        if (!empty($mainImg)) {
            $hasExtraImg = false;
            $imgPaths[] = $this->_shellDir . DS . 'images' . DS . $code . DS . 'main' . DS . $mainImg;
        } else {
            $hasExtraImg = true;
            $extraImgPath = $this->_shellDir . DS . 'images' . DS . 'extras' . DS . $code . DS . "$sku.jpg";
            if (file_exists($extraImgPath)) {
                $imgPaths[] = $extraImgPath;
            }
        }

        // Add additional/galery images
        if (isset($galleryImgs)) {
            foreach ($galleryImgs as $one) {
                if (!empty($one)) {
                    $imgPaths[] = $this->_shellDir . DS . 'images' . DS . $code . DS . 'additional' . DS . $one;
                }
            }
        }

        if ($product->getId()) {
            $this->_removeExistingImages($product);
        } else {
            $product->setMediaGallery(
                array('images' => array(), 'values' => array())
            ); //media gallery initialization
        }

        $i = 0;
        foreach ($imgPaths as $imgPath) {
            if ($i == 0) {  // only first image get defult assignment
                $assignment = array('image', 'thumbnail', 'small_image');
            } else {
                $assignment = array();
            }

            try {
                $product->addImageToMediaGallery(
                    $imgPath,
                    $assignment,
                    false,
                    false
                );
            } catch (Exception $e) {
                $this->log(
                    "Failed to add image from $imgPath: . Error: "
                        . $e->getMessage()
                );
            }

            $i++;
        }
    }

    /**
     * Remove all existing images. Do for all types of product media images.
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _removeExistingImages($product)
    {
        $gallery1 = $product->getMediaGalleryImages();   // Varien_Data_Collection
        $gallery2 = $product->getInstructionImages();

        $this->_removeImages($product, $gallery1);
        $this->_removeImages($product, $gallery2);

        $product->setMediaGallery(array());
    }

    /**
     * Removes the images in the collection
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Data_Collection $gallery
     */
    protected function _removeImages($product, $gallery)
    {
        foreach ($gallery as $image) {
            try {
                if (unlink($image->getPath())) {          // Delete the actual file
                    // $this->out('Deleted file: ' . $image->getPath());
                } else {
                    // $this->out('Failed to delete file: ' . $image->getPath());
                }
                $this->_removeImageRecord($image->getValueId());    // ID of the record to remove

            } catch (Exception $e) {
                $this->log(
                    'Failed to remove image: ' . $image->getFile()
                );
            }
        }
    }

    /**
     * Remove the media gallery record directly
     *
     * @param int $id
     */
    protected function _removeImageRecord($id)
    {
        if (!$id) {
            $this->log('No gallery image value ID passed');
            return;
        }

        $q = "DELETE FROM `catalog_product_entity_media_gallery`
            WHERE `value_id` = '$id'";
        $this->getConn('core_write')->query($q);
    }

    /**
     * DEPRACTED: This does not seem to actually remove the records in DB
     *
     * Removes the images in the collection
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Data_Collection $gallery
     */
    // protected function _removeImages($product, $gallery)
    // {
    //     $attribute = Mage::getModel('catalog/resource_eav_attribute')
    //         ->loadByCode($product->getEntityTypeId(), 'media_gallery');
    //     foreach ($gallery as $image) {
    //         $info = pathinfo($image->getPath());
    //         $data[] = $info['basename'];
    //         try {
    //             $attribute->getBackend()->removeImage($product, $image->getFile());
    //             // When removing image, the actual files are not removed
    //             unlink($image->getPath());  // manually remove then
    //             $this->out('Deleted: ' . $image->getPath());
    //             // $image->delete();    // this not how deletion works

    //         } catch (Exception $e) {
    //             $this->log(
    //                 'Failed to remove image: ' . $image->getFile()
    //             );
    //         }
    //     }
    // }

    /**
     * Returns the media image directory path
     * @return string
     */
    protected function _getImagePath()
    {
        return Mage::getBaseDir('media') . DS . 'import';
    }


    /**
     * Use item group to determine which description to use in this initial
     * description update. Ellison will update store-specific descriptions
     * later via a feed.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     */
    protected function _setDescriptions($product, $data)
    {
        $des = '';

        if ($data->item_group == 'Sizzix') {
            $des = $this->_helper->cleanStr($data->description_szus);
            if (empty($des)) {
                $des = $this->_helper->cleanStr($data->description_szuk);
                if (empty($des)) {
                    $des = $this->_helper->cleanStr($data->description_erus);
                }
            }
        } else {    // "Ellison" and "Third Party"
            $des = $this->_helper->cleanStr($data->description_eeus);
        }

        // Set "description" (for both products and ideas)
        if (!empty($des)) {
            $product->setDescription($des);
        }

        // Set "objective" (ideas only)
        if ($product->getTypeId() === self::IDEA_PRODUCT_TYPE) {
            $objective = $this->_helper->cleanStr($data->objective);
            if (!empty($objective)) {
                $product->setObjective($objective);
            }
        }
    }

    /**
     * Note that Ellison has two inventory pools, one UK and another for the rest
     * of the websites.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     * @param bool $isUk
     */
    protected function _setProductAvailability($product, $data)
    {
        $stock = array('is_in_stock' => 1);

        // Status
        if ($data->active) {
            $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        } else {
            $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
        }

        // Stock
        if ($product->getId()) {    // Existing product
            if ($product->getTypeId() == self::PRODUCT_PRODUCT_TYPE) {
                $stock['qty'] = $data->quantity_us;
            } elseif ($product->getTypeId() == self::IDEA_PRODUCT_TYPE) {
                $stock['qty'] = 0;
            } else {
                $this->log("**Product type is not recognized: {$product->getSizzixProductType()}.");
            }
        } else {    // New product
            if ($product->getSizzixProductType() == 'product') {
                $stock['qty'] = $data->quantity_us;
            } elseif ($product->getSizzixProductType() == 'idea') {
                $stock['qty'] = 0;
            } else {
                $this->log("***Product type is not recognized: {$product->getSizzixProductType()}.");
            }
        }

        $product->setStockData($stock);
    }

    /**
     * Updates Aitoc's stock item table with UK inventory
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     */
    protected function _updateUkInventory($product, $data)
    {
        if ($product->getTypeId() == self::IDEA_PRODUCT_TYPE) {
            return;
        }

        $ukQty = $data->quantity_uk;
        $productId = $product->getId();

        if ($productId) {
            $q = "UPDATE `aitoc_cataloginventory_stock_item`
                SET qty = $ukQty, use_default_website_stock = 0
                WHERE product_id = $productId AND website_id = 3"; // 3 is for SZUK
            // Mage::log($q);
            $this->getConn('core_write')->query($q);
        }

    }

    /**
     * Checks the `systems_enabled` column to identify websites to be assigned.
     * Returns false if no website assignment.
     *
     * @param array $codes
     * @param bool $override
     *
     * @return array|null
     */
    protected function _getWebsiteIds($codes, $override = false)
    {
        $webIds = array();
        if ($override) {
            $allSystemCodes = array_flip($this->_websiteMapping);
        } else {
            $allSystemCodes = $this->_systemCodes;
        }

        // Only migrate products of desired website
        foreach ($allSystemCodes as $systemCode) {
            if (strpos($codes, $systemCode) !== false) {
                if (!isset($this->_websiteMapping[$systemCode])) {
                    $this->log("Website mapping is incomplete: $codes vs. $systemCode");
                }
                $webIds[] = $this->_websiteMapping[$systemCode];
            }
        }

        if (empty($webIds)) {
            return null;
        }

        return $webIds;
    }

    /**
     * Categorize product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param str $enabledSystems
     *
     * @todo Finish discount and possibly other categorization
     */
    protected function _setCategories($product, $enabledSystems)
    {
        $catIds = array();
        $enabledSystems = explode('|', trim($enabledSystems, '|'));

        foreach ($enabledSystems as $code) {
            if ($code == 'eeuk') {  // Website no longer exists
                continue;
            }
            $catIds[] = $this->_categories[$code];
        }

        $product->setCategoryIds($catIds);

        // Discount categories are actually categorized in Sizzix
        // Add this in.
    }

    /**
     * Returns am empty product object or an existing one, if available
     */
    protected function _getMagentoProduct($sku, $type)
    {
        $q = "SELECT entity_id FROM catalog_product_entity WHERE sku = '$sku'";
        $id = $this->getConn()->fetchOne($q);  // false on no hit; returns first value of row

        if ($id === false) {
            $product = Mage::getModel('catalog/product')
                ->setSku($sku)
                ->setSizzixProductType($type);    // temporary indicator for new products
        } else {
            $product = Mage::getModel('catalog/product')->load($id);
        }

        return $product;
    }

    /**
     * -------------------------------------------------------------------------
     * Functions for Ellison's original database -------------------------------
     * -------------------------------------------------------------------------
     */

    /**
     * Fetch all product images and save them locally. These include "product",
     * "ideas", and "instruction" images from Ellison.
     */
    protected function _getProductImages()
    {
        $this->log('------- Downloading product images -------', null, 'image_download.log');
        $codes = array('products', 'ideas');
        // $codes = array('products');  // Test only
        // $codes = array('ideas');     // Test only
        $extraImages = array();

        foreach ($codes as $code) {
            if ($code === 'products') {
                $colName = 'item_num';
            } elseif ($code ==='ideas') {
                $colName = 'idea_num';
            } else {
                $this->log("Unknown product type: $code");
                exit;
            }

            $q = "SELECT `$colName`,`image_filename`,`images` FROM $code";
            // $q .= " WHERE `$colName` IN ('26923')";

            // For testing each scenario (all verified)
            // $q .= " WHERE $colName = '655268'"; // main yes; extra yes
            // $q .= " WHERE $colName = '654448'"; // main yes; extra no
            // $q .= " WHERE $colName = '654324'"; // main no; extra yes
            // $q .= " WHERE $colName = '654376'"; // main no; extra no

            // $q .= " WHERE $colName = '2082'"; // main yes; extra yes
            // $q .= " WHERE $colName = '1489'"; // main yes; extra no
            // $q .= " WHERE $colName = '302'"; // main no; extra yes
            // $q .= " WHERE $colName = '126'"; // main no; extra no

            $result = $this->query($q);
            $saveDir = $this->_shellDir . DS . 'images' . DS . $code;
            // $this->out($q);
            // print_r($result);

            // Compile a list of image file names
            foreach ($result as $one) {
                $strs = array();
                $strs[0] = trim(trim($one->image_filename), '/');   // single image
                // print_r($strs);

                if (empty($strs[0])) {
                    $sku = $one->{$colName};
                    // "extra images" are images found from the
                    $extraImages[$code]["$sku.jpg"] = $this->_getPossibleExtraImagePaths($sku, $code);
                    $strs[0] = '';
                }

                $strs += explode('|', $one->images);     // list of images

                // Make a list of files to obtain
                $files = array();
                foreach ($strs as $i => $str) {
                    if (!empty($str)) {
                        $files[$i]['name'] = $str;
                        if ($i === 0) {
                            $files[$i]['url'] = $code;
                            $files[$i]['local'] = 'main';
                        } else {
                            $files[$i]['url'] = 'images';
                            $files[$i]['local'] = 'additional';
                        }
                    }
                }   // End of getting images for one item
                // print_r($files);

                // Download product images
                $i = 0;
                foreach ($files as $file) {
                    $url = self::SIZZIX_WEBSITE . DS . 'images' . DS . $file['url'] . DS . $file['name'];
                    // $this->out($url);

                    // Skip already downloaded image
                    $dir = $saveDir . DS . $file['local'] . DS . $file['name'];
                    // $this->out($url); $this->out($dir);

                    if (file_exists($dir)) {
                        $this->out("Image already exists: $dir");
                        continue;
                    }

                    $content = file_get_contents($url);
                    if ($content !== false) {
                        if (file_put_contents($dir, $content)) {
                            $this->out("Image saved: $dir");
                        } else {
                            $this->log("Failed to save image: $dir", null, 'image_download.log');
                        }
                    } else {
                        $this->log("Failed to download image: $url", null, 'image_download.log');
                    }

                    $i++;
                }
            }
        }

        $this->out('Extra images:'); print_r($extraImages);
        // $this->out('Downloading images only'); $this->out($codes); exit;
        // continue;

        // "Extra" images are stored in a different location. These are items
        // that were migrated to the current Ellison platform.
        $this->log('------- Processing extra images -------', null, 'image_download.log');

        foreach ($extraImages as $code => $imagesPerType) {
            $k = 0;
            $N = count($imagesPerType);
            $saveDir = $this->_shellDir . DS . 'images' . DS . 'extras' . DS . $code;

            foreach ($imagesPerType as $fileName => $images) {
                $k++;
                foreach ($images as $url) {
                    $dir = $saveDir . DS . $fileName;
                    if (file_exists($dir)) {
                        $this->log("$k/$N ($code): Extra image already exists: $dir");
                        continue;
                    }

                    $content = file_get_contents($url);

                    if ($content !== false) {
                        if (file_put_contents($dir, $content)) {
                            $this->out("$k/$N ($code): Image saved: $dir");

                        } else {
                            $this->log("$k/$N ($code): Failed to save image: $dir", null, 'image_download.log');
                        }
                    } else {
                        $this->log("$k/$N ($code): Failed to download image: $url", null, 'image_download.log');
                    }
                }
            }
        }
    }

    protected function _initTaxonomyData()
    {
        $saveDir = $this->_shellDir . DS . 'images' . DS . 'taxonomy';
        $filename = $this->_taxonomyDataFile;
        $delimiter = ',';
        $newline = "\r\n";  // This file needs both EOLs
        $helper = Mage::helper('taxonomy');
        $fh = fopen($filename, "r");
        if (!$fh) {
            $this->out('Missing file. Exiting. ' . $filename);
            exit;
        }

        $contents = fread($fh, filesize($filename));
        $splits = explode($newline, $contents);

        $i = -1;
        $this->_taxonomyData = null;
        foreach ($splits as $line) {
            if ($i === -1) {
                $headers = array_flip(str_getcsv($line));
            } else {
                // $line = str_replace('\""', '\"', $line);    // Mahavi's CSV file has [\""] in it
                $row = str_getcsv($line, ',', '"');         // Ignores [\"]

                if (!isset($row[$headers['_id']])) {    // Most likely the last row
                    continue;
                }

                $description = $row[$headers['description']];
                $description = str_replace('\"', '"', $description);
                // $description = str_replace('®', '&reg;', $description);    // Replacement does not work
                // $description = str_replace('™', '&trade', $description);   // Replacement does not work
                $description = $this->_helper->cleanStr($description);

                // For some reason, some paths have space(s) under the 'Image' column
                $imagePathNoSpaces = str_replace(' ', '', $row[$headers['Image']]);

                $key = $row[$headers['_id']] . '-' . $row[$headers['permalink']];
                $this->_taxonomyData[$key]['mongoid'] = $row[$headers['_id']];
                $this->_taxonomyData[$key]['code'] = $row[$headers['permalink']];
                $this->_taxonomyData[$key]['image'] = $imagePathNoSpaces;
                $this->_taxonomyData[$key]['description'] = $description;
                $this->_taxonomyData[$key]['type'] = $row[$headers['tag_type']];
            }
            $i++;
        }
        // print_r($this->_taxonomyData); die; // aaa
    }

    /**
     * Download all taxonomy tag images.
     *
     * Gets image and descrip data from Madhavi's file.
     */
    protected function _getTaxonomyImages()
    {
        $imageNames = array();
        $saveDir = Mage::getBaseDir() . '/shell/images/taxonomy/';
        if (!$this->_taxonomyData) {
            $this->_initTaxonomyData();
        }

        foreach ($this->_taxonomyData as $one) {
            // Logic provided by Ellison to locate taxonomy images is incorrect. Images cannot be found.
            $image = trim($one['image']);
            if (!empty($image)) {
                $key = $one['mongoid'] . '-' . $one['code'];
                if (isset($imageNames[$key])) {
                    $this->out($imageNames);
                    $this->out('Non-unique taxonomy tag code: ' . $key); exit;
                }
                $imageNames[$key]['original'] = self::ELLISON_WEBSITE . $image;
                // Append codified taxonomy name and file name to avoid possible duplicates
                $imageNames[$key]['modified'] =  $one['code'] . '--' . basename($image);
                // $i++;
            }
        }

        // Download the images
        $this->out('Downloading taxonomy images(' . count($imageNames) . ')...');
        foreach ($imageNames as $name) {
            $this->_downloadImage(
                $name['original'],
                $saveDir . $name['modified']
            );
        }
        $this->out('Finished downloading taxonomy images.');
    }

    /**
     * Instruction image name are not unique. In order to have all images in one
     * convenient directory for access, SKUs are prefixed to the image file name.
     *
     * e.g. SKU-filename.jpg
     */
    protected function _getInstructionsImages()
    {
        $imageNames = array();
        $saveDir = $this->_shellDir . DS . 'images' . DS . 'insturctions';
        $i = 0;

        $q = "SELECT t.`idea_num`,t.`images`
            FROM `idea_tabs` AS t
            WHERE t.`name` = 'Instructions' AND t.`images` != ''";
        $res = $this->query($q);

        // Get a list of image file names
        foreach ($res as $idea) {
            $images = $this->_extractInstructionImages(
                $this->_helper->cleanDelimitedString($idea->images)
            );

            foreach ($images as $one) {
                $temp = reset($one);
                $imageNames[$i]['modified'] = "{$idea->idea_num}-$temp";
                $imageNames[$i]['original'] = $temp;
                $i++;
            }
        }

        // Download the images
        $this->out('Downloading instruction images(' . count($imageNames) . ')...');
        foreach ($imageNames as $name) {
            $this->_downloadImage(
                self::EDUCATION_WEBSITE . '/images/images/' . $name['original'],
                $saveDir . DS . $name['modified']
            );
        }
        $this->out('Finished downloading instruction images.');
    }

    /**
     * Given a full URL, download the image into the given directory.
     *
     * @param str $url Full path including file name
     * @param str $fullPath Full path including file name
     *
     * @return bool
     */
    protected function _downloadImage($url, $fullPath)
    {
        if (file_exists($fullPath)) {
            $this->out("Image already exists: $fullPath");
            return;
        }

        $content = file_get_contents($url);

        if ($content !== false) {
            if (file_put_contents($fullPath, $content)) {
                $this->out("Image saved: $fullPath");
                return true;

            } else {
                $this->log("Failed to save image: $fullPath", null, 'image_download.log');
                unlink($fullPath);  // Sometimes an empty file is saved
                return false;
            }

        } else {
            $this->log("Failed to download image: $url", null, 'image_download.log');
            return false;
        }

        return false;
    }

    protected function _getPossibleExtraImagePaths($sku, $code)
    {
        $images = array();

        // Each product type have its own possible directories where images are located..
        if ($code === 'products') {
            $images[0] = self::SIZZIX_WEBSITE . "/image/$code/zoom/$sku.jpg";
            $images[1] = self::SIZZIX_WEBSITE . "/images/$code/large/$sku.jpg";

            $dashPos = strpos($sku, '-');
            if ($dashPos !== false) {
                $skuTrimmed = substr($sku, 0, $dashPos);
                $images[2] = self::SIZZIX_WEBSITE . "/images/$code/large/$skuTrimmed.jpg";
            }

        } else {    // assumed 'ideas'
            $images[0] = self::SIZZIX_WEBSITE .  "/image/$code/larges/{$sku}_pop.jpg";
            $images[1] = self::SIZZIX_WEBSITE . "/images/$code/large/$sku.jpg" ;
        }

        return $images;
    }

    protected function _getAllIdeaData($idea)
    {
        $skuCol = self::IDEA_SKU_COLUMN;
        $q1 = "SELECT {$this->getProductAttributeList()}
            FROM ideas
            WHERE `$skuCol` = '". $idea->{self::IDEA_SKU_COLUMN}
            . "' LIMIT 1";

        $res = $this->query($q1);
        $data = reset($res);

        // Products and ideas have different auxillary data
        $data->tags = $this->_getIdeaTags($idea->{self::IDEA_SKU_COLUMN});
        $data->tabs = $this->_getIdeaTabData($idea->{self::IDEA_SKU_COLUMN});;
        $data->usedProducts = $this->_getUsedProducts($idea->{self::IDEA_SKU_COLUMN});;
        $data->details = '';

        return $data;
    }

    /**
     * Returns product data retrieved from the Ellison DB.
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return StdClass
     */
    protected function _getAllProductData($product)
    {
        $skuCol = self::PRODUCT_SKU_COLUMN;
        $q1 = "SELECT p.*,c.`name` AS discount_category_name
            FROM products as p
                LEFT JOIN discount_categories as c
                    ON p.`discount_category_id` = c.`mongoid`

            WHERE `$skuCol` = '". $product->{self::PRODUCT_SKU_COLUMN}
            . "' LIMIT 1";

        $res = $this->query($q1);
        $data = reset($res);
        // $this->out($q1);print_r($data); exit;

        // Products and ideas have different auxillary data
        $data->tags = $this->_getProductTags($product->{self::PRODUCT_SKU_COLUMN});
        $data->tabs = $this->_getProductTabData($product->{self::PRODUCT_SKU_COLUMN});;
        $data->details = '';
        // Some products have "Accessories" (from tab_data_for_m) that are basically just related products
        // Madhavi wants this migrated.

        return $data;
    }

    protected function _getIdeaTags($sku)
    {
        $tags = array();
        $q = "SELECT t.`tag_type`,t.`name`,t.`systems_enabled`,t.`mongoid`,t.`permalink`
            FROM ideas AS p
                INNER JOIN `tag_to_idea` AS pt
                    ON p.`mongoid` = pt.`idea_mongoid`
                INNER JOIN tags AS t
                    ON pt.`tag_mongoid` = t.`mongoid`
            WHERE p.`" . self::IDEA_SKU_COLUMN . "` = '$sku'
                AND t.`active` = 1
            GROUP BY t.id;";

        $res = $this->query($q);

        foreach ($res as $one) {
            $tags[$one->tag_type][] = array(
                'name' => $this->_helper->cleanStr($one->name),
                // 'name' => $one->name,
                'system_enabled' => $one->systems_enabled,
                'mongoid' => $one->mongoid,
                'permalink' => $one->permalink,
            );
        }

        return $tags;
    }

    /**
     * Returns the idea tab data. Note that only three kind of tab data are
     * migrated. They are listed in $_ideaTabToMigrate.
     *
     * @param str $sku
     * @return array
     */
    protected function _getIdeaTabData($sku)
    {
        $tabs = array();

        $q = "SELECT t.`name`,t.`systems_enabled`,t.`text`,t.`images`,t.`idea_num`
            FROM ideas AS i
                INNER JOIN `idea_tabs` AS t
                    ON i.`idea_num` = t.`idea_num`
            WHERE i.`" . self::IDEA_SKU_COLUMN . "` = '$sku'";

        $res = $this->query($q);

        foreach ($res as $one) {
            if (isset($this->_ideaTabToMigrate[$one->name])) {

                // There can be more than one Instructions tab
                if ($one->name == 'Instructions') {
                    $tabs[$one->name][] = array(
                        'text' => $this->_helper->cleanStr($one->text),
                        'images' => $this->_helper->cleanDelimitedString($one->images),
                        'system_enabled' => $one->systems_enabled,
                    );
                } else {
                    $tabs[$one->name] = array(
                        'text' => $this->_helper->cleanStr($one->text),
                        'images' => $this->_helper->cleanDelimitedString($one->images),
                        'system_enabled' => $one->systems_enabled,
                    );
                }
            }

        }

        return $tabs;
    }

    protected function _getUsedProducts($sku)
    {
        $usedSkus = array();
        $q = "SELECT p.`item_num`
            FROM ideas AS i
                INNER JOIN `product_to_idea` AS pi
                    ON i.`mongoid` = pi.`idea_mongoid`
                INNER JOIN `products` AS p
                    ON p.`mongoid` = pi.`product_mongoid`
            WHERE i.`" . self::IDEA_SKU_COLUMN . "` = '$sku'";

        $res = $this->query($q);

        foreach ($res as $one) {
            $str = trim($one->item_num);

            if (!empty($str)) {
                $usedSkus[] = $str;
            }
        }

        return $usedSkus;
    }

    /**
     * Returns "relevant" tab data for a given product. Requirement document
     * tabs_products_projects_home_Work_for_migrations.docx specifies only
     * "Accessories" needs to be migrated, excluding "Details". "Details" will
     * be separately done by Ellison.
     *
     * @param str $sku
     *
     * @return array
     */
    protected function _getProductTabData($sku)
    {
        $tabs = array();

        $q = "SELECT pt.*
            FROM products AS p
                INNER JOIN `tab_data_for_m` AS pt
                    ON p.`item_num` = pt.`item_num`
            WHERE p.`" . self::PRODUCT_SKU_COLUMN . "` = '$sku'
                AND pt.`name` = 'Accessories'";

        $res = $this->query($q);

        foreach ($res as $one) {
            // Array must have numeric indices
            $tabs = array_merge($tabs, explode('|', trim($one->products, '|')));
            // $enabledSystems = $one->systems;
        }

        // Assumed systems are uniform across all accessory entries for simplicity
        return array(
            'Accessories' => array(
                'skus' => implode(',', $tabs),
                // 'website_codes' => $enabledSystems
            )

        );
    }

    protected function _getProductTags($sku)
    {
        $tags = array();

        $q = "SELECT t.`tag_type`,t.`name`,t.`systems_enabled`,t.`mongoid`,t.`permalink`
            FROM products AS p
                INNER JOIN `product_to_tag` AS pt
                    ON p.`mongoid` = pt.`product_mongoid`
                INNER JOIN tags AS t
                    ON pt.`tag_mongoid` = t.`mongoid`
            WHERE p.`" . self::PRODUCT_SKU_COLUMN . "` = '$sku'
                AND t.`active` = 1
            GROUP BY t.id;";

        $res = $this->query($q);

        foreach ($res as $one) {
            $tags[$one->tag_type][] = array(
                'name' => $this->_helper->cleanStr($one->name),
                // 'name' => $one->name,
                'system_enabled' => $one->systems_enabled,
                'mongoid' => $one->mongoid,
                'permalink' => $one->permalink,
            );
        }

        return $tags;
    }

    /**
     * Checks to see if the designated SKU column is unique
     */
    protected function _checkSkusAreUnique()
    {
        $skuCol = self::PRODUCT_SKU_COLUMN;
        $q = "SELECT $skuCol, COUNT(*) c FROM products GROUP BY $skuCol HAVING c > 1";
        $nonUniques = $this->query($q);
        if (count($nonUniques) > 0) {
            $this->out('There are non-unique SKUs. Aborting.');
            $this->out($nonUniques);
            exit;
        }
    }

    /**
     * Updates the SDM_Taxonomy table
     */
    protected function _updateTaxonomyTable()
    {
        $helper = Mage::helper('taxonomy');
        $this->getConn('core_write')->query("TRUNCATE TABLE `sdm_taxonomy_date`");
        $this->getConn('core_write')->query("TRUNCATE TABLE `sdm_taxonomy_product`");
        $this->getConn('core_write')->query("TRUNCATE TABLE `sdm_taxonomy`");   // truncate table
        $this->getConn('core_write')->query("TRUNCATE TABLE `sdm_calendar_event`");

        // Taxonomy items from Ellison that are going to part of the taxonomy in Magento
        foreach ($this->_taxonomy as $ellisonCode => $magentoCode) {
            // Not sure why this was done.
            if ($ellisonCode === 'calendar_event') {
                $magentoAttCode = 'event'; // This is incorrect
            } else {
                $magentoAttCode = $ellisonCode;
            }
            // $magentoAttCode = $ellisonCode;

            /*if ($ellisonCode == 'product_line') {    // skip b/c mapped product line tax. available.
                continue;
            }*/

            // Get 'em dates!
            $q = "SELECT t.`name`,t.`permalink`,t.`mongoid`,t.`systems_enabled`,
                    t.`start_date_szus`,t.`end_date_szus`,
                    t.`start_date_szuk`,t.`end_date_szuk`,
                    t.`start_date_erus`,t.`end_date_erus`,
                    t.`start_date_eeus`,t.`end_date_eeus`,
                    t.`color`,
                    t.`calendar_start_date_eeus`,t.`calendar_end_date_eeus`
                FROM `tags` AS t
                WHERE t.`tag_type` = '$ellisonCode'
                    AND t.`name` NOT LIKE '%old tag%'
                    AND t.`name` NOT LIKE '%test%'
                    AND t.`active` = 1";
            $res = $this->query($q);

            foreach ($res as $one) {
                // Set some data first
                $tax = Mage::getModel('taxonomy/item')
                    // ->setName($one->name)
                    ->setName(Mage::helper('taxonomy')->removeNonStdAscii($one->name))   // Ellison complained about characters being removed
                    // ->setCode(Mage::helper('taxonomy')->transformNameToCode($one->name))
                    ->setCode($one->permalink)   // Will not work in cases where permalink code is not available --> What? - YK
                    ->setType($magentoAttCode);

                // if ($ellisonCode == 'product_line') {    // test
                //     echo $one->name . PHP_EOL;
                // }

                // Check variable that has the file data
                $key = $one->mongoid . '-' . $one->permalink;

                // Add description if available
                if (isset($this->_taxonomyData[$key]['description'])) {
                    $descrip = trim($this->_taxonomyData[$key]['description']);
                    if (!empty($descrip)) {
                        $tax->setDescription($descrip);
                    }
                }

                // Add image if available
                if (isset($this->_taxonomyData[$key]['image'])) {
                    $image = trim($this->_taxonomyData[$key]['image']);
                    $image = basename($image);
                    $filePath =  $this->_taxonomyData[$key]['code'] . '--' . $image;
                    if (!empty($image)) {
                        $tax->setImageUrl('taxonomy' . DS . $filePath);
                        // Copy over image to media directory
                        $source = Mage::getBaseDir() . '/shell/images/taxonomy/'
                            . $filePath;
                        $destination = Mage::getBaseDir() . '/media/taxonomy/'
                            . $filePath;

                        if (file_exists($source)) {
                            copy($source, $destination);
                        } else {
                            $this->log('Taxnomy image not found locally: ' . $source);
                        }
                    }
                }

                $tax->save();

                // Save the dates per website
                $ellisonCodes = array(
                    'szus' => 1,
                    'szuk' => 3,
                    'erus' => 4,
                    'eeus' => 5,
                );
                // Always assigned to ERUS and EEUS together
                $websiteIds = array($ellisonCodes['erus'], $ellisonCodes['eeus']);

                foreach ($ellisonCodes as $code => $siteId) {
                    if (strpos($one->systems_enabled, $code) !== false) {
                        $isNotExpired = $this->_isTodayWithinRange(
                            $one->{"start_date_$code"},
                            $one->{"end_date_$code"}
                        );

                        $taxDate = Mage::getModel('taxonomy/item_date')
                            ->setTaxonomyId($tax->getId())
                            ->setWebsiteId($siteId);

                        if (!$isNotExpired) {   // Only migrate dates if they're expired
                            $taxDate->setStartDate($one->{"start_date_$code"})
                                ->setEndDate($one->{"end_date_$code"});
                        }

                        $taxDate->save();
                    }
                }

                // Event (calendar_event) tags have associated products
                // and need SDM_Calendat event entries created
                if ($ellisonCode === 'calendar_event') {
                    $this->out('Updating Event taxnomy ID ' . $tax->getId());
                    $this->_updateEvent($one, $tax->getId(), $websiteIds);
                }
            }
        }

        // Discount categories are being added to the taxonomy model
        $q2 = "SELECT c.`name` FROM discount_categories AS c";
        $results = $this->query($q2);

        foreach ($results as $i => $one) {
            Mage::getModel('taxonomy/item')
                ->setName($one->name)
                ->setCode($this->_helper->transformNameToCode($one->name))
                ->setType(SDM_Taxonomy_Model_Attribute_Source_Discountcategory::CODE)
                ->setPosition($i)
                ->save();
        }

        $this->out('Taxonomy table updated!');

        // Update the product line and import subproduct lines taxonomy
        $this->_updateProductLines();
        $this->_initSubProductLines();
        $this->out('Subproduct lines updated!');

        // Update Ellison table
        $this->_updateEllisonTagsTable();
    }

    /**
     * Update the events
     *
     * @param StdObject Row record from MongoDB
     * @param int $tagId
     * @param array $websiteIds
     */
    protected function _updateEvent($row, $tagId, $websiteIds)
    {
        $calendarId = 1;    // Static; already defined manually in DB.
        $color = str_replace('#', '', $row->color);

        $start = $row->calendar_start_date_eeus;
        $end = $row->calendar_end_date_eeus;
        if ($start == '0000-00-00 00:00:00' || $end == '0000-00-00 00:00:00') {
            $start = '1970-01-01 00:00:00';
            $end = '1970-01-01 00:00:00';
        }

        // Add event
        Mage::getModel('sdm_calendar/event')->setCalendarId($calendarId)
            ->setName($row->name)
            ->setColor($color)
            ->setStart($start)
            ->setEnd($end)
            ->setRecurring(0) // n/a from MongoDB
            ->setTaxonomyId($tagId)
            ->setWebsites($websiteIds)
            ->save();

        // Add products and ideas to the taxonomy
        $tagMongoId = $row->mongoid;
        $products = $this->_getAssociatedProducts($tagMongoId);
        $ideas = $this->_getAssociatedIdeas($tagMongoId);
        $allProducts = array_merge($products, $ideas);
        foreach ($allProducts as $product) {
            $tag = Mage::getModel('taxonomy/item_product')
                ->setTaxonomyId($tagId)
                ->setProductId($product['id'])
                ->setSku($product['sku'])
                ->setDiscountType(SDM_Taxonomy_Helper_Data::DISCOUNT_TYPE_PERCENT_CODE)
                ->setDiscountValue(0)
                ->save();
        }
    }

    protected function _getAssociatedProducts($mongoId)
    {
        $skus = array();
        $q = "SELECT t.`product_mongoid`,p.`item_num`
            FROM product_to_tag AS t
                LEFT JOIN products AS p
                    ON t.`product_mongoid` = p.`mongoid`
            WHERE t.tag_mongoid = '$mongoId'";
        $results = $this->query($q);

        foreach ($results as $one) {
            $skus[] = "'" . $one->item_num . "'";
        }
        $skus = array_unique($skus);
        $products = $this->_getMagentoProductId($skus);

        return $products;
    }

    protected function _getAssociatedIdeas($mongoId)
    {
        $skus = array();
        $q = "SELECT t.`idea_mongoid`,p.`idea_num`
            FROM idea_to_tag AS t
                LEFT JOIN ideas AS p
                    ON t.`idea_mongoid` = p.`mongoid`
            WHERE t.`tag_mongoid` = '$mongoId'";
        $results = $this->query($q);

        foreach ($results as $one) {
            $skus[] = "'" . $one->idea_num . "'";
        }
        $skus = array_unique($skus);
        $products = $this->_getMagentoProductId($skus);

        return $products;
    }

    protected function _getMagentoProductId($skus)
    {
        $products = array();
        if (empty($skus)) {
            return $products;
        }

        $q = "SELECT entity_id, sku
            FROM catalog_product_entity
            WHERE sku IN (" . implode(',', $skus) . ')';

        $results = $this->getConn()->fetchAll($q);

        $i = 0;
        foreach ($results as $one) {
            $products[$i]['id'] = $one['entity_id'];
            $products[$i]['sku'] = $one['sku'];
            $i++;
        }

        return $products;
    }

    protected function _updateRetailCustomers()
    {
        $this->out('Retail customer groups cannot be re-created! All new groups must be added manually!');
        return;

        // Create Magento customer groups, if necessary
        foreach ($this->_customerGroupMapping as $i => $groupName) {
            $this->_createCustomerGroup($groupName);
        }
    }

    /**
     * Creates new customer groups only.
     *
     * This function works under the assumption that all necessary tax
     * settings have been configured.
     *
     * @param str $name
     */
    protected function _createCustomerGroup($name) {
        $name = substr($name, 0, 32);   // max VARCHAR length for code: 32
        $collection = Mage::getModel('customer/group')->getCollection()
            ->addFieldToFilter('customer_group_code', $name);

        $group = Mage::getModel('customer/group')->load($collection->getFirstItem()->getId())
            ->setCode($name)
            ->setTaxClassId(5) // Tax clas is HARDCODED: "Wholesale Customer"
            ->save(); //save group

        $this->out("Created/Updated customer group: {$group->getCode()}");
    }

    protected function _updateDiscountMatrix()
    {
        // Truncate the table to start from scratch
        $this->getConn('core_write')->query("TRUNCATE `sdm_customer_discount_group`");

        $matrix = $this->query("SELECT * FROM `discount_categories`");

        foreach ($matrix as $row) {
            $groupCode = $this->_helper->transformNameToCode($row->name);
            $catId = Mage::getResourceModel('taxonomy/item')
                ->getIdByCode(
                    'discount_category',
                    $groupCode
                );
            if (!$catId) {
                $this->out("Discount category ID not found for '{$row->name}'. Exiting...");
                exit;
            }
            // $this->out($catId);

            // For each discount category, create corresponding entries in
            // `sdm_customer_discount_group`
            foreach ($this->_customerGroupMapping as $i => $groupName) {
                $groupName = substr($groupName, 0, 32);
                $column = "discount_$i";
                $groupId = Mage::helper('customerdiscount')
                    ->getCustomerGroupIdByCode($groupName);
                if (!$groupId) {
                    $this->out("Customer group ID not found for '$groupName'");
                    exit;
                }

                $groupDiscount = Mage::getModel('customerdiscount/discountgroup');
                $groupDiscount->setCategoryId($catId)
                    ->setCustomerGroupId($groupId)
                    ->setAmount($row->$column)
                    ->setCreatedAt($row->created_at)
                    ->setUpdatedAt($row->updated_at)
                    ->save();
            }
        }

        $this->out("Retailer discount 'matrix' updated");
    }

    /**
     * Update SDM_Taxonomy table's product lines with new mapped entries.
     *
     * Note: only adds. Doesn't delete. (may change)
     */
    protected function _updateProductLines()
    {
        $prodLines = array();
        $helper = Mage::helper('taxonomy');

        // The names in the array are already cleaned
        foreach ($this->_productLineMapping as $one) {
            $code = trim($helper->transformNameToCode($one['productline']));
            if (!empty($code)) {
                $prodLines[$code]['name'] = $one['productline'];
                $prodLines[$code]['websites'] = $one['websites'];
            }
        }
        // print_r($prodLines); die;

        foreach ($prodLines as $code => $data) {
            $name = $data['name'];

            // First check and remove if there's one with the same code
            // $code = $helper->transformNameToCode($name);
            $id = Mage::getModel('taxonomy/item')->getIdByCode('product_line', $code);
            if ($id) {
                Mage::getModel('taxonomy/item')->load($id)
                    ->delete();
            }

            // Save new, mapped taxonomy
            $tag = Mage::getModel('taxonomy/item')->setType('product_line')
                ->setName($name)
                ->save();
// echo 'New: ' . $name . PHP_EOL;

            // Save website assignments, too
            $tagId = $tag->getId();
            if ($tagId) {
                foreach ($data['websites'] as $webCode) {
                    if ($webCode === 'eeuk') {  // Website no longer relevant
                        continue;
                    }
                    $siteId = $this->_websiteMapping[$webCode];

                    $taxDate = Mage::getModel('taxonomy/item_date')
                        ->setTaxonomyId($tagId)
                        ->setWebsiteId($siteId)
                        ->setStartDate(null)    // Not migrating dates for mapped prod lines
                        ->setEndDate(null)
                        ->save();
                }
            }
        }
    }

    /**
     * Update SDM_Taxonomy table with sub-product line data
     */
    protected function _initSubProductLines()
    {
        $subProdLines = array();
        $helper = Mage::helper('taxonomy');

        // The names in the array are already cleaned
        foreach ($this->_productLineMapping as $one) {

            $code = trim($helper->transformNameToCode($one['subproductline']));
            if (!empty($code)) {
                $subProdLines[$code]['name'] = $one['subproductline'];
                $subProdLines[$code]['websites'] = $one['websites'];
            }
        }

        foreach ($subProdLines as $data) {
            $name = $data['name'];

            $tag = Mage::getModel('taxonomy/item')->setType('subproduct_line')
                ->setName($name)
                ->save();

            // Save website assignments, too
            $tagId = $tag->getId();
            if ($tagId) {
                foreach ($data['websites'] as $webCode) {
                    if ($webCode === 'eeuk') {
                        continue;
                    }
                    $siteId = $this->_websiteMapping[$webCode];

                    $taxDate = Mage::getModel('taxonomy/item_date')
                        ->setTaxonomyId($tagId)
                        ->setWebsiteId($siteId)
                        ->setStartDate(null)    // Not migrating dates for mapped prod lines
                        ->setEndDate(null)
                        ->save();
                }
            }
        }
    }

    /**
     * Update the Ellison database at its source, so the migration can run
     * smoothly.
     */
    protected function _updateEllisonTagsTable()
    {
        $helper = Mage::helper('taxonomy');

        // Get all of the product line tags
        $q = "SELECT t.`name`,t.`systems_enabled`
            FROM `tags` AS t
            WHERE t.`tag_type` = 'product_line'
                AND t.`name` NOT LIKE '%old tag%'
                AND t.`name` NOT LIKE '%test%'
                AND t.`active` = 1";
        $res = $this->query($q);
        // print_r($res);

        $this->_resetProductLineTagTable();

        // Insert all of them first so that it can be seen, at the end, how many
        // of the "old" product line tags are not mapped.
        foreach ($res as $one) {
            // $name = $helper->removeNonStdAscii($one->name);
            $one->code = $helper->transformNameToCode($one->name);
            $this->_insertNewTagRecord($one);
        }

        foreach ($res as $one) {
            $key = $helper->transformNameToCode($one->name);

            // See if a mapping is available
            if (isset($this->_productLineMapping[$key])) {
                // New product line (just need name)
                $name = $helper->removeNonStdAscii($this->_productLineMapping[$key]['productline']);  // Clean

                $this->_updateTagRecord($key, $name, 'product_line'); // $key is assumed to be unique at this point

                // New sub-product line
                if (!empty($this->_productLineMapping[$key]['subproductline'])) {
                    $this->_updateTagRecord($key, $this->_productLineMapping[$key]['subproductline'], 'subproduct_line');
                }
            }
        }
    }

    /**
     * Returns the tag ID from the Ellison table
     *
     * @param str $type
     * @param str $code
     *
     * @return int
     */
    protected function _getTagRecord($code)
    {
        $q = "SELECT * FROM tags_product_line WHERE `code` = '$code';";
        $res = $this->query($q);
        $row = reset($res);

        return $row;
    }

    /**
     * Removes the tag record from the Ellison table
     *
     * @param int $id
     */
    protected function _deleteTag($id)
    {
        if (!$id) {
            return;
        }
        $q = "DELETE FROM tags_product_line WHERE id = $id";
        $this->query($q);
    }

    /**
     * Inserts a new tag record into the Ellison table with only the code
     *
     * @param str $code
     * @param str $nane
     */
    protected function _updateTagRecord($code, $name, $column)
    {
        $q = "UPDATE tags_product_line SET `$column` = '$name' WHERE `code` = '$code';";
        $res = $this->query($q);
    }

    /**
     * Inserts a new tag record into the Ellison table with only the code
     *
     * @param array $data
     */
    protected function _insertNewTagRecord($data)
    {
        $code = trim($data->code);
        $websites = trim($data->systems_enabled);

        $q = "INSERT INTO tags_product_line (`code`,`websites`)
            VALUES ('$code','$websites');";

        $res = $this->query($q);
    }

    /**
     * Removes and creates the product line tag table in the Ellison database.
     */
    protected function _resetProductLineTagTable()
    {
        $this->query("DROP TABLE IF EXISTS `tags_product_line`;");
        $this->query("
            CREATE TABLE `tags_product_line` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `code` varchar(255) DEFAULT NULL,
              `product_line` varchar(255) DEFAULT NULL,
              `subproduct_line` varchar(255) DEFAULT NULL,
              `websites` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Sets the product line mapping. Each element of the array contain the
     * codified old product line name as its index and associated websites and
     * names as its data for that index.
     */
    protected function _initProductLineConsolidationMapping()
    {
        $data = array();
        $filename = $this->_taxonomyMappingFile;
        $delimiter = "\t";
        $newline = "\r";
        $helper = Mage::helper('taxonomy');
        $fh = fopen($filename, "r");
        if (!$fh) {
            $this->out('Missing file. Exiting. ' . $filename);
            exit;
        }

        $contents = fread($fh, filesize($filename));
        $split = explode($newline, $contents);

        foreach ($split as $i => $row) {
            if ($i == 0) {
                $headers = explode($delimiter, $row);
            } else {
                $line = explode($delimiter, $row);
                if (!reset($line)) {
                    continue;
                }
                $index = $helper->transformNameToCode($line[1]);

                if (isset($data[$index])) {
                    $this->out('Non unique old product line name: ' . $line[1]);
                    exit;
                }

                $data[$index]['productline'] = Mage::helper('taxonomy')->removeNonStdAscii($line[4]);
                $data[$index]['subproductline'] = Mage::helper('taxonomy')->removeNonStdAscii($line[5]);
                $data[$index]['websites'] = $this->_cleanUpWebsiteString($line[2]); // Clean up websites
            }
        }

        fclose ($fh);
        // print_r($data); die; // aaa

        $this->_productLineMapping = $data;
    }

    /**
     * Removes the unwanted characters included from the mapping text file.
     *
     * @return array
     */
    protected function _cleanUpWebsiteString($str)
    {
        $str = trim($str);
        $str = trim($str, '"');
        $str = trim($str, '[');
        $str = trim($str, ']');
        $str = trim($str);
        $codes = explode(',', $str);

        foreach ($codes as &$code) {
            $code = trim($code);
            $code = trim($code, '"');
        }

        return $codes;
    }

    /**
     * Compatibility product lines also have its own custom mapped data not
     * relfected in the Ellison database.
     */
    protected function _initCompatibilityProductLineMapping()
    {
        $data = array();
        $filename = $this->_compatibilityProductLineMappingFile;
        $delimiter = "\t";
        $newline = "\r";
        $helper = Mage::helper('taxonomy');
        $fh = fopen($filename, "r");
        if (!$fh) {
            $this->out("Missing file. Exiting. $fileName");
            exit;
        }
        $contents = fread($fh, filesize($filename));
        $split = explode($newline, $contents);

        foreach ($split as $i => $row) {
            if ($i == 0) {
                $headers = explode($delimiter, $row);
            } else {
                $line = explode($delimiter, $row);
                $sku = trim($line[0]);
                $code = trim($helper->transformNameToCode($line[5]));

                if (isset($data[$sku])) {
                    $this->out('Non unique SKU: ' . $sku);
                    exit;
                }
                if (!empty($code) && !is_null($code) && trim($line[4]) == 'die') {
                    $data[$sku] = $code;
                }
            }
        }

        fclose ($fh);
        // print_r($data); die; // aaa

        $this->_compatibilityProductLineMapping = $data;
    }

    /**
     * Re-populate the product line and compatibility matrix
     */
    protected function _updateCompatibilityMatrix()
    {
        if ($this->isExtensionEnabled('SDM_Compatibility')) {
            $compatibilityTable = $this->getTableName('compatibility/compatibility');
            $prodLineTable = $this->getTableName('compatibility/productline');
            $codeMapping = array_flip($this->_helper->getAssociativeEllisonSystemCodes());

            // Wipe all data first
            $this->getConn('core_write')->query("TRUNCATE $compatibilityTable");
            $this->getConn('core_write')->query("TRUNCATE $prodLineTable");

            // Product lines: could be dies or machines
            $productLines = $this->query(
                "SELECT p.`item_type`,t.`name`,t.`active`,t.`systems_enabled`
                FROM products AS p
                    INNER JOIN `product_to_tag` AS pt
                        ON p.`mongoid` = pt.`product_mongoid`
                    INNER JOIN tags AS t
                        ON pt.`tag_mongoid` = t.`mongoid`
                WHERE t.`tag_type` = 'product_line'
                    AND t.`active` = 1
                    AND t.`name` NOT LIKE '%old tag%'
                    AND t.`name` NOT LIKE '%test%'
                GROUP BY t.`name`
                ORDER BY t.`name`"
            );

            foreach ($productLines as $k => $line) {
                $websiteIds = array();
                $codes = explode('|', trim($line->systems_enabled, '|'));

                foreach ($codes as $code) {
                    if (isset($codeMapping[$code])) {
                        $websiteIds[] = $codeMapping[$code];
                    }
                }

                $description = null;    // From visual assets; data not yet available. Wait for John P.

                $name = Mage::helper('taxonomy')->removeNonStdAscii($line->name);
                Mage::getModel('compatibility/productline')
                    // Code is set in _beforeSave()
                    ->setName($name)
                    ->setType($line->item_type)
                    ->setWebsiteIds(implode(',', $websiteIds))
                    // Image unknonwn how to obtain
                    ->setImageLink()
                    ->setDescription($description)
                    ->save();
            }

            // Grab all inserted dies and find their compatibilities
            $allDies = Mage::getModel('compatibility/productline')->getCollection()
                ->addFieldToFilter('type', 'die');
            $dieProdLines = $this->_getAllDieProductLines();

            // Go through each die product line and find their associated machine product lines
            foreach ($allDies as $die) {
                $id = $die->getId();    // ID in Magento
                $code = $die->getCode();
                $mongoId = $dieProdLines[$code]['id'];
                $relatedData = $this->_getAssociatedMachineProductLines($mongoId);

                $k = 0;
                foreach ($relatedData as $machineCode => $one) {
                    $row = Mage::getModel('compatibility/productline')->getCollection()
                        // Commented out because some machines have item_type other than "machine" for some reason
                        // ->addFieldToFilter('type', 'machine')
                        ->addFieldToFilter('code', $machineCode)
                        ->getFirstItem();

                    if (!$row->getId()){
                        $this->out($machineCode);
                        $this->out($one);
                        $this->out('Machine product line is not found! Supposed to be available');
                        exit;
                    }

                    $comp = Mage::getModel('compatibility/compatibility')
                        ->setPosition($k)
                        ->setDieProductlineId($id)
                        ->setMachineProductlineId($row->getProductlineId())
                        ->setAssociatedProducts($one['associated_skus'])
                        ->save();

                    $k++;
                }
            }

            $this->out('Compatibility Tables Updated');

        } else {
            $this->log('SDM_Compatibility is not enabled.');
            exit;
        }
    }

    /**
     * Returns all of the die product line tags with name codes as the keys of
     * the returned array.
     *
     * @return
     */
    protected function _getAllDieProductLines()
    {
        $dies = array();

        $results = $this->query(
            "SELECT t.`name`,t.`mongoid`
            FROM products AS p
                INNER JOIN `product_to_tag` AS pt
                    ON p.`mongoid` = pt.`product_mongoid`
                INNER JOIN tags AS t
                    ON pt.`tag_mongoid` = t.`mongoid`
            WHERE t.`tag_type` = 'product_line'
                AND p.`item_type` = 'die'
                AND t.`name` NOT LIKE '%old tag%'
                AND t.`name` NOT LIKE '%test%'
                AND t.`active` = 1
            GROUP BY t.`name`
            ORDER BY t.`name`"
        );

        foreach ($results as $die) {
            $code = Mage::helper('taxonomy')->transformNameToCode($die->name);
            if (isset($dies[$code])) {
                $this->out("Duplicate die product line! --> {$die->name} | $code");
                exit;
            }
            $dies[$code]['id'] = $die->mongoid;
            $dies[$code]['name'] = $die->name;
        }

        return $dies;
    }

    /**
     * Returns all of the associated machine product lines
     *
      * @return array
     */
    protected function _getAssociatedMachineProductLines($dieId)
    {
        $machines = array();

        $result = $this->query(
            "SELECT c.*,t.`name`
            FROM `compatibilities` AS c
                INNER JOIN `tags` AS t
                    ON c.`machineproductline_mongoid` = t.`mongoid`
            WHERE c.`dieprodline_mongoid` = '$dieId'
                AND t.`active` = 1
            ORDER BY c.`id`"
        );

        foreach ($result as $machine) {
            $code = Mage::helper('taxonomy')->transformNameToCode($machine->name);
            $machines[$code]['name'] = $machine->name;
            $machines[$code]['associated_skus'] = $this->_helper
                ->cleanDelimitedString($machine->products);
        }

        return $machines;
    }

    protected function _getProductLineId($mongoId)
    {
        $matrixTable = $this->getTableName('hadderach_machine/productline');
        $res = $this->getConn()->fetchOne(
            "SELECT p.`productline_id`
            FROM `$matrixTable` AS p
            WHERE p.`mongodb_id` = '$mongoId'
            LIMIT 1"
        );
        return $res;
        $productLineId = reset($res);

        return $productLineId;
    }

    /**
     * Deprecated
     *
     * Checks the MD5 hash of the retrieved data to see if changes have been made.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     *
     * @return boolean
     */
    protected function _productDataChanged($product, $data)
    {
        return true;

        if ($this->_alwaysUpdate) {
            return true;
        }

        $hash = $this->getHash($data);
        // echo $hash . PHP_EOL;
        // echo $product->getMd5Hash() . PHP_EOL;
        if ($product->getMd5Hash() == $hash) {
            return false;
        }
        return true;
    }

    /**
     * Returns entity ID if SKU exists in the catalog. False otherwise.
     *
     * @param str $sku
     *
     * @return int|bool
     */
    protected function _doesSkuExist($sku) {
        if ($this->_alwaysUpdate) {
            return false;
        }
        $q = "SELECT `entity_id` FROM `catalog_product_entity` WHERE `sku` = '$sku'";
        $result = $this->getConn()->fetchOne($q);

        return $result;
    }

    /**
     * Get a hash of the data retireved from Ellison. This is used to see any
     * changes are made. Stock qtys are removed as they are updated via the ERP
     * integration.
     *
     * @param stdClass $data
     *
     * @return str
     */
    public function getHash($data)
    {
        // Ignore Magento product status-related data; update this separately before launching
        $temp = clone $data;
        unset($temp->quantity);
        unset($temp->quantity_sz);
        unset($temp->quantity_uk);
        unset($temp->quantity_us);
        unset($temp->active);

        return md5(json_encode($temp));
    }

    protected function _initScpDb()
    {
        $this->_dbcScp = new DB($this->_dbScp, 'root', 'chuck111', 'scp.separationdegrees.com');
    }

    protected function _initShortDescriptions()
    {
        $results = $this->queryScp("SELECT sku, short_description FROM products;");

        foreach ($results as $row) {
            if (isset($row->sku) && $row->sku) {
                $this->_shortDescriptions[$row->sku] = $row->short_description;
            }
        }
    }

    protected function _initVars()
    {
        $this->_helper = Mage::helper('sdm_migration');
        $this->_shellDir = Mage::getBaseDir() . DS . 'shell';
        $this->_productAttSetId = $this->_helper
            ->getAttributeSet(self::PRODUCT_ATTRIBUTE_SET_NAME)
            ->getId();
        $this->_projectAttSetId = $this->_helper
            ->getAttributeSet(self::IDEA_ATTRIBUTE_SET_NAME)
            ->getId();

        // Categories
        $parentIds = $this->_getRootCategories();
        $this->_systemCodes = $this->_helper->getEllisonSystemCodes();
        $this->_websiteMapping = $this->_helper->websiteMapping();
        $this->_storeMapping = $this->_helper->storeMapping();

        $this->_categories = array(
            SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_US => $this->_helper->getCategory(
                'Catalog',
                '2',
                $parentIds[SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_US]
            )->getId(),
            SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_UK => $this->_helper->getCategory(
                'Catalog',
                '2',
                $parentIds[SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_UK]
            )->getId(),
            SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_RE => $this->_helper->getCategory(
                'Catalog',
                '2',
                $parentIds[SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_RE]
            )->getId(),
            SDM_Core_Helper_Data::ELLISON_SYSTEM_CODE_ED => $this->_helper->getCategory(
                'Catalog',
                '2',
                $parentIds[SDM_Core_Helper_Data::WEBSITE_ROOT_CATEGORY_CODE_ED]
            )->getId(),
        );

        // Validate some
        if (!is_numeric($this->_productAttSetId)) {
            $this->log(
                "Product attribute set ID could not be loaded correctly: {$this->_productAttSetId}"
            );
            exit;
        }
        if (!is_numeric($this->_projectAttSetId)) {
            $this->log(
                "Project attribute set ID could not be loaded correctly: {$this->_projectAttSetId}"
            );
            exit;
        }

        $this->out("Product attribute set ID: {$this->_productAttSetId}");
        $this->out("Idea attribute set ID: {$this->_projectAttSetId}");
    }


    protected function _getRootCategories()
    {
        $ids = array();
        $websiteCodes = $this->_helper->getWebsiteCodes();

        foreach ($websiteCodes as $code => $name) {
            $ids[$code] = $this->_helper->getCategory($name, '1', '1')->getId();
        }

        return $ids;
    }

    /**
     * Migrate all of the Ellison categories to Magento
     *
     * Note: This was run once at the very beginning
     */
    protected function _migrateCategories()
    {
        return; // As this function needs to run only once, return it right away.

        // Cleaning categories is done in the installation script in order to
        // prevent unwanted category removal.
        $this->_cleanCategories();   // Note: no such function defined

        // Root categories; mapped manually to reduce work.
        $codeTocatId = array(
            'szus' => 2,
            'szuk' => 7,
            'erus' => 9,
            'eeus' => 11,
        );

        // Go through each website and create all categories
        $menus = Mage::helper('navigation')->getTopNavMenu();
        foreach ($menus as $code => $topMenu) {
            $topMenuData = $this->_getTopEllisonCategories($code);
            $rootCatId = $codeTocatId[$code];

            foreach ($topMenuData as $k => $cols) {
                // Create a level 2 category
                $parentCatId = $this->_createMagentoCategory(
                    array('name' => $topMenu[$k]['name'], 'link' => $topMenu[$k]['link']),
                    "1/$rootCatId",
                    Mage_Core_Model_App::ADMIN_STORE_ID
                );

                foreach ($cols as $col) {
                    // Create a level 3 category
                    $columnData = reset($col);
                    $colLeadCat = $this->_createMagentoCategory(
                        array('name' => $columnData->top_label, 'link' => $columnData->top_link),
                        "1/$rootCatId/$parentCatId",
                        Mage_Core_Model_App::ADMIN_STORE_ID
                    );

                    foreach ($col as $item) {
                        $this->_createMagentoCategory(
                            array('name' => $item->col_label, 'link' => $item->col_link),
                            "1/$rootCatId/$parentCatId/$colLeadCat",
                            Mage_Core_Model_App::ADMIN_STORE_ID
                        );
                    }
                }   // End of one column
            }      // End of all columns
        }   // End of one website
    }

    public function _createMagentoCategory($data, $parentPath, $storeId)
    {
        $skipNameClean = false;
        $convertedLink = Mage::helper('sdm_migration')->ellisonUrlParamsToMagento($data['link']);

        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        $cat['path'] = $parentPath; // absolute category path in IDs
        $cat['filtering_parameter'] = $convertedLink;
        $cat['is_anchor'] = 1;  //for layered navigation
        $cat['is_active'] = 1;  // not sure what makes a category inactive in Melton
        $cat['display_mode'] = "PRODUCTS";

        // Special flags
        if (strpos($data['name'], '____') !== false) {
            $cat['is_divider'] = 1;
            $skipNameClean = true;
        }
        if (strpos($data['name'], '<b>') !== false && strpos($data['name'], '</b>') !== false) {
            $data['name'] = str_replace('<b>', '', $data['name']);
            $data['name'] = str_replace('</b>', '', $data['name']);
            $cat['is_bold'] = 1;
        }

        // Clean name string
        if (!$skipNameClean) {
            $cat['name'] = Mage::helper('sdm_migration')->removeNonStdAscii($data['name']);
        } else {
            $cat['name'] = $data['name'];
        }

        if (is_null($cat['name']) || empty($cat['name'])) {
            $cat['name'] = 'N/A';
        }

        $category->addData($cat);
        $category->save();
        $thisCatId = $category->getId();
        $this->out('Creating: ' . $cat['name'] . ' | ID: ' . $thisCatId);

        return $thisCatId;
    }

    protected function _getTopEllisonCategories($code)
    {
        $menu = array();
        $q = "SELECT v.`mongoid`,v.`columnn` as 'column',v.`label` as 'top_label',
                v.`link` as 'top_link',v.`navigation_type`,v.`tag_type`,v.`top_nav`,
                l.`id`,l.`label` as 'col_label',l.`link` as 'col_link'
            FROM `navigation` AS v
                LEFT JOIN `navigation_links` AS l
                    ON v.`mongoid` = l.`navigations_mongoid`
            WHERE v.`system` = '$code'
            ORDER BY v.`top_nav` ASC,v.`columnn` ASC,l.`id` ASC;";
        $result = $this->query($q);
        // $this->out($result);

        foreach ($result as $item) {
            $menu[(int)$item->top_nav][(int)$item->column][(int)$item->id] = $item;
        }

        return $menu;
    }

    protected function _cleanCategories()
    {
        $collection = SDM_Migration_Model_Resource_Catalog_Setup::getAllNonBaseCategories();

        foreach ($collection as $category) {
            $category->delete();
            $this->out('Deleted: ' . $category->getId() . ' | ' . $category->getName());
        }
    }

    protected function _getLimitRange($type)
    {
        $pageSize = $this->getArg('n');
        $pageNum = $this->getArg('p');
        $limitRange = array();
        $res = $this->query("SELECT min(id) AS min, max(id) AS max FROM $type");
        $res = reset($res);

        $range = abs($res->max - $res->min) + 1;
        $k = 0;
        for ($i = 0 + $pageNum - 1; $i < ceil($res->max/$pageSize); $i++) {
            $limitRange[$k]['page'] = $pageSize*$i;
            $limitRange[$k]['size'] = $pageSize;
            $k++;
        }

        return $limitRange;
    }

    /**
     * Stops the script if a recursive method is going to potentially loop
     * endlessly.
     */
    protected function _recursiveHandBreak()
    {
        if ($this->_recursiveCount == 2) {
            $this->out('Max save iteration reached. Check recursive function.');
            $this->out(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5));
            exit;
        }
        $this->_recursiveCount++;
    }

    /**
     * Returns true if today's date is within the given range
     *
     * @param str $start MySQL DATEIME format
     * @param str $end MySQL DATETIME format
     *
     * @return bool
     */
    protected function _isTodayWithinRange($start, $end)
    {
        $today = strtotime(now());
        $start = strtotime($start);
        $end = strtotime($end);

        if ($today >= $start && $today <= $end) {
            return true;
        }

        return false;
    }

    /**
     * Only fixes "zero" dates. Empty dates are ignored because they are not supposed
     * to get anything assigned if it's empty.
     */
    protected function _fixZeroDate($datetime)
    {
        if ($datetime == '0000-00-00 00:00:00') {
            return $this->_someStartDate;
        }

        return $datetime;
    }

    /**
     * Converts the encoding to UTF-8. Assumes windows-1250 encoding of the argument
     * string.
     *
     * @param str $text
     *
     * @return str
     */
    public function convertEncoding($text)
    {
        $text = iconv('windows-1250', 'utf-8', $text);

        return $text;
    }

    public function getProductAttributeList()
    {
        return implode(',', $this->_productAttributes);
    }

    public function getTagAttributeList()
    {
        return implode(',', $this->_tagAttributes);
    }


    public function queryScp($q)
    {
        return $this->_dbcScp->query($q)->result();
    }

    /**
     * Set script arguments
     */
    public function setArgs()
    {
        $pageSize = $this->getArg('n');     // SQL page size
        $pageNum = $this->getArg('p');      // SQL page number
        $break = $this->getArg('b');        // Break after one run: default 1
        $type = $this->getArg('t');         // Product type to migrate

        // Set page number and page size of the SELECT query
        if ($pageSize && $pageNum) {
            $this->_pageSize = $pageSize;

            $this->_paginationSteps['ideas'] = $this->_getLimitRange('ideas');
            $this->_paginationSteps['products'] = $this->_getLimitRange('products');

            $this->out('Pagination activated. N = ' . $pageSize);
        } elseif ($pageSize) {
            $this->out('You must supply both arguments n and p');
            exit;
        } elseif ($pageNum) {
            $this->out('You must supply both arguments n and p');
            exit;
        } elseif (!$pageSize && !$pageNum) {
            $this->_paginationSteps['ideas'][0] = array('page' => 0, 'size' => 9999999);
            $this->_paginationSteps['products'][0] = array('page' => 0, 'size' => 9999999);
        }
        // print_r($this->_paginationSteps); die;

        // Note: _breakAfterOnePage is true by default
        if ((bool)$break === true && $pageSize && $pageNum) {
            $this->_breakAfterOnePage = true;
        }

        if ($type == 'products' || $type == 'ideas') {
            $this->_productType = $type;
        } elseif (!empty($type)) {
            $this->out('Error: -t argument must be either "products" or "ideas".'); exit;
        }

        // Image (all types) download flag
        if ($this->getArg('i') && $this->getArg('i') == 1) {
            $this->_downloadImages = true;
        } else {
            $this->_downloadImages = false;
        }

        // Data (customer groups, taxonomy, etc.) update flag
        if ($this->getArg('d') && $this->getArg('d') == 1) {
            $this->_updateData = true;
        } else {
            $this->_updateData = false;
        }
    }
}

$shell = new SDM_Shell_MigrateProducts();
$shell->run();
