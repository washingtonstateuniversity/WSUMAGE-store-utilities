<?php
class Wsu_Storeutilities_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action {

    // When set to true, attribute set changes from anything but the default are prohibited
    const RESTRICT_CHANGES_FROM_NON_DEFAULT=false;
    const DEFAULT_ATTRIBUTE_SET_NAME='Default';
    
    protected $_defaultAttributes=array();
    
    /**
     * Get or initialize an array of the default attributes for every product.
     * 
     * @return array The default attribute codes.
     */
    protected function _getRequiredAttributes($product) {
		$_requiredAttributes=array();
		$attributes = Mage::getModel('catalog/product_attribute_api')->items($product->getAttributeSetId());
		foreach($attributes as $_attribute) {
			$_requiredAttributes[]=$_attribute['code'];
		}
        return $_requiredAttributes;
    }
    
    /**
     * Attempt to remove any non-default attributes linked to the product.
     * 
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _cleanAttributes(Mage_Catalog_Model_Product $product) {
        $write=$product->getResource()->getWriteConnection();
		$required=$this->_getRequiredAttributes($product);
        foreach($product->getAttributes() as $attribute) {
            try {
                // Check if this is a default attribute
                if($attribute->getAttributeId() && !in_array($attribute->getAttributeCode(),$required)) {
                    $write->delete($attribute->getBackend()->getTable(),join(' AND ',array(
                        $write->quoteInto('attribute_id=?',$attribute->getAttributeId()),
                        $write->quoteInto('entity_id=?',$product->getId())
                    )));
                }
            } catch(Exception $e) {
                $this->_getSession()->addError("Failed to unlink attribute {$attribute->getAttributeId()} from product.");
            }
        }
    }
    
    /**
     * Change the attribute set of the product.
     */
    public function changeattributesetAction() {
        $_productIds        = $this->getRequest()->getParam('product');
        $productIds         = array_map('intval',$_productIds);
        $affectedProductIds = array();
        $storeId            = (int) $this->getRequest()->getParam('store',0);
        $attributeSet       = (int) $this->getRequest()->getParam('attribute_set');
        $defaultSetId       = Mage::getSingleton('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId();

		if (!is_array($productIds)) {
			$this->_getSession()->addError($this->__('Please select product(s)'));
		} else {
			try {
				foreach ($productIds as $productId) {
					$product = Mage::getSingleton('catalog/product')->unsetData()->setStoreId($storeId)->load($productId);
						
					$ptype = $product->getTypeID();
					$is_simple = (!$product->isComposite() && !$product->isSuper());
					
					if($is_simple && $ptype!="bundle"){
						
						$product->setAttributeSetId($attributeSet)
							->setIsMassupdate(true)
							->save();
						$this->_cleanAttributes($product);
						$affectedProductIds[]=$product->getEntityId();
					}else{
						$this->_getSession()->addError($this->__('Skipping product '.$product->getName().' as it is not the base product.'));
					}
                }
                Mage::dispatchEvent('catalog_product_massupdate_after',array('products'=>$affectedProductIds));
                
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated',count($affectedProductIds)));
				
            } catch(Exception $e) {
                $this->_getSession()->addException($e,$e->getMessage());
            }
        }
        $this->_redirect('adminhtml/catalog_product/index/',array());
    }
}
