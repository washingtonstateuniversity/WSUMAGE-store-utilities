<?php
class Wsu_Storeutilities_Adminhtml_Wsu_StoreutilitiesController extends Mage_Adminhtml_Controller_Action {
    public function indexAction() {
        $this->loadLayout()->_setActiveMenu('sales/wsu_storeutilities');
        $this->_addContent($this->getLayout()->createBlock('wsu_storeutilities/adminhtml_edit'));
        $this->renderLayout();
    }
    public function gridAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('wsu_storeutilities/adminhtml_edit_grid')->toHtml());
    }
    public function loginAction() {
        $info = Mage::helper('core')->encrypt(serialize(array(
            'website_id' => $this->getRequest()->getParam('website_id'),
            'customer_id' => $this->getRequest()->getParam('customer_id'),
            'timestamp' => time()
        )));
        $this->_redirectUrl(Mage::app()->getWebsite($this->getRequest()->getParam('website_id'))->getConfig('web/unsecure/base_url') . 'index.php/wsu_storeutilities/customer/login?loginAsCustomer=' . base64_encode($info));
    }
}
