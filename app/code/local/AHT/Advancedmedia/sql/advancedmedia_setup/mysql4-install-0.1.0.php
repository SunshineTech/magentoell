<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('advancedmedia')};
CREATE TABLE {$this->getTable('advancedmedia')} (
  `advancedmedia_id` int(11) unsigned NOT NULL auto_increment,
  `product_id` int(11) NOT NULL default '0',
  `media_type` varchar(255) NOT NULL default '',
  `media_src` varchar(255) NOT NULL default '',
  `media_embed` text NOT NULL default '',
  `media_label` varchar(255) NOT NULL default '',
  `use_type` int(11) NOT NULL default '0',
  `media_position` int(11) NOT NULL default '0',
  `is_exclude` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`advancedmedia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 