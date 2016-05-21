<?php
require_once 'app/code/core/Mage/Adminhtml/controllers/Catalog/ProductController.php';
class AHT_Advancedmedia_Adminhtml_AdvancedmediaController extends Mage_Adminhtml_Catalog_ProductController
{
	/**
     * Save product action
     */
    public function saveAction()
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $productId      = $this->getRequest()->getParam('id');
        $isEdit         = (int)($this->getRequest()->getParam('id') != null);

        $data = $this->getRequest()->getPost();
		
		//$videoPost =  $data['video']; echo '<pre>'; print_r($videoPost).'<br><br><br>'; die();
		
        if ($data) {
            if (!isset($data['product']['stock_data']['use_config_manage_stock'])) {
                $data['product']['stock_data']['use_config_manage_stock'] = 0;
            }
            $product = $this->_initProductSave();

            try {
                $product->save();
                $productId = $product->getId();
				
				//Begin Advanced Media
				
				$oldMedias = Mage::getModel('advancedmedia/advancedmedia')
					->getCollection()
					->addFieldToFilter('product_id', $productId);
				if(count($oldMedias)>0){
					foreach($oldMedias as $_oldMedia){
						Mage::getModel('advancedmedia/advancedmedia')->load($_oldMedia->getId())->delete();
					}
				}
				
				if($videoPost =  $data['video']){
					$advancedMedia = Mage::getModel('advancedmedia/advancedmedia');
					//foreach($videoPost['use_type'] as $key=>$valueArr){
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
					//}
				}

                /**
                 * Do copying data to stores
                 */
                if (isset($data['copy_to_stores'])) {
                    foreach ($data['copy_to_stores'] as $storeTo=>$storeFrom) {
                        $newProduct = Mage::getModel('catalog/product')
                            ->setStoreId($storeFrom)
                            ->load($productId)
                            ->setStoreId($storeTo)
                            ->save();
                    }
                }

                Mage::getModel('catalogrule/rule')->applyAllRulesToProduct($productId);

                $this->_getSession()->addSuccess($this->__('The product has been saved.'));
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setProductData($data);
                $redirectBack = true;
            }
            catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id'    => $productId,
                '_current'=>true
            ));
        }
        else if($this->getRequest()->getParam('popup')) {
            $this->_redirect('*/*/created', array(
                '_current'   => true,
                'id'         => $productId,
                'edit'       => $isEdit
            ));
        }
        else {
            $this->_redirect('*/*/', array('store'=>$storeId));
        }
    }
}