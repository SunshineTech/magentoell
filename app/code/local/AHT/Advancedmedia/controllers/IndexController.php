<?php
class AHT_Advancedmedia_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$zoomWidth = Mage::getStoreConfig('advancedmedia/imagezoom/width');
		$zoomHeight = Mage::getStoreConfig('advancedmedia/imagezoom/height');
		$zoomPosition = Mage::getStoreConfig('advancedmedia/imagezoom/position');
		$mainImageWidth = Mage::getStoreConfig('advancedmedia/general/width');
		$mainImageHeight = Mage::getStoreConfig('advancedmedia/general/height');
		$url = $this->getRequest()->getParam('url');
		$rel = 'zoomWidth: '.$zoomWidth.',zoomHeight: '.$zoomHeight.',position: '.$zoomPosition.',smoothMove: 3,showTitle: true,titleOpacity: 0.01,lensOpacity: 0.005,tintOpacity: 0.005,softFocus: false';
		//$html = '<p class="product-image product-image-zoom">';
		$html = '<div id="wrap" style="top:0px;z-index:9999;position:relative;">';
		$html.= '<a id="cloudZoom" class="cloud-zoom" href="'.$url.'" rel="'.$rel.'" gallery="'.$url.'">';
		$html.= '<img id="image" src="'.$url.'" width="'.$mainImageWidth.'" height="'.$mainImageheight.'"/>';
		$html.= '</a>';
		$html.= '<div class="mousetrap" style="background-image:url(&quot;.&quot;);z-index:999;position:absolute;width:'.$mainImageWidth.'px;height:'.$mainImageheight.'px;left:0px;top:0px;"></div>';
		$html.= '</div>';
		$html.= '<div class="advancedmedia-loading" style="display:none"><img src="'.Mage::getDesign()->getSkinUrl('advancedmedia/images/ajax-loader.gif').'" alt="Loading..."/></div>';
		echo $html;
	}

	public function videoAction()
	{
		$mainImageWidth = Mage::getStoreConfig('advancedmedia/general/width');
		$mainImageHeight = Mage::getStoreConfig('advancedmedia/general/height');
		$id = $this->getRequest()->getParam('id');
		$advancedMedia = Mage::getModel('advancedmedia/advancedmedia')->load($id);

		if($advancedMedia->getUseType()==1){
			$html = '<a href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$advancedMedia->getMediaSrc().'" style="display:block;width:'.$mainImageWidth.'px;height:'.$mainImageHeight.'px" id="player"></a>';
		}
		else{
			$html = $advancedMedia->getMediaEmbed();
		}
		echo $html;

	}

	public function uploadAction(){
		$tempFile = $_FILES['Filedata']['tmp_name'];
		$targetPath = Mage::getBaseDir('media').'/catalog/product/';//$_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';

		$firstCharacter = substr ($_FILES['Filedata']['name'], 0, 1);
		$targetPath.=$firstCharacter;

		if(!is_dir($targetPath)){
			mkdir($targetPath , 0777, true);
		}

		$secondCharacter = substr ($_FILES['Filedata']['name'], 1, 1);
		$targetPath.='/'.$secondCharacter;

		if(!is_dir($targetPath)){
			mkdir($targetPath , 0777, true);
		}
		$targetFile = $targetPath.'/'.$_FILES['Filedata']['name'];
		move_uploaded_file($tempFile,$targetFile);
		$string = str_replace(Mage::getBaseDir('media'), '', $targetFile);

		echo str_replace('/catalog', 'catalog' ,$string);
	}

	public function iframeAction(){
		$this->loadLayout();
		$this->renderLayout();
	}
}