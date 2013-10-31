<?php
/**
 * Catalog product model
 *
 * @method Mage_Catalog_Model_Resource_Product getResource()
 * @method Mage_Catalog_Model_Resource_Product _getResource()
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Wsu_Storeutilities_Model_Product extends Mage_Catalog_Model_Product {
    public function getProductUrl($useSid = NULL){
		$_proId =  $this->getId();
		$product= Mage::getModel('catalog/product')->load($_proId); 
		$stores = $product->getStoreIds();
		$pstore_id = count($stores)>1?array_shift(array_values($product->getStoreIds())):$stores[0];
		/*if(Mage::app()->getStore()->getStoreId() == $pstore_id){
			$purl = $this->getUrlModel()->getProductUrl($this, $useSid);//$this->getProductUrl();
		}else{
			$base = Mage::app()->getStore($pstore_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
			$purl = $base.$product->getUrlPath();
		}*/
		
		$base = Mage::app()->getStore($pstore_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		$purl = $base.$product->getUrlPath();
		
		return $purl;
    }
}
