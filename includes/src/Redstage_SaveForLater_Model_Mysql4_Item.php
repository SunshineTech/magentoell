<?php

class Redstage_SaveForLater_Model_Mysql4_Item extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct(){
		$this->_init('saveforlater/item', 'id');
	}

}

?>