<?php

/**
 * Modified this file so we can have Amasty_Sorting extend from Adjustware_Nav
 *
 *  if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Shopby/active')){
 *      class Amasty_Sorting_Block_Catalog_Product_List_Toolbar_Pure extends Amasty_Shopby_Block_Catalog_Product_List_Toolbar {}
 *  } else {
 *      class Amasty_Sorting_Block_Catalog_Product_List_Toolbar_Pure extends Mage_Catalog_Block_Product_List_Toolbar {}
 *  }
*/

class Amasty_Sorting_Block_Catalog_Product_List_Toolbar_Pure extends AdjustWare_Nav_Block_Rewrite_FrontCatalogProductListToolbar {}
