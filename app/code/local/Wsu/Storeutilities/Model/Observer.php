<?php
/**
 *
 * @category    Wsu
 * @package     Wsu_Storeutilities
 * @author      jeremybass <jeremy.bass@wsu.edu>
 */
class Wsu_Storeutilities_Model_Observer{


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
		}
	}
	
	

	
	
}
