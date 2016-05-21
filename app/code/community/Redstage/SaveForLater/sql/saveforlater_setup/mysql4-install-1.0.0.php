<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('saveforlater_item')};
CREATE TABLE {$this->getTable('saveforlater_item')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11),
  `quote_id` int(11),
  `product_id` int(11) NOT NULL,
  `name` varchar(255),
  `qty` int(11),
  `price` decimal(12,4) default NULL,
  `buy_request` TEXT,
  `date_saved` DATETIME,
  PRIMARY KEY ( `id` )
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

");

$installer->endSetup();

?>