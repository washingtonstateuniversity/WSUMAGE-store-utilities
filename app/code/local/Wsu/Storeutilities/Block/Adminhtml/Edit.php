<?php
class Wsu_Storeutilities_Block_Adminhtml_Edit extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_blockGroup = 'wsu_storeutilities';
        $this->_controller = 'adminhtml_edit';
        $this->_headerText = Mage::helper('storeutilities')->__('Login as customer %s (choose a website to log into)', $this->getCustomer()->getEmail());
        if ($this->getCustomer()->getSharingConfig()->isWebsiteScope() == true) {
            //$this->_getSession()->addWarning(Mage::helper('wsu_loginAsCustomer')->__('Option "System > Configuration > Customers > Customer Configuration > Account Sharing Options > Share Customer Accounts" is set to "Per Website" value. What this means is that the grid below will only show a single website, for the customer you are currently looking at.'));
        }
        parent::__construct();
        $this->_removeButton('add');
    }
    public function getCustomer() {
        $customerId = $this->getRequest()->getParam('customer_id');
        $customer   = Mage::getModel('customer/customer')->load($customerId);
        return $customer;
    }
    protected function _getSession() {
        return Mage::getSingleton('adminhtml/session');
    }
}
