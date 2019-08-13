<?php
class WebspeaksBasket_Basket_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }

    public function getcartAction()
    {
		echo $this->getLayout()->createBlock('basket/basket')->setTemplate('basket/basket.phtml')->toHtml();  
    }

    public function showcartAction()
    {
		echo $this->getLayout()->createBlock('basket/basket')->setTemplate('basket/cart.phtml')->toHtml();  
    }

    public function removeitemAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
		$result = array();
        if ($id) {
            try {
				$cartHelper = Mage::helper('checkout/cart');
				$items = $cartHelper->getCart()->getItems();
				foreach ($items as $item) {
					if ($item->getProduct()->getId() == $id) {
						$itemId = $item->getItemId();
						$cartHelper->getCart()->removeItem($itemId)->save();
					}
				}
				$totItems = Mage::helper('checkout/cart')->getCart()->getSummaryQty();

				$result['status'] = 'success';
				$result['message'] = 'Item removed.';
				$result['cart_count'] = $totItems;
				$result['cart_total'] = Mage::helper('checkout')->formatPrice(Mage::helper('checkout/cart')->getQuote()->getGrandTotal());
            } catch (Exception $e) {
				$result['status'] = 'error';
				$result['message'] = 'Cannot remove the item.';
            }
        } else {
			$result['status'] = 'error';
			$result['message'] = 'Cannot remove the item.';
		}
		echo json_encode($result);
    }

    /**
     * Empty customer's shopping cart
     */
    public function emptyshoppingcartAction()
    {
		$result = array();
        try {
			$cartHelper = Mage::helper('checkout/cart');
            $cartHelper->getCart()->truncate()->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

			$result['status'] = 'success';
			$result['message'] = 'Cart cleared.';
			$result['cart_count'] = 0;
			$result['cart_total'] = Mage::helper('checkout')->formatPrice(Mage::helper('checkout/cart')->getQuote()->getGrandTotal());
        } catch (Mage_Core_Exception $exception) {
			$result['status'] = 'error';
			$result['message'] = $exception->getMessage();
        } catch (Exception $exception) {
			$result['status'] = 'error';
			$result['message'] = 'Cannot update shopping cart.';
        }
		echo json_encode($result);
    }

    /**
     * Update customer's shopping cart
     */
    public function updatecartAction()
    {
		$result = array();
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                $cart = Mage::getSingleton('checkout/cart');
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

			$totItems = Mage::helper('checkout/cart')->getCart()->getSummaryQty();
			$result['status'] = 'success';
			$result['message'] = 'Cart updated.';
			$result['cart_count'] = $totItems;
        } catch (Mage_Core_Exception $e) {
			$result['status'] = 'error';
			$result['message'] = Mage::helper('core')->escapeHtml($e->getMessage());
        } catch (Exception $e) {
			$result['status'] = 'error';
			$result['message'] = 'Cannot update shopping cart.';
        }
		echo json_encode($result);
    }

}