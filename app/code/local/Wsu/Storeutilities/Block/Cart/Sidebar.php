<?php
/**
 * Wishlist sidebar block
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Wsu_Storeutilities_Block_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar//Mage_Checkout_Block_Cart_Abstract
{
	/**
     * Retrieve shopping cart url
     *
     * @return string
     */
    public function getMainStoreUrl($arg='checkout/cart'){
		$storeId = 1;//pick up from setting.. look to latter but hard code now
		return Mage::app()->getStore($storeId)->getUrl($arg);
        return $this->_getUrl($arg);
    }

	/**
     * Retrieve shopping cart url
     *
     * @return string
    
    public function getUrl($arg='checkout/cart'){
		$storeId = 1;//pick up from setting.. look to latter but hard code now
		return Mage::app()->getStore($storeId)->getUrl($arg);
        return $this->_getUrl($arg);
    }
	 */

}
