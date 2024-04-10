<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Coupon
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerCoupon\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magetop\SellerCoupon\Model\SellerCouponFactory;
use Magento\Customer\Model\Session;

class Save extends \Magento\Framework\App\Action\Action
{
	protected $_customerFactory;
	protected $_mkHelperMail;
	protected $_SellerCouponFactory;
    protected $_customerSession;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
		SellerCouponFactory $SellerCouponFactory,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
		$this->_SellerCouponFactory = $SellerCouponFactory;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		try{
            $isseller = $this->_objectManager->get('Magetop\Marketplace\Helper\Data')->checkIsSeller();
            if($isseller){	
                $customerSession = $this->_customerSession;
                if($customerSession->isLoggedIn()){
            		$post = $this->getRequest()->getPostValue();
                    $model = $this->_objectManager->create('Magetop\SellerCoupon\Model\SellerCoupon');
                    if(@$post['coupon_id']){
                        $model->load($post['coupon_id']);
                        if($model->getSellerId() == $this->_customerSession->getId()){
                            /*$model->setData('seller_coupon_code', $post['seller_coupon_code']);*/
                            $model->setData('seller_coupon_type', $post['seller_coupon_type']);
                            $model->setData('seller_coupon_price', $post['seller_coupon_price']);
                            $model->setData('seller_id', $this->_customerSession->getId());
                            $model->setData('created_at', $post['created_at']);
                            $model->setData('expire_date', $post['expire_date']);
                            $model->setData('status', $post['status']);
                            $model->save();
                            $msg = __('You saved coupon successfully.');
                            $this->messageManager->addSuccess( $msg );
                        }
                    }else{
                        $oldModel = $this->_objectManager->create('Magetop\SellerCoupon\Model\SellerCoupon')->getCollection()->addFieldToFilter('seller_coupon_code',$post['seller_coupon_code'])->getFirstItem();
                        if($oldModel['seller_coupon_code']){
                            $msg = __('Can\'t save, the coupon code already exist');
                            $this->messageManager->addError( $msg );
                        }else{
                            $model->setData('seller_coupon_code', $post['seller_coupon_code']);
                            $model->setData('seller_coupon_type', $post['seller_coupon_type']);
                            $model->setData('seller_coupon_price', $post['seller_coupon_price']);
                            $model->setData('seller_id', $this->_customerSession->getId());
                            $model->setData('created_at', $post['created_at']);
                            $model->setData('expire_date', $post['expire_date']);
                            $model->setData('status', $post['status']);
                            $model->save();
                            $msg = __('You saved coupon successfully.');
                            $this->messageManager->addSuccess( $msg );
                        }
                    }
                }
            }
            $this->_redirect( 'sellercoupon' );            
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());                                 
			$this->_redirect( 'sellercoupon' );
		}	 
    }
}