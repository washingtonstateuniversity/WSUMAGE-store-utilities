<?php
//just to change the url when in the view.. blaa.. *this is stupid (*in yelling sparta voice*)*
class Wsu_Storeutilities_Block_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer{
	
	//getItemUrl
	
	
    public function getProductUrl(){	
		$product = $this->getProduct();
        $option  = $this->getItem()->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }
		$_proId =  $product->getId();
		$product= Mage::getModel('catalog/product')->load($_proId); 
		$stores = $product->getStoreIds();
		$pstore_id = count($stores)>1?array_shift(array_values($product->getStoreIds())):$stores[0];
		$base = Mage::app()->getStore($pstore_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		$purl = $base.$product->getUrlPath();
		return $purl;
    }
}