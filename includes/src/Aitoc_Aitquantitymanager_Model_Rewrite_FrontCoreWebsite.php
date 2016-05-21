<?php
/**
 * Multi-Location Inventory
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitquantitymanager
 * @version      2.2.3
 * @license:     rJhV4acfvLy4sPgpe7MoLJnfOEhDVfWVuKRvbpcv30
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCoreWebsite extends Mage_Core_Model_Website
{
    
    protected function _construct()
    {
        Mage::getModel('aitquantitymanager/moduleObserver')->onAitocModuleLoad();
        
#        $this->_init('core/website');
        $this->_init('aitquantitymanager/core_website');
    }
    
    
    // override parent
    public function load($id, $field = null)
    {
        
// start aitoc code
        if ($id AND is_numeric($id) AND $id == Mage::helper('aitquantitymanager')->getHiddenWebsiteId()) 
        {
            $id = 0; 
        }
// finish aitoc code

        return parent::load($id, $field);
    }
    
}