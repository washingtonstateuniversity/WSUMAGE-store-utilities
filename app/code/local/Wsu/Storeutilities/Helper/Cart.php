<?php
/**
 * Checkout url helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Wsu_Storeutilities_Helper_Cart extends Mage_Checkout_Helper_Cart {
    /**
     * Retrieve shopping cart url
     *
     * @return string
     */
    public function getCartUrl() {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::app()->getStore($storeId)->getUrl('checkout/cart');
        return $this->_getUrl('checkout/cart');
    }
}
