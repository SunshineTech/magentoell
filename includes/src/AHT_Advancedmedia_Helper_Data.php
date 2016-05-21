<?php

class AHT_Advancedmedia_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getYoutubeKey($embed){
		$arrString = explode(" ", $embed);
		$str = '';
		foreach($arrString as $string){
			$_arrString = explode("=", $string);
			if($_arrString[0]=='src'){
				$str = $_arrString[1];
			}
		}
		if($str!=''){
			$str = str_replace('"','',$str);
			$arrStr = explode("/",$str);
			$key = end($arrStr);
			return $key;
		}
		return;
	}
	public function getYoutubeLink($embed){
		$arrString = explode(" ", $embed);
		$str = '';
		foreach($arrString as $string){
			$_arrString = explode("=", $string);
			if($_arrString[0]=='src'){
				$str = $_arrString[1];
			}
		}
		return str_replace('"','',$str);
	}
}