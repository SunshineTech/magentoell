<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty Ltd. ( http://www.amasty.com/ )
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Saving extends Amasty_Sorting_Model_Method_Abstract
{
    public function getCode()
    {
        return 'saving';
    }    
    
    public function getName()
    {
        return 'Biggest Saving';
    }
    
    public function apply($collection, $currDir)  
    {
        if (!$this->isEnabled()){
            return $this;
        }

        $alias = 'price_index';
        if (preg_match('/`([a-z0-9\_]+)`\.`final_price`/', $collection->getSelect()->__toString(), $m)){
		    $alias = $m[1];
        }

        $collection->getSelect()->columns(array('saving' => "(`$alias`.price - IF(`$alias`.tier_price IS NOT NULL, LEAST(`$alias`.min_price, `$alias`.tier_price), `$alias`.min_price))"));
        $collection->getSelect()->order("saving $currDir");
        
        return $this;
    }
}