<?php
/**
 * Separation Degrees Media
 *
 * SDM's core extension
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Core
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

// Remove adjustware_nav from the database
$this->run("DROP TABLE  `adjnav_catalog_product_index_configurable`");
$this->run("DROP TABLE  `adjnav_cron`");
$this->run("DROP TABLE  `adjnav_eav_attribute_option_hit`");
$this->run("DROP TABLE  `adjnav_eav_attribute_option_stat`");
$this->run("DROP TABLE  `adjnav_eav_attribute_stat`");
$this->run("DROP TABLE  `adjnav_option_hit_replace`");
$this->run("DELETE FROM core_resource WHERE code='adjnav_setup'");
