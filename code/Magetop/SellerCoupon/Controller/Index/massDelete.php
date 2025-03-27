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

class massDelete extends \Magento\Framework\App\Action\Action
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
			$post = $this->getRequest()->getPostValue();
            $deleteCoupon = 0;
            foreach($post['coupon_selected'] as $coupon_id){
                $model = $this->_objectManager->create('Magetop\SellerCoupon\Model\SellerCoupon');
                $model->load($coupon_id);
                if( $model->getSellerId() == $this->_customerSession->getId()){
                    $model->delete();
                    $deleteCoupon++;
                }
                
            }
            $msg = __('A total of %1 record(s) have been deleted.', $deleteCoupon);
            $this->messageManager->addSuccess( $msg );
            $this->_redirect( 'sellercoupon' );  
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());
			$this->_redirect( 'sellercoupon' );
		}	 
    }
}