<?php
class Wsu_Storeutilities_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConfig($field, $default = null){
        $value = Mage::getStoreConfig('localeselector/option/'.$field);
        if(!isset($value) or trim($value) == ''){
            return $default;
        }else{
            return $value;
        }
	}
}
	 