<?php
require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';

class Wsu_Storeutilities_IndexController extends Mage_Adminhtml_Sales_OrderController {
    public function indexAction() {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $deletedOrders = 0;
        if ($orderIds) {
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                $transactionContainer = Mage::getModel('core/resource_transaction');
                if ($order->getId()) {
                    $deletedOrders++;
                    // add invoices to delete
                    if ($order->hasInvoices()){
                      $invoices = Mage::getResourceModel('sales/order_invoice_collection')->setOrderFilter($orderId)->load();
                      if ($invoices) {
                          foreach ($invoices as $invoice){
                              $invoice = Mage::getModel('sales/order_invoice')->load($invoice->getId());
                              $transactionContainer->addObject($invoice);
                          }
                      }
                   }
                   
                   // add shipments to delete
                   if ($order->hasShipments()){
                       $shipments = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($orderId)->load();
                       foreach ($shipments as $shipment){
                           $shipment = Mage::getModel('sales/order_shipment')->load($shipment->getId());
                           $transactionContainer->addObject($shipment);
                       }
                   }
                   //delete
                   $transactionContainer->addObject($order)->delete();
                }
            }
        }
        
        if ($deletedOrders) {
            $this->_getSession()->addSuccess($this->__('%s order(s) was/were successfully deleted.', $deletedOrders));
        }
        $this->_redirect('adminhtml/sales_order/', array());
    }
}
