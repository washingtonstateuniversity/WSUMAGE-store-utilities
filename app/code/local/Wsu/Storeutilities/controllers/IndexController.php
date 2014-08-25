<?php
require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';

class Wsu_Storeutilities_IndexController extends Mage_Adminhtml_Sales_OrderController {
    public function indexAction() {
        
		if(strpos(Mage::helper('core/url')->getCurrentUrl(),'wsu_delete_orders')!==false){
		
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
		}elseif(strpos(Mage::helper('core/url')->getCurrentUrl(),'wsu_uncancel_orders')!==false){
			$orderIds = $this->getRequest()->getPost('order_ids', array());
			$uncancelOrders = 0;
			if ($orderIds) {
				foreach ($orderIds as $orderId) {
					$order = Mage::getModel('sales/order')->load($orderId);
					if ($order->getId()) {
						$uncancelOrders++;
						$order->setData('state',Mage_Sales_Model_Order::STATE_PROCESSING)
								->setData('status',Mage_Sales_Model_Order::STATE_PROCESSING)
								->setData('base_discount_canceled',0)
								->setData('base_shipping_canceled',0)
								->setData('base_subtotal_canceled',0)
								->setData('base_tax_canceled',0)
								->setData('base_total_canceled',0)
								->setData('discount_canceled',0)
								->setData('shipping_canceled',0)
								->setData('subtotal_canceled',0)
								->setData('tax_canceled',0)
								->setData('total_canceled',0);
						$_items = $order->getAllItems();
						if(!empty($_items)){
							foreach ($_items as $item) {
								$item->setData('qty_canceled',0);
								$item->save();
							}
						}
						$order->save();
					}
				}
			}
			
			if ($uncancelOrders) {
				$this->_getSession()->addSuccess($this->__('%s order(s) was/were successfully deleted.', $uncancelOrders));
			}
			
		}
		
		
		
		
        $this->_redirect('adminhtml/sales_order/', array());
    }
	public function uncancelAction() {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $uncancelOrders = 0;
        if ($orderIds) {
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order->getId()) {
                    $uncancelOrders++;
					$order->setData('state','pending')
							->setData('status','pending')
							->setData('base_discount_canceled',0)
							->setData('base_shipping_canceled',0)
							->setData('base_subtotal_canceled',0)
							->setData('base_tax_canceled',0)
							->setData('base_total_canceled',0)
							->setData('discount_canceled',0)
							->setData('shipping_canceled',0)
							->setData('subtotal_canceled',0)
							->setData('tax_canceled',0)
							->setData('total_canceled',0);
					$_items = $order->getAllItems();
					if(!empty($_items)){
						foreach ($_items as $item) {
							$item->setData('qty_canceled',0);
						}
						$_items->save();
					}
					$order->save();
                }
            }
        }
        
        if ($uncancelOrders) {
            $this->_getSession()->addSuccess($this->__('%s order(s) was/were successfully deleted.', $uncancelOrders));
        }
        $this->_redirect('adminhtml/sales_order/', array());
    }
}
