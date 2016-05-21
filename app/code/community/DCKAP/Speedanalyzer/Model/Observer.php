<?php

class DCKAP_Speedanalyzer_Model_Observer extends Mage_Core_Block_Abstract {

  public function showSpeed(Varien_Event_Observer $observer) {
     
      /* @var $timer Mage_Core_Block_Profiler */
      $timers            = Varien_Profiler::getTimers();   
      $showSpeedAnalyzer = Mage::getStoreConfig('speedanalyzer/general/speedanalyzerenable');      
      if($showSpeedAnalyzer):
      /* @var $block Mage_Core_Block_Abstract */
      $block             = $observer->getBlock();    
      $transport         = $observer->getTransport();
      $fileName          = $block->getTemplateFile();
      $thisClass         = get_class($block);
      $thisname          = $block->getNameInLayout();
      if(!empty($timers[$fileName]))
      {
        $fileLoadTime      = number_format($timers[$fileName]['sum'],4);      
        $fileLoadCount     = $timers[$fileName]['count'];
      }
      else{
        $fileLoadTime ='';
      }
      if(!empty($timers[$thisname]))
      {
        $blockLoadTime     = number_format($timers['BLOCK: '.$thisname]['sum'],4);
        $blockLoadCount    = $timers['BLOCK: '.$thisname]['count'];
      }
      else {
       $blockLoadTime     = '';
      }     
      
    
      $showPath          =  Mage::getStoreConfig('speedanalyzer/general/templatepath');
      $showTime          =  Mage::getStoreConfig('speedanalyzer/general/showtime');
      $showCount         = Mage::getStoreConfig('speedanalyzer/general/showcount');
      
      
      //check if speedAnalyzer is enabled in backend and load time is more than 0
      if($fileName && $fileLoadTime > 0 && $showSpeedAnalyzer) {    
        $preHtml = '<div style="position:relative; border:1px dotted red; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;">
    <div style="position: absolute; z-index: 998; top: 0px; text-align: left ! important; left: 0px; font: 11px Arial; padding: 0px; background: none repeat scroll 0% 0% rgb(237, 63, 63); color: white; border: 1px solid rgb(255, 255, 255);" onmouseover="this.style.zIndex=\'999\'"
    onmouseout="this.style.zIndex=\'998\'" title="'.$fileName.'">';
      //check if showPath is enabled in backend 
        if($showPath)
            $preHtml .= '<div style="float: left; margin: 2px;">'.$fileName.'</div>';
      //check if showTime is enabled in backend 
        if($showTime) 
          $preHtml .= '<div style="float: right; width: 50px; padding: 2px 2px 2px 9px; background: none repeat scroll 0% 0% rgb(55, 55, 55);">'.$fileLoadTime.'</div>';
      //check if showCount is enabled in backend 
        if($showCount)
          $preHtml .= '<div style="color: white; float: right; width: 20px; padding: 2px 2px 2px 9px; background: none repeat scroll 0% 0% rgb(0, 195, 117);">'.$fileLoadCount.'</div>';
        $preHtml .= '</div>';
        if($blockLoadTime > 0){
        $preHtml .= '<div  style="position:absolute; right:0; top:0; padding:0; background: none repeat scroll 0% 0% rgb(237, 63, 63); color: white; border: 1px solid rgb(255, 255, 255);font:normal 11px Arial;
            text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'" onmouseout="this.style.zIndex=\'998\'"title="'.$thisClass.'">';
      //check if showPath is enabled in backend 
        if($showPath)
            $preHtml .= '<div style="float: left; margin: 2px;">'.$thisClass.'</div>';
      //check if showTime is enabled in backend 
        if($showTime) 
            $preHtml .= '<div style="float: right; width: 50px; padding: 2px 2px 2px 9px; background: none repeat scroll 0% 0% rgb(55, 55, 55);">'.$blockLoadTime.'</div>';
      //check if showCount is enabled in backend 
        if($showCount)
            $preHtml .= '<div style="color: white; float: right; width: 20px; padding: 2px 2px 2px 9px; background: none repeat scroll 0% 0% rgb(0, 195, 117);">'.$blockLoadCount.'</div>';
        
         $preHtml .= '</div>';
        }
         $postHtml = '</div>';
      }
      else {
         $preHtml   = null;
         $postHtml  = null;
      }


      $html = $transport->getHtml();
      $html = $preHtml . $html . $postHtml;
      $transport->setHtml($html);
      endif;
    //endif;
  }
}
