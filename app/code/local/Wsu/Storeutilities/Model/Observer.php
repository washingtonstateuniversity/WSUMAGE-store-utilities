<?php
/**
 *
 * @category    Wsu
 * @package     Wsu_Storeutilities
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Storeutilities_Model_Observer{

    const SALES_ORDER_GRID_NAME = 'sales_order_grid';
    
    public function addOptionToSelect($observer){
        if (self::SALES_ORDER_GRID_NAME == $observer->getEvent()->getBlock()->getId()) {
            $massBlock = $observer->getEvent()->getBlock()->getMassactionBlock();
            if ($massBlock) {
                $massBlock->addItem('wsu_delete_orders', array(
                    'label'=> Mage::helper('core')->__('Delete'),
                    'url'  => Mage::getUrl('wsu_delete_orders', array('_secure'=>true)),
                    'confirm' => Mage::helper('core')->__('Are you sure to delete the selected orders?'),
                ));
            }
        }
    }
    
    public function deleteOrderFromGrid($observer) {
        // This is actually not needed for databases with working foreign keys but some databases are corrupt :(
        $order = $observer->getOrder();
        if ($order->getId()) {
            $coreResource = Mage::getSingleton('core/resource');
            $writeConnection = $coreResource->getConnection('core_write');
            $salesOrderGridTable = $coreResource->getTableName('sales_flat_order_grid');
            $query = sprintf('Delete from %s where entity_id = %s', $salesOrderGridTable, (int)$order->getId());
            $writeConnection->raw_query($query);
        }
    }

    public function setStatusUnCancel($observer) {
        $order = $observer->getOrder();
		$state = Mage::helper('storeutilities')->getConfig('storeutilities_conf/orders/uncancelstate',Mage_Sales_Model_Order::STATE_COMPLETE);
		if($order->getId()){

		}
    }
    public function addStatusUnCancelOptionToSelect($observer){
        if (self::SALES_ORDER_GRID_NAME == $observer->getEvent()->getBlock()->getId()) {
            $massBlock = $observer->getEvent()->getBlock()->getMassactionBlock();
			$state = Mage::helper('storeutilities')->getConfig('storeutilities_conf/orders/uncancelstate',Mage_Sales_Model_Order::STATE_COMPLETE);
            if ($massBlock) {
                $massBlock->addItem('wsu_uncancel_orders', array(
                    'label'=> Mage::helper('core')->__('Un-Cancel'),
                    'url'  => Mage::getUrl('wsu_uncancel_orders', array('_secure'=>true)),
                    'confirm' => Mage::helper('core')->__('Are you sure to set the status from CANCELED to '.$state.' the selected orders?'),
                ));
            }
        }
    }




	public function alter_output($observer){
		if(	Mage::getStoreConfig('storeutilities_conf/dev/show_block_type_fe') && !Mage::app()->getStore()->isAdmin()
			|| Mage::getStoreConfig('storeutilities_conf/dev/show_block_type_admin') && Mage::app()->getStore()->isAdmin()
		){
			$_block = $observer->getBlock();
			$_type = $_block->getType();
			var_dump($_type);//@todo change this out to append to the html at this point.
		}
	}

	public function cleanConfigCache(){
		Mage::helper('storeutilities')->cleanConfigCache();
	}	

	public function admin_simpleHtmlMinify($observer){
		if(Mage::getStoreConfig('storeutilities_conf/html/minify_admin_html_output')){
			$response = $observer->getResponse(); 
			$html     = $response->getBody(); 
			$html = $this->quicktmlMinify($html);
			$response->setBody($html); 
		}
	}
    public function simpleHtmlMinify($observer) {
        if (Mage::getStoreConfig('storeutilities_conf/html/minify_html_output')) {
            // Fetches the current event
            $event = $observer->getEvent();
            $controller = $event->getControllerAction();
            $allHtml = $controller->getResponse()->getBody();

			$content = $this->quicktmlMinify($allHtml);
            $controller->getResponse()->setBody($content);
        }
    }
	
	//move this
	public function quicktmlMinify($text) {
		$re = '%# Collapse whitespace everywhere but in blacklisted elements.
			(?>             # Match all whitespans other than single space.
			  [^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
			| \s{2,}        # or two or more consecutive-any-whitespace.
			) # Note: The remaining regex consumes no text at all...
			(?=             # Ensure we are not in a blacklist tag.
			  [^<]*+        # Either zero or more non-"<" {normal*}
			  (?:           # Begin {(special normal*)*} construct
				<           # or a < starting a non-blacklist tag.
				(?!/?(?:textarea|pre|script)\b)
				[^<]*+      # more non-"<" {normal*}
			  )*+           # Finish "unrolling-the-loop"
			  (?:           # Begin alternation group.
				<           # Either a blacklist start tag.
				(?>textarea|pre|script)\b
			  | \z          # or end of file.
			  )             # End alternation group.
			)  # If we made it here, we are not in a blacklist tag.
			%Six';
		$text = preg_replace($re, " ", $text);
		return $text;
    }
	
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
	
	
	//mass attribute
	public function addMassactionToProductGrid($observer){
		$block = $observer->getBlock();
		if($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid){
			 if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')){
				$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
					->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
					->load()
					->toOptionHash();
			
				$block->getMassactionBlock()->addItem('wsu_storeutilities_changeattributeset', array(
					'label'=> Mage::helper('catalog')->__('Change attribute set'),
					'url'  => $block->getUrl('*/*/changeattributeset', array('_current'=>true)),
					'additional' => array(
						'visibility' => array(
							'name' => 'attribute_set',
							'type' => 'select',
							'class' => 'required-entry',
							'label' => Mage::helper('catalog')->__('Attribute Set'),
							'values' => $sets
						)
					)
				));
				$block->getMassactionBlock()->addItem('wsu_storeutilities_cleanattributes', array(
					'label'=> Mage::helper('catalog')->__('Clean attributes'),
					'url'  => $block->getUrl('*/*/cleanattributes', array('_current'=>true))
				));
	        }		
			
			if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')){
				$block->getMassactionBlock()->addItem('wsu_storeutilities_startchangecategories', array(
					'label'=> Mage::helper('catalog')->__('Modify Categories'),
					'url'  => $block->getUrl('*/*/startchangecategories', array('_current'=>true))
				));
			}			
	
		}
	}
	
	

	
	
}
