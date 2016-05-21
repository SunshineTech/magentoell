<?php
class AHT_Advancedmedia_Block_Video extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('advancedmedia/video.phtml');
    }
	
	public function getMediaUrl(){
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
	}
	
	public function getCancelImg(){
		return $this->getMediaUrl().'advancedmedia/uploadify/cancel.png';
	}
	
	public function getUploaderSwfUrl(){
		return $this->getMediaUrl().'advancedmedia/uploadify/uploadify.swf';
	}
	
	public function getScriptAction(){
		return Mage::getUrl('advancedmedia/index/upload');
	}
	
	public function getMedia(){
		$collection = Mage::getModel('advancedmedia/advancedmedia')->getCollection();
		return $collection;
	}
	
	public function getMediaByProduct(){
		if($productId = $this->getRequest()->getParam('id')){
			$media = $this->getMedia()->addFieldToFilter('product_id', $productId);
		}
		return @$media;
	}
	
	public function getLastedId(){
		$media = $this->getMedia()->setOrder('advancedmedia_id', 'DESC')->setPageSize(1);
		if(count($media)>0){
			foreach($media as $_media){
				return $_media->getId();
			}
		}
		else{
			return 0;
		}
	}
}
