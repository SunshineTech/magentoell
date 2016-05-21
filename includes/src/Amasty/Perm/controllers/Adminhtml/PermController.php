<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Adminhtml_PermController extends Mage_Adminhtml_Controller_Action
{
    public function reportsAction() 
	{
	    $this->getResponse()->setBody(
	           $this->getLayout()->createBlock('amperm/adminhtml_reports')->toHtml()
	    ); 
	}    
    
    public function relationAction() 
	{
        $grid = $this->getLayout()->createBlock('amperm/adminhtml_relation')
                    ->setSelectedCustomers($this->getRequest()->getPost('selected_customers', null));
        
        // get serializer block html if needed
        $serializerHtml = ''; 
        if ($this->isFirstTime()){
            $serializer = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
            $serializer->initSerializerBlock($grid, 'getSavedCustomers', 'selected_customers', 'selected_customers');
            $serializerHtml = $serializer->toHtml();
        } 
                
	    $this->getResponse()->setBody(
	           $grid->toHtml() . $serializerHtml
	    ); 
	}
	
	private function isFirstTime()
	{
	    $res = true;
	    
        $params = $this->getRequest()->getParams();
        $keys   = array('sort', 'filter', 'limit', 'page');
        
        foreach($keys as $k){
            if (array_key_exists($k, $params))
                $res = false;
        }
        
        return $res;	    
	}

    public function exportCsvAction()
    {
        $content = $this->getLayout()->createBlock('amperm/adminhtml_reports')
            ->getCsvFile();
        $this->_prepareDownloadResponse('reports.csv', $content);  
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/acl/users');
    }
}