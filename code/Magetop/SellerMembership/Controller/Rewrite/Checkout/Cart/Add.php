<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Controller\Rewrite\Checkout\Cart;
use Magento\Framework\Filter\LocalizedToNormalized;
class Add extends \Magento\Checkout\Controller\Cart\Add
{
   /**
	 * Add product to shopping cart action
	 *
	 * @return \Magento\Framework\Controller\Result\Redirect
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
    public function getCheckoutSession(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();//instance of object manager 
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');//checkout session
        return $checkoutSession;
    }
    public function getItemModel(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();//instance of object manager
        $itemModel = $objectManager->create('Magento\Quote\Model\Quote\Item');//Quote item model to load quote item
        return $itemModel;
    }
	public function execute()
	{
		//if (!$this->_formKeyValidator->validate($this->getRequest())) {
			//return $this->resultRedirectFactory->create()->setPath('*/*/');
		//}
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $version = $objectManager->get('Magetop\Marketplace\Helper\Data')->getMagentoVersion();
		$params = $this->getRequest()->getParams();
		try {
			if (isset($params['qty'])) {
				$filter = new LocalizedToNormalized(
					['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
				);
				$params['qty'] = $filter->filter($params['qty']);
			}

			$product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            $moduleManager = $objectManager->create('Magento\Framework\Module\Manager');
            //Membership 
            /*if($moduleManager->isEnabled('Magetop_SellerMembership')){
                if($product->getTypeId() == 'membership'){
                    $checkoutSession = $this->getCheckoutSession();
                    $allItems = $checkoutSession->getQuote()->getAllVisibleItems();//returns all the items in session
                    foreach ($allItems as $item) {
                        $itemId = $item->getItemId();//item id of particular item
                        $quoteItem = $this->getItemModel()->load($itemId);//load particular item which you want to delete by his item id
                        $quoteItem->delete();//deletes the item
                    }
                }else{
                    $checkoutSession = $this->getCheckoutSession();
                    $allItems = $checkoutSession->getQuote()->getAllVisibleItems();//returns all the items in session
                    foreach ($allItems as $item) {
                        if($item->getProductType() == 'membership'){
                            $itemId = $item->getItemId();//item id of particular item
                            $quoteItem = $this->getItemModel()->load($itemId);//load particular item which you want to delete by his item id
                            $quoteItem->delete();//deletes the item
                        }
                    }
                }
            }*/
            //End membership 

			/**
			 * Check product availability
			 */
			if (!$product) {
				return $this->goBack();
			}
			
            $assignproduct_id = @$params['assignproduct_id'];
            if($assignproduct_id){
                $sellerAssignProduct = $this->_objectManager->create('Magetop\SellerAssignProduct\Model\SellerAssignProduct')->load($assignproduct_id);
                $sellerDetail        = $this->_objectManager->create('Magetop\Marketplace\Model\Sellers')->getCollection()->addFieldToFilter('user_id',$sellerAssignProduct->getSellerId())->getFirstItem();
				$additionalOptions[] = array(
					'label' => 'Seller',
					'value' => $sellerDetail['storetitle'],
                    'price' => $sellerAssignProduct->getPrice()
				);      
                if(version_compare($version, '2.2.0') >= 0){
                    $product->addCustomOption('additional_options', $objectManager->get('Magento\Framework\Serialize\Serializer\Json')->serialize($additionalOptions));
                }else{
                    $product->addCustomOption('additional_options', serialize($additionalOptions));
                }
            }else{
                $mkProductCollection = $objectManager->create('Magetop\Marketplace\Model\Products')->getCollection()
                                        ->addFieldToFilter('product_id',$params['product'])
                                        ->addFieldToFilter('status',1);
                //Kien 19/5/2020 - update filter seller approve        
                $tableMKuser = $objectManager->create('Magento\Framework\App\ResourceConnection')->getTableName('multivendor_user');
                $mkProductCollection->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())
                    ->where('mk_user.userstatus = 1'); 
                $sellerId = 0;
                if(count($mkProductCollection)){
					foreach($mkProductCollection as $mkProductCollect){
						$sellerId = $mkProductCollect->getUserId();                       
						break;
					}
				}
                if($sellerId != 0){
                    $productDetail       = $this->_objectManager->create('Magetop\Marketplace\Model\Products')->getCollection()->addFieldToFilter('product_id',$params['product'])->getFirstItem();
                    $sellerDetail        = $this->_objectManager->create('Magetop\Marketplace\Model\Sellers')->getCollection()->addFieldToFilter('user_id',$productDetail->getUserId())->getFirstItem();
        			//kien fix 5/10/2020
                    if($sellerDetail['storeurl']){
						$additionalOptions[] = array(
    						'label' => 'Seller',
    						'value' => $sellerDetail['storetitle']
    					);            
            			if(version_compare($version, '2.2.0') >= 0){
                            $product->addCustomOption('additional_options', $objectManager->get('Magento\Framework\Serialize\Serializer\Json')->serialize($additionalOptions));
                        }else{
                            $product->addCustomOption('additional_options', serialize($additionalOptions));
                        }
                    }
                }
            }
            
			$this->cart->addProduct($product, $params);
			if (!empty($related)) {
				$this->cart->addProductsByIds(explode(',', $related??''));
			}

			$this->cart->save();

			/**
			 * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
			 */
			$this->_eventManager->dispatch(
				'checkout_cart_add_product_complete',
				['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
			);

			if (!$this->_checkoutSession->getNoCartRedirect(true)) {
				if (!$this->cart->getQuote()->getHasError()) {
					$message = __(
						'You added %1 to your shopping cart.',
						$product->getName()
					);
					$this->messageManager->addSuccessMessage($message);
				}
				//Membership
				if($moduleManager->isEnabled('Magetop_SellerMembership') && $objectManager->create('Magetop\Marketplace\Helper\Data')->getSellerMembershipIsEnabled() && ($product->getTypeId() == 'membership')){
        			return $this->_redirect('checkout/cart');
                }else{
                    return $this->goBack(null, $product);
                }
                //End membership
			}
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
			if ($this->_checkoutSession->getUseNotice(true)) {
				$this->messageManager->addNotice(
					$this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
				);
			} else {
				$messages = array_unique(explode("\n", $e->getMessage()??''));
				foreach ($messages as $message) {
					$this->messageManager->addError(
						$this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
					);
				}
			}

			$url = $this->_checkoutSession->getRedirectUrl(true);

			if (!$url) {
				$cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
				$url = $this->_redirect->getRedirectUrl($cartUrl);
			}

			return $this->goBack($url);

		} catch (\Exception $e) {
			$this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
			$this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
			return $this->goBack();
		}
	}
}