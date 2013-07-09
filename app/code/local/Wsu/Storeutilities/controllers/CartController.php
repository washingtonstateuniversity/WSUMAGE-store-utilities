	<?php
	require_once 'Mage/Checkout/controllers/CartController.php';
	class Wsu_Storeutilities_CartController extends Mage_Checkout_CartController
	{
		/**
		 * Add product to shopping cart action
		 */
		public function addAction()
		{
			$cart   = $this->_getCart();
			$params = $this->getRequest()->getParams();

			try {
				if (isset($params['qty'])) {
					$filter = new Zend_Filter_LocalizedToNormalized(
						array('locale' => Mage::app()->getLocale()->getLocaleCode())
					);
					$params['qty'] = $filter->filter($params['qty']);
				}
	 
				$product = $this->_initProduct();
				$related = $this->getRequest()->getParam('related_product');
	 
				/**
				 * Check product availability
				 */
				if (!$product) {
					$this->_goBack();
					return;
				}
	 
				$cart->addProduct($product, $params);
				if (!empty($related)) {
					$cart->addProductsByIds(explode(',', $related));
				}
	 
				$cart->save();
	 
				$this->_getSession()->setCartWasUpdated(true);
	 
				/**
				 * @todo remove wishlist observer processAddToCart
				 */
				Mage::dispatchEvent('checkout_cart_add_product_complete',
					array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
				);
	 
				if (!$this->_getSession()->getNoCartRedirect(true)) {
					if (!$cart->getQuote()->getHasError()){
						$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
						$this->_getSession()->addSuccess($message);
					}
					



					if ($returnUrl = $this->getRequest()->getParam('return_url')) {
						// clear layout messages in case of external url redirect
						if ($this->_isUrlInternal($returnUrl)) {
							$this->_getSession()->getMessages(true);
						}
						$this->getResponse()->setRedirect($returnUrl);
					} elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
						&& !$this->getRequest()->getParam('in_cart')
						&& $backUrl = $this->_getRefererUrl()) {
	 
						$this->getResponse()->setRedirect($backUrl);
					} else {
						if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
							$this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
						}
	 					$store      = Mage::app()->getStore(1);
						$store_url  = $store->getUrl('checkout/cart');
						
						header('Location: '. $store_url);
						exit;
						
						
						if($this->getRequest()->getParam('noCheckoutYet')=="yes")
							$this->getResponse()->setRedirect($this->_getRefererUrl());
						else
							$this->_redirect('checkout/cart');
					}
				}
			}
			catch (Mage_Core_Exception $e) {
				if ($this->_getSession()->getUseNotice(true)) {
					$this->_getSession()->addNotice($e->getMessage());
				} else {
					$messages = array_unique(explode("\n", $e->getMessage()));
					foreach ($messages as $message) {
						$this->_getSession()->addError($message);
					}
				}

				$url = $this->_getSession()->getRedirectUrl(true);
				if ($url) {
					$this->getResponse()->setRedirect($url);
				} else {
					$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
				}
			}
			catch (Exception $e) {
				$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
				$this->_goBack();
			}
		}
	
		
		
		
		
		
		
		
		
		
		
	}
