<?php
class Wsu_Storeutilities_Helper_Cloudflarehttp extends Mage_Core_Helper_Http{
	
	public function getRemoteAddr($ipToLong = false){
		
		if (is_null($this->_remoteAddr)) {
			if ( isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && $_SERVER["HTTP_CF_CONNECTING_IP"] ) {
				$this->_remoteAddr = $_SERVER["HTTP_CF_CONNECTING_IP"];
			} else {
				$this->_remoteAddr = parent::getRemoteAddr(false);
			}
		}		
		return $ipToLong ? ip2long($this->_remoteAddr) : $this->_remoteAddr;
	}

}
?>