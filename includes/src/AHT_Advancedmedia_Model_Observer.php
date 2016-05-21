<?php
class AHT_Advancedmedia_Model_Observer
{


    public function saveMedia(Varien_Event_Observer $observer)
    {  
        $data = Mage::app()->getRequest()->getPost();
		
		$product = $observer->getEvent()->getProduct();
		$productId = $product->getId();
		
		$oldMedias = Mage::getModel('advancedmedia/advancedmedia')
			->getCollection()
			->addFieldToFilter('product_id', $productId);
		if(count($oldMedias)>0){
			foreach($oldMedias as $_oldMedia){
				Mage::getModel('advancedmedia/advancedmedia')->load($_oldMedia->getId())->delete();
			}
		}
		
		if(isset($data['video'])){
			if($videoPost =  $data['video']){
				$advancedMedia = Mage::getModel('advancedmedia/advancedmedia');
				
				$arrData = array();
				foreach($videoPost['use_type'] as $key2=>$value){
					if($videoPost['remove'][$key2]==0){
						empty($arrData);
						$arrData['product_id'] = $productId;
						$arrData['media_type'] = 1;
						$arrData['media_src'] = $videoPost['src'][$key2];
						$arrData['media_embed'] = $videoPost['embed'][$key2];
						$arrData['media_label'] = $videoPost['label'][$key2];
						$arrData['use_type'] = $videoPost['use_type'][$key2];
						$arrData['media_position'] = $videoPost['position'][$key2];
						$arrData['is_exclude'] = $videoPost['exclude'][$key2];
						if($arrData['media_src']!='' || $arrData['media_embed']!=''){
							$advancedMedia->setData($arrData)->save();
						}
					}
				}

			}
		}
	}


}