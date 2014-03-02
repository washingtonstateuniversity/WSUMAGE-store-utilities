<?php
class Wsu_Storeutilities_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action {
	
	public $cleared_attrs = 0;
    /**
     * Attempt to remove any required attributes linked to the product that are not in the new attribute set.
     * 
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _cleanAttributes(Mage_Catalog_Model_Product $product) {
        $write          = $product->getResource()->getWriteConnection();
        $required       = Mage::helper('storeutilities')->_getRequiredAttributes($product->getAttributeSetId());
        $all_attributes = Mage::helper('storeutilities')->_getAllAttributes();
        foreach ($all_attributes as $attribute) {
            try {
                if (!in_array($attribute['code'], $required)) {
                    $write->delete($attribute['table'], join(' AND ', array(
                        $write->quoteInto('attribute_id=?', $attribute['id']),
                        $write->quoteInto('entity_id=?', $product->getId())
                    )));
					$this->cleared_attrs++;
                }
            }
            catch (Exception $e) {
                $this->_getSession()->addError("Failed to unlink attribute {$attribute['code']} from product.".$e->getMessage());
            }
        }
    }
	

    /**
     * Change the attribute set of the product.
     */
    public function cleanattributesAction() {
        $_productIds        	= $this->getRequest()->getParam('product');
        $productIds         	= array_map('intval', $_productIds);
        $affectedProductIds 	= array();
        $storeId            	= (int) $this->getRequest()->getParam('store', 0);
        $this->cleared_attrs	=0;
        $defaultSetId       	= Mage::getSingleton('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId();
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s)'));
        } else {
            try {
                foreach ($productIds as $productId) {
                    $product			= Mage::getSingleton('catalog/product')->unsetData()->setStoreId($storeId)->load($productId);
                    $ptype     			= $product->getTypeID();
                    $is_simple 			= (!$product->isComposite() && !$product->isSuper());
                    //at this time we want to just do the simple product types.  Maybe later we can test out for something better
                    if ($is_simple && $ptype != "bundle") {
						if(Mage::helper('storeutilities')->hasExt('Wsu_Logger')){
							$_product=Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
							Mage::getModel('wsu_logger/stock_observer')->insertStockMovement($_product, "Cleaned attributes for {$product->getName()} in mass edit");
						}
                        $this->_cleanAttributes($product);
                        $affectedProductIds[] = $product->getEntityId();
                    } else {
                        $this->_getSession()->addError($this->__('Skipping product ' . $product->getName() . ' as it is not the base product.'));
                    }
                }
                Mage::dispatchEvent('catalog_product_massupdate_after', array(
                    'products' => $affectedProductIds
                ));
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated with %d attributes cleared.', count($affectedProductIds),$this->cleared_attrs));
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e, $e->getMessage());
            }
        }
        $this->_redirect('adminhtml/catalog_product/index/', array());
    }	
	
	
    /**
     * Change the attribute set of the product.
     */
    public function changeattributesetAction() {
        $_productIds        = $this->getRequest()->getParam('product');
        $productIds         = array_map('intval', $_productIds);
        $affectedProductIds = array();
        $storeId            = (int) $this->getRequest()->getParam('store', 0);
        $attributeSet       = (int) $this->getRequest()->getParam('attribute_set');
        $defaultSetId       = Mage::getSingleton('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId();
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s)'));
        } else {
            try {
                foreach ($productIds as $productId) {
                    $product   = Mage::getSingleton('catalog/product')->unsetData()->setStoreId($storeId)->load($productId);
                    $ptype     = $product->getTypeID();
                    $is_simple = (!$product->isComposite() && !$product->isSuper());
                    //at this time we want to just do the simple product types.  Maybe later we can test out for something better
                    if ($is_simple && $ptype != "bundle") {
                        $product->setAttributeSetId($attributeSet)->setIsMassupdate(true)->save();
						if(Mage::helper('storeutilities')->hasExt('Wsu_Logger')){
							$_product=Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
							Mage::getModel('wsu_logger/stock_observer')->insertStockMovement($_product, "Changed to attribute $attributeSet in mass edit");
						}
                        $this->_cleanAttributes($product);
                        $affectedProductIds[] = $product->getEntityId();
                    } else {
                        $this->_getSession()->addError($this->__('Skipping product ' . $product->getName() . ' as it is not the base product.'));
                    }
                }
                Mage::dispatchEvent('catalog_product_massupdate_after', array(
                    'products' => $affectedProductIds
                ));
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($affectedProductIds)));
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e, $e->getMessage());
            }
        }
        $this->_redirect('adminhtml/catalog_product/index/', array());
    }

	public function startchangecategoriesAction(){
		$this->loadLayout();
		
		//create a text block with the name of "example-block"
		$block = $this->getLayout()
		->createBlock('core/template', 'choose_categories')
		->setTemplate('wsu/storeutilities/choose_categories.phtml');
		
		$_productIds        = $this->getRequest()->getParam('product');
		$block->assign('_productIds',$_productIds);
		
		$_method        = $this->getRequest()->getParam('method');
		$block->assign('method',$_method);
		
		$this->_addContent($block);

        $this->renderLayout();
		//die('here');	
	}
	
	public function changeCategoriesAction(){
		$_productIds        = $this->getRequest()->getParam('product');
        $productIds         = array_map('intval', $_productIds);
        $affectedProductIds = array();
        $storeId            = (int) $this->getRequest()->getParam('store', 0);	
		
		$_categoryIds        = $this->getRequest()->getParam('categories');
		$_method        = $this->getRequest()->getParam('method');
		try {
			foreach ($productIds as $productId) {
				$pro_cat_list = array();
				$fin_cat_list = array();
				$newcat_array = array();
				$_product   = Mage::getSingleton('catalog/product')->unsetData()->setStoreId($storeId)->load($productId);

				if( $_method=='add' || $_method=='remove'  ){
					$catCollection = $_product->getCategoryCollection();
					# export this collection to array so we could iterate on it's elements
					$categs = $catCollection->exportToArray();
	
					foreach($categs as $cat){
						$pro_cat_list[] = $cat['entity_id'];
					}	
					if( $_method=='remove' ){
						foreach($pro_cat_list as $cat){
							if(!in_array($cat,$_categoryIds)){
								$fin_cat_list[] = 	$cat;
							}
						}	
						$newcat_array = $fin_cat_list;
						$action = Mage::helper('storeutilities')->__('Removing');
					}else{
						$newcat_array = Mage::helper('storeutilities/utilities')->extend($pro_cat_list,$_categoryIds);
						$action = Mage::helper('storeutilities')->__('Extending what was there');
					}
				}
				if(empty($newcat_array) && !empty($_categoryIds) && $_method!='remove'){
					$newcat_array = $_categoryIds;
					$action = Mage::helper('storeutilities')->__('Matching exactly');
				}
				$newcat_array=array_unique($newcat_array);
				$_product->setCategoryIds($newcat_array); 
				$_product->save();

				if(Mage::helper('storeutilities')->hasExt('Wsu_Logger')){
					$product = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
					$message = Mage::helper('storeutilities')->__("Altered it's categories in mass edit by ").$action;
					Mage::getModel('wsu_logger/stock_observer')->insertStockMovement($product,$message);
				}
				$affectedProductIds[] = $_product->getEntityId();
				unset($_product);
			}
			Mage::dispatchEvent('catalog_product_massupdate_after', array(
				'products' => $affectedProductIds
			));
			
			$this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($affectedProductIds)));
		}
		catch (Exception $e) {
			var_dump($e->getMessage());die();
			$this->_getSession()->addException($e, $e->getMessage());
		}
		$this->_redirect('adminhtml/catalog_product/index/', array());
	}
	
	
	
}
