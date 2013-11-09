<?php
class Wsu_Storeutilities_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action {
    protected $_allAttributes = array();
    /**
     * Get or initialize an array of the default attributes for every product.
     * 
     * @return array The Require attribute codes for the produt's new attribute set.
     */
    protected function _getRequiredAttributes($attrSetId) {
        $_requiredAttributes = array();
        $attributes          = Mage::getModel('catalog/product_attribute_api')->items($attrSetId);
        foreach ($attributes as $_attribute) {
            $_requiredAttributes[] = $_attribute['code'];
        }
        return $_requiredAttributes;
    }
    protected function _getAllAttributes() {
        if (empty($this->_allAttributes)) {
            $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
            foreach ($attributes as $_attribute) {
                $attr['code']           = $_attribute['attribute_code'];
                $attribute              = Mage::getModel('eav/entity_attribute')->load($_attribute['attribute_id']);
                $attr['table']          = $attribute->getBackendTable();
                $attr['id']             = $_attribute['attribute_id'];
                $this->_allAttributes[] = $attr;
            }
        }
        return $this->_allAttributes;
    }
    /**
     * Attempt to remove any required attributes linked to the product that are not in the new attribute set.
     * 
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _cleanAttributes(Mage_Catalog_Model_Product $product) {
        $write          = $product->getResource()->getWriteConnection();
        $required       = $this->_getRequiredAttributes($product->getAttributeSetId());
        $all_attributes = $this->_getAllAttributes();
        foreach ($all_attributes as $attribute) {
            try {
                if (!in_array($attribute['code'], $required)) {
                    $write->delete($attribute['table'], join(' AND ', array(
                        $write->quoteInto('attribute_id=?', $attribute['id']),
                        $write->quoteInto('entity_id=?', $product->getId())
                    )));
                }
            }
            catch (Exception $e) {
                $this->_getSession()->addError("Failed to unlink attribute {$attribute->getAttributeId()} from product.");
            }
        }
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
}
