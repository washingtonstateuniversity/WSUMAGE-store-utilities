<?php
class Wsu_Storeutilities_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action {
	
	/**
	 * Product list page
	 */
	public function changeattributesetAction(){
		$productIds = $this->getRequest()->getParam('product');
		$storeId = (int)$this->getRequest()->getParam('store', 0);
		if (!is_array($productIds)) {
			$this->_getSession()->addError($this->__('Please select product(s)'));
		} else {
			try {
				foreach ($productIds as $productId) {
					//look at https://gist.github.com/vbuck/5911170
					// and http://stackoverflow.com/a/17429681/746758
					// about unlinking the values
					$product = Mage::getSingleton('catalog/product')
						->unsetData()
						->setStoreId($storeId)
						->load($productId)
						->setAttributeSetId($this->getRequest()->getParam('attribute_set'))
						->setIsMassupdate(true)
						->save();
				}
				Mage::dispatchEvent('catalog_product_massupdate_after', array('products'=>$productIds));
				$this->_getSession()->addSuccess(
					$this->__('Total of %d record(s) were successfully updated', count($productIds))
				);
			} catch (Exception $e) {
				$this->_getSession()->addException($e, $e->getMessage());
			}
		}
		$this->_redirect('adminhtml/catalog_product/index/', array());
	}	
}
