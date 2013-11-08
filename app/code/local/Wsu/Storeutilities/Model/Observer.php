<?php
/**
 *
 * @category    Wsu
 * @package     Wsu_Storeutilities
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Storeutilities_Model_Observer{
    public function injectStoreutilitiesButton($observer) {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Customer_Edit) {
            if ($this->getCustomer() && $this->getCustomer()->getId()) {
                $block->addButton('loginAsCustomer', array(
                    'label' => Mage::helper('customer')->__('Login as Customer'),
                    'onclick' => 'setLocation(\'' . $this->getCustomerLoginUrl() . '\')',
                    'class' => 'loginAsCustomer'
                ), 0);
            }
        }
    }
    public function getCustomer() {
        return Mage::registry('current_customer');
    }
    public function getCustomerLoginUrl() {
        /*
        If option "System > Configuration > Customers > Customer Configuration > Account Sharing Options > Share Customer Accounts"
        is set to "Per Website" value. What this means is that this account is tied to single website.
        */
        if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) {
            return Mage::helper('adminhtml')->getUrl('*/wsu_storeutilities/login', array(
                'customer_id' => $this->getCustomer()->getId(),
                'website_id' => $this->getCustomer()->getWebsiteId()
            ));
        }
        /* else, this means we have "Global", so customer can login to any website, so we show him the list of websites */
        return Mage::helper('adminhtml')->getUrl('*/wsu_storeutilities/index', array(
            'customer_id' => $this->getCustomer()->getId()
        ));
    }
}
