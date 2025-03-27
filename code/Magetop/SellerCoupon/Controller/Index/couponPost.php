<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Coupon
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerCoupon\Controller\Index;

class CouponPost extends \Magento\Checkout\Controller\Cart
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Coupon factory
     *
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->couponFactory = $couponFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        try{
    		$post = $this->getRequest()->getParams();
            $model = $this->_objectManager->create('Magetop\SellerCoupon\Model\SellerCoupon')
                                          ->getCollection()
                                          ->addFieldToFilter('seller_coupon_code',$post['coupon_code'])
                                          ->addFieldToFilter('seller_id', array('in' => $post['seller_id']))
                                          ->addFieldToFilter('used_status',0)
                                          ->getFirstItem();
            $seller_coupon_price = array();
            if($this->_checkoutSession->getData('seller_coupon_price')){
                foreach($this->_checkoutSession->getData('seller_coupon_price') as $key => $val){
                    $seller_coupon_price[$key] = $val;
                }
            }
            //Apply coupon code for seller
            if($model->getSellerCouponPrice()){
                $price_total_seller = 0;
                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $cartData = $om->create('Magento\Checkout\Model\Cart')->getQuote()->getAllVisibleItems();
                foreach( $cartData as $item ){
                    $mkProductData = $om->create('\Magetop\Marketplace\Model\Products')->getCollection()
                                        ->addFieldToFilter('product_id',$item->getProductId())
                                        ->addFieldToFilter('status',1);
                    $tableMKuser = $om->create('\Magento\Framework\App\ResourceConnection')->getTableName('multivendor_user');
                    $mkProductData->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())
                                  ->where('mk_user.userstatus = 1')
                                  ->where('mk_user.user_id = '.$model->getSellerId());
                        
                    $mkProductData = $mkProductData->getFirstItem();
                    if($mkProductData && $mkProductData->getId())
                    {
                        $price_total_seller += $item->getBasePrice()*$item->getQty();
                    }
                }
                if($model->getSellerCouponType() == 2){
                    $price_discount = $price_total_seller*($model->getSellerCouponPrice()/100);
                }else{
                    $price_discount = $model->getSellerCouponPrice();
                }
                
                $seller_coupon_price[$model->getSellerId()] = array($model->getSellerCouponCode(),$price_discount);
                $this->_checkoutSession->setData('seller_coupon_price', $seller_coupon_price);
                $cartQuote = $this->cart->getQuote();
                $itemsCount = $cartQuote->getItemsCount();
                if ($itemsCount) {
                    $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                    $cartQuote->setCouponCode($cartQuote->getCouponCode())->collectTotals();
                    $this->quoteRepository->save($cartQuote);
                }
                $msg = __('You used coupon code "'.$post['coupon_code'].'".');
                $this->messageManager->addSuccess( $msg );
            //Apply coupon code for admin
            }else{
                $this->_checkoutSession->setData('seller_coupon_price', $seller_coupon_price);
                $couponCode = $this->getRequest()->getParam('remove') == 1
                    ? ''
                    : trim($this->getRequest()->getParam('coupon_code'));
        
                $cartQuote = $this->cart->getQuote();
                $oldCouponCode = $cartQuote->getCouponCode();
        
                $codeLength = strlen($couponCode);
                if (!$codeLength && !strlen($oldCouponCode)) {
                    return $this->_goBack();
                }
        
                try {
                    $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;
        
                    $itemsCount = $cartQuote->getItemsCount();
                    if ($itemsCount) {
                        $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                        $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                        $this->quoteRepository->save($cartQuote);
                    }
        
                    if ($codeLength) {
                        $escaper = $this->_objectManager->get('Magento\Framework\Escaper');
                        if (!$itemsCount) {
                            if ($isCodeLengthValid) {
                                $coupon = $this->couponFactory->create();
                                $coupon->load($couponCode, 'code');
                                if ($coupon->getId()) {
                                    $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                                    $this->messageManager->addSuccess(
                                        __(
                                            'You used coupon code "%1".',
                                            $escaper->escapeHtml($couponCode)
                                        )
                                    );
                                } else {
                                    $this->messageManager->addError(
                                        __(
                                            'The coupon code "%1" is not valid.',
                                            $escaper->escapeHtml($couponCode)
                                        )
                                    );
                                }
                            } else {
                                $this->messageManager->addError(
                                    __(
                                        'The coupon code "%1" is not valid.',
                                        $escaper->escapeHtml($couponCode)
                                    )
                                );
                            }
                        } else {
                            if ($isCodeLengthValid && $couponCode == $cartQuote->getCouponCode()) {
                                $this->messageManager->addSuccess(
                                    __(
                                        'You used coupon code "%1".',
                                        $escaper->escapeHtml($couponCode)
                                    )
                                );
                            } else {
                                $this->messageManager->addError(
                                    __(
                                        'The coupon code "%1" is not valid.',
                                        $escaper->escapeHtml($couponCode)
                                    )
                                );
                                $this->cart->save();
                            }
                        }
                    } else {
                        $this->messageManager->addSuccess(__('You canceled coupon code "'.$post['coupon_code'].'".'));
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('We cannot apply the coupon code.'));
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                }
            }        
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());                                 
		}

        return $this->_goBack();
    }
}
