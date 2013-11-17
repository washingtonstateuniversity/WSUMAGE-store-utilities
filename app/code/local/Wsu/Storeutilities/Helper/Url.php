<?php
class Wsu_Storeutilities_Helper_Url extends Mage_Checkout_Helper_Url {
    /**
     * Retrieve shopping cart url
     *
     * @return string
     */
    public function getUrl($arg = 'checkout/cart') {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::app()->getStore($storeId)->getUrl($arg);
        return $this->_getUrl($arg);
    }
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
    /**
     * Retrieve checkout url
     *
     * @return string
     */
    public function getCheckoutUrl() {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::app()->getStore($storeId)->getUrl('checkout/onepage');
        return $this->_getUrl('checkout/onepage');
    }
    /**
     * Multi Shipping (MS) checkout urls
     */
    /**
     * Retrieve multishipping checkout url
     *
     * @return string
     */
    public function getMSCheckoutUrl() {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::getModel('core/store')->load($storeId)->getUrl('checkout/multishipping');
        return $this->_getUrl('checkout/multishipping');
    }
    public function getMSLoginUrl() {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::getModel('core/store')->load($storeId)->getUrl('checkout/multishipping/login', array(
            '_secure' => true,
            '_current' => true
        ));
        return $this->_getUrl('checkout/multishipping/login', array(
            '_secure' => true,
            '_current' => true
        ));
    }
    public function getMSAddressesUrl() {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::getModel('core/store')->load($storeId)->getUrl('checkout/multishipping/addresses');
        return $this->_getUrl('checkout/multishipping/addresses');
    }
    public function getMSShippingAddressSavedUrl() {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::getModel('core/store')->load($storeId)->getUrl('checkout/multishipping_address/shippingSaved');
        return $this->_getUrl('checkout/multishipping_address/shippingSaved');
    }
    public function getMSRegisterUrl() {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::getModel('core/store')->load($storeId)->getUrl('checkout/multishipping/register');
        return $this->_getUrl('checkout/multishipping/register');
    }
    /**
     * One Page (OP) checkout urls
     */
    public function getOPCheckoutUrl() {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::getModel('core/store')->load($storeId)->getUrl('checkout/onepage');
        return $this->_getUrl('checkout/onepage');
    }
    /**
     * Url to Registration Page
     *
     * @return string
     */
    public function getRegistrationUrl() {
        $storeId = 1; //pick up from setting.. look to latter but hard code now
        return Mage::getModel('core/store')->load($storeId)->getUrl('checkout/account/create');
        return $this->_getUrl('customer/account/create');
    }
}
