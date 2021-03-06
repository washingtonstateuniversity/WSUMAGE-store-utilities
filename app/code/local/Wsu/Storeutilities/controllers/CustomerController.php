<?php
class Wsu_Storeutilities_CustomerController extends Mage_Core_Controller_Front_Action {
    public function loginAction() {
        /* parse the 'loginAsCustomer' param */
        $info = unserialize(Mage::helper('core')->decrypt( 
		/* important step; use Magento encryption key to decrypt/extract info */
		base64_decode($this->getRequest()->getParam('loginAsCustomer'))));
        /* Check to be sure that all 'website_id' & 'customer_id' & 'timestamp' info is passed */
        if (isset($info['website_id']) && isset($info['customer_id']) && isset($info['timestamp']) && (time() < ($info['timestamp'] + 5))) {
			$customer_id=$info['customer_id'];
			$admin_id = Mage::getSingleton('admin/session')->getUserId();
            /* 5 second validity for request */
            $customerSession = Mage::getSingleton('customer/session');
            /* Share Customer Accounts is set to "Per Website" */
            if (Mage::getSingleton('customer/config_share')->isWebsiteScope()) { // @todo re look at this it should be if you have permission under that store not only if the customer exists under that store
                if (Mage::app()->getWebsite()->getId() != $info['website_id']) {
                    Mage::getSingleton('customer/session')->addNotice($this->__('<i>Share Customer Accounts</i> option is set to <i>Per Website</i>. You are trying to login as customer from website %d into website %s. This action is not allowed.', $info['website_id'], Mage::app()->getWebsite()->getId()));
                    $this->_redirect('customer/account');
                    return;
                }
            }
            /* Logout any currently logged in customer */
            if ($customerSession->isLoggedIn()) {
                $customerSession->logout();
                $this->_redirectUrl($this->getRequest()->getRequestUri());
				Mage::log("Customer account with id:$customer_id was log out by admin user with id:$admin_id", Zend_Log::INFO);
                return;
            }
            /* Login new customer as requested on the admin interface */
            $customerSession->loginById($info['customer_id']);


					
			Mage::log("Customer account with id:$customer_id was loging by admin user with id:$admin_id", Zend_Log::INFO);
        }
        $this->_redirect('customer/account');
    }
}
